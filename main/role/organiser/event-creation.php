<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../../include/permissions.php';

require_manage_events();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Form submitted to event-creation.php");
}

include '../../../include/config.php';
include '../../../include/updateEventStatus.php';

// Helper function
function nextCode($conn, $table, $code_col, $prefix, $numDigits = 3)
{
    $q = "SELECT MAX(CAST(SUBSTRING($code_col, " . (strlen($prefix) + 1) . ") AS UNSIGNED)) AS maxnum FROM $table";
    $res = $conn->query($q);
    $row = $res->fetch_assoc();
    $max = intval($row['maxnum']);
    return $prefix . str_pad($max + 1, $numDigits, "0", STR_PAD_LEFT);
}

function redirectBack()
{
    header("Location: create-event.php");
    exit;
}

// Load dropdown data
$eventTypes = $conn->query("SELECT event_type_id, event_type_name FROM att_event_type");
$states = $conn->query("SELECT state_id, state_name FROM att_state");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("✅ POST received in event-creation.php");
    error_log(print_r($_POST, true));
    $event_name        = trim($_POST['event_name'] ?? '');
    $event_type_id     = trim($_POST['event_type_id'] ?? '');
    $event_startDate   = trim($_POST['event_startDate'] ?? '');
    $event_endDate     = trim($_POST['event_endDate'] ?? '');
    $event_openRegistration  = trim($_POST['event_openRegistration'] ?? '');
    $event_closeRegistration = trim($_POST['event_closeRegistration'] ?? '');
    $state_id          = trim($_POST['state_id'] ?? '');
    $location_name     = trim($_POST['location_name'] ?? '');
    $location_buildingName = trim($_POST['location_buildingName'] ?? '');
    $address_line1     = trim($_POST['address_line1'] ?? '');
    $address_line2     = trim($_POST['address_line2'] ?? '');
    $address_city      = trim($_POST['address_city'] ?? '');
    $address_postcode  = trim($_POST['address_postcode'] ?? '');
    $location_room     = trim($_POST['location_room'] ?? '');
    $location_level    = trim($_POST['location_level'] ?? '');

    if ($event_name === '' || $event_type_id === '' || $event_startDate === '' || $event_endDate === '' || $state_id === '' || $location_name === '') {
        $_SESSION['msg'] = [
            'type' => 'warning',
            'text' => '⚠️ Please fill all required fields.'
        ];
        redirectBack();
    }

    // Convert dates to timestamps for easy comparison
    $startDateTS = strtotime($event_startDate);
    $endDateTS = strtotime($event_endDate);
    $openRegTS = strtotime($event_openRegistration);
    $closeRegTS = strtotime($event_closeRegistration);

    if ($endDateTS < $startDateTS) {
        $_SESSION['msg'] = [
            'type' => 'warning',
            'text' => '⚠️ Event end date cannot be earlier than start date.'
        ];
        redirectBack();
    }

    if ($closeRegTS < $openRegTS) {
        $_SESSION['msg'] = [
            'type' => 'warning',
            'text' => '⚠️ Registration closing date cannot be earlier than opening date.'
        ];
        redirectBack();
    }

    if ($openRegTS > $startDateTS) {
        $_SESSION['msg'] = [
            'type' => 'warning',
            'text' => '⚠️ Registration cannot open after event starts.'
        ];
        redirectBack();
    }

    if ($closeRegTS > $endDateTS) {
        $_SESSION['msg'] = [
            'type' => 'warning',
            'text' => '⚠️ Registration cannot close after event ends.'
        ];
        redirectBack();
    }

    $conn->begin_transaction();
    try {
        $location_id = nextCode($conn, 'att_location', 'location_id', 'LOC', 3);
        $stmtLoc = $conn->prepare("
            INSERT INTO att_location (
                location_id,
                location_name,
                location_buildingName,
                location_level,
                location_room,
                address_line1,
                address_line2,
                address_city,
                address_postcode,
                state_id
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmtLoc) {
            die("❌ SQL ERROR (att_location insert): " . $conn->error);
        }

        $stmtLoc->bind_param(
            "ssssssssss",
            $location_id,
            $location_name,
            $location_buildingName,
            $location_level,
            $location_room,
            $address_line1,
            $address_line2,
            $address_city,
            $address_postcode,
            $state_id
        );
        $stmtLoc->execute();
        $stmtLoc->close();

        $event_id = nextCode($conn, 'att_event', 'event_id', 'EV', 3);
        $event_description = trim($_POST['event_description'] ?? '');

        // Default event image (store as web-relative path)
        $imagePathForDB = 'images/custom/no_image.jpg';

        $status = NULL;
        $today = date('Y-m-d');

        if ($today < $event_startDate) {
            $status = 'Upcoming';
        } elseif ($today >= $event_startDate && $today < $event_endDate) {
            $status = 'Current';
        } elseif ($today >= $event_endDate) {
            $status = 'Completed';
        } else {
            $status = 'Invalid';
        }

        $stmtEv = $conn->prepare("
                    INSERT INTO att_event (event_id, event_name, event_description, event_type_id, location_id, state_id, 
                    event_startDate, event_endDate, event_openRegistration, event_closeRegistration, event_status, event_owner_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmtEv) {
            die("❌ SQL ERROR (att_event insert): " . $conn->error);
        }

        $eventOwnerId = $_SESSION['userId'];

        $stmtEv->bind_param(
            "ssssssssssss",
            $event_id,
            $event_name,
            $event_description,
            $event_type_id,
            $location_id,
            $state_id,
            $event_startDate,
            $event_endDate,
            $event_openRegistration,
            $event_closeRegistration,
            $status,
            $eventOwnerId
        );

        $stmtEv->execute();
        $stmtEv->close();

        //Image Upload PHP backend 
        if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../images/uploads/events/'; //physical path
            $dbPath = 'images/uploads/events/';

            //Check if folder exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $tmpName = $_FILES['event_image']['tmp_name'];
            $fileSize = $_FILES['event_image']['size'];
            $ext = strtolower(pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION));

            $allowed = ['jpg', 'jpeg', 'png'];

            //Validate extension
            if (!in_array($ext, $allowed)) {
                throw new Exception("Invalid format. Only JPG & PNG allowed.");
            }

            //Validate size
            if ($fileSize > 2 * 1024 * 1024) {
                throw new Exception("Image size must not be exceed 2MB.");
            }

            //Validate real image
            if (!getimagesize($tmpName)) {
                throw new Exception("Uploaded file is not a valid image.");
            }

            //Rename using event ID
            $fileName = $event_id . '.' . $ext;
            $fullPath = $uploadDir . $fileName;

            if (!move_uploaded_file($tmpName, $fullPath)) {
                throw new Exception("Failed to upload event image.");
            }

            $imagePathForDB = $dbPath . $fileName;
        }

        $stmtImg = $conn->prepare("
            UPDATE att_event 
            SET event_image = ? 
            WHERE event_id = ?
        ");

        $stmtImg->bind_param("ss", $imagePathForDB, $event_id);
        $stmtImg->execute();
        $stmtImg->close();

        $conn->commit();

        // Update event statuses after successful creation
        updateEventStatuses($conn);

        $_SESSION['event_created'] = [
            'id' => $event_id,
            'name' => $event_name
        ];
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['msg'] = [
            'type' => 'danger',
            'text' => '❌ Error: ' . $e->getMessage()
        ];
    }

    redirectBack();
}
