<?php
/**
 * send-qr-blast.php — API endpoint: send QR code emails to selected registrations.
 *
 * POST params:
 *   event_id            (string) required
 *   registration_ids[]  (string[]) required — one or more registration IDs
 *
 * Returns JSON:
 *   { success: bool, sent: int, failed: int, errors: [{id, reason}] }
 */
session_start();
ob_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../include/config.php';
/** @var mysqli $conn */
require_once __DIR__ . '/../../include/permissions.php';
require_once __DIR__ . '/../role/organiser/email/qr-blast.php';

// ── Auth ──────────────────────────────────────────────────────────────────
if (empty($_SESSION['userId'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorised.']);
    exit;
}

// ── Input validation ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$eventId      = trim($_POST['event_id'] ?? '');
$regIds       = $_POST['registration_ids'] ?? [];
$instructions = trim($_POST['instructions'] ?? '');

if ($eventId === '' || empty($regIds) || !is_array($regIds)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing event_id or registration_ids.']);
    exit;
}

// Sanitise IDs
$regIds = array_values(array_unique(array_filter(array_map('trim', $regIds))));
if (empty($regIds)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No valid registration IDs supplied.']);
    exit;
}

// ── Optional agenda image upload ────────────────────────────────────────────
$agendaUrl = '';
if (!empty($_FILES['agenda']['tmp_name']) && $_FILES['agenda']['error'] === UPLOAD_ERR_OK) {
    $agFile   = $_FILES['agenda'];
    $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mime     = $finfo->file($agFile['tmp_name']);
    if (!in_array($mime, $allowed, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Agenda must be an image (JPEG, PNG, GIF, WEBP).']);
        exit;
    }
    if ($agFile['size'] > 10 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Agenda image must be under 10 MB.']);
        exit;
    }
    $uploadDir = __DIR__ . '/../../images/uploads/agenda/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $ext      = strtolower(pathinfo($agFile['name'], PATHINFO_EXTENSION) ?: 'jpg');
    $ext      = preg_replace('/[^a-z0-9]/', '', $ext);
    $filename = 'agenda_' . preg_replace('/[^a-zA-Z0-9_\-]/', '', $eventId) . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    if (move_uploaded_file($agFile['tmp_name'], $uploadDir . $filename)) {
        $agendaUrl = $uploadDir . $filename; // physical path — embedded as CID, no URL needed
    }
}

// ── Verify event exists & caller has access ───────────────────────────────
$evStmt = $conn->prepare("
    SELECT e.event_id, e.event_name, e.event_startDate, e.event_endDate,
           e.event_startTime, e.event_endTime, e.event_owner_id,
           l.location_name, l.location_buildingName, l.location_level, l.location_room,
           l.address_line1, l.address_line2, l.address_city, l.address_postcode,
           s.state_name
    FROM att_event e
    LEFT JOIN att_location l ON l.location_id = e.location_id
    LEFT JOIN att_state s    ON s.state_id    = l.state_id
    WHERE e.event_id = ? LIMIT 1");
if (!$evStmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error.']);
    exit;
}
$evStmt->bind_param("s", $eventId);
$evStmt->execute();
$evRes = $evStmt->get_result();
if (!$evRes || $evRes->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Event not found.']);
    exit;
}
$event = $evRes->fetch_assoc();
$evStmt->close();

// Only event organiser or admin may blast
$sessionRole = $_SESSION['roleId'] ?? null;
$sessionUser = $_SESSION['userId'] ?? '';
if ($sessionRole != 1 && $event['event_owner_id'] !== $sessionUser) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

// ── Build a placeholders string for the IN clause ─────────────────────────
$placeholders = implode(',', array_fill(0, count($regIds), '?'));
$types        = str_repeat('s', count($regIds));

$sql = "
    SELECT
        r.registration_id,
        COALESCE(NULLIF(u.email, ''),  NULLIF(r.walkin_email, ''))  AS email,
        COALESCE(NULLIF(u.nama, ''),   NULLIF(r.walkin_name, ''))   AS name,
        COALESCE(NULLIF(p.participant_phone, ''), NULLIF(r.walkin_phone, ''))   AS phone,
        COALESCE(NULLIF(p.participant_company, ''), NULLIF(r.walkin_company, '')) AS company
    FROM att_registration r
    LEFT JOIN user u ON u.userId = r.participant_id
    LEFT JOIN att_participant p ON p.participant_id = r.participant_id
    WHERE r.registration_id IN ({$placeholders})
      AND r.event_id = ?
";

$fetchStmt = $conn->prepare($sql);
if (!$fetchStmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error building query.']);
    exit;
}

// Bind: all registration IDs + event_id
$bindArgs = array_merge([$types . 's'], $regIds, [$eventId]);
$refs     = [];
foreach ($bindArgs as $k => $v) {
    $refs[$k] = &$bindArgs[$k];
}
call_user_func_array([$fetchStmt, 'bind_param'], $refs);

$fetchStmt->execute();
$fetchRes = $fetchStmt->get_result();

$rows = [];
while ($row = $fetchRes->fetch_assoc()) {
    $rows[] = $row;
}
$fetchStmt->close();

// ── Send emails ───────────────────────────────────────────────────────────
$sentCount  = 0;
$failCount  = 0;
$errors     = [];

foreach ($rows as $row) {
    $rid   = $row['registration_id'];
    $email = trim($row['email'] ?? '');
    $name  = trim($row['name']  ?? '');

    if ($email === '') {
        $failCount++;
        $errors[] = ['id' => $rid, 'reason' => 'No email address on file.'];
        continue;
    }

    // Build venue string from location fields
    $locParts = [];
    foreach (['location_name', 'location_buildingName'] as $col) {
        $val = trim((string)($event[$col] ?? ''));
        if ($val !== '') $locParts[] = $val;
    }
    $level = trim((string)($event['location_level'] ?? ''));
    $room  = trim((string)($event['location_room']  ?? ''));
    if ($level !== '') $locParts[] = 'Level ' . $level;
    if ($room  !== '') $locParts[] = 'Room '  . $room;
    $venueStr = implode(', ', $locParts);

    // Build address string
    $addrParts = [];
    foreach (['address_line1', 'address_line2', 'address_city', 'address_postcode', 'state_name'] as $col) {
        $val = trim((string)($event[$col] ?? ''));
        if ($val !== '') $addrParts[] = $val;
    }
    $addressStr = implode(', ', $addrParts);

    $result = sendQrBlastEmail(
        $email,
        $name,
        $rid,
        $event['event_id'],
        $event['event_name'],
        $event['event_startDate'],
        $event['event_endDate'],
        trim($row['phone']   ?? ''),
        trim($row['company'] ?? ''),
        $instructions,
        (string)($event['event_startTime'] ?? ''),
        (string)($event['event_endTime']   ?? ''),
        $venueStr,
        $addressStr,
        $agendaUrl  // physical file path for CID embedding
    );

    if ($result['sent']) {
        $sentCount++;
    } else {
        $failCount++;
        $errors[] = ['id' => $rid, 'reason' => $result['reason']];
    }
}

// Registrations requested but not found in DB (wrong event / invalid ID)
$foundIds = array_column($rows, 'registration_id');
foreach ($regIds as $reqId) {
    if (!in_array($reqId, $foundIds, true)) {
        $failCount++;
        $errors[] = ['id' => $reqId, 'reason' => 'Registration not found for this event.'];
    }
}

ob_clean();
echo json_encode([
    'success' => $sentCount > 0 || $failCount === 0,
    'sent'    => $sentCount,
    'failed'  => $failCount,
    'errors'  => $errors,
]);
