<?php
session_start();
include "config.php";

$userId = $_SESSION['userId'] ?? null;
$newPassword = md5($_POST['password']);

if (!$userId || !$newPassword) {
    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => 'Incomplete data.'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

$stmt = $conn->prepare("UPDATE user SET password = ? WHERE userId = ?");
$stmt->bind_param("ss", $newPassword, $userId);

if ($stmt->execute()) {
    $_SESSION['msg'] = [
        'type' => 'success',
        'text' => 'Password updated successfully.'
    ];
} else {
    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => 'Failed to update password.'
    ];
}

$stmt->close();
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>