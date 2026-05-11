<?php
/**
 * qr-blast.php — QR Email Blast trigger.
 *
 * Sends a QR code email to one or more registered participants.
 * Uses the same email layout / style as created-event.php.
 */

require_once __DIR__ . '/../../../../include/email/mail-helpers.php';
require_once __DIR__ . '/../../../../include/email/mail-sender.php';

if (!function_exists('wrapEmailLayout')) {
    /**
     * Shared email shell — header, banner, footer.
     */
    function wrapEmailLayout(string $innerHtml): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:8px;overflow:hidden;">
<tr><td align="center" style="padding:20px;font-size:26px;font-weight:bold;color:#2d336b;">SIRIM ACE</td></tr>
<tr><td><img src="https://www.businesstoday.com.my/wp-content/uploads/2022/12/SIRIM.jpg" width="100%" style="display:block;"></td></tr>
<tr><td style="padding:30px;color:#333;">{$innerHtml}</td></tr>
<tr><td align="center" style="padding:20px;font-size:12px;color:#999;">This is an automated email from SIRIM ACE. Please do not reply.</td></tr>
</table>
</td></tr></table>
</body></html>
HTML;
    }
}

/**
 * Build the QR blast HTML email for a single participant.
 */
function buildQrBlastHtml(array $vars): string
{
    $v     = array_map('htmlspecialchars', $vars);
    $qrUrl = htmlspecialchars($vars['qr_url'] ?? '');

    // Phone / company / email rows — only shown when values are present
    $phoneRow   = ($vars['phone']   !== '') ? "<tr><td style=\"padding:6px 0;width:160px;\"><strong>Phone:</strong></td><td>{$v['phone']}</td></tr>" : '';
    $companyRow = ($vars['company'] !== '') ? "<tr><td style=\"padding:6px 0;width:160px;\"><strong>Company:</strong></td><td>{$v['company']}</td></tr>" : '';
    $emailRow   = ($vars['email']   !== '') ? "<tr><td style=\"padding:6px 0;width:160px;\"><strong>Email:</strong></td><td>{$v['email']}</td></tr>" : '';

    // Instructions block — only shown when provided
    $instructionsBlock = '';
    if (!empty(trim($vars['instructions'] ?? ''))) {
        $instructionsHtml  = nl2br($v['instructions']);
        $instructionsBlock = <<<INST
<h3 style="margin-top:25px;font-size:18px;color:#2d336b;">Instructions</h3>
<div style="background:#f9f9f9;border-left:4px solid #2d336b;padding:14px 16px;border-radius:4px;font-size:14px;line-height:1.7;color:#333;">{$instructionsHtml}</div>
INST;
    }

    $inner = <<<HTML
<h2 style="margin-top:0;color:#2f4f4f;">Your Event Registration QR Code</h2>
<p>Hello {$v['participant_name']},</p>
<p>Thank you for registering for the following event. Please present the QR code below to the staff for attendance scanning.</p>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;font-size:15px;">
<tr><td style="padding:6px 0;width:160px;"><strong>Event Name:</strong></td><td>{$v['event_name']}</td></tr>
<tr><td style="padding:6px 0;width:160px;"><strong>Event Date:</strong></td><td>{$v['event_date']}</td></tr>
<tr><td style="padding:6px 0;width:160px;"><strong>Name:</strong></td><td>{$v['participant_name']}</td></tr>
{$emailRow}
{$phoneRow}
{$companyRow}
</table>
{$instructionsBlock}
<h3 style="margin-top:25px;font-size:18px;color:#2d336b;">Your QR Code</h3>
<table cellpadding="0" cellspacing="0" align="center" style="margin:16px auto;">
<tr><td align="center" style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:12px;">
<img src="{$qrUrl}" alt="QR Code" width="200" height="200" style="display:block;">
</td></tr>
</table>
<p style="text-align:center;font-size:13px;color:#555;margin-top:8px;">Show this QR code to staff at the event entrance for attendance recording.</p>
<p style="font-size:13px;margin-top:25px;color:#777;">If you have any questions, please contact the event organiser.</p>
HTML;

    return wrapEmailLayout($inner);
}

/**
 * Send QR blast email to a single participant.
 *
 * @return array{sent: bool, reason: string}
 */
function sendQrBlastEmail(
    string $toEmail,
    string $toName,
    string $registrationId,
    string $eventId,
    string $eventName,
    string $eventStartDate,
    string $eventEndDate,
    string $phone = '',
    string $company = '',
    string $instructions = ''
): array {
    $config = resolveMailConfig();
    if (!$config) {
        return ['sent' => false, 'reason' => 'SMTP not configured.'];
    }

    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($registrationId);

    // Build event date: single date if start == end, otherwise range
    $startFormatted = '';
    $endFormatted   = '';
    $startYmd       = '';
    $endYmd         = '';
    try {
        if ($eventStartDate) {
            $dtStart        = new DateTime($eventStartDate);
            $startFormatted = $dtStart->format('d/m/Y');
            $startYmd       = $dtStart->format('Y-m-d');
        }
        if ($eventEndDate) {
            $dtEnd        = new DateTime($eventEndDate);
            $endFormatted = $dtEnd->format('d/m/Y');
            $endYmd       = $dtEnd->format('Y-m-d');
        }
    } catch (Exception $e) {
        $startFormatted = $eventStartDate;
        $endFormatted   = $eventEndDate;
    }

    $eventDate = ($startYmd && $endYmd && $startYmd === $endYmd)
        ? $startFormatted
        : trim($startFormatted . ($startFormatted && $endFormatted ? ' - ' : '') . $endFormatted);

    $vars = [
        'participant_name' => $toName ?: 'Participant',
        'email'            => $toEmail,
        'event_name'       => $eventName,
        'event_date'       => $eventDate,
        'phone'            => $phone,
        'company'          => $company,
        'instructions'     => $instructions,
        'qr_url'           => $qrUrl,
    ];

    $html = buildQrBlastHtml($vars);

    ob_start();
    sendHtmlEmail(
        $config,
        $toEmail,
        $toName ?: 'Participant',
        'QR Code – ' . $eventName,
        $html,
        'QR Blast [' . $registrationId . ']'
    );
    ob_end_clean();

    return ['sent' => true, 'reason' => ''];
}
