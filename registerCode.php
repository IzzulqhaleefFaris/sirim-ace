<?php

require_once('include/config.php');
require_once('include/aes7.php');

$conn->begin_transaction();

try{
    $nama = $_POST['name'];
    $userId = $_POST['userId'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $roleId = $_POST['roleId'];
    $status = $_POST['status'] ?? 'A';

    //Check duplicate userId
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

    if (!$stmt -> execute()){
        throw new Exception("Gagal Daftar Akaun.");
    }

    //if role is participant:
    if ($roleId = 2){
        $participant_phone = null;
        $participant_company = null;

        $stmt2 = $conn -> prepare(
            "INSERT INTO att_participant 
                        (participant_id, participant_name, participant_email, participant_phone, participant_company)
            VALUES (?,?,?,?,?)"
        );

        $stmt2 -> bind_param(
            "sssss",
            $userId,
            $nama,
            $email,
            $participant_phone,
            $participant_company
        );

        if (!$stmt2 -> execute()){
            throw new Exception("Gagal daftar participant.");
        }
    }
    $conn -> commit();

    header("Location: index.php?register=success");
    exit;
} catch (Exception $e){

    $conn -> rollback();
    echo $e -> getMessage();
}
?>