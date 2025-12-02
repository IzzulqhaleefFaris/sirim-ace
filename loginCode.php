<?php
//DATE PROGRAMMED : 19/02/24
//PROGRAMMER : NOR SHUHADA CHE AHMAD
//VERSION : 3

session_start();
 
require_once('include/config.php');
require_once('include/aes7.php');
 
if(isset($_POST['login']))
{
	if(!empty($_POST['user_name']) && !empty($_POST['user_pass']))
	{
		$username = $_REQUEST["user_name"];
	    $password = $_REQUEST["user_pass"];
		
		$encryptPassword = md5($password);
		
        $sql = "SELECT * FROM user WHERE userId = '".$username."' AND password = '".$encryptPassword."'";
		$rs = mysqli_query($conn,$sql);
		$getNumRows = mysqli_num_rows($rs);
		
		if($getNumRows == 1)
		{
			$row = mysqli_fetch_assoc($rs);
			
			$_SESSION["userId"] = $row['userId'];
			$_SESSION["password"] = $row['password'];
            $_SESSION["stafId"] = $row['stafId'];
			$_SESSION["nama"] = $row['nama'];
			$_SESSION["roleId"] = $row['roleId'];
			$_SESSION["status"] = $row['status'];
			$_SESSION["logged_in"] = true;
			
			if($_SESSION["roleId"] == 1 && $_SESSION["status"] == 'A')
			{
                $queryInsert  = "INSERT INTO useraccess (userId, tarikhMasuk)VALUES('$username', CURRENT_TIMESTAMP())";
		        $resultInsert = mysqli_query($conn, $queryInsert);
				//header('location:dashboard.php?pg=ADM');
				header('location:home.php?pg=OFCR');
				exit;
			}else if($_SESSION["roleId"] == 2 && $_SESSION["status"] == 'A')
			{
                $queryInsert  = "INSERT INTO useraccess (userId, tarikhMasuk)VALUES('$username', CURRENT_TIMESTAMP())";
		        $resultInsert = mysqli_query($conn, $queryInsert);
				header('location:home.php?pg=OFCR');
				exit;
			}else if($_SESSION["roleId"] == 3 && $_SESSION["status"] == 'A')
			{
                $queryInsert  = "INSERT INTO useraccess (userId, tarikhMasuk)VALUES('$username', CURRENT_TIMESTAMP())";
		        $resultInsert = mysqli_query($conn, $queryInsert);
				header('location:dashboard.php?pg=OPR');
				exit;
			}else{
				header('location:loginError.php?ReturnId=2');
				exit;
			}
		}else{
			// login failed - set flash message and redirect to index
            $_SESSION['msg'] = [
                'type' => 'danger',
                'text' => 'Akaun tidak wujud atau kata laluan salah.'
            ];
            header('Location: index.php');
            exit;
		}
	}else{
		header('location:login.php');
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