<?php
/**
 * mail-helpers.php — Shared data-fetching & config utilities for all email triggers.
 *
 * One job: resolve SendGrid credentials, fetch user/event data from DB,
 *          and build URLs. No sending logic lives here.
 */

function resolveMailConfig(): ?array
{
    $apiKey = getenv('SENDGRID_API_KEY');
    if ($apiKey === false || $apiKey === '') {
        $envKey = $_ENV['SENDGRID_API_KEY'] ?? '';
        $apiKey = $envKey !== '' ? $envKey : null;
    }

    if (!$apiKey) {
        error_log("SendGrid skipped: missing api key.");
        return null;
    }

    return [
        'apiKey'    => $apiKey,
        'fromEmail' => getenv('SENDGRID_FROM_EMAIL') ?: 'izzulqhaleef@sirim.my',
        'fromName'  => getenv('SENDGRID_FROM_NAME')  ?: 'SIRIM Attendance',
    ];
}

function getUserBasicById(mysqli $conn, string $userId): array
{
    $result = [
        'nama'  => null,
        'email' => null,
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
        $result['nama']  = $row['nama']  ?? null;
        $result['email'] = $row['email'] ?? null;
    }
    $stmt->close();

    return $result;
}

function getEventMetadata(mysqli $conn, string $eventId): array
{
    $result = [
        'eventType'        => '',
        'eventDescription' => '',
        'locationSummary'  => '',
    ];

    if ($eventId === '') {
        return $result;
    }

    $stmt = $conn->prepare(
        "SELECT e.event_description, t.event_type_name,
                l.location_name, l.location_buildingName, l.location_level, l.location_room
         FROM att_event e
         LEFT JOIN att_event_type t ON t.event_type_id = e.event_type_id
         LEFT JOIN att_location l   ON l.location_id   = e.location_id
         WHERE e.event_id = ?
         LIMIT 1"
    );

    if (!$stmt) {
        return $result;
    }

    $stmt->bind_param("s", $eventId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && ($row = $res->fetch_assoc())) {
        $result['eventType'] = (string)($row['event_type_name'] ?? '');
        $result['eventDescription'] = (string)($row['event_description'] ?? '');

        $parts = [];
        foreach (['location_name', 'location_buildingName'] as $col) {
            $val = trim((string)($row[$col] ?? ''));
            if ($val !== '') {
                $parts[] = $val;
            }
        }
        $level = trim((string)($row['location_level'] ?? ''));
        $room  = trim((string)($row['location_room']  ?? ''));
        if ($level !== '') {
            $parts[] = 'Level ' . $level;
        }
        if ($room !== '') {
            $parts[] = 'Room ' . $room;
        }

        $result['locationSummary'] = implode(', ', $parts);
    }

    $stmt->close();
    return $result;
}

function buildEventUrl(string $eventId, string $role = 'organiser'): string
{
    $configuredBaseUrl = trim((string)(getenv('APP_URL') ?: ''));
    if ($configuredBaseUrl !== '') {
        return rtrim($configuredBaseUrl, '/') . '/main/role/' . $role . '/event-view.php?id=' . urlencode($eventId);
    }

    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === '') {
        return '';
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $host . '/sirimace/main/role/' . $role . '/event-view.php?id=' . urlencode($eventId);
}
