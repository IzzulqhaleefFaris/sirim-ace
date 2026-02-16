<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('ROLE_ORGANISER')) {
    define('ROLE_ORGANISER', 1);
}
if (!defined('ROLE_PARTICIPANT')) {
    define('ROLE_PARTICIPANT', 2);
}
if (!defined('ROLE_ADMIN')) {
    define('ROLE_ADMIN', 3);
}

if (!function_exists('current_role')) {
    function current_role(): int
    {
        return (int)($_SESSION['roleId'] ?? 0);
    }
}

if (!function_exists('require_login')) {
    function require_login(): void
    {
        if (!isset($_SESSION['userId'])) {
            header('Location: /attendance');
            exit;
        }
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        return current_role() === ROLE_ADMIN;
    }
}

if (!function_exists('is_organiser')) {
    function is_organiser(): bool
    {
        return current_role() === ROLE_ORGANISER;
    }
}

if (!function_exists('is_participant')) {
    function is_participant(): bool
    {
        return current_role() === ROLE_PARTICIPANT;
    }
}

if (!function_exists('can_manage_events')) {
    function can_manage_events(): bool
    {
        return is_admin() || is_organiser();
    }
}

if (!function_exists('can_participate')) {
    function can_participate(): bool
    {
        return is_admin() || is_organiser() || is_participant();
    }
}

if (!function_exists('require_manage_events')) {
    function require_manage_events(): void
    {
        require_login();
        if (!can_manage_events()) {
            header('Location: /attendance');
            exit;
        }
    }
}

if (!function_exists('require_admin')) {
    function require_admin(): void
    {
        require_login();
        if (!is_admin()) {
            header('Location: /attendance');
            exit;
        }
    }
}

if (!function_exists('ensure_event_owner_or_admin')) {
    function ensure_event_owner_or_admin(mysqli $conn, string $eventId, ?string $userId = null): bool
    {
        if (is_admin()) {
            return true;
        }

        if (!has_event_owner_column($conn)) {
            return is_organiser();
        }

        $userId = $userId ?? ($_SESSION['userId'] ?? '');
        if ($userId === '') {
            return false;
        }

        $stmt = $conn->prepare("SELECT 1 FROM att_event WHERE event_id = ? AND event_owner_id = ? LIMIT 1");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ss", $eventId, $userId);
        $stmt->execute();
        $stmt->store_result();
        $ok = $stmt->num_rows > 0;
        $stmt->close();

        return $ok;
    }
}

if (!function_exists('require_event_owner_or_admin')) {
    function require_event_owner_or_admin(mysqli $conn, string $eventId, string $redirect = '/attendance'): void
    {
        if (!ensure_event_owner_or_admin($conn, $eventId)) {
            $_SESSION['msg'] = [
                'type' => 'danger',
                'text' => 'Anda tidak mempunyai akses kepada event ini.'
            ];
            header("Location: {$redirect}");
            exit;
        }
    }
}

if (!function_exists('has_event_owner_column')) {
    function has_event_owner_column(mysqli $conn): bool
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $stmt = $conn->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'att_event' AND COLUMN_NAME = 'event_owner_id' LIMIT 1");
        if (!$stmt) {
            $cached = false;
            return $cached;
        }

        $stmt->execute();
        $stmt->store_result();
        $cached = $stmt->num_rows > 0;
        $stmt->close();

        return $cached;
    }
}

