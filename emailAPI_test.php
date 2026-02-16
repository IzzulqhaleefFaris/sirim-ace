<?php
require __DIR__ . '/vendor/autoload.php';

use SendGrid\Mail\Mail;

$email = new Mail();
$email->setFrom("izzulqhaleef@sirim.my", "SIRIM Attendance");
$email->setSubject("Email Testing");
$email->addTo("sirimorg1@yopmail.com", "Test User");
//Email organiser: sirimorg1@yopmail.com

$textContent = "This is a test email from SIRIM Attendance System.
Your event has been successfully created.
Event ID: EV001";

$htmlContent = "
<h2>Email Testing</h2>
<p>This is a test email from <strong>SIRIM Attendance System</strong>.</p>
<p>Your event has been successfully created.</p>
<p><strong>Event ID:</strong> EV001</p>
";

$email->addContent("text/plain", $textContent);
$email->addContent("text/html", $htmlContent);

$apiKey = getenv('SENDGRID_API_KEY');
if ($apiKey === false || $apiKey === '') {
    $envKey = $_ENV['SENDGRID_API_KEY'] ?? '';
    $apiKey = $envKey !== '' ? $envKey : null;
}

if (!$apiKey) {
    echo "Error: SENDGRID_API_KEY is not set.";
    exit;
}

$sendgrid = new \SendGrid($apiKey);

try {
    $response = $sendgrid->send($email);

    echo "Status Code: " . $response->statusCode() . "<br>";
    echo "Headers:<br><pre>";
    print_r($response->headers());
    echo "</pre>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

