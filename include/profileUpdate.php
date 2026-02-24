<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['userId'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$userId = $_SESSION['userId'];

// Get POST data safely
$nama = trim($_POST['nama'] ?? '');
$stafId = trim($_POST['stafId'] ?? '');
$email = trim($_POST['email'] ?? '');
$participant_phone = trim($_POST['participant_phone'] ?? '');
$participant_company = trim($_POST['participant_company'] ?? '');

if ($nama === '' || $email === '') {
    $message = 'Name and email are required.';
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }

    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => $message
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// Get role from session
$roleId = $_SESSION['roleId'];

$conn->begin_transaction();

try{
    // Update User Table
    $stmt = $conn -> prepare(
        "UPDATE user SET nama = ?, stafId = ?, email = ? WHERE userId = ?"
    );

    $stmt -> bind_param("ssss", $nama, $stafId, $email, $userId);

    if (!$stmt -> execute()){
        throw new Exception("Failed to update user information: " . $stmt->error);
    }

    $stmt->close();

    //Update Participant Data
    $shouldSyncParticipant = ($roleId == 2) || $participant_phone !== '' || $participant_company !== '';
    if ($shouldSyncParticipant){
        //Check if participant already exists
        $check = $conn -> prepare(
            "SELECT participant_id FROM att_participant WHERE participant_id = ?"
        );
        $check -> bind_param ("s", $userId);
        $check -> execute();
        $res = $check-> get_result();
        $check->close();

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
            throw new Exception ("Failed to update participant information: " . $stmt2->error);
        }

        $stmt2->close();
    }
    // Commit transaction
    $conn -> commit();

    // Update session cache
    $_SESSION['nama'] = $nama;
    $_SESSION['email'] = $email;

    // Check if this is an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully.'
        ]);
        exit;
    }

    $_SESSION['msg'] = [
        'type' => 'success',
        'text' => 'Profile updated successfully.'
    ];
    
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
} catch (Exception $e){
    $conn -> rollback();
    
    // Check if this is an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
    
    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => $e->getMessage()
    ];

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}