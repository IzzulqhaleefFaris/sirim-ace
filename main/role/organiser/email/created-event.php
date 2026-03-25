<?php
/**
 * created-event.php — EVENT_CREATED trigger.
 *
 * One job: when an event is created, resolve recipients + template data
 *          and dispatch emails via the shared sender.
 *
 * Owner Template ID:  d-f968171212aa45c592d802b97b78309b
 *   {{user_name}}, {{event_name}}, {{event_id}}, {{event_type}},
 *   {{start_datetime}}, {{end_datetime}}, {{pic_name}}, {{email}},
 *   {{location_summary}}, {{event_url}}
 *
 * PIC Template ID:    d-37029ad739c2418384aca097e0cddcda
 *   {{user_name}}, {{event_name}}, {{event_description}}, {{event_type}},
 *   {{start_datetime}}, {{end_datetime}}, {{location_summary}}
 */

require_once __DIR__ . '/../../../../include/email/mail-helpers.php';
require_once __DIR__ . '/../../../../include/email/mail-sender.php';

define('TEMPLATE_EVENT_CREATED', 'd-f968171212aa45c592d802b97b78309b');
define('TEMPLATE_PIC_ASSIGNED',  'd-37029ad739c2418384aca097e0cddcda');

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

    // ── 1. Resolve mail config (API key, from address) ──
    $config = resolveMailConfig();
    if (!$config) {
        error_log("[EMAIL] resolveMailConfig returned null — no API key.");
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
            error_log('SendGrid owner fallback used from session for event ' . $eventId . '.');
            $ownerUser = ['nama' => $sessionName, 'email' => $sessionEmail];
        }
    }

    // ── 3. Fetch event metadata for template variables ──
    $meta          = getEventMetadata($conn, $eventId);
    $ownerName     = $ownerUser['nama'] ?? 'User';
    $picName       = $picUser['nama']   ?? 'PIC';
    $eventUrl      = buildEventUrl($eventId, 'organiser');

    // ── 4. Send to event owner ──
    if (!empty($ownerUser['email'])) {
        sendTemplateEmail(
            $config['apiKey'],
            $config['fromEmail'],
            $config['fromName'],
            $ownerUser['email'],
            $ownerName,
            TEMPLATE_EVENT_CREATED,
            [
                'user_name'        => $ownerName,
                'event_name'       => $eventName,
                'event_id'         => $eventId,
                'event_type'       => $meta['eventType'],
                'start_datetime'   => $eventStartDate,
                'end_datetime'     => $eventEndDate,
                'pic_name'         => $picName,
                'email'            => $picUser['email'] ?? '',
                'location_summary' => $meta['locationSummary'],
                'event_url'        => $eventUrl,
            ],
            'Event Created (owner): ' . $eventName
        );
    }

    // ── 5. Send to PIC ──
    if (!empty($picUser['email'])) {
        sendTemplateEmail(
            $config['apiKey'],
            $config['fromEmail'],
            $config['fromName'],
            $picUser['email'],
            $picName,
            TEMPLATE_PIC_ASSIGNED,
            [
                'user_name'          => $picName,
                'event_name'         => $eventName,
                'event_description'  => $meta['eventDescription'],
                'event_type'         => $meta['eventType'],
                'start_datetime'     => $eventStartDate,
                'end_datetime'       => $eventEndDate,
                'location_summary'   => $meta['locationSummary'],
            ],
            'PIC Assigned: ' . $eventName
        );
    }

    // ── 6. Log gaps ──
    if (empty($ownerUser['email']) || empty($picUser['email'])) {
        error_log("SendGrid partially skipped: missing owner or PIC recipient email for event " . $eventId);
    }
}
