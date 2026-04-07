<?php
/**
 * mail-sender.php — PHPMailer transport layer.
 *
 * One job: send an HTML email via SMTP using PHPMailer.
 * Every trigger file includes this to dispatch mail.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send an HTML email via PHPMailer SMTP.
 *
 * @param array  $config   Mail config from resolveMailConfig()
 * @param string $toEmail  Recipient email
 * @param string $toName   Recipient name
 * @param string $subject  Email subject line
 * @param string $htmlBody Full HTML body
 * @param string $logLabel Label for error_log messages
 */
function sendHtmlEmail(
    array  $config,
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody,
    string $logLabel
): void {
    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        error_log('PHPMailer skipped invalid recipient for ' . $logLabel . ': ' . $toEmail);
        return;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['username'];
        $mail->Password   = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $config['port'];

        $mail->setFrom($config['fromEmail'], $config['fromName']);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        $mail->send();
        error_log('[EMAIL] Sent OK: ' . $logLabel . ' -> ' . $toEmail);
    } catch (Exception $e) {
        error_log('[EMAIL] PHPMailer error (' . $logLabel . '): ' . $mail->ErrorInfo);
    }
}
