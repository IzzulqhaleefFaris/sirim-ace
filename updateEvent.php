<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include __DIR__ . '/include/config.php';

// verify DB connection exists
if (!isset($conn) || !$conn) {
    error_log("DB connection missing in updateEvent.php");
    $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Database connection error.'];
    header("Location: eventList.php");
    exit;
}

// ensure POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: eventList.php");
    exit;
}

$eventId = trim($_POST['event_id'] ?? '');
$name = trim($_POST['event_name'] ?? '');
$start = trim($_POST['event_startDate'] ?? '');
$end = trim($_POST['event_endDate'] ?? '');
$openReg = trim($_POST['event_openRegistration'] ?? '');
$closeReg = trim($_POST['event_closeRegistration'] ?? '');
$locationId = trim($_POST['location_id'] ?? '');
$location_name = trim($_POST['location_name'] ?? '');
$building_name = trim($_POST['building_name'] ?? '');
$location_address = trim($_POST['location_address'] ?? '');
$state_id = trim($_POST['state_id'] ?? '');
$event_type_id = trim($_POST['event_type_id'] ?? '');

// Validation
if ($eventId === '' || $name === '' || $start === '' || $end === '') {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Sila lengkapkan semua medan yang diperlukan.'];
    header("Location: editEvent.php?id=" . urlencode($eventId));
    exit;
}

// Convert to timestamps & rules
$startTS = strtotime($start);
$endTS = strtotime($end);
$openTS = $openReg ? strtotime($openReg) : null;
$closeTS = $closeReg ? strtotime($closeReg) : null;

if ($endTS < $startTS) {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Tarikh tamat tidak boleh lebih awal dari tarikh mula'];
    header("Location: editEvent.php?id=" . urlencode($eventId));
    exit;
}
if ($openTS !== null && $closeTS !== null && $closeTS < $openTS) {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Tarikh tutup pendaftaran tidak boleh lebih awal dari tarikh buka'];
    header("Location: editEvent.php?id=" . urlencode($eventId));
    exit;
}
if ($openTS !== null && $openTS > $startTS) {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Tarikh buka pendaftaran tidak boleh selepas tarikh mula event'];
    header("Location: editEvent.php?id=" . urlencode($eventId));
    exit;
}
if ($closeTS !== null && $closeTS > $endTS) {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Tarikh tutup pendaftaran tidak boleh selepas tarikh tamat event'];
    header("Location: editEvent.php?id=" . urlencode($eventId));
    exit;
}

if ($locationId !== '') {
    $sqlLoc = "UPDATE att_location
               SET location_name = ?, building_name = ?, location_address = ?, state_id = ?
               WHERE location_id = ?";
    $stmtLoc = $conn->prepare($sqlLoc);
    if ($stmtLoc === false) {
        error_log("Prepare failed (loc): " . $conn->error);
        $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Ralat pangkalan data (location).'];
        header("Location: editEvent.php?id=" . urlencode($eventId));
        exit;
    }
    $stmtLoc->bind_param("sssss", $location_name, $building_name, $location_address, $state_id, $locationId);
    if (!$stmtLoc->execute()) {
        error_log("Execute failed (loc): " . $stmtLoc->error);
        $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Gagal mengemaskini lokasi: ' . $stmtLoc->error];
        $stmtLoc->close();
        header("Location: editEvent.php?id=" . urlencode($eventId));
        exit;
    }
    $stmtLoc->close();
}

// Prepare update and check prepare success
$sql = "
    UPDATE att_event 
    SET event_name = ?, event_type_id = ?, event_startDate = ?, event_endDate = ?, 
        event_openRegistration = ?, event_closeRegistration = ?, location_id = ?
    WHERE event_id = ?
";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error . " | SQL: " . $sql);
    $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Ralat pangkalan data: ' . $conn->error];
    header("Location: editEvent.php?id=" . urlencode($eventId));
    exit;
}

$openReg = $openReg ?: null;
$closeReg = $closeReg ?: null;

// Bind all as strings (IDs are alphanumeric)
$stmt->bind_param(
    "ssssssss",
    $name,
    $event_type_id,
    $start,
    $end,
    $openReg,
    $closeReg,
    $locationId,
    $eventId
);

if ($stmt->execute()) {
    $_SESSION['msg'] = ['type' => 'success', 'text' => 'Event updated successfully'];
} else {
    error_log("Execute failed: " . $stmt->error);
    $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Failed to update event: ' . $stmt->error];
}

$stmt->close();
header("Location: eventList.php");
exit;
