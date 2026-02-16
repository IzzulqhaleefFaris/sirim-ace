<?php
session_start();
include "../../../include/config.php";
include "../../../include/permissions.php";

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php');
    exit;
}

$userId = trim($_POST['userId'] ?? '');
$roleId = (int)($_POST['roleId'] ?? 0);
$status = trim($_POST['status'] ?? '');

$validRoles = [1, 2, 3];
$validStatus = ['A', 'D'];

if ($userId === '' || !in_array($roleId, $validRoles, true) || !in_array($status, $validStatus, true)) {
    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => 'Data pengguna tidak sah.'
    ];
    header('Location: users.php');
    exit;
}

if ($userId === ($_SESSION['userId'] ?? '')) {
    $_SESSION['msg'] = [
        'type' => 'warning',
        'text' => 'Tidak boleh mengubah peranan atau status untuk akaun sendiri.'
    ];
    header('Location: users.php');
    exit;
}

$stmt = $conn->prepare("UPDATE user SET roleId = ?, status = ? WHERE userId = ?");
if (!$stmt) {
    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => 'Ralat pangkalan data.'
    ];
    header('Location: users.php');
    exit;
}

$stmt->bind_param("iss", $roleId, $status, $userId);
if ($stmt->execute()) {
    $_SESSION['msg'] = [
        'type' => 'success',
        'text' => 'Pengguna berjaya dikemaskini.'
    ];
} else {
    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => 'Gagal mengemaskini pengguna.'
    ];
}

$stmt->close();
header('Location: users.php');
exit;
