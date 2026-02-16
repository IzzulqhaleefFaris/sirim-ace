<?php

session_start();
 
// CSRF Token check
if (
    !isset($_POST['csrf_token']) ||
    !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => 'Permintaan tidak sah (CSRF).'
    ];
    header('Location: index.php');
    exit;
}

require_once('include/config.php');
require_once('include/aes7.php');
 
if(isset($_POST['login']))
{
	if(!empty($_POST['email']) && !empty($_POST['user_pass']))
	{
		$email = $_REQUEST["email"];
	    $password = $_REQUEST["user_pass"];
		$encryptPassword = md5($password);
		
		// Use prepared statement to avoid SQL injection
		$stmt = $conn->prepare("SELECT * FROM user WHERE email = ? AND password = ?");
        $stmt -> bind_param("ss", $email, $encryptPassword);
		$stmt -> execute();
		$rs = $stmt -> get_result();
		$getNumRows = $rs -> num_rows;
		
		if($getNumRows == 1)
		{
			$row = $rs -> fetch_assoc();
			
			$_SESSION["userId"] = $row['userId'];
			$_SESSION["password"] = $row['password'];
            $_SESSION["stafId"] = $row['stafId'];
			$_SESSION["nama"] = $row['nama'];
			$_SESSION["roleId"] = $row['roleId'];
			$_SESSION["status"] = $row['status'];
			$_SESSION["logged_in"] = true;

			// Insert login timestamp
            $queryInsert  = "INSERT INTO useraccess (userId, tarikhMasuk) VALUES (?, CURRENT_TIMESTAMP())";
            $stmtInsert = $conn->prepare($queryInsert);
            $stmtInsert->bind_param("s", $row['userId']);
            $stmtInsert->execute();
			
			//Redirect based on role
            if($_SESSION["roleId"] == 1 && $_SESSION["status"] == 'A') {
                header('location: main/role/organiser/home.php?pg=OFCR');
                exit;
            } else if($_SESSION["roleId"] == 2 && $_SESSION["status"] == 'A') {
                header('location: main/role/participant/home.php?pg=OFCR');
                exit;
            } else if($_SESSION["roleId"] == 3 && $_SESSION["status"] == 'A') {
                header('location: main/role/admin/home.php?pg=ADMIN');
                exit;
            } else {
                header('location:loginError.php?ReturnId=2');
                exit;
            }
        } else {
            $_SESSION['msg'] = [
                'type' => 'danger',
                'text' => 'Email atau kata laluan salah.'
            ];
            header('Location: index.php');
            exit;
        }
    } else {
        $_SESSION['msg'] = [
            'type' => 'danger',
            'text' => 'Sila masukkan email dan kata laluan.'
        ];
        header('location:index.php');
        exit;
    }
}

if(isset($_GET['logout']) && $_GET['logout'] == true)
{
    session_destroy();
    header("location:index.php");
    exit;
}

if(isset($_GET['lmsg']) && $_GET['lmsg'] == true)
{
    $errorMsg = "Sign in required to access dashboard";
}
?>