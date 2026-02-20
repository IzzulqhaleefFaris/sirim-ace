<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use SendGrid\Mail\Mail;

if (!function_exists('buildQrImageUrl')) {
    function buildQrImageUrl(string $registrationId): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=' . urlencode($registrationId);
    }
}

if (!function_exists('sendRegistrationQrEmail')) {
    function sendRegistrationQrEmail(
        string $recipientEmail,
        string $recipientName,
        string $registrationId,
        string $eventId,
        string $eventName,
        ?string &$failureReason = null
    ): bool {
        $failureReason = null;

        $apiKey = getenv('SENDGRID_API_KEY');
        if ($apiKey === false || $apiKey === '') {
            $envKey = $_ENV['SENDGRID_API_KEY'] ?? '';
            $apiKey = $envKey !== '' ? $envKey : null;
        }

        if (!$apiKey) {
            $failureReason = 'SENDGRID_API_KEY belum diset pada server.';
            return false;
        }

        if (!$recipientEmail) {
            $failureReason = 'Alamat e-mel penerima kosong.';
            return false;
        }

        $fromEmail = getenv('SENDGRID_FROM_EMAIL') ?: 'izzulqhaleef@sirim.my';
        $fromName = getenv('SENDGRID_FROM_NAME') ?: 'SIRIM Attendance';

        if (!$fromEmail) {
            $failureReason = 'SENDGRID_FROM_EMAIL belum diset.';
            return false;
        }

        $qrUrl = buildQrImageUrl($registrationId);

        $mail = new Mail();
        $mail->setFrom($fromEmail, $fromName);
        $mail->setSubject('QR Attendance - ' . $eventName);
        $mail->addTo($recipientEmail, $recipientName !== '' ? $recipientName : 'Participant');

        $textContent = "Pendaftaran berjaya.\n" .
            "Event: {$eventName}\n" .
            "Registration ID: {$registrationId}\n" .
            "QR Link: {$qrUrl}\n";

        $htmlContent = "
            <h3>Pendaftaran Berjaya</h3>
            <p><strong>Event:</strong> {$eventName} ({$eventId})</p>
            <p><strong>Registration ID:</strong> {$registrationId}</p>
            <p>Sila tunjukkan QR ini semasa kehadiran event:</p>
            <p><img src=\"{$qrUrl}\" alt=\"Attendance QR\" style=\"max-width:240px;border:1px solid #ddd;padding:8px;border-radius:8px;\"></p>
            <p>Jika imej tidak dipaparkan, guna pautan ini: <a href=\"{$qrUrl}\">Lihat QR</a></p>
        ";

        $mail->addContent('text/plain', $textContent);
        $mail->addContent('text/html', $htmlContent);

        try {
            $sendgrid = new \SendGrid($apiKey);
            $response = $sendgrid->send($mail);

            if ($response->statusCode() >= 400) {
                $failureReason = 'SendGrid menolak permintaan (HTTP ' . $response->statusCode() . ').';
                error_log('SendGrid registration email rejected: HTTP ' . $response->statusCode() . ' | Body: ' . $response->body());
                return false;
            }

            return true;
        } catch (Exception $mailError) {
            error_log('SendGrid registration email error: ' . $mailError->getMessage());
            $failureReason = $mailError->getMessage();
            return false;
        }
    }
}
