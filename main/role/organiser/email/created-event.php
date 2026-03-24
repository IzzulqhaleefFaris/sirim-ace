<?php
require_once __DIR__ . '/../../../../vendor/autoload.php';

use SendGrid\Mail\Mail;

function sendEventCreatedEmail(mysqli $conn, string $picUserId, string $eventOwnerId, string $eventId, string $eventName, string $eventStartDate, string $eventEndDate): void
{
    $picUser = getUserBasicById($conn, $picUserId);
    $ownerUser = getUserBasicById($conn, $eventOwnerId);

    if (empty($ownerUser['email'])) {
        $sessionEmail = $_SESSION['email'] ?? null;
        $sessionName = $_SESSION['nama'] ?? 'User';
        if (!empty($sessionEmail)) {
            error_log('SendGrid owner fallback used from session for event ' . $eventId . '.');
            $ownerUser = [
                'nama' => $sessionName,
                'email' => $sessionEmail
            ];
        }
    }

    $apiKey = getenv('SENDGRID_API_KEY');
    if ($apiKey === false || $apiKey === '') {
        $envKey = $_ENV['SENDGRID_API_KEY'] ?? '';
        $apiKey = $envKey !== '' ? $envKey : null;
    }

    if (!$apiKey) {
        error_log("SendGrid skipped: missing api key.");
        return;
    }

    $fromEmail = getenv('SENDGRID_FROM_EMAIL') ?: 'izzulqhaleef@sirim.my';
    $fromName = getenv('SENDGRID_FROM_NAME') ?: 'SIRIM Attendance';

    $emailContext = [
        'eventId' => $eventId,
        'eventName' => $eventName,
        'eventStartDate' => $eventStartDate,
        'eventEndDate' => $eventEndDate
    ];

    // Email 1: Notify event creator
    if (!empty($ownerUser['email'])) {
        $ownerMessage = buildEventCreatedOwnerMessage($emailContext);

        sendSingleEmail(
            $apiKey,
            $fromEmail,
            $fromName,
            $ownerUser['email'],
            $ownerUser['nama'] ?? 'User',
            'Event Created: ' . $eventName,
            $ownerMessage['text'],
            $ownerMessage['html']
        );
    }

    // Email 2: Notify PIC assignment
    if (!empty($picUser['email'])) {
        $picMessage = buildEventCreatedPicMessage($emailContext);

        sendSingleEmail(
            $apiKey,
            $fromEmail,
            $fromName,
            $picUser['email'],
            $picUser['nama'] ?? 'PIC',
            'PIC Notification: ' . $eventName,
            $picMessage['text'],
            $picMessage['html']
        );
    }

    if (empty($ownerUser['email']) || empty($picUser['email'])) {
        error_log("SendGrid partially skipped: missing owner or PIC recipient email.");
    }
}

function getUserBasicById(mysqli $conn, string $userId): array
{
    $result = [
        'nama' => null,
        'email' => null
    ];

    if ($userId === '') {
        return $result;
    }

    $stmt = $conn->prepare("SELECT nama, email FROM user WHERE userId = ? LIMIT 1");
    if (!$stmt) {
        return $result;
    }

    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && ($row = $res->fetch_assoc())) {
        $result['nama'] = $row['nama'] ?? null;
        $result['email'] = $row['email'] ?? null;
    }
    $stmt->close();

    return $result;
}

function buildEventCreatedOwnerMessage(array $ctx): array
{
    $eventId = (string)($ctx['eventId'] ?? '');
    $eventName = (string)($ctx['eventName'] ?? 'Event');
    $eventStartDate = (string)($ctx['eventStartDate'] ?? '');
    $eventEndDate = (string)($ctx['eventEndDate'] ?? '');

    return [
        'text' => "Your event has been created successfully.\n" .
            "Event ID: {$eventId}\n" .
            "Event Name: {$eventName}\n" .
            "Start Date: {$eventStartDate}\n" .
            "End Date: {$eventEndDate}\n",
        'html' => "
            <h2>Event Created Successfully</h2>
            <p>Your event has been created in the system.</p>
            <p><strong>Event ID:</strong> {$eventId}</p>
            <p><strong>Event Name:</strong> {$eventName}</p>
            <p><strong>Start Date:</strong> {$eventStartDate}</p>
            <p><strong>End Date:</strong> {$eventEndDate}</p>
        "
    ];
}

function buildEventCreatedPicMessage(array $ctx): array
{
    $eventId = (string)($ctx['eventId'] ?? '');
    $eventName = (string)($ctx['eventName'] ?? 'Event');
    $eventStartDate = (string)($ctx['eventStartDate'] ?? '');
    $eventEndDate = (string)($ctx['eventEndDate'] ?? '');

    return [
        'text' => "You have been assigned as Person In-Charge (PIC) for this event.\n" .
            "Event ID: {$eventId}\n" .
            "Event Name: {$eventName}\n" .
            "Start Date: {$eventStartDate}\n" .
            "End Date: {$eventEndDate}\n",
        'html' => "
            <h2>Person In-Charge Assignment</h2>
            <p>You have been assigned as <strong>Person In-Charge (PIC)</strong> for the following event.</p>
            <p><strong>Event ID:</strong> {$eventId}</p>
            <p><strong>Event Name:</strong> {$eventName}</p>
            <p><strong>Start Date:</strong> {$eventStartDate}</p>
            <p><strong>End Date:</strong> {$eventEndDate}</p>
        "
    ];
}

function sendSingleEmail(
    string $apiKey,
    string $fromEmail,
    string $fromName,
    string $toEmail,
    string $toName,
    string $subject,
    string $textContent,
    string $htmlContent
): void {
    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        error_log('SendGrid skipped invalid recipient email for subject ' . $subject . ': ' . $toEmail);
        return;
    }

    $mail = new Mail();
    $mail->setFrom($fromEmail, $fromName);
    $mail->setSubject($subject);
    $mail->addTo($toEmail, $toName);
    $mail->addContent("text/plain", $textContent);
    $mail->addContent("text/html", $htmlContent);

    try {
        $sendgrid = new \SendGrid($apiKey);
        $response = $sendgrid->send($mail);
        if ($response->statusCode() >= 400) {
            error_log('SendGrid rejected (' . $response->statusCode() . ') for subject ' . $subject . '. Body: ' . $response->body());
        }
    } catch (Exception $mailError) {
        error_log("SendGrid error ({$subject}): " . $mailError->getMessage());
    }
}
