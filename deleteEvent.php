<?php
include "include/config.php";

if (!isset($_POST['id'])) {
    echo "Invalid request";
    exit;
}

$event_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($event_id <= 0) {
    echo "Invalid ID";
    exit;
}

$sql = "DELETE FROM att_event WHERE event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "Database error: " . $conn->error;
}

$stmt->close();
$conn->close();
?>