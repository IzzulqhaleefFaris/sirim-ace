<?php
session_start();
require_once('include/config.php');

// Verify state parameter to prevent CSRF
if (!isset($_GET['state']) || !isset($_SESSION['google_oauth_state']) 
    || !hash_equals($_SESSION['google_oauth_state'], $_GET['state'])) {
    $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Invalid OAuth state. Please try again.'];
    header('Location: index.php');
    exit;
}
unset($_SESSION['google_oauth_state']);

// Check for errors from Google
if (isset($_GET['error'])) {
    $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Google login was cancelled or failed.'];
    header('Location: index.php');
    exit;
}

if (!isset($_GET['code'])) {
    $_SESSION['msg'] = ['type' => 'danger', 'text' => 'No authorization code received.'];
    header('Location: index.php');
    exit;
}

$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);

// Exchange authorization code for access token
$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

if (isset($token['error'])) {
    $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Failed to authenticate with Google.'];
    header('Location: index.php');
    exit;
}

$client->setAccessToken($token);

// Get user info from Google
$oauth2 = new Google\Service\Oauth2($client);
$googleUser = $oauth2->userinfo->get();

$googleId = $googleUser->id;
$email = $googleUser->email;
$name = $googleUser->name;

// Check if user exists by google_id
$stmt = $conn->prepare("SELECT * FROM user WHERE google_id = ?");
$stmt->bind_param("s", $googleId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    // Existing Google user — log them in
    $row = $result->fetch_assoc();
} else {
    // Check if email already exists (existing user linking Google account)
    $stmt2 = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt2->bind_param("s", $email);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    if ($result2->num_rows === 1) {
        // Link Google account to existing user
        $row = $result2->fetch_assoc();
        $stmtUpdate = $conn->prepare("UPDATE user SET google_id = ?, provider = 'google' WHERE userId = ?");
        $stmtUpdate->bind_param("ss", $googleId, $row['userId']);
        $stmtUpdate->execute();
    } else {
        // No account found — auto-register as Participant (roleId=2)
        // Generate unique userId in format ATDXXXXXXX
        do {
            $randomDigits = str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
            $newUserId = 'ATD' . $randomDigits;
            $checkStmt = $conn->prepare("SELECT userId FROM user WHERE userId = ?");
            $checkStmt->bind_param("s", $newUserId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
        } while ($checkResult->num_rows > 0);

        $defaultRole = 2;
        $defaultStatus = 'A';

        $conn->begin_transaction();
        try {
            // Insert into user table
            $stmtInsert = $conn->prepare("
                INSERT INTO user (userId, password, stafId, nama, roleId, email, status, google_id, provider)
                VALUES (?, NULL, NULL, ?, ?, ?, ?, ?, 'google')
            ");
            $stmtInsert->bind_param("ssisss", $newUserId, $name, $defaultRole, $email, $defaultStatus, $googleId);
            $stmtInsert->execute();

            // Insert into att_participant
            $stmtPart = $conn->prepare("
                INSERT INTO att_participant (participant_id, participant_name, participant_email, participant_phone, participant_company)
                VALUES (?, ?, ?, NULL, NULL)
            ");
            $stmtPart->bind_param("sss", $newUserId, $name, $email);
            $stmtPart->execute();

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Failed to create account. Please try again.'];
            header('Location: index.php');
            exit;
        }

        // Fetch the newly created user
        $stmtNew = $conn->prepare("SELECT * FROM user WHERE userId = ?");
        $stmtNew->bind_param("s", $newUserId);
        $stmtNew->execute();
        $row = $stmtNew->get_result()->fetch_assoc();
    }
}

// Set session variables
$_SESSION["userId"]    = $row['userId'];
$_SESSION["password"]  = $row['password'];
$_SESSION["stafId"]    = $row['stafId'];
$_SESSION["nama"]      = $row['nama'];
$_SESSION["roleId"]    = $row['roleId'];
$_SESSION["status"]    = $row['status'];
$_SESSION["logged_in"] = true;

// Insert login timestamp
$stmtAccess = $conn->prepare("INSERT INTO useraccess (userId, tarikhMasuk) VALUES (?, CURRENT_TIMESTAMP())");
$stmtAccess->bind_param("s", $row['userId']);
$stmtAccess->execute();

// Redirect based on role
if ($row['roleId'] == 1 && $row['status'] == 'A') {
    header('Location: main/role/organiser/home.php?pg=OFCR');
    exit;
} elseif ($row['roleId'] == 2 && $row['status'] == 'A') {
    header('Location: main/role/participant/home.php?pg=OFCR');
    exit;
} elseif ($row['roleId'] == 3 && $row['status'] == 'A') {
    header('Location: main/role/admin/home.php?pg=ADMIN');
    exit;
} else {
    header('Location: loginError.php?ReturnId=2');
    exit;
}
