<?php
session_start();
include "../../../include/config.php";
include "../../../include/permissions.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_manage_events();

// parse raw input if PHP didn't populate $_POST (fetch with urlencoded or raw body)
if (empty($_POST)) {
    $raw = file_get_contents('php://input');
    if ($raw) {
        if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
            $data = json_decode($raw, true);
            if (is_array($data)) $_POST = $data + $_POST;
        } else {
            parse_str($raw, $data);
            if (is_array($data)) $_POST = $data + $_POST;
        }
    }
}

// treat id as string (supports EV013 or numeric strings)
$event_id = trim((string)filter_input(INPUT_POST, 'id', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
if ($event_id === '') {
    http_response_code(400);
    echo 'Invalid ID';
    exit;
}

if (!ensure_event_owner_or_admin($conn, $event_id)) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// fetch linked location_id first
$locStmt = $conn->prepare("SELECT location_id FROM att_event WHERE event_id = ? LIMIT 1");
if (!$locStmt) {
    http_response_code(500);
    echo 'DB prepare error: ' . htmlspecialchars($conn->error);
    exit;
}
$locStmt->bind_param("s", $event_id);
$locStmt->execute();
$locResult = $locStmt->get_result();
$location_id = null;
if ($locResult && $locResult->num_rows > 0) {
    $location_id = $locResult->fetch_assoc()['location_id'] ?? null;
}
$locStmt->close();

$conn->begin_transaction();
try {
    // collect participant ids linked to this event before deleting registrations
    $participantIds = [];
    $pidStmt = $conn->prepare("SELECT DISTINCT participant_id FROM att_registration WHERE event_id = ?");
    if (!$pidStmt) {
        throw new Exception('DB prepare error: ' . $conn->error);
    }
    $pidStmt->bind_param("s", $event_id);
    $pidStmt->execute();
    $pidResult = $pidStmt->get_result();
    if ($pidResult) {
        while ($pidRow = $pidResult->fetch_assoc()) {
            $pid = trim((string)($pidRow['participant_id'] ?? ''));
            if ($pid !== '') {
                $participantIds[] = $pid;
            }
        }
    }
    $pidStmt->close();

    // delete linked attendance rows for this event registrations
    $attStmt = $conn->prepare("DELETE a FROM att_attendance a INNER JOIN att_registration r ON a.registration_id = r.registration_id WHERE r.event_id = ?");
    if (!$attStmt) {
        throw new Exception('DB prepare error: ' . $conn->error);
    }
    $attStmt->bind_param("s", $event_id);
    if (!$attStmt->execute()) {
        throw new Exception('Database error: ' . $attStmt->error);
    }
    $attStmt->close();

    // delete linked registrations for this event
    $regStmt = $conn->prepare("DELETE FROM att_registration WHERE event_id = ?");
    if (!$regStmt) {
        throw new Exception('DB prepare error: ' . $conn->error);
    }
    $regStmt->bind_param("s", $event_id);
    if (!$regStmt->execute()) {
        throw new Exception('Database error: ' . $regStmt->error);
    }
    $regStmt->close();

    // cleanup orphan participant rows (walk-in/non-user only)
    if (!empty($participantIds)) {
        $cleanupStmt = $conn->prepare("DELETE p
            FROM att_participant p
            LEFT JOIN user u ON u.userId = p.participant_id
            WHERE p.participant_id = ?
              AND u.userId IS NULL
              AND NOT EXISTS (
                SELECT 1
                FROM att_registration r
                WHERE r.participant_id = p.participant_id
              )");

        if (!$cleanupStmt) {
            throw new Exception('DB prepare error: ' . $conn->error);
        }

        foreach ($participantIds as $pid) {
            $cleanupStmt->bind_param("s", $pid);
            if (!$cleanupStmt->execute()) {
                throw new Exception('Database error: ' . $cleanupStmt->error);
            }
        }

        $cleanupStmt->close();
    }

    // delete event
    $stmt = $conn->prepare("DELETE FROM att_event WHERE event_id = ?");
    if (!$stmt) {
        throw new Exception('DB prepare error: ' . $conn->error);
    }
    $stmt->bind_param("s", $event_id);
    if (!$stmt->execute()) {
        throw new Exception('Database error: ' . $stmt->error);
    }
    $stmt->close();

    // delete linked location only if unused by other events
    if (!empty($location_id)) {
        $checkStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM att_event WHERE location_id = ?");
        if (!$checkStmt) {
            throw new Exception('DB prepare error: ' . $conn->error);
        }
        $checkStmt->bind_param("s", $location_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $count = 0;
        if ($checkResult && $row = $checkResult->fetch_assoc()) {
            $count = (int)$row['cnt'];
        }
        $checkStmt->close();

        if ($count === 0) {
            $delLocStmt = $conn->prepare("DELETE FROM att_location WHERE location_id = ?");
            if (!$delLocStmt) {
                throw new Exception('DB prepare error: ' . $conn->error);
            }
            $delLocStmt->bind_param("s", $location_id);
            if (!$delLocStmt->execute()) {
                throw new Exception('Database error: ' . $delLocStmt->error);
            }
            $delLocStmt->close();
        }
    }

    $conn->commit();
    echo 'success';
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo $e->getMessage();
}

$conn->close();
?>