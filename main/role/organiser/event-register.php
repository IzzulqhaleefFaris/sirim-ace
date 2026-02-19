<?php
session_start();
include "../../../include/config.php";

// Must be logged in
if (!isset($_SESSION['userId'])) {
    header("Location: /attendance");
    exit;
}

$participantId = $_SESSION['userId'];
$eventId = $_GET['id'] ?? null;

if (!$eventId) {
    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => 'Event tidak sah.'
    ];
    header("Location: event-list.php");
    exit;
}

// 1. Prevent duplicate registration
$checkSQL1 =    "SELECT registration_id
                FROM att_registration
                WHERE event_id = ? AND participant_id = ?";
$stmt = $conn->prepare($checkSQL1);
$stmt->bind_param("ss", $eventId, $participantId);
$stmt->execute();
$stmt->store_result();


if ($stmt->num_rows > 0) {
    $_SESSION['msg'] = [
        'type' => 'warning',
        'text' => 'Anda telah berdaftar untuk event ini.'
    ];
    header("Location: event-view.php?id=$eventId");
    exit;
}
$stmt->close();

// 2. Generate registration ID
$checkSQL2 =    "SELECT registration_id
                FROM att_registration
                ORDER BY registration_id DESC 
                LIMIT 1";
$result = $conn->prepare($checkSQL2);
$result->execute();
$resultSet = $result->get_result();

if ($resultSet && $row = $resultSet->fetch_assoc()) {
    $lastNumber = (int) substr($row['registration_id'], 3);
    $newCode    = 'REG' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
} else {
    $newCode = 'REG0001';
}

// 3. Insert Registration
$insertSql = "
    INSERT INTO att_registration 
        (registration_id, event_id, participant_id, registration_source)
    VALUES (?, ?, ?, 'account')
";

$insertStmt = $conn->prepare($insertSql);
$insertStmt->bind_param("sss", $newCode, $eventId, $participantId);

if ($insertStmt->execute()) {
    // cache latest registration id for QR display
    $_SESSION['latest_registration_id'] = $newCode;
    $_SESSION['msg']                    = [
        'type' => 'success',
        'text' => 'Pendaftaran berjaya! QR anda sedia di halaman My Events.'
    ];
} else {
    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => 'Pendaftaran gagal. Sila cuba lagi.'
    ];
}

$insertStmt->close();
header("Location: event-view.php?id=$eventId");
exit;
?>
