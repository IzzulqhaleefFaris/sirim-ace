<?php

require_once('include/config.php');
require_once('include/aes7.php');

$nama = $_POST['name'];
$userId = $_POST['userId'];
$email = $_POST['email'];
$password = md5($_POST['password']);
$roleId = $_POST['roleId'];
$status = $_POST['status'] ?? 'A';

$check = $conn->prepare("SELECT * FROM user WHERE userId = ?");
$check->bind_param("s", $userId);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    die("User ID sudah wujud. Sila pilih ID lain.");
}

// Insert data
$stmt = $conn->prepare("
    INSERT INTO user (userId, password, stafId, nama, roleId, email, status)
    VALUES (?, ?, NULL, ?, ?, ?, ?)
");

$stmt->bind_param("sssiss", $userId, $password, $nama, $roleId, $email, $status);

if ($stmt->execute()) {
    header("Location: index.php?register=success");
    exit;
} else {
    echo "Error: " . $conn->error;
}
?>