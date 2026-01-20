<?php
session_start();
include "include/config.php";
include "include/updateEventStatus.php";

if (!isset($_SESSION['userId']) || ($_SESSION['roleId'] ?? null) != 1) {
    header('Location: /attendance');
    exit;
}

updateEventStatuses($conn);

$eventId = trim($_GET['id'] ?? '');
$filter = trim($_GET['filter'] ?? ''); // Registered|Present|Absent|''(all)

if ($eventId === '') {
    http_response_code(400);
    echo "Missing event id";
    exit;
}

// Load event status once (for absent logic)
$eventStmt = $conn->prepare("SELECT event_status, event_name FROM att_event WHERE event_id = ? LIMIT 1");
$eventStmt->bind_param("s", $eventId);
$eventStmt->execute();
$eventRes = $eventStmt->get_result();
if (!$eventRes || $eventRes->num_rows === 0) {
    http_response_code(404);
    echo "Event not found";
    exit;
}
$event = $eventRes->fetch_assoc();
$eventStmt->close();

$sql = "
    SELECT
        r.registration_id,
        r.participant_id,
        u.nama AS participant_name,
        u.email AS participant_email,
        p.participant_phone,
        p.participant_company,
        a.attendance_id,
        a.check_in_time
    FROM att_registration r
    LEFT JOIN user u ON u.userId = r.participant_id
    LEFT JOIN att_participant p ON p.participant_id = r.participant_id
    LEFT JOIN att_attendance a ON a.registration_id = r.registration_id
    WHERE r.event_id = ?
    ORDER BY u.nama ASC, r.registration_id ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $eventId);
$stmt->execute();
$res = $stmt->get_result();

// CSV headers (Excel-friendly)
$filename = 'event_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $eventId) . '_registrations.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// BOM for Excel UTF-8
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');
fputcsv($out, [
    'Registration ID',
    'Participant ID',
    'Name',
    'Email',
    'Phone',
    'Company',
    'Status',
    'Check-in Time'
]);

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $isPresent = !empty($row['attendance_id']);
        $isAbsent = (!$isPresent && ($event['event_status'] ?? '') === 'Completed');
        $status = $isPresent ? 'Present' : ($isAbsent ? 'Absent' : 'Registered');

        if ($filter !== '' && $status !== $filter) {
            continue;
        }

        fputcsv($out, [
            $row['registration_id'],
            $row['participant_id'],
            $row['participant_name'],
            $row['participant_email'],
            $row['participant_phone'],
            $row['participant_company'],
            $status,
            $row['check_in_time'],
        ]);
    }
}

fclose($out);
$stmt->close();
exit;

