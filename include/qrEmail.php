<?php

require_once __DIR__ . '/email/mail-helpers.php';
require_once __DIR__ . '/email/mail-sender.php';

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

        if ($recipientEmail === '' || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            $failureReason = 'Recipient email address is empty or invalid.';
            return false;
        }

        $config = resolveMailConfig();
        if (!$config) {
            $failureReason = 'SMTP is not configured on the server.';
            return false;
        }

        // ── Fetch event details (time, venue, address) from DB ────────────
        $eventStartTime = '';
        $eventEndTime   = '';
        $eventStartDate = '';
        $eventEndDate   = '';
        $venue          = '';
        $address        = '';

        global $conn;
        if ($conn instanceof mysqli) {
            $evStmt = $conn->prepare("
                SELECT e.event_startDate, e.event_endDate,
                       e.event_startTime, e.event_endTime,
                       l.location_name, l.location_buildingName, l.location_level, l.location_room,
                       l.address_line1, l.address_line2, l.address_city, l.address_postcode,
                       s.state_name
                FROM att_event e
                LEFT JOIN att_location l ON l.location_id = e.location_id
                LEFT JOIN att_state s    ON s.state_id    = l.state_id
                WHERE e.event_id = ? LIMIT 1
            ");
            if ($evStmt) {
                $evStmt->bind_param('s', $eventId);
                $evStmt->execute();
                $evRow = $evStmt->get_result()->fetch_assoc();
                $evStmt->close();
                if ($evRow) {
                    $eventStartDate = (string)($evRow['event_startDate'] ?? '');
                    $eventEndDate   = (string)($evRow['event_endDate']   ?? '');
                    $eventStartTime = (string)($evRow['event_startTime'] ?? '');
                    $eventEndTime   = (string)($evRow['event_endTime']   ?? '');

                    $locParts = [];
                    foreach (['location_name', 'location_buildingName'] as $col) {
                        $val = trim((string)($evRow[$col] ?? ''));
                        if ($val !== '') $locParts[] = $val;
                    }
                    $level = trim((string)($evRow['location_level'] ?? ''));
                    $room  = trim((string)($evRow['location_room']  ?? ''));
                    if ($level !== '') $locParts[] = 'Level ' . $level;
                    if ($room  !== '') $locParts[] = 'Room '  . $room;
                    $venue = implode(', ', $locParts);

                    $addrParts = [];
                    foreach (['address_line1', 'address_line2', 'address_city', 'address_postcode', 'state_name'] as $col) {
                        $val = trim((string)($evRow[$col] ?? ''));
                        if ($val !== '') $addrParts[] = $val;
                    }
                    $address = implode(', ', $addrParts);
                }
            }
        }

        // ── Format date ───────────────────────────────────────────────────
        $startFormatted = '';
        $endFormatted   = '';
        $startYmd       = '';
        $endYmd         = '';
        try {
            if ($eventStartDate) {
                $dt             = new DateTime($eventStartDate);
                $startFormatted = $dt->format('d/m/Y');
                $startYmd       = $dt->format('Y-m-d');
            }
            if ($eventEndDate) {
                $dt           = new DateTime($eventEndDate);
                $endFormatted = $dt->format('d/m/Y');
                $endYmd       = $dt->format('Y-m-d');
            }
        } catch (Exception $e) {
            $startFormatted = $eventStartDate;
            $endFormatted   = $eventEndDate;
        }
        $dateStr = ($startYmd && $endYmd && $startYmd === $endYmd)
            ? $startFormatted
            : trim($startFormatted . ($startFormatted && $endFormatted ? ' - ' : '') . $endFormatted);

        // ── Format time ───────────────────────────────────────────────────
        $timeStr = '';
        try {
            $tStart = $eventStartTime ? (new DateTime($eventStartTime))->format('h:i A') : '';
            $tEnd   = $eventEndTime   ? (new DateTime($eventEndTime))->format('h:i A')   : '';
            if ($tStart && $tEnd) {
                $timeStr = $tStart . ' – ' . $tEnd;
            } elseif ($tStart) {
                $timeStr = $tStart;
            }
        } catch (Exception $e) {
            $timeStr = trim($eventStartTime . ($eventEndTime ? ' – ' . $eventEndTime : ''));
        }

        // ── Build HTML rows (only shown when values present) ──────────────
        $eName  = htmlspecialchars($eventName);
        $rName  = htmlspecialchars($recipientName ?: 'Participant');
        $rEmail = htmlspecialchars($recipientEmail);
        $dateRow  = $dateStr  !== '' ? "<tr><td style=\"padding:6px 0;width:160px;\"><strong>Event Date:</strong></td><td>" . htmlspecialchars($dateStr)  . "</td></tr>" : '';
        $timeRow  = $timeStr  !== '' ? "<tr><td style=\"padding:6px 0;width:160px;\"><strong>Time:</strong></td><td>"       . htmlspecialchars($timeStr)  . "</td></tr>" : '';
        $venueRow = $venue    !== '' ? "<tr><td style=\"padding:6px 0;width:160px;\"><strong>Venue:</strong></td><td>"      . htmlspecialchars($venue)    . "</td></tr>" : '';
        $addrRow  = $address  !== '' ? "<tr><td style=\"padding:6px 0;width:160px;\"><strong>Address:</strong></td><td>"   . htmlspecialchars($address)  . "</td></tr>" : '';

        $qrUrl = buildQrImageUrl($registrationId);

        $inner = <<<HTML
<h2 style="margin-top:0;color:#2f4f4f;">Registration Successful!</h2>
<p>Hello {$rName},</p>
<p>Thank you for registering. Please present the QR code below to staff for attendance scanning.</p>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;font-size:15px;">
<tr><td style="padding:6px 0;width:160px;"><strong>Event Name:</strong></td><td>{$eName}</td></tr>
{$dateRow}
{$timeRow}
{$venueRow}
{$addrRow}
<tr><td style="padding:6px 0;width:160px;"><strong>Name:</strong></td><td>{$rName}</td></tr>
<tr><td style="padding:6px 0;width:160px;"><strong>Email:</strong></td><td>{$rEmail}</td></tr>
</table>
<h3 style="margin-top:25px;font-size:18px;color:#2d336b;">Your QR Code</h3>
<table cellpadding="0" cellspacing="0" align="center" style="margin:16px auto;">
<tr><td align="center" style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:12px;">
<img src="cid:qr_code_img" alt="QR Code" width="200" height="200" style="display:block;">
</td></tr>
</table>
<p style="text-align:center;font-size:13px;color:#555;margin-top:8px;">Show this QR code to staff at the event entrance for attendance recording.</p>
<p style="font-size:13px;margin-top:25px;color:#777;">If you have any questions, please contact the event organiser.</p>
HTML;

        // ── Wrap in email shell ───────────────────────────────────────────
        $bannerPath = __DIR__ . '/../images/custom/Sirim-50.jpg';
        $bannerCid  = file_exists($bannerPath) ? 'cid:banner_img' : 'https://www.businesstoday.com.my/wp-content/uploads/2022/12/SIRIM.jpg';
        $htmlBody   = <<<SHELL
<!DOCTYPE html>
<html>
<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:8px;overflow:hidden;">
<tr><td align="center" style="padding:20px;font-size:26px;font-weight:bold;color:#2d336b;">SIRIM ACE</td></tr>
<tr><td><img src="{$bannerCid}" width="100%" style="display:block;"></td></tr>
<tr><td style="padding:30px;color:#333;">{$inner}</td></tr>
<tr><td align="center" style="padding:20px;font-size:12px;color:#999;">This is an automated email from SIRIM ACE. Please do not reply.</td></tr>
</table>
</td></tr></table>
</body></html>
SHELL;

        // ── Build CID embedded images ─────────────────────────────────────
        $embeddedImages = [];
        if (file_exists($bannerPath)) {
            $embeddedImages[] = [
                'path' => $bannerPath,
                'cid'  => 'banner_img',
                'name' => 'Sirim-50.jpg',
                'type' => 'image/jpeg',
            ];
        }
        // QR code: fetch from external API and embed as CID
        $qrData = @file_get_contents($qrUrl);
        if ($qrData !== false) {
            $tmpQr = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
            file_put_contents($tmpQr, $qrData);
            $embeddedImages[] = [
                'path' => $tmpQr,
                'cid'  => 'qr_code_img',
                'name' => 'qr-code.png',
                'type' => 'image/png',
            ];
        } else {
            // Fallback: external URL in src
            $htmlBody = str_replace('cid:qr_code_img', htmlspecialchars($qrUrl), $htmlBody);
        }

        // ── Send ──────────────────────────────────────────────────────────
        try {
            sendHtmlEmail(
                $config,
                $recipientEmail,
                $recipientName ?: 'Participant',
                'Registration Confirmed – ' . $eventName,
                $htmlBody,
                'RegistrationQR [' . $registrationId . ']',
                $embeddedImages
            );
        } catch (Exception $e) {
            $failureReason = $e->getMessage();
            // clean up temp QR file
            if (!empty($tmpQr) && file_exists($tmpQr)) @unlink($tmpQr);
            return false;
        }

        // clean up temp QR file
        if (!empty($tmpQr) && file_exists($tmpQr)) @unlink($tmpQr);

        return true;
    }
}
