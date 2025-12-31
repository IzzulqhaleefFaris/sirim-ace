<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['userId'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['userId'];

// Get POST data safely
$nama = trim($_POST['nama']);
$stafId = trim($_POST['stafId'] ?? null);
$email = trim($_POST['email']);
$participant_phone = trim($_POST['participant_phone'] ?? null);
$participant_company = trim($_POST['participant_company'] ?? null);

// Get role from session
$roleId = $_SESSION['roleId'];

$conn->begin_transaction();

try{
    // Update User Table
    $stmt = $conn -> prepare(
        "UPDATE USER SET nama = ?, stafId = ?, email = ? WHERE userId = ?"
    );

    $stmt -> bind_param("ssss", $nama, $stafId, $email, $userId);

    if (!$stmt -> execute()){
        throw new Exception("Gagal kemaskini maklumat pengguna.");
    }

    //Update Participant Data
    if ($roleId == 2){
        //Check if participant already exists
        $check = $conn -> prepare(
            "SELECT participant_id FROM att_participant WHERE participant_id = ?"
        );
        $check -> bind_param ("s", $userId);
        $check -> execute();
        $res = $check-> get_result();

        if($res -> num_rows > 0){
            //UPDATE
            $stmt2 = $conn -> prepare(
                "UPDATE att_participant SET participant_phone = ?, participant_company = ? WHERE participant_id = ?"
            );
            $stmt2 -> bind_param("sss", $participant_phone, $participant_company, $userId);
        } else{
            //INSERT
            $stmt2 = $conn -> prepare(
                "INSERT INTO att_participant (participant_id, participant_name, participant_email, participant_phone, participant_company)
                VALUES (?,?,?,?,?)"
            );
            $stmt2 -> bind_param("sssss", 
                                $userId,
                                $nama,
                                $email,
                                $participant_phone, 
                                $participant_company 
                            );
        }
        if (!$stmt2 -> execute()){
            throw new Exception ("Gagal kemaskini maklumat pengguna.");
        }
    }
    // Commit transaction
    $conn -> commit();

    // Update session cache
    $_SESSION['nama'] = $nama;

    $_SESSION['msg'] = [
        'type' => 'success',
        'text' => 'Profil berjaya dikemaskini.'
    ];
    
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
} catch (Exception $e){
    $conn -> rollback();
    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => $e->getMessage()
    ];

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}