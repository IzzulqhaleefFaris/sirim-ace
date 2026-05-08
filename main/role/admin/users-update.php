<?php
session_start();
include "../../../include/config.php";
/** @var mysqli $conn */
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
        'text' => 'Invalid user data.'
    ];
    header('Location: users.php');
    exit;
}

if ($userId === ($_SESSION['userId'] ?? '')) {
    $_SESSION['msg'] = [
        'type' => 'warning',
        'text' => 'You cannot change role or status for your own account.'
    ];
    header('Location: users.php');
    exit;
}

$stmt = $conn->prepare("UPDATE user SET roleId = ?, status = ? WHERE userId = ?");
if (!$stmt) {
    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => 'Database error.'
    ];
    header('Location: users.php');
    exit;
}

$stmt->bind_param("iss", $roleId, $status, $userId);
if ($stmt->execute()) {
    $_SESSION['msg'] = [
        'type' => 'success',
        'text' => 'User updated successfully.'
    ];
} else {
    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => 'Failed to update user.'
    ];
}

$stmt->close();
header('Location: users.php');
exit;
