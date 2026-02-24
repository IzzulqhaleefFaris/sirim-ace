<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../include/config.php';
require_once __DIR__ . '/../../include/updateEventStatus.php';

updateEventStatuses($conn);

function respond(string $status, string $message, array $extra = []): void
{
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $extra));
    exit;
}

$registrationId = trim($_POST['registration_id'] ?? $_GET['registration_id'] ?? '');

if ($registrationId === '') {
    respond('error', 'Registration ID is required.');
}

// 1. Check registration exists and pull event context
$stmt = $conn->prepare("
    SELECT
        r.registration_id,
        r.event_id,
        r.participant_id,
        e.event_name,
        e.event_startDate,
        e.event_endDate,
        e.event_status
    FROM att_registration r
    JOIN att_event e ON e.event_id = r.event_id
    WHERE r.registration_id = ?
    LIMIT 1
");

if (!$stmt) {
    respond('error', 'Server error: unable to validate registration.');
}

$stmt->bind_param("s", $registrationId);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    respond('error', 'Invalid Registration ID.');
}

$reg = $res->fetch_assoc();
$stmt->close();

// 2. Date window validation
$tz    = new DateTimeZone('Asia/Kuala_Lumpur');
$now   = new DateTime('now', $tz);
$start = new DateTime($reg['event_startDate'], $tz);
$start->setTime(0, 0, 0);
$end = new DateTime($reg['event_endDate'], $tz);
$end->setTime(23, 59, 59);

if ($now < $start) {
    respond('error', 'Attendance not open yet');
}

if ($now > $end) {
    respond('error', 'Event has ended. Attendance closed.');
}

// 3. Status validation
if (strcasecmp($reg['event_status'], 'Current') !== 0) {
    respond('error', 'Event is not active for attendance.');
}

// 4. Prevent duplicate scan
$checkAtt = $conn->prepare("SELECT attendance_id FROM att_attendance WHERE registration_id = ? LIMIT 1");
$checkAtt->bind_param("s", $registrationId);
$checkAtt->execute();
$checkRes = $checkAtt->get_result();

if ($checkRes && $checkRes->num_rows > 0) {
    respond('error', 'Attendance already recorded');
}
$checkAtt->close();

// 5. Generate attendance_id
$nextId = 'ATT0001';
$last   = $conn->query("SELECT attendance_id FROM att_attendance ORDER BY attendance_id DESC LIMIT 1");
if ($last && $row = $last->fetch_assoc()) {
    $lastNumber = (int) substr($row['attendance_id'], 3);
    $nextId     = 'ATT' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
}

// 6. Insert attendance
$insert = $conn->prepare("
    INSERT INTO att_attendance
        (attendance_id, registration_id, event_id, participant_id, check_in_time, attendance_status)
    VALUES (?, ?, ?, ?, NOW(), 'Present')
");

if (!$insert) {
    respond('error', 'Server error: unable to create attendance.');
}

$insert->bind_param("ssss", $nextId, $reg['registration_id'], $reg['event_id'], $reg['participant_id']);

if ($insert->execute()) {
    respond('success', 'Attendance recorded successfully', [
        'attendance_id'   => $nextId,
        'registration_id' => $reg['registration_id'],
        'event_id'        => $reg['event_id'],
        'event_name'      => $reg['event_name'],
        'check_in_time'   => date('Y-m-d H:i:s', $now->getTimestamp())
    ]);
}

respond('error', 'Attendance could not be saved. Please try again.');
