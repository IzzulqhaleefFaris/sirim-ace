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