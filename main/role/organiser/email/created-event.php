<?php
require_once __DIR__ . '/../../../../vendor/autoload.php';

use SendGrid\Mail\Mail;

function sendEventCreatedEmail(mysqli $conn, string $eventOwnerId, string $eventId, string $eventName, string $eventStartDate, string $eventEndDate): void
{
    $recipientEmail = $_SESSION['email'] ?? null;
    $recipientName = $_SESSION['nama'] ?? 'User';

    if (!$recipientEmail) {
        $stmtUser = $conn->prepare("SELECT nama, email FROM user WHERE userId = ?");
        if ($stmtUser) {
            $stmtUser->bind_param("s", $eventOwnerId);
            $stmtUser->execute();
            $resUser = $stmtUser->get_result();
            if ($resUser && ($rowUser = $resUser->fetch_assoc())) {
                $recipientName = $rowUser['nama'] ?: $recipientName;
                $recipientEmail = $rowUser['email'] ?: $recipientEmail;
            }
            $stmtUser->close();
        }
    }

    $apiKey = getenv('SENDGRID_API_KEY');
    if ($apiKey === false || $apiKey === '') {
        $envKey = $_ENV['SENDGRID_API_KEY'] ?? '';
        $apiKey = $envKey !== '' ? $envKey : null;
    }

    if (!$apiKey || !$recipientEmail) {
        error_log("SendGrid skipped: missing api key or recipient email.");
        return;
    }

    $fromEmail = getenv('SENDGRID_FROM_EMAIL') ?: 'izzulqhaleef@sirim.my';
    $fromName = getenv('SENDGRID_FROM_NAME') ?: 'SIRIM Attendance';

    $mail = new Mail();
    $mail->setFrom($fromEmail, $fromName);
    $mail->setSubject("Event Created: " . $eventName);
    $mail->addTo($recipientEmail, $recipientName);

    $textContent = "Your event has been created successfully.\n" .
        "Event Name: {$eventName}\n" .
        "Start Date: {$eventStartDate}\n" .
        "End Date: {$eventEndDate}\n";

    $htmlContent = "
        <h2>Event Created Successfully</h2>
        <p>Your event has been created successfully.</p>
        <p><strong>Event ID:</strong> {$eventId}</p>
        <p><strong>Event Name:</strong> {$eventName}</p>
        <p><strong>Start Date:</strong> {$eventStartDate}</p>
        <p><strong>End Date:</strong> {$eventEndDate}</p>
    ";

    $mail->addContent("text/plain", $textContent);
    $mail->addContent("text/html", $htmlContent);

    try {
        $sendgrid = new \SendGrid($apiKey);
        $sendgrid->send($mail);
    } catch (Exception $mailError) {
        error_log("SendGrid error: " . $mailError->getMessage());
    }
}
