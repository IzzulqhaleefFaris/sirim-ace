<?php
session_start();
include "../../../include/config.php";
include "../../../include/updateEventStatus.php";
include "../../../include/permissions.php";

require_manage_events();

updateEventStatuses($conn);

$eventId = trim($_GET['id'] ?? '');
$filter = trim($_GET['filter'] ?? ''); // Registered|Present|Absent|''(all)

if ($eventId === '') {
    http_response_code(400);
    echo "Missing event id";
    exit;
}

if (!ensure_event_owner_or_admin($conn, $eventId)) {
    http_response_code(403);
    echo "Forbidden";
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
        IFNULL(NULLIF(r.registration_source, ''), 'account') AS registration_source,
        COALESCE(NULLIF(u.nama, ''), NULLIF(r.walkin_name, ''), '-') AS participant_name,
        COALESCE(NULLIF(u.email, ''), NULLIF(r.walkin_email, ''), '-') AS participant_email,
        COALESCE(NULLIF(p.participant_phone, ''), NULLIF(r.walkin_phone, ''), '-') AS participant_phone,
        COALESCE(NULLIF(p.participant_company, ''), NULLIF(r.walkin_company, ''), '-') AS participant_company,
        a.attendance_id,
        a.check_in_time
    FROM att_registration r
    LEFT JOIN user u ON u.userId = r.participant_id
    LEFT JOIN att_participant p ON p.participant_id = r.participant_id
    LEFT JOIN att_attendance a ON a.registration_id = r.registration_id
    WHERE r.event_id = ?
    ORDER BY participant_name ASC, r.registration_id ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $eventId);
$stmt->execute();
$res = $stmt->get_result();

// Ensure no previous output corrupts CSV (blank rows / BOM shown as text)
while (ob_get_level() > 0) {
    ob_end_clean();
}

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
    'Source',
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

        // Normalize null values
        $registrationId = $row['registration_id'] ?? '';
        $participantId  = $row['participant_id'] ?? '';
        $source         = (($row['registration_source'] ?? 'account') === 'walk_in') ? 'Walk-in' : 'Account';
        $name           = $row['participant_name'] ?? '';
        $email          = $row['participant_email'] ?? '';
        $phone          = $row['participant_phone'] ?? '';
        $company        = $row['participant_company'] ?? '';
        $checkIn        = $row['check_in_time'] ?? '';

        // Prevent Excel formula injection
        foreach ([$registrationId, $participantId, $name, $email, $phone, $company] as &$field) {
            if (preg_match('/^[=\-+@]/', $field)) {
                $field = "'" . $field;
            }
        }

        // Format datetime for Excel
        if (!empty($checkIn)) {
            $checkIn = date('Y-m-d H:i:s', strtotime($checkIn));
        }

        fputcsv($out, [
            $registrationId,
            $participantId,
            $source,
            $name,
            $email,
            $phone,
            $company,
            $status,
            $checkIn
        ]);
    }
}

fclose($out);
$stmt->close();
exit;
