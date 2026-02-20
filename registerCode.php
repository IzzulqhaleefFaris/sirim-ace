<?php

session_start();

require_once('include/config.php');
require_once('include/aes7.php');

$conn->begin_transaction();

try {
    $nama = $_POST['name'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $roleId = 2;
    $status = $_POST['status'] ?? 'A';

    //Check duplicate email
    $checkEmail = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $resultEmail = $checkEmail->get_result();
    if ($resultEmail->num_rows > 0) {
        throw new Exception("Email sudah wujud. Sila gunakan email lain.");
    }

    //Generate unique user ID in format of ATDXXXXXXX
    do {
        $randomDigits = str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
        $userId = 'ATD' . $randomDigits;

        $check = $conn->prepare("SELECT * FROM user WHERE userId = ?");
        $check->bind_param("s", $userId);
        $check->execute();
        $result = $check->get_result();
    } while ($result->num_rows > 0); //repeat if generated ID already exists

    //Insert into user table
    $stmt = $conn->prepare("
        INSERT INTO user (userId, password, stafId, nama, roleId, email, status)
        VALUES (?, ?, NULL, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssiss", $userId, $password, $nama, $roleId, $email, $status);

    if (!$stmt->execute()) {
        throw new Exception("Gagal Daftar Akaun.");
    }

    //If role is participant
    if ($roleId == 2) {
        $participant_phone = null;
        $participant_company = null;

        $stmt2 = $conn->prepare("
            INSERT INTO att_participant 
                (participant_id, participant_name, participant_email, participant_phone, participant_company)
            VALUES (?,?,?,?,?)
        ");

        $stmt2->bind_param(
            "sssss",
            $userId,
            $nama,
            $email,
            $participant_phone,
            $participant_company
        );

        if (!$stmt2->execute()) {
            throw new Exception("Gagal daftar participant.");
        }
    }

    // Optional: show generated User ID on login page
    $_SESSION['msg'] = [
        'type' => 'info',
        'text' => "Pendaftaran berjaya! User ID anda: $userId"
    ];

    $conn->commit();

    header("Location: index.php?register=success");
    exit;
} catch (Exception $e) {

    $conn->rollback();

    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => $e->getMessage()
    ];

    header("Location: register.php");
    exit;
}
