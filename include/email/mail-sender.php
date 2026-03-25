<?php
/**
 * mail-sender.php — SendGrid transport layer.
 *
 * One job: send an email via a SendGrid Dynamic Template.
 * Every trigger file includes this to dispatch mail.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use SendGrid\Mail\Mail;

function sendTemplateEmail(
    string $apiKey,
    string $fromEmail,
    string $fromName,
    string $toEmail,
    string $toName,
    string $templateId,
    array $dynamicData,
    string $logSubject
): void {
    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        error_log('SendGrid skipped invalid recipient email for template ' . $templateId . ': ' . $toEmail);
        return;
    }

    $mail = new Mail();
    $mail->setFrom($fromEmail, $fromName);
    $mail->addTo($toEmail, $toName);
    $mail->setTemplateId($templateId);
    $mail->addDynamicTemplateDatas($dynamicData);

    try {
        $sendgrid = new \SendGrid($apiKey);
        $response = $sendgrid->send($mail);
        if ($response->statusCode() >= 400) {
            error_log('SendGrid template rejected (' . $response->statusCode() . ') for ' . $logSubject . '. Body: ' . $response->body());
        }
    } catch (Exception $mailError) {
        error_log('SendGrid template error (' . $logSubject . '): ' . $mailError->getMessage());
    }
}
