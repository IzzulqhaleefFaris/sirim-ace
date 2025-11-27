<?php
session_start();
include "include/config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// delete using string column (event_id appears to be alphanumeric in DB)
$stmt = $conn->prepare("DELETE FROM att_event WHERE event_id = ?");
if (!$stmt) {
    http_response_code(500); echo 'DB prepare error: ' . htmlspecialchars($conn->error); exit;
}
$stmt->bind_param("s", $event_id);

if ($stmt->execute()) {
    echo 'success';
} else {
    http_response_code(500);
    echo 'Database error: ' . htmlspecialchars($stmt->error);
}
$stmt->close();
$conn->close();
?>