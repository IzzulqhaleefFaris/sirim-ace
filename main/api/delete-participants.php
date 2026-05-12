<?php
/**
 * delete-participants.php — API endpoint: delete selected registrations (and their attendance records).
 *
 * POST params:
 *   event_id            (string) required
 *   registration_ids[]  (string[]) required — one or more registration IDs
 *
 * Returns JSON:
 *   { success: bool, deleted: int, message?: string }
 */
session_start();
ob_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../include/config.php';
/** @var mysqli $conn */
require_once __DIR__ . '/../../include/permissions.php';

// ── Auth ──────────────────────────────────────────────────────────────────
if (empty($_SESSION['userId'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorised.']);
    exit;
}

// ── Method check ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// ── Input ─────────────────────────────────────────────────────────────────
$eventId         = trim($_POST['event_id'] ?? '');
$registrationIds = $_POST['registration_ids'] ?? [];

if ($eventId === '' || empty($registrationIds) || !is_array($registrationIds)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
    exit;
}

// Sanitise IDs — only allow alphanumeric / dash
$sanitised = [];
foreach ($registrationIds as $rid) {
    $rid = trim((string)$rid);
    if ($rid !== '' && preg_match('/^[a-zA-Z0-9_\-]+$/', $rid)) {
        $sanitised[] = $rid;
    }
}

if (empty($sanitised)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No valid registration IDs provided.']);
    exit;
}

// ── Authorisation: verify registrations belong to this event and the caller owns/manages it ──
if (!ensure_event_owner_or_admin($conn, $eventId)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden: you do not have access to this event.']);
    exit;
}

// Verify all provided registration_ids actually belong to the event
$placeholders = implode(',', array_fill(0, count($sanitised), '?'));
$types        = str_repeat('s', count($sanitised));

$checkSql  = "SELECT registration_id FROM att_registration WHERE event_id = ? AND registration_id IN ($placeholders)";
$checkStmt = $conn->prepare($checkSql);
if (!$checkStmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error.']);
    exit;
}

$bindArgs = array_merge([$eventId], $sanitised);
$bindTypes = 's' . $types;
$checkStmt->bind_param($bindTypes, ...$bindArgs);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$verifiedIds = [];
while ($row = $checkResult->fetch_assoc()) {
    $verifiedIds[] = $row['registration_id'];
}
$checkStmt->close();

if (empty($verifiedIds)) {
    echo json_encode(['success' => false, 'message' => 'No matching registrations found for this event.']);
    exit;
}

// ── Delete ────────────────────────────────────────────────────────────────
$delPlaceholders = implode(',', array_fill(0, count($verifiedIds), '?'));
$delTypes        = str_repeat('s', count($verifiedIds));

$conn->begin_transaction();
try {
    // Delete attendance records first (FK dependency)
    $delAttSql  = "DELETE FROM att_attendance WHERE registration_id IN ($delPlaceholders)";
    $delAttStmt = $conn->prepare($delAttSql);
    if (!$delAttStmt) throw new RuntimeException('Prepare failed (attendance): ' . $conn->error);
    $delAttStmt->bind_param($delTypes, ...$verifiedIds);
    $delAttStmt->execute();
    $delAttStmt->close();

    // Delete registrations
    $delRegSql  = "DELETE FROM att_registration WHERE registration_id IN ($delPlaceholders)";
    $delRegStmt = $conn->prepare($delRegSql);
    if (!$delRegStmt) throw new RuntimeException('Prepare failed (registration): ' . $conn->error);
    $delRegStmt->bind_param($delTypes, ...$verifiedIds);
    $delRegStmt->execute();
    $deleted = $delRegStmt->affected_rows;
    $delRegStmt->close();

    $conn->commit();

    ob_end_clean();
    echo json_encode(['success' => true, 'deleted' => $deleted]);
} catch (RuntimeException $ex) {
    $conn->rollback();
    http_response_code(500);
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
}
