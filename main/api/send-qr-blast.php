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

// ── Verify event exists & caller has access ───────────────────────────────
$evStmt = $conn->prepare("SELECT event_id, event_name, event_startDate, event_endDate, event_owner_id FROM att_event WHERE event_id = ? LIMIT 1");
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
        $instructions
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
