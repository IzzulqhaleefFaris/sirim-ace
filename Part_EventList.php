<?php
session_start();
include "include/config.php";

// Redirect to login if not logged in
if (!isset($_SESSION['userId'])) {
    header('Location: /attendance');
    exit;
}



//SQL Query
$sql = "SELECT DISTINCT
        e.event_id,
        e.event_name,
        e.event_startDate,
        e.event_endDate,
        e.event_status,
        e.event_image,
        t.event_type_name,
        l.location_name,
        s.state_name
        FROM att_event e
        LEFT JOIN att_event_type t ON e.event_type_id = t.event_type_id
        LEFT JOIN att_location l ON e.location_id = l.location_id
        LEFT JOIN att_state s ON l.state_id = s.state_id
        ORDER BY e.event_startDate ASC";
$res = $conn->query($sql);

//color for event status
$colors = [
    'Upcoming'  => '#6c757d',
    'Completed' => '#28a745',
    'Current'   => '#007bff',
];
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
    <!--end::Global Stylesheets Bundle-->
    <style>
        .event-img {
            width: 100%;
            height: 180px; /* fixed height */
            object-fit: cover; /* crop instead of stretch */
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
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
                    <div class="container px-4 px-lg-5 mt-5">
                        <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-start">
                            <?php if ($res && $res->num_rows > 0): ?>
                                <?php while ($event = $res->fetch_assoc()): ?>
                                    <div class="col mb-5">
                                        <div class="card h-100" style="border-radius: 20px;">
                                            <!-- Event image-->
                                            <?php
                                            $eventImage = "/attendance/" . $event['event_image'];
                                            ?>
                                            <img class="card-img-top event-img" src="<?= htmlspecialchars($eventImage) ?>"
                                                alt="Event Image" />

                                            <!-- Event details-->
                                            <div class="card-body p-4">
                                                <div class="">
                                                    <!-- Event name-->
                                                    <h2 class="fw-bolder"><?= htmlspecialchars($event['event_name']) ?></h2>
                                                    <!-- Event location-->
                                                    <h6 class="fw-bolder"><?= htmlspecialchars($event['location_name']) ?> - <?= htmlspecialchars($event['state_name']) ?></h6>
                                                    <!-- Event date -->
                                                    <div class="small">
                                                        <?= date('d M Y', strtotime($event['event_startDate'])) ?>
                                                        –
                                                        <?= date('d M Y', strtotime($event['event_endDate'])) ?>
                                                    </div>
                                                </div>
                                                <!-- Event status badge -->
                                                <div class="mt-2">
                                                    <?php
                                                    $status = $event['event_status'];
                                                    $bgColor = $colors[$status] ?? '#343a40';
                                                    ?>
                                                    <span class="badge px-4 py-2"
                                                        style="background-color: <?= $bgColor ?>; color: #fff;">
                                                        <?= htmlspecialchars($status) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <!-- Event See Details Button -->
                                            <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                                <div class="text-center">
                                                    <a class="btn btn-light-dark mt-auto rounded-pill px-10" href="Part_EventView.php?id=<?= $event['event_id'] ?>">
                                                        See Details
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="col-12 text-center">
                                    <p class="text-muted">No events available</p>
                                </div>
                            <?php endif; ?>
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