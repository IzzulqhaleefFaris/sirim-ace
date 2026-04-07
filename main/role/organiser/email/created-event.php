<?php
/**
 * created-event.php — EVENT_CREATED trigger.
 *
 * Sends email to the event owner and the PIC when an event is created.
 * Uses PHPMailer with inline HTML templates.
 */

require_once __DIR__ . '/../../../../include/email/mail-helpers.php';
require_once __DIR__ . '/../../../../include/email/mail-sender.php';

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

/**
 * Build the "Event Created" HTML email for the event OWNER.
 */
function buildOwnerEventHtml(array $vars): string
{
    $v = array_map('htmlspecialchars', $vars);

    $inner = <<<HTML
<h2 style="margin-top:0;color:#2f4f4f;">Event Created Successfully</h2>
<p>Hello {$v['user_name']},</p>
<p>Your event has been successfully created in the <strong>SIRIM ACE System</strong>.</p>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;font-size:15px;">
<tr><td style="padding:6px 0;width:150px;"><strong>Event Name:</strong></td><td>{$v['event_name']}</td></tr>
<tr><td style="padding:6px 0;width:150px;"><strong>Event ID:</strong></td><td>{$v['event_id']}</td></tr>
<tr><td style="padding:6px 0;width:150px;"><strong>Event Type:</strong></td><td>{$v['event_type']}</td></tr>
</table>
<h3 style="margin-top:25px;font-size:18px;color:#2d336b;">Schedule</h3>
<table width="100%" cellpadding="0" cellspacing="0" style="font-size:15px;">
<tr><td style="padding:6px 0;width:150px;"><strong>Start:</strong></td><td>{$v['start_datetime']}</td></tr>
<tr><td style="padding:6px 0;width:150px;"><strong>End:</strong></td><td>{$v['end_datetime']}</td></tr>
</table>
<h3 style="margin-top:25px;font-size:18px;color:#2d336b;">Person In-Charge</h3>
<p style="margin:0;">{$v['pic_name']} ({$v['pic_email']})</p>
<h3 style="margin-top:25px;font-size:18px;color:#2d336b;">Location &amp; Address</h3>
<p style="margin:0;">{$v['location_name']}</p>
<p style="margin:4px 0 0 0;color:#555;">{$v['address']}</p>
<table cellpadding="0" cellspacing="0" align="center" style="margin-top:30px;">
<tr><td align="center" bgcolor="#2d336b" style="border-radius:25px;">
<a href="{$v['event_url']}" style="display:inline-block;padding:12px 25px;font-size:16px;color:#fff;text-decoration:none;">View Event</a>
</td></tr></table>
<p style="font-size:13px;margin-top:25px;color:#777;">If you did not create this event, please contact the system administrator.</p>
HTML;

    return wrapEmailLayout($inner);
}

/**
 * Build the "PIC Assigned" HTML email for the Person In-Charge.
 */
function buildPicAssignedHtml(array $vars): string
{
    $v = array_map('htmlspecialchars', $vars);

    $inner = <<<HTML
<h2 style="margin-top:0;color:#2f4f4f;">You Have Been Assigned as Person In-Charge</h2>
<p>Hello {$v['user_name']},</p>
<p>You have been assigned as the <strong>Person In-Charge (PIC)</strong> for the following event in the <strong>SIRIM ACE System</strong>.</p>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;font-size:15px;">
<tr><td style="padding:6px 0;width:150px;"><strong>Event Name:</strong></td><td>{$v['event_name']}</td></tr>
<tr><td style="padding:6px 0;width:150px;"><strong>Event ID:</strong></td><td>{$v['event_id']}</td></tr>
<tr><td style="padding:6px 0;width:150px;"><strong>Event Type:</strong></td><td>{$v['event_type']}</td></tr>
</table>
<h3 style="margin-top:25px;font-size:18px;color:#2d336b;">Schedule</h3>
<table width="100%" cellpadding="0" cellspacing="0" style="font-size:15px;">
<tr><td style="padding:6px 0;width:150px;"><strong>Start:</strong></td><td>{$v['start_datetime']}</td></tr>
<tr><td style="padding:6px 0;width:150px;"><strong>End:</strong></td><td>{$v['end_datetime']}</td></tr>
</table>
<h3 style="margin-top:25px;font-size:18px;color:#2d336b;">Organised By</h3>
<p style="margin:0;">{$v['organiser_name']} ({$v['organiser_email']})</p>
<h3 style="margin-top:25px;font-size:18px;color:#2d336b;">Location &amp; Address</h3>
<p style="margin:0;">{$v['location_name']}</p>
<p style="margin:4px 0 0 0;color:#555;">{$v['address']}</p>
<table cellpadding="0" cellspacing="0" align="center" style="margin-top:30px;">
<tr><td align="center" bgcolor="#2d336b" style="border-radius:25px;">
<a href="{$v['event_url']}" style="display:inline-block;padding:12px 25px;font-size:16px;color:#fff;text-decoration:none;">View Event</a>
</td></tr></table>
<p style="font-size:13px;margin-top:25px;color:#777;">If you believe this was a mistake, please contact the event organiser.</p>
HTML;

    return wrapEmailLayout($inner);
}

function sendEventCreatedEmail(
    mysqli $conn,
    string $picUserId,
    string $eventOwnerId,
    string $eventId,
    string $eventName,
    string $eventStartDate,
    string $eventEndDate
): void {
    error_log("[EMAIL] sendEventCreatedEmail called for event: {$eventId}, owner: {$eventOwnerId}, pic: {$picUserId}");

    // ── 1. Resolve mail config (SMTP credentials) ──
    $config = resolveMailConfig();
    if (!$config) {
        error_log("[EMAIL] resolveMailConfig returned null — no SMTP credentials.");
        return;
    }
    error_log("[EMAIL] Mail config resolved OK.");

    // ── 2. Resolve recipients ──
    $ownerUser = getUserBasicById($conn, $eventOwnerId);
    $picUser   = getUserBasicById($conn, $picUserId);
    error_log("[EMAIL] Owner: " . ($ownerUser['email'] ?? 'NONE') . ", PIC: " . ($picUser['email'] ?? 'NONE'));

    if (empty($ownerUser['email'])) {
        $sessionEmail = $_SESSION['email'] ?? null;
        $sessionName  = $_SESSION['nama']  ?? 'User';
        if (!empty($sessionEmail)) {
            error_log('PHPMailer owner fallback used from session for event ' . $eventId . '.');
            $ownerUser = ['nama' => $sessionName, 'email' => $sessionEmail];
        }
    }

    // ── 3. Fetch event metadata ──
    $meta      = getEventMetadata($conn, $eventId);
    $ownerName = $ownerUser['nama'] ?? 'User';
    $picName   = $picUser['nama']   ?? 'PIC';
    $picEmail  = $picUser['email']  ?? '';
    $eventUrl  = buildEventUrl($eventId, 'organiser');

    // Format dates to dd/mm/yyyy
    $startFormatted = date('d/m/Y', strtotime($eventStartDate));
    $endFormatted   = date('d/m/Y', strtotime($eventEndDate));

    // ── 4. Send to event owner ──
    if (!empty($ownerUser['email'])) {
        $ownerVars = [
            'user_name'        => $ownerName,
            'event_name'       => $eventName,
            'event_id'         => $eventId,
            'event_type'       => $meta['eventType'],
            'start_datetime'   => $startFormatted,
            'end_datetime'     => $endFormatted,
            'pic_name'         => $picName,
            'pic_email'        => $picEmail,
            'owner_name'       => $ownerName,
            'owner_email'      => $ownerUser['email'] ?? '',
            'location_name'    => $meta['locationName'],
            'address'          => $meta['address'],
            'event_url'        => $eventUrl,
        ];
        $html = buildOwnerEventHtml($ownerVars);
        sendHtmlEmail($config, $ownerUser['email'], $ownerName, 'Event Created: ' . $eventName, $html, 'Event Created (owner): ' . $eventName);
    }

    // ── 5. Send to PIC ──
    if (!empty($picUser['email'])) {
        $picVars = [
            'user_name'        => $picName,
            'event_name'       => $eventName,
            'event_id'         => $eventId,
            'event_type'       => $meta['eventType'],
            'start_datetime'   => $startFormatted,
            'end_datetime'     => $endFormatted,
            'organiser_name'   => $ownerName,
            'organiser_email'  => $ownerUser['email'] ?? '',
            'location_name'    => $meta['locationName'],
            'address'          => $meta['address'],
            'event_url'        => $eventUrl,
        ];
        $html = buildPicAssignedHtml($picVars);
        sendHtmlEmail($config, $picUser['email'], $picName, 'You are assigned as PIC: ' . $eventName, $html, 'PIC Assigned: ' . $eventName);
    }

    // ── 6. Log gaps ──
    if (empty($ownerUser['email']) || empty($picUser['email'])) {
        error_log("PHPMailer partially skipped: missing owner or PIC recipient email for event " . $eventId);
    }
}
