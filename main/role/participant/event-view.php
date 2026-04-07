<?php
session_start();
include "../../../include/config.php";

// Redirect to login if not logged in
if (!isset($_SESSION['userId'])) {
    header('Location: /sirimace');
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
    l.location_buildingName,
    l.address_line1,
    l.address_line2,
    l.address_city,
    l.address_postcode,
    l.location_room,
    l.location_level,
    s.state_name,
    u.nama AS owner_name,
    u.email AS owner_email
    FROM att_event e
    LEFT JOIN att_event_type t ON e.event_type_id = t.event_type_id
    LEFT JOIN att_location l ON e.location_id = l.location_id
    LEFT JOIN att_state s ON l.state_id = s.state_id
    LEFT JOIN user u ON e.event_owner_id = u.userId
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

$addressParts = array_filter([
    $event['address_line1'] ?? '',
    $event['address_line2'] ?? '',
    $event['address_city'] ?? '',
    $event['address_postcode'] ?? ''
]);
$fullAddress = !empty($addressParts) ? implode(', ', $addressParts) : '-';

$startDate = date('d M Y', strtotime($event['event_startDate']));
$endDate   = date('d M Y', strtotime($event['event_endDate']));

$openDate = date('d M Y', strtotime($event['event_openRegistration']));
$closeDate = date('d M Y', strtotime($event['event_closeRegistration']));
$isRegistrationClosed = strtotime(date('Y-m-d')) > strtotime($event['event_closeRegistration']);

//Disable Register button if registered
$isRegistered = false;
$chk = $conn->prepare("
    SELECT registration_id 
    FROM att_registration 
    WHERE event_id = ? AND participant_id = ?
");
$chk->bind_param("ss", $event['event_id'], $_SESSION['userId']);
$chk->execute();
$chk->store_result();
$isRegistered = $chk->num_rows > 0;
$chk->close();
?>

<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
    <base href="">
    <meta charset="utf-8" />
    <title>View Event | ATTENDANCE SYSTEM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="../../../assets/media/logos/soljar_ico.ico" />

    <!-- Global Javascript -->
    <script src="../../../assets/plugins/global/plugins.bundle.js"></script>
    <script src="../../../assets/js/scripts.bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


    <!--begin::Page Custom Javascript(used by this page)-->
    <script src="../../../assets/js/custom/widgets.js"></script>
    <script src="../../../assets/js/custom/apps/chat/chat.js"></script>
    <script src="../../../assets/js/custom/modals/create-app.js"></script>
    <script src="../../../assets/js/custom/modals/upgrade-plan.js"></script>
    <!--end::Page Custom Javascript-->

    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->

    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="../../../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../../../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <!--end::Global Stylesheets Bundle-->

    <style>
        .event-page {
            max-width: 1100px;
        }

        .simple-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
        }

        .simple-title {
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0;
        }

        .simple-subtitle {
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 4px;
        }

        .simple-section-title {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .simple-grid {
            display: grid;
            grid-template-columns: 170px 1fr;
            gap: 10px 14px;
        }

        .simple-label {
            color: #6b7280;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .simple-value {
            color: #111827;
            font-size: 0.92rem;
        }

        .event-image {
            width: 100%;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            object-fit: cover;
            aspect-ratio: 16 / 10;
            background: #f8fafc;
        }

        .description-box {
            white-space: pre-line;
            line-height: 1.55;
            color: #374151;
            font-size: 0.92rem;
        }

        @media (max-width: 768px) {
            .simple-grid {
                grid-template-columns: 1fr;
            }
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
                <?php include "../../../include/header.php"; ?>
                <!--end::Header-->
                <!--begin::Content-->
                <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                    <!--begin::Toolbar-->
                    <?php include "../../../include/toolbar.php"; ?>
                    <!--end::Toolbar-->

                    <!--begin::Post-->
                    <div class="container event-page my-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h2 class="simple-title"><?= htmlspecialchars($event['event_name']) ?></h2>
                            </div>
                            <button class="btn btn-light border bg-white btn-back-events" onclick="history.go(-1);"><i class="bi bi-arrow-left me-1"></i>Back </button>
                        </div>

                        <div class="row g-3">
                            <div class="col-lg-7">
                                <div class="simple-card p-3 p-lg-4 mb-3">
                                    <div class="simple-section-title">Event Information</div>
                                    <div class="simple-grid">
                                        <div class="simple-label">Event Type</div>
                                        <div class="simple-value"><?= htmlspecialchars($event['event_type_name'] ?? '-') ?></div>

                                        <div class="simple-label">Status</div>
                                        <div class="simple-value"><?= htmlspecialchars($event['event_status'] ?? '-') ?></div>

                                        <div class="simple-label">Event Date</div>
                                        <div class="simple-value"><?= $startDate ?><?= $startDate !== $endDate ? ' - ' . $endDate : '' ?></div>

                                        <div class="simple-label">Registration Date</div>
                                        <div class="simple-value"><?= $openDate ?><?= $openDate !== $closeDate ? ' - ' . $closeDate : '' ?></div>

                                        <div class="simple-label">Created By</div>
                                        <div class="simple-value"><?= htmlspecialchars($event['owner_name'] ?? '-') ?> (<?= htmlspecialchars($event['owner_email'] ?? '-') ?>)</div>
                                    </div>
                                </div>

                                <div class="simple-card p-3 p-lg-4 mb-3">
                                    <div class="simple-section-title">Location Information</div>
                                    <div class="simple-grid">
                                        <div class="simple-label">Location</div>
                                        <div class="simple-value"><?= htmlspecialchars($event['location_name'] ?? '-') ?></div>

                                        <div class="simple-label">State</div>
                                        <div class="simple-value"><?= htmlspecialchars($event['state_name'] ?? '-') ?></div>

                                        <div class="simple-label">Building</div>
                                        <div class="simple-value"><?= htmlspecialchars($event['location_buildingName'] ?? '-') ?></div>

                                        <div class="simple-label">Level</div>
                                        <div class="simple-value"><?= htmlspecialchars($event['location_level'] ?? '-') ?></div>

                                        <div class="simple-label">Room</div>
                                        <div class="simple-value"><?= htmlspecialchars($event['location_room'] ?? '-') ?></div>

                                        <div class="simple-label">Address</div>
                                        <div class="simple-value"><?= htmlspecialchars($fullAddress) ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="simple-card p-3 p-lg-4 mb-3">
                                    <?php
                                    $rawImg = ltrim((string)($event['event_image'] ?? ''), '/');
                                    $eventImg = '/sirimace/images/custom/no_image.jpg';
                                    $imgVer = time();
                                    if ($rawImg !== '') {
                                        $physImg = __DIR__ . '/../../../' . $rawImg;
                                        if (is_file($physImg)) {
                                            $eventImg = '/sirimace/' . $rawImg;
                                            $imgVer = (string)filemtime($physImg);
                                        }
                                    }
                                    $eventImgUrl = $eventImg . '?v=' . urlencode((string)$imgVer);
                                    ?>
                                    <img src="<?= htmlspecialchars($eventImgUrl) ?>" class="event-image" alt="Event Image" onerror="this.onerror=null;this.src='/sirimace/images/custom/no_image.jpg';">
                                </div>

                                <div class="simple-card p-3 p-lg-4 mb-3">
                                    <div class="simple-section-title">Description</div>
                                    <div class="description-box"><?= htmlspecialchars($event['event_description'] ?? '-') ?></div>
                                </div>

                                <div class="simple-card p-3 p-lg-4">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <?php if ($isRegistered): ?>
                                            <button class="btn btn-secondary" disabled>
                                                Registered
                                            </button>
                                            <small class="text-muted mb-0">
                                                You have successfully registered for this event.
                                            </small>
                                        <?php elseif ($isRegistrationClosed): ?>
                                            <button class="btn btn-secondary" disabled>
                                                Registration Closed
                                            </button>
                                            <small class="text-muted mb-0">
                                                Registration has closed for this event.
                                            </small>
                                        <?php else: ?>
                                            <button type="button"
                                                class="btn btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmRegisterModal">
                                                Register
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Registration Confirmation Modal -->
                    <div class="modal fade" id="confirmRegisterModal" tabindex="-1" aria-labelledby="confirmRegisterModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title" id="confirmRegisterModalLabel">Confirm Registration</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    Do you want to register for event "<strong><?= htmlspecialchars($event['event_name']) ?></strong>"?
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <a href="event-register.php?id=<?= $event['event_id'] ?>" class="btn btn-primary">Yes, Register</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Post-->
                </div>
                <!--end::Content-->

                <!--begin::Footer-->
                <?php include "../../../include/footer.php"; ?>
                <!--end::Footer-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Page-->
    </div>
    <!--end::Root-->

    <!--begin::Scrolltop-->
    <?php include "../../../include/scrolltop.php"; ?>
    <!--end::Scrolltop-->
    <!--end::Main-->

    <!--begin::Javascript-->
    <div>
        <!--begin::Global Javascript Bundle(used by all pages)-->
        <script src="../../../assets/plugins/global/plugins.bundle.js"></script>
        <script src="../../../assets/js/scripts.bundle.js"></script>
        <!--end::Global Javascript Bundle-->
    </div>
    <!--end::Javascript-->
</body>