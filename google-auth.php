<?php
session_start();
require_once('include/config.php');
/** @var mysqli $conn */

$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
$client->addScope('email');
$client->addScope('profile');

// Generate a CSRF state token and store in session
$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;
$client->setState($state);

$authUrl = $client->createAuthUrl();
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit;
