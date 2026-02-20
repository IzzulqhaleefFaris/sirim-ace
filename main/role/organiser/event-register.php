<?php
session_start();
include "../../../include/config.php";
include "../../../include/qrEmail.php";

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

    $emailSent = false;
    $mailReason = null;

    $participantName = '';
    $participantEmail = '';
    $eventName = 'Event';

    $userStmt = $conn->prepare("SELECT nama, email FROM user WHERE userId = ? LIMIT 1");
    if ($userStmt) {
        $userStmt->bind_param("s", $participantId);
        $userStmt->execute();
        $userRes = $userStmt->get_result();
        if ($userRes && ($userRow = $userRes->fetch_assoc())) {
            $participantName = trim($userRow['nama'] ?? '');
            $participantEmail = trim($userRow['email'] ?? '');
        }
        $userStmt->close();
    }

    $eventStmt = $conn->prepare("SELECT event_name FROM att_event WHERE event_id = ? LIMIT 1");
    if ($eventStmt) {
        $eventStmt->bind_param("s", $eventId);
        $eventStmt->execute();
        $eventRes = $eventStmt->get_result();
        if ($eventRes && ($eventRow = $eventRes->fetch_assoc())) {
            $eventName = trim($eventRow['event_name'] ?? 'Event');
        }
        $eventStmt->close();
    }

    if ($participantEmail !== '' && filter_var($participantEmail, FILTER_VALIDATE_EMAIL)) {
        $emailSent = sendRegistrationQrEmail(
            $participantEmail,
            $participantName,
            $newCode,
            $eventId,
            $eventName,
            $mailReason
        );
    } else {
        $mailReason = 'Email berdaftar tidak sah atau kosong.';
    }

    $_SESSION['msg']                    = [
        'type' => 'success',
        'text' => $emailSent
            ? 'Pendaftaran berjaya! QR anda sedia di halaman My Events dan juga telah dihantar ke email berdaftar anda.'
            : 'Pendaftaran berjaya! QR anda sedia di halaman My Events. QR email tidak berjaya dihantar: ' . ($mailReason ?: 'Ralat tidak diketahui')
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
