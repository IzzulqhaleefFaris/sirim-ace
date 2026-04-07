<?php
/**
 * mail-helpers.php — Shared data-fetching & config utilities for all email triggers.
 *
 * One job: resolve SendGrid credentials, fetch user/event data from DB,
 *          and build URLs. No sending logic lives here.
 */

function resolveMailConfig(): ?array
{
    $host     = getenv('SMTP_HOST')     ?: ($_ENV['SMTP_HOST']     ?? 'smtp.gmail.com');
    $port     = getenv('SMTP_PORT')     ?: ($_ENV['SMTP_PORT']     ?? 587);
    $username = getenv('SMTP_USERNAME') ?: ($_ENV['SMTP_USERNAME'] ?? '');
    $password = getenv('SMTP_PASSWORD') ?: ($_ENV['SMTP_PASSWORD'] ?? '');

    if ($username === '' || $password === '') {
        error_log("PHPMailer skipped: missing SMTP credentials.");
        return null;
    }

    return [
        'host'      => $host,
        'port'      => (int) $port,
        'username'  => $username,
        'password'  => $password,
        'fromEmail' => getenv('SMTP_FROM_EMAIL') ?: ($_ENV['SMTP_FROM_EMAIL'] ?? $username),
        'fromName'  => getenv('SMTP_FROM_NAME')  ?: ($_ENV['SMTP_FROM_NAME']  ?? 'SIRIM Attendance'),
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
        'locationName'     => '',
        'address'          => '',
    ];

    if ($eventId === '') {
        return $result;
    }

    $stmt = $conn->prepare(
        "SELECT e.event_description, t.event_type_name,
                l.location_name, l.location_buildingName, l.location_level, l.location_room,
                l.address_line1, l.address_line2, l.address_city, l.address_postcode,
                s.state_name
         FROM att_event e
         LEFT JOIN att_event_type t ON t.event_type_id = e.event_type_id
         LEFT JOIN att_location l   ON l.location_id   = e.location_id
         LEFT JOIN att_state s      ON s.state_id      = l.state_id
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

        // Location name (building, level, room)
        $locParts = [];
        foreach (['location_name', 'location_buildingName'] as $col) {
            $val = trim((string)($row[$col] ?? ''));
            if ($val !== '') {
                $locParts[] = $val;
            }
        }
        $level = trim((string)($row['location_level'] ?? ''));
        $room  = trim((string)($row['location_room']  ?? ''));
        if ($level !== '') {
            $locParts[] = 'Level ' . $level;
        }
        if ($room !== '') {
            $locParts[] = 'Room ' . $room;
        }
        $result['locationName'] = implode(', ', $locParts);

        // Full address
        $addrParts = [];
        foreach (['address_line1', 'address_line2', 'address_city', 'address_postcode', 'state_name'] as $col) {
            $val = trim((string)($row[$col] ?? ''));
            if ($val !== '') {
                $addrParts[] = $val;
            }
        }
        $result['address'] = implode(', ', $addrParts);
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
