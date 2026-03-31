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
$startTime = trim($_POST['event_startTime'] ?? '09:00');
$endTime = trim($_POST['event_endTime'] ?? '17:00');
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
if ($eventId === '' || $name === '' || $start === '' || $end === '' || $startTime === '' || $endTime === '') {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Please complete all required fields.'];
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
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'End date cannot be earlier than start date.'];
    header("Location: edit-event.php?id=" . urlencode($eventId));
    exit;
}
if ($openTS !== null && $closeTS !== null && $closeTS < $openTS) {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Registration close date cannot be earlier than open date.'];
    header("Location: edit-event.php?id=" . urlencode($eventId));
    exit;
}
if ($openTS !== null && $openTS > $startTS) {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Registration open date cannot be after event start date.'];
    header("Location: edit-event.php?id=" . urlencode($eventId));
    exit;
}
if ($closeTS !== null && $closeTS > $endTS) {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Registration close date cannot be after event end date.'];
    header("Location: edit-event.php?id=" . urlencode($eventId));
    exit;
}

// Start transaction for data integrity
$conn->begin_transaction();

try {
    $totalAffectedRows = 0;

    // Get current image path for replacement cleanup (if new image uploaded)
    $currentImagePath = null;
    $oldImgStmt = $conn->prepare("SELECT event_image FROM att_event WHERE event_id = ? LIMIT 1");
    if ($oldImgStmt) {
        $oldImgStmt->bind_param("s", $eventId);
        $oldImgStmt->execute();
        $oldImgRes = $oldImgStmt->get_result();
        if ($oldImgRes && ($oldRow = $oldImgRes->fetch_assoc())) {
            $currentImagePath = $oldRow['event_image'] ?? null;
        }
        $oldImgStmt->close();
    }

    // Update location if location_id exists
    if ($locationId !== '' && $location_name !== '') {
        $sqlLoc = "UPDATE att_location
               SET location_name = ?, location_buildingName = ?, address_line1 = ?, address_line2 = ?, address_city = ?, address_postcode = ?, state_id = ?
               WHERE location_id = ?";
        $stmtLoc = $conn->prepare($sqlLoc);
        
        if ($stmtLoc === false) {
            throw new Exception("Error preparing location query: " . $conn->error);
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
            throw new Exception("Failed to update location: " . $stmtLoc->error);
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
                event_startTime = ?,
                event_endTime = ?,
                event_openRegistration = ?, 
                event_closeRegistration = ?, 
                location_id = ?
            WHERE event_id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Error preparing event query: " . $conn->error);
    }

    // Convert empty strings to null for optional fields
    $openReg = ($openReg === '') ? null : $openReg;
    $closeReg = ($closeReg === '') ? null : $closeReg;
    $locationId = ($locationId === '') ? null : $locationId;

    // Bind parameters - 10 parameters total
    $stmt->bind_param(
        "ssssssssss",
        $name,
        $event_type_id,
        $start,
        $end,
        $startTime,
        $endTime,
        $openReg,
        $closeReg,
        $locationId,
        $eventId
    );

    if (!$stmt->execute()) {
        throw new Exception("Failed to update event: " . $stmt->error);
    }

    $totalAffectedRows += $stmt->affected_rows;
    $stmt->close();

    // Handle image update (optional)
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../../images/uploads/events/';
        $dbPath = 'images/uploads/events/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $tmpName = $_FILES['event_image']['tmp_name'];
        $fileSize = $_FILES['event_image']['size'];
        $ext = strtolower(pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowed, true)) {
            throw new Exception('Invalid image format. Only JPG and PNG are allowed.');
        }

        if ($fileSize > 2 * 1024 * 1024) {
            throw new Exception('Image size must not exceed 2MB.');
        }

        if (!getimagesize($tmpName)) {
            throw new Exception('Uploaded file is not a valid image.');
        }

        $fileName = $eventId . '.' . $ext;
        $fullPath = $uploadDir . $fileName;
        if (!move_uploaded_file($tmpName, $fullPath)) {
            throw new Exception('Failed to upload event image.');
        }

        $imagePathForDB = $dbPath . $fileName;
        $imgStmt = $conn->prepare("UPDATE att_event SET event_image = ? WHERE event_id = ?");
        if (!$imgStmt) {
            throw new Exception('Error preparing image update query: ' . $conn->error);
        }

        $imgStmt->bind_param("ss", $imagePathForDB, $eventId);
        if (!$imgStmt->execute()) {
            throw new Exception('Failed to update event image: ' . $imgStmt->error);
        }
        $imgStmt->close();

        // Remove old event image file if it exists and is different
        if (!empty($currentImagePath) && $currentImagePath !== $imagePathForDB && strpos($currentImagePath, 'images/uploads/events/') === 0) {
            $oldPhysicalPath = __DIR__ . '/../../../' . $currentImagePath;
            if (is_file($oldPhysicalPath)) {
                @unlink($oldPhysicalPath);
            }
        }

        $totalAffectedRows++;
    }

    // Commit transaction
    $conn->commit();
    
    // Update event statuses after successful update
    updateEventStatuses($conn);
    
    // Check if any data was actually changed
    if ($totalAffectedRows > 0) {
        $_SESSION['msg'] = [
            'type' => 'success',
            'text' => 'Event updated successfully!'
        ];
    } else {
        $_SESSION['msg'] = [
            'type' => 'info',
            'text' => 'No changes were made to the event data.'
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
