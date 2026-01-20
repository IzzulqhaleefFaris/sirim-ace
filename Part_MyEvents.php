<?php
session_start();
include "include/config.php";

// Redirect to login if not logged in
if (!isset($_SESSION['userId'])) {
    header('Location: /attendance');
    exit;
}

$participantId = $_SESSION['userId'];

$sql = "
    SELECT
        r.registration_id,
        r.event_id,
        e.event_name,
        e.event_startDate,
        e.event_endDate,
        e.event_status,
        e.event_image,
        l.location_name,
        a.attendance_id,
        a.check_in_time
    FROM att_registration r
    JOIN att_event e ON e.event_id = r.event_id
    LEFT JOIN att_location l ON e.location_id = l.location_id
    LEFT JOIN att_attendance a ON a.registration_id = r.registration_id
    WHERE r.participant_id = ?
    ORDER BY e.event_startDate DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $participantId);
$stmt->execute();
$registrations = $stmt->get_result();

function buildQrUrl(string $registrationId): string
{
    $data = urlencode($registrationId);
    return "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={$data}";
}
?>

<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
    <base href="">
    <meta charset="utf-8" />
    <title>My Events | ATTENDANCE SYSTEM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="assets/media/logos/soljar_ico.ico" />
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!--end::Global Stylesheets Bundle-->
    <style>
        .qr-img {
            width: 180px;
            height: 180px;
            object-fit: contain;
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
                    <div class="container py-6">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <h2 class="fw-bold mb-0">My Events</h2>
                                <p class="text-muted mb-0">QR anda berada di sini. Tunjukkan kepada petugas untuk imbasan.</p>
                            </div>
                            <a href="Part_EventList.php" class="btn btn-light border">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Senarai Event
                            </a>
                        </div>

                        <?php if ($registrations && $registrations->num_rows > 0): ?>
                            <div class="row g-4">
                                <?php while ($row = $registrations->fetch_assoc()): ?>
                                    <?php
                                    $hasAttendance = !empty($row['attendance_id']);
                                    $eventStatus   = $row['event_status'];
                                    $statusLabel   = $hasAttendance
                                        ? 'Present'
                                        : ($eventStatus === 'Completed' ? 'Absent' : 'Not checked-in');
                                    $qrUrl         = buildQrUrl($row['registration_id']);
                                    ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card h-100 shadow-sm border">
                                            <div class="card-body d-flex flex-column">
                                                <div class="mb-3">
                                                    <span class="badge bg-light text-dark border mb-2">Registration ID: <?= htmlspecialchars($row['registration_id']) ?></span>
                                                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($row['event_name']) ?></h5>
                                                    <div class="text-muted">
                                                        <?= date('d M Y', strtotime($row['event_startDate'])) ?> – <?= date('d M Y', strtotime($row['event_endDate'])) ?>
                                                    </div>
                                                    <?php if (!empty($row['location_name'])): ?>
                                                        <div class="text-muted small">Lokasi: <?= htmlspecialchars($row['location_name']) ?></div>
                                                    <?php endif; ?>
                                                    <div class="mt-2">
                                                        <span class="badge bg-primary me-2"><?= htmlspecialchars($eventStatus) ?></span>
                                                        <span class="badge bg-<?= $hasAttendance ? 'success' : 'warning' ?> text-dark">
                                                            <?= htmlspecialchars($statusLabel) ?>
                                                        </span>
                                                    </div>
                                                    <?php if ($hasAttendance && !empty($row['check_in_time'])): ?>
                                                        <div class="text-muted small mt-1">Check-in: <?= htmlspecialchars($row['check_in_time']) ?></div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="text-center mb-3">
                                                    <img src="<?= htmlspecialchars($qrUrl) ?>" alt="QR Code" class="qr-img border rounded p-2 bg-white" />
                                                    <div class="small text-muted mt-2">QR mengandungi Registration ID anda.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">Anda belum mendaftar mana-mana event.</div>
                        <?php endif; ?>
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
    </div>
    <!--end::Javascript-->
</body>
<!--end::Body-->

</html>
