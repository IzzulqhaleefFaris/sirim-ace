<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not logged in
if (!isset($_SESSION['userId'])) {
    header('Location: /attendance');
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Form submitted to eventCreation.php");
}

include 'config.php';
include 'updateEventStatus.php';

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
    header("Location: /attendance/Org_CreateEvent.php");
    exit;
}

// Load dropdown data
$eventTypes = $conn->query("SELECT event_type_id, event_type_name FROM att_event_type");
$states = $conn->query("SELECT state_id, state_name FROM att_state");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("✅ POST received in eventCreation.php");
    error_log(print_r($_POST, true));
    $event_name        = trim($_POST['event_name'] ?? '');
    $event_type_id     = trim($_POST['event_type_id'] ?? '');
    $event_startDate   = trim($_POST['event_startDate'] ?? '');
    $event_endDate     = trim($_POST['event_endDate'] ?? '');
    $event_openRegistration  = trim($_POST['event_openRegistration'] ?? '');
    $event_closeRegistration = trim($_POST['event_closeRegistration'] ?? '');
    $state_id          = trim($_POST['state_id'] ?? '');
    $location_name     = trim($_POST['location_name'] ?? '');
    $building_name     = trim($_POST['building_name'] ?? '');
    $location_address  = trim($_POST['location_address'] ?? '');
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
            INSERT INTO att_location (location_id, location_name, building_name, location_address, location_room, location_level, state_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmtLoc) {
            die("❌ SQL ERROR (att_location insert): " . $conn->error);
        }

        $stmtLoc->bind_param("sssssss", $location_id, $location_name, $building_name, $location_address, $location_room, $location_level, $state_id);
        $stmtLoc->execute();
        $stmtLoc->close();

        $event_id = nextCode($conn, 'att_event', 'event_id', 'EV', 3);
        $event_description = trim($_POST['event_description'] ?? '');
        $status = 'Draft';

        $stmtEv = $conn->prepare("
                            INSERT INTO att_event (event_id, event_name, event_description, event_type_id, location_id, state_id, 
                            event_startDate, event_endDate, event_openRegistration, event_closeRegistration, event_status)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmtEv) {
            die("❌ SQL ERROR (att_event insert): " . $conn->error);
        }

        $stmtEv->bind_param(
            "sssssssssss",
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
            $status
        );

        $stmtEv->execute();
        $stmtEv->close();

        $conn->commit();

        updateEventStatuses($conn);

        $_SESSION['event_created'] = [
            'id' => $event_id,
            'name' => $event_name
        ];
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['msg'] = "❌ Error: " . $e->getMessage();
    }

    redirectBack();
}
