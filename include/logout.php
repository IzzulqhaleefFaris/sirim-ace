<?php
session_start();
require_once __DIR__ . '/config.php';

if (isset($_SESSION['userId'])) {
    $userId = $_SESSION['userId'];
    $logoutTime = date('Y-m-d H:i:s');
    $sql = "UPDATE useraccess SET tarikhKeluar = '$logoutTime' WHERE userId = '$userId' AND tarikhKeluar IS NULL";
    mysqli_query($conn, $sql); // 
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();
header('Location: /sirimace/login.php');
exit;
?>
