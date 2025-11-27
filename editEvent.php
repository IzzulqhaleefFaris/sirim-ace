<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header("Location: /attendance");
    exit;
}

include "include/config.php";

// 1. Validate ID
if (!isset($_GET['id']) || trim($_GET['id']) === '') {
    die("Invalid event ID.");
}

$event_id = $_GET['id'];

// 2. Load event data
$sql = "
    SELECT e.*, l.location_name, l.building_name, l.location_address, 
           l.location_room, l.location_level, l.state_id AS loc_state
    FROM att_event e
    LEFT JOIN att_location l ON e.location_id = l.location_id
    WHERE e.event_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Event not found.");
}

$event = $result->fetch_assoc();

// 3. Load dropdown options
$types = $conn->query("SELECT * FROM att_event_type");
$states = $conn->query("SELECT * FROM att_state");
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
    <link href="assets/css/custom.css" rel="stylesheet" type="text/css" />
    <!--end::Global Stylesheets Bundle-->
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed toolbar-tablet-and-mobile-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
    <!--begin::Main-->
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Page-->
        <div class="page d-flex flex-row flex-column-fluid min-vh-100">
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
                    <div class="post d-flex flex-column-fluid" id="kt_post">
                        <div id="kt_content_container" class="container-fluid">
                            <div class="row justify-content-center">
                                <div class="col-md-8 col-lg-6">
                                    <div class="container mt-4">
                                        <h2>Edit Event</h2>

                                        <form action="updateEvent.php" method="POST">

                                            <!-- hidden id -->
                                            <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['event_id']) ?>">

                                            <div class="mb-3">
                                                <label>Nama Event</label>
                                                <input type="text" name="event_name" class="form-control"
                                                    value="<?= htmlspecialchars($event['event_name']) ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label>Jenis Event</label>
                                                <select name="event_type_id" class="form-control" required>
                                                    <?php while ($t = $types->fetch_assoc()): ?>
                                                        <option value="<?= $t['event_type_id'] ?>"
                                                            <?= $event['event_type_id'] == $t['event_type_id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($t['event_type_name']) ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>

                                            <div class="row">
                                                <div class="col">
                                                    <label>Event Mula</label>
                                                    <input type="date" name="event_startDate" class="form-control"
                                                        value="<?= $event['event_startDate'] ?>" required>
                                                </div>
                                                <div class="col">
                                                    <label>Event Tamat</label>
                                                    <input type="date" name="event_endDate" class="form-control"
                                                        value="<?= $event['event_endDate'] ?>" required>
                                                </div>
                                            </div>

                                            <hr>

                                            <h5>Lokasi</h5>

                                            <div class="mb-3">
                                                <label>Nama Lokasi</label>
                                                <input type="text" name="location_name" class="form-control"
                                                    value="<?= htmlspecialchars($event['location_name']) ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label>Bangunan</label>
                                                <input type="text" name="building_name" class="form-control"
                                                    value="<?= htmlspecialchars($event['building_name']) ?>">
                                            </div>

                                            <div class="mb-3">
                                                <label>Alamat</label>
                                                <input type="text" name="location_address" class="form-control"
                                                    value="<?= htmlspecialchars($event['location_address']) ?>">
                                                <input type="hidden" name="location_id" value="<?= htmlspecialchars($event['location_id']) ?>">

                                            </div>

                                            <div class="mb-3">
                                                <label>Negeri</label>
                                                <select name="state_id" class="form-control" required>
                                                    <?php while ($s = $states->fetch_assoc()): ?>
                                                        <option value="<?= $s['state_id'] ?>"
                                                            <?= $event['loc_state'] == $s['state_id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($s['state_name']) ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>

                                            <button class="btn btn-primary mt-3">Update Event</button>
                                            <a href="eventList.php" class="btn btn-secondary mt-3">Cancel</a>

                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Section-->
                </div>
                <!--end::Container-->
            </div>
            <!--end::Post-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Wrapper-->
    <footer>
        <?php include "include/footer.php"; ?>
    </footer>
    </div>
    <!--end::Page-->
    </div>
    <!--end::Root-->
</body>
<!--end::Body-->

</html>