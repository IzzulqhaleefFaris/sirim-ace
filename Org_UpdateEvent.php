<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include __DIR__ . '/include/config.php';
include __DIR__ . '/include/updateEventStatus.php';

// verify DB connection exists
if (!isset($conn) || !$conn) {
    error_log("DB connection missing in Org_UpdateEvent.php");
    $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Database connection error.'];
    header("Location: Org_EventList.php");
    exit;
}

// ensure POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: Org_EventList.php");
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
    header("Location: Org_EditEvent.php?id=" . urlencode($eventId));
    exit;
}

// Convert to timestamps & rules
$startTS = strtotime($start);
$endTS = strtotime($end);
$openTS = $openReg ? strtotime($openReg) : null;
$closeTS = $closeReg ? strtotime($closeReg) : null;

if ($endTS < $startTS) {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Tarikh tamat tidak boleh lebih awal dari tarikh mula'];
    header("Location: Org_EditEvent.php?id=" . urlencode($eventId));
    exit;
}
if ($openTS !== null && $closeTS !== null && $closeTS < $openTS) {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Tarikh tutup pendaftaran tidak boleh lebih awal dari tarikh buka'];
    header("Location: Org_EditEvent.php?id=" . urlencode($eventId));
    exit;
}
if ($openTS !== null && $openTS > $startTS) {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Tarikh buka pendaftaran tidak boleh selepas tarikh mula event'];
    header("Location: Org_EditEvent.php?id=" . urlencode($eventId));
    exit;
}
if ($closeTS !== null && $closeTS > $endTS) {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Tarikh tutup pendaftaran tidak boleh selepas tarikh tamat event'];
    header("Location: Org_EditEvent.php?id=" . urlencode($eventId));
    exit;
}

// Start transaction for data integrity
$conn->begin_transaction();

try {
    $errorOccurred = false;
    $errorMessage = '';

    // Update location if location_id exists
    if ($locationId !== '' && $location_name !== '') {
        $sqlLoc = "UPDATE att_location
                   SET location_name = ?, building_name = ?, location_address = ?, state_id = ?
                   WHERE location_id = ?";
        $stmtLoc = $conn->prepare($sqlLoc);
        
        if ($stmtLoc === false) {
            throw new Exception("Ralat menyediakan query lokasi: " . $conn->error);
        }
        
        // Convert empty strings to null for optional fields
        $building_name = $building_name === '' ? null : $building_name;
        $location_address = $location_address === '' ? null : $location_address;
        
        $stmtLoc->bind_param("sssss", $location_name, $building_name, $location_address, $state_id, $locationId);
        
        if (!$stmtLoc->execute()) {
            throw new Exception("Gagal mengemaskini lokasi: " . $stmtLoc->error);
        }
        
        $stmtLoc->close();
    }

    // Prepare event update query
    $sql = "UPDATE att_event 
            SET event_name = ?, 
                event_type_id = ?, 
                event_startDate = ?, 
                event_endDate = ?, 
                event_openRegistration = ?, 
                event_closeRegistration = ?, 
                location_id = ?
            WHERE event_id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Ralat menyediakan query event: " . $conn->error);
    }

    // Convert empty strings to null for optional fields
    $openReg = ($openReg === '') ? null : $openReg;
    $closeReg = ($closeReg === '') ? null : $closeReg;
    $locationId = ($locationId === '') ? null : $locationId;

    // Bind parameters - 8 parameters total
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

    if (!$stmt->execute()) {
        throw new Exception("Gagal mengemaskini event: " . $stmt->error);
    }

    // Check if any rows were affected
    if ($stmt->affected_rows === 0) {
        throw new Exception("Tiada perubahan dibuat. Event mungkin tidak wujud atau tiada data berubah.");
    }

    $stmt->close();

    // Commit transaction if everything succeeded
    $conn->commit();
    
    // Update event statuses after successful update
    updateEventStatuses($conn);
    
    $_SESSION['msg'] = [
        'type' => 'success',
        'text' => 'Event berjaya dikemaskini. Status event telah dikemaskini berdasarkan tarikh semasa.'
    ];
    
    header("Location: Org_EventList.php");
    exit;

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    error_log("Update event error: " . $e->getMessage());
    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => $e->getMessage()
    ];
    
    header("Location: Org_EditEvent.php?id=" . urlencode($eventId));
    exit;
}
