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
 * @param array  $config         Mail config from resolveMailConfig()
 * @param string $toEmail        Recipient email
 * @param string $toName         Recipient name
 * @param string $subject        Email subject line
 * @param string $htmlBody       Full HTML body
 * @param string $logLabel       Label for error_log messages
 * @param array  $embeddedImages Optional list of images to embed as CID.
 *                               Each entry: ['path'=>string, 'cid'=>string, 'name'=>string, 'type'=>string]
 */
function sendHtmlEmail(
    array  $config,
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody,
    string $logLabel,
    array  $embeddedImages = []
): void {
    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        error_log('PHPMailer skipped invalid recipient for ' . $logLabel . ': ' . $toEmail);
        return;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->CharSet   = PHPMailer::CHARSET_UTF8;
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['username'];
        $mail->Password   = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $config['port'];

        $mail->setFrom($config['fromEmail'], $config['fromName']);
        $mail->addReplyTo($config['fromEmail'], $config['fromName']);
        $mail->addAddress($toEmail, $toName);
        $mail->XMailer = 'SIRIM ACE Mailer';

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        foreach ($embeddedImages as $img) {
            if (!empty($img['path']) && file_exists($img['path'])) {
                $mail->addEmbeddedImage(
                    $img['path'],
                    $img['cid']  ?? basename($img['path']),
                    $img['name'] ?? basename($img['path']),
                    'base64',
                    $img['type'] ?? 'image/jpeg'
                );
            }
        }

        $mail->send();
        error_log('[EMAIL] Sent OK: ' . $logLabel . ' -> ' . $toEmail);
    } catch (Exception $e) {
        error_log('[EMAIL] PHPMailer error (' . $logLabel . '): ' . $mail->ErrorInfo);
    }
}
