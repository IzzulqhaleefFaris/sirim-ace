<?php
session_start();
include "include/config.php";

// Redirect to login if not logged in
if (!isset($_SESSION['userId'])) {
    header('Location: /attendance');
    exit;
}

// Get the event ID from the URL
$eventId = $_GET['id'] ?? null;
if (!$eventId) {
    echo "Invalid Event ID.";
    exit;
}

// Fetch event details from the database
$sql = "SELECT
        e.*,
        t.event_type_name,
        l.location_name,
        l.building_name,
        l.location_address,
        s.state_name
    FROM att_event e
    LEFT JOIN att_event_type t ON e.event_type_id = t.event_type_id
    LEFT JOIN att_location l ON e.location_id = l.location_id
    LEFT JOIN att_state s ON l.state_id = s.state_id
    WHERE e.event_id = ?
    ";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $eventId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die('Event not found.');
}

$event = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
    <base href="">
    <meta charset="utf-8" />
    <title>ATTENDANCE SYSTEM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="assets/media/logos/soljar_ico.ico" />
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <!--end::Global Stylesheets Bundle-->
    <style>
        .event-img-detail {
            width: 100%;
            max-width: 640px;
            aspect-ratio: 16 / 9;
            object-fit: cover;
            border-radius: 15px;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed toolbar-tablet-and-mobile-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
    <!--begin::Main-->
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Page-->
        <div class="page d-flex flex-row flex-column-fluid">
            <!--begin::Wrapper-->
            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                <!--begin::Header-->
                <?php include "include/header.php"; ?>
                <!--end::Header-->
                <!--begin::Content-->
                <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                    <!--begin::Toolbar-->
                    <?php include "include/toolbar.php"; ?>
                    <!--end::Toolbar-->

                    <!--begin::Post-->
                    <div class="card" style="width: 90%; align-self:center;">
                        <br>
                        <!-- Back Button -->
                        <a href="Part_EventList.php" class="btn btn-primary btn-sm ms-6" style="width: 70px;">
                            Back
                        </a>

                        <!-- Event Image -->
                        <img src="/attendance/<?= htmlspecialchars($event['event_image']) ?>"
                            class="event-img-detail" alt="Event Image">
                        <div class="card-body">
                            <div class="container text-center">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title">Card title</h5>
                                    </div>
                                    <div class="col">
                                        2 of 2
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        1 of 3
                                    </div>
                                    <div class="col">
                                        2 of 3
                                    </div>
                                    <div class="col">
                                        3 of 3
                                    </div>
                                </div>
                            </div>
                            <h5 class="card-title">Card title</h5>
                            <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p>
                            <a href="#" class="btn btn-primary">Go somewhere</a>
                        </div>
                    </div>










                    <!--end::Post-->
                </div>
                <!--end::Content-->

                <!--begin::Footer-->
                <?php include "include/footer.php"; ?>
                <!--end::Footer-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Page-->
    </div>
    <!--end::Root-->
    <!--begin::Scrolltop-->
    <?php include "include/scrolltop.php"; ?>
    <!--end::Scrolltop-->
    <!--end::Main-->

    <!--begin::Javascript-->
    <div>
        <!--begin::Global Javascript Bundle(used by all pages)-->
        <script src="assets/plugins/global/plugins.bundle.js"></script>
        <script src="assets/js/scripts.bundle.js"></script>
        <!--end::Global Javascript Bundle-->
        <!--begin::Page Custom Javascript(used by this page)-->
        <script src="assets/js/custom/widgets.js"></script>
        <script src="assets/js/custom/apps/chat/chat.js"></script>
        <script src="assets/js/custom/modals/create-app.js"></script>
        <script src="assets/js/custom/modals/upgrade-plan.js"></script>
        <!--end::Page Custom Javascript-->
        <script>

        </script>
    </div>
    <!--end::Javascript-->
</body>