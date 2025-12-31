<?php
session_start();
include "config.php";

$userId = $_SESSION['userId'] ?? null;
$newPassword = md5($_POST['password']);

if (!$userId || !$newPassword) {
    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => 'Data tidak lengkap.'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

$stmt = $conn->prepare("UPDATE user SET password = ? WHERE userId = ?");
$stmt->bind_param("ss", $newPassword, $userId);

if ($stmt->execute()) {
    $_SESSION['msg'] = [
        'type' => 'success',
        'text' => 'Kata laluan berjaya dikemaskini.'
    ];
} else {
    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => 'Gagal kemaskini kata laluan.'
    ];
}

$stmt->close();
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>