<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "../../../include/config.php";
include "../../../include/updateEventStatus.php";
include "../../../include/permissions.php";

require_manage_events();

// verify DB connection exists
if (!isset($conn) || !$conn) {
    error_log("DB connection missing in update-event-status.php");
    $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Database connection error.'];
    header("Location: event-list.php");
    exit;
}

// ensure POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: event-list.php");
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
$location_buildingName = trim($_POST['location_buildingName'] ?? '');
$address_line1 = trim($_POST['address_line1'] ?? '');
$address_line2 = trim($_POST['address_line2'] ?? '');
$address_city = trim($_POST['address_city'] ?? '');
$address_postcode = trim($_POST['address_postcode'] ?? '');
$state_id = trim($_POST['state_id'] ?? '');
$event_type_id = trim($_POST['event_type_id'] ?? '');

// Validation
if ($eventId === '' || $name === '' || $start === '' || $end === '') {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Sila lengkapkan semua medan yang diperlukan.'];
    header("Location: edit-event.php?id=" . urlencode($eventId));
    exit;
}

require_event_owner_or_admin($conn, $eventId, 'event-list.php');

// Convert to timestamps & rules
$startTS = strtotime($start);
$endTS = strtotime($end);
$openTS = $openReg ? strtotime($openReg) : null;
$closeTS = $closeReg ? strtotime($closeReg) : null;

if ($endTS < $startTS) {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Tarikh tamat tidak boleh lebih awal dari tarikh mula'];
    header("Location: edit-event.php?id=" . urlencode($eventId));
    exit;
}
if ($openTS !== null && $closeTS !== null && $closeTS < $openTS) {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Tarikh tutup pendaftaran tidak boleh lebih awal dari tarikh buka'];
    header("Location: edit-event.php?id=" . urlencode($eventId));
    exit;
}
if ($openTS !== null && $openTS > $startTS) {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Tarikh buka pendaftaran tidak boleh selepas tarikh mula event'];
    header("Location: edit-event.php?id=" . urlencode($eventId));
    exit;
}
if ($closeTS !== null && $closeTS > $endTS) {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Tarikh tutup pendaftaran tidak boleh selepas tarikh tamat event'];
    header("Location: edit-event.php?id=" . urlencode($eventId));
    exit;
}

// Start transaction for data integrity
$conn->begin_transaction();

try {
    $totalAffectedRows = 0;

    // Update location if location_id exists
    if ($locationId !== '' && $location_name !== '') {
        $sqlLoc = "UPDATE att_location
               SET location_name = ?, location_buildingName = ?, address_line1 = ?, address_line2 = ?, address_city = ?, address_postcode = ?, state_id = ?
               WHERE location_id = ?";
        $stmtLoc = $conn->prepare($sqlLoc);
        
        if ($stmtLoc === false) {
            throw new Exception("Ralat menyediakan query lokasi: " . $conn->error);
        }
        
        // Convert empty strings to null for optional fields
        $location_buildingName = $location_buildingName === '' ? null : $location_buildingName;
        $address_line1 = $address_line1 === '' ? null : $address_line1;
        $address_line2 = $address_line2 === '' ? null : $address_line2;
        $address_city = $address_city === '' ? null : $address_city;
        $address_postcode = $address_postcode === '' ? null : $address_postcode;
        
        $stmtLoc->bind_param(
            "ssssssss",
            $location_name,
            $location_buildingName,
            $address_line1,
            $address_line2,
            $address_city,
            $address_postcode,
            $state_id,
            $locationId
        );
        
        if (!$stmtLoc->execute()) {
            throw new Exception("Gagal mengemaskini lokasi: " . $stmtLoc->error);
        }
        
        $totalAffectedRows += $stmtLoc->affected_rows;
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

    $totalAffectedRows += $stmt->affected_rows;
    $stmt->close();

    // Commit transaction
    $conn->commit();
    
    // Update event statuses after successful update
    updateEventStatuses($conn);
    
    // Check if any data was actually changed
    if ($totalAffectedRows > 0) {
        $_SESSION['msg'] = [
            'type' => 'success',
            'text' => 'Event berjaya dikemaskini!'
        ];
    } else {
        $_SESSION['msg'] = [
            'type' => 'info',
            'text' => 'Tiada perubahan dibuat pada data event.'
        ];
    }
    
    header("Location: edit-event.php?id=" . urlencode($eventId));
    exit;

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    error_log("Update event error: " . $e->getMessage());
    $_SESSION['msg'] = [
        'type' => 'danger',
        'text' => $e->getMessage()
    ];
    
    header("Location: edit-event.php?id=" . urlencode($eventId));
    exit;
}
