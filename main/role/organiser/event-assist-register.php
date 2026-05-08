<?php
session_start();
include "../../../include/config.php";
/** @var mysqli $conn */
include "../../../include/permissions.php";

require_manage_events();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: event-list.php");
    exit;
}

$eventId = trim($_POST['event_id'] ?? '');
$registerMode = trim($_POST['register_mode'] ?? 'staff');

if ($eventId === '') {
    $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Invalid event ID.'];
    header("Location: event-list.php");
    exit;
}

require_event_owner_or_admin($conn, $eventId, 'event-list.php');

function nextRegistrationId(mysqli $conn): string
{
    $newCode = 'REG0001';
    $codeSql = "SELECT registration_id FROM att_registration WHERE registration_id LIKE 'REG%' ORDER BY registration_id DESC LIMIT 1";
    $codeRes = $conn->query($codeSql);
    if ($codeRes && ($row = $codeRes->fetch_assoc())) {
        $lastNumber = (int) substr($row['registration_id'], 3);
        $newCode = 'REG' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }
    return $newCode;
}

function nextWalkinParticipantId(mysqli $conn): string
{
    $walkinParticipantId = 'WKI0000001';
    $pidSql = "SELECT participant_id FROM att_registration WHERE participant_id LIKE 'WKI%' ORDER BY participant_id DESC LIMIT 1";
    $pidRes = $conn->query($pidSql);
    if ($pidRes && ($rowPid = $pidRes->fetch_assoc())) {
        $lastWalkinNum = (int) substr($rowPid['participant_id'], 3);
        $walkinParticipantId = 'WKI' . str_pad($lastWalkinNum + 1, 7, '0', STR_PAD_LEFT);
    }
    return $walkinParticipantId;
}

function redirectBackToEvent(string $eventId): void
{
    header("Location: event-registration.php?id=" . urlencode($eventId));
    exit;
}

try {
    $txStarted = false;
    $conn->begin_transaction();
    $txStarted = true;

    if ($registerMode === 'staff') {
        $staffIds = $_POST['staff_ids'] ?? [];
        if (!is_array($staffIds)) {
            $staffIds = [];
        }

        $staffIds = array_values(array_unique(array_filter(array_map('trim', $staffIds), function ($v) {
            return $v !== '';
        })));

        if (count($staffIds) === 0) {
            throw new Exception('Please select at least one staff member.');
        }

        $registeredCount = 0;
        $alreadyCount = 0;
        $invalidCount = 0;

        $checkUserStmt = $conn->prepare("SELECT userId FROM user WHERE userId = ? AND status = 'A' LIMIT 1");
        $checkRegStmt = $conn->prepare("SELECT registration_id FROM att_registration WHERE event_id = ? AND participant_id = ? LIMIT 1");
        $insertStmt = $conn->prepare("INSERT INTO att_registration (registration_id, event_id, participant_id, registration_source) VALUES (?, ?, ?, 'account')");

        if (!$checkUserStmt || !$checkRegStmt || !$insertStmt) {
            throw new Exception('Server error while preparing staff registration.');
        }

        foreach ($staffIds as $staffId) {
            $checkUserStmt->bind_param("s", $staffId);
            $checkUserStmt->execute();
            $userRes = $checkUserStmt->get_result();
            if (!$userRes || $userRes->num_rows === 0) {
                $invalidCount++;
                continue;
            }

            $checkRegStmt->bind_param("ss", $eventId, $staffId);
            $checkRegStmt->execute();
            $regRes = $checkRegStmt->get_result();
            if ($regRes && $regRes->num_rows > 0) {
                $alreadyCount++;
                continue;
            }

            $registrationId = nextRegistrationId($conn);
            $insertStmt->bind_param("sss", $registrationId, $eventId, $staffId);
            if ($insertStmt->execute()) {
                $registeredCount++;
            }
        }

        $checkUserStmt->close();
        $checkRegStmt->close();
        $insertStmt->close();

        $conn->commit();

        $msgParts = [];
        $msgParts[] = "Staff registered: {$registeredCount}";
        if ($alreadyCount > 0) {
            $msgParts[] = "already registered: {$alreadyCount}";
        }
        if ($invalidCount > 0) {
            $msgParts[] = "invalid users: {$invalidCount}";
        }

        $_SESSION['msg'] = [
            'type' => $registeredCount > 0 ? 'success' : 'warning',
            'text' => implode(' | ', $msgParts)
        ];

        redirectBackToEvent($eventId);
    }

    if ($registerMode === 'external') {
        $name = trim($_POST['participant_name'] ?? '');
        $email = trim($_POST['participant_email'] ?? '');
        $phone = trim($_POST['participant_phone'] ?? '');
        $company = trim($_POST['participant_company'] ?? '');

        if ($name === '') {
            throw new Exception('External participant name is required.');
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid external participant email format.');
        }

        // If email belongs to existing account, register by account
        $existingUserId = null;
        if ($email !== '') {
            $findUserStmt = $conn->prepare("SELECT userId FROM user WHERE email = ? LIMIT 1");
            if ($findUserStmt) {
                $findUserStmt->bind_param("s", $email);
                $findUserStmt->execute();
                $findRes = $findUserStmt->get_result();
                if ($findRes && ($u = $findRes->fetch_assoc())) {
                    $existingUserId = $u['userId'] ?? null;
                }
                $findUserStmt->close();
            }
        }

        if ($existingUserId) {
            $checkRegStmt = $conn->prepare("SELECT registration_id FROM att_registration WHERE event_id = ? AND participant_id = ? LIMIT 1");
            if (!$checkRegStmt) {
                throw new Exception('Server error while checking existing registration.');
            }

            $checkRegStmt->bind_param("ss", $eventId, $existingUserId);
            $checkRegStmt->execute();
            $regRes = $checkRegStmt->get_result();
            if ($regRes && $regRes->num_rows > 0) {
                $checkRegStmt->close();
                throw new Exception('This account is already registered for the event.');
            }
            $checkRegStmt->close();

            $registrationId = nextRegistrationId($conn);
            $insertStmt = $conn->prepare("INSERT INTO att_registration (registration_id, event_id, participant_id, registration_source) VALUES (?, ?, ?, 'account')");
            if (!$insertStmt) {
                throw new Exception('Server error while registering account participant.');
            }

            $insertStmt->bind_param("sss", $registrationId, $eventId, $existingUserId);
            if (!$insertStmt->execute()) {
                $insertStmt->close();
                throw new Exception('Failed to register external participant.');
            }
            $insertStmt->close();

            $conn->commit();
            $_SESSION['msg'] = [
                'type' => 'success',
                'text' => 'External participant registered using existing account.'
            ];
            redirectBackToEvent($eventId);
        }

        // Otherwise register as walk-in
        if ($email !== '' || $phone !== '') {
            $dupSql = "
                SELECT registration_id
                FROM att_registration
                WHERE event_id = ?
                  AND registration_source = 'walk_in'
                  AND ((? <> '' AND walkin_email = ?) OR (? <> '' AND walkin_phone = ?))
                LIMIT 1
            ";

            $dupStmt = $conn->prepare($dupSql);
            if ($dupStmt) {
                $dupStmt->bind_param("sssss", $eventId, $email, $email, $phone, $phone);
                $dupStmt->execute();
                $dupRes = $dupStmt->get_result();
                if ($dupRes && $dupRes->num_rows > 0) {
                    $dupStmt->close();
                    throw new Exception('External participant already registered (walk-in match by email/phone).');
                }
                $dupStmt->close();
            }
        }

        $registrationId = nextRegistrationId($conn);
        $walkinParticipantId = nextWalkinParticipantId($conn);

        $insertSql = "
            INSERT INTO att_registration
                (registration_id, event_id, participant_id, registration_source, walkin_name, walkin_email, walkin_phone, walkin_company)
            VALUES (?, ?, ?, 'walk_in', ?, ?, ?, ?)
        ";

        $insertStmt = $conn->prepare($insertSql);
        if (!$insertStmt) {
            throw new Exception('Server error while registering walk-in participant.');
        }

        $insertStmt->bind_param(
            "sssssss",
            $registrationId,
            $eventId,
            $walkinParticipantId,
            $name,
            $email,
            $phone,
            $company
        );

        if (!$insertStmt->execute()) {
            $insertStmt->close();
            throw new Exception('Failed to register external walk-in participant.');
        }
        $insertStmt->close();

        $conn->commit();
        $_SESSION['msg'] = [
            'type' => 'success',
            'text' => 'External participant registered successfully.'
        ];

        redirectBackToEvent($eventId);
    }

    throw new Exception('Invalid registration mode.');
} catch (Exception $e) {
    if (!empty($txStarted)) {
        $conn->rollback();
    }
    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => $e->getMessage()
    ];
    redirectBackToEvent($eventId);
}
