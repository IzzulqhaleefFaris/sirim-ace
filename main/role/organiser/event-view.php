<?php
session_start();
include "../../../include/config.php";

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
    l.location_buildingName,
    l.address_line1,
    l.address_line2,
    l.address_city,
    l.address_postcode,
    l.location_room,
    l.location_level,
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

//Disable Daftar button if registered
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
            max-width: 900px;
        }

        .event-hero {
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        }

        .event-hero-header {
            background: linear-gradient(135deg, #4f46e5, #6d28d9);
            color: #fff;
            padding: 18px 24px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .event-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .event-date {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.95rem;
        }

        .event-description {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            margin-top: 8px;
        }

        .event-cover {
            max-width: 100%;
            margin: 0 auto;
            height: auto;
            background: #f5f7fb;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .event-img-detail {
            width: 65%;
            aspect-ratio: 16 / 10;
            object-fit: cover;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .info-section {
            border: 1px solid #eef1f6;
            border-radius: 14px;
            padding: 16px;
            background: #ffffff;
            margin-bottom: 14px;
        }

        .info-section-title {
            font-weight: 700;
            margin-bottom: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
        }

        .info-item {
            background: #f9fafb;
            border: 1px solid #eef1f6;
            border-radius: 12px;
            padding: 10px 12px;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 4px;
        }

        .info-value {
            font-weight: 600;
            color: #111827;
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
                        <div class="card event-hero border-0">
                            <div class="event-hero-header">
                                <div>
                                    <h2 class="event-title mb-1"><?= htmlspecialchars($event['event_name']) ?></h2>
                                    <div class="event-date">
                                        <?= $startDate ?> – <?= $endDate ?>
                                    </div>
                                    <div class="event-description">
                                        <?= nl2br(htmlspecialchars($event['event_description'] ?? '-')) ?>
                                    </div>
                                </div>
                                <a href="browse-event-list.php" class="btn btn-light btn-sm">
                                    <i class="bi bi-arrow-left me-1"></i> Back
                                </a>
                            </div>

                            <div class="row g-0">
                                <div class="col-12">
                                    <div class="event-cover">
                                        <img src="/attendance/<?= htmlspecialchars($event['event_image']) ?>"
                                            class="event-img-detail"
                                            alt="Event Image">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-4 p-lg-5">
                                        <div class="info-section">
                                            <div class="info-section-title">Information</div>
                                            <div class="info-grid">
                                                <div class="info-item">
                                                    <div class="info-label">Event Type</div>
                                                    <div class="info-value"><?= htmlspecialchars($event['event_type_name'] ?? '-') ?></div>
                                                </div>
                                                <div class="info-item">
                                                    <div class="info-label">Status</div>
                                                    <div class="info-value"><?= htmlspecialchars($event['event_status'] ?? '-') ?></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="info-section">
                                            <div class="info-section-title">Location & Address</div>
                                            <div class="info-grid">
                                                <div class="info-item">
                                                    <div class="info-label">Location</div>
                                                    <div class="info-value"> <?= htmlspecialchars($event['location_name'] ?? '-') ?></div>
                                                </div>
                                                <div class="info-item">
                                                    <div class="info-label">Address</div>
                                                    <div class="info-value"><?= htmlspecialchars($fullAddress) ?></div>
                                                </div>
                                                <div class="info-item">
                                                    <div class="info-label">Building</div>
                                                    <div class="info-value"><?= htmlspecialchars($event['location_buildingName'] ?? '-') ?></div>
                                                </div>
                                                <div class="info-item">
                                                    <div class="info-label">Level</div>
                                                    <div class="info-value"><?= htmlspecialchars($event['location_level'] ?? '-') ?></div>
                                                </div>
                                                <div class="info-item">
                                                    <div class="info-label">Room</div>
                                                    <div class="info-value"><?= htmlspecialchars($event['location_room'] ?? '-') ?></div>
                                                </div>
                                                <div class="info-item">
                                                    <div class="info-label">State</div>
                                                    <div class="info-value"><?= htmlspecialchars($event['state_name'] ?? '-') ?></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="info-section">
                                            <div class="info-section-title">Registration</div>
                                            <div class="info-grid">
                                                <div class="info-item">
                                                    <div class="info-label">Opens</div>
                                                    <div class="info-value">📝 <?= $openDate ?></div>
                                                </div>
                                                <div class="info-item">
                                                    <div class="info-label">Closes</div>
                                                    <div class="info-value">⛔ <?= $closeDate ?></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-wrap align-items-center gap-2 mt-4">
                                            <?php if ($isRegistered): ?>
                                                <button class="btn btn-secondary btn-lg px-5" disabled>
                                                    Registered
                                                </button>
                                                <small class="text-muted mb-0">
                                                    You have successfully registered for this event.
                                                </small>
                                            <?php elseif ($isRegistrationClosed): ?>
                                                <button class="btn btn-secondary btn-lg px-5" disabled>
                                                    Registration Closed
                                                </button>
                                                <small class="text-muted mb-0">
                                                    Registration has closed for this event.
                                                </small>
                                            <?php else: ?>
                                                <button type="button"
                                                    class="btn btn-primary btn-lg rounded px-5 fw-bold"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmRegisterModal">
                                                    <i class="bi bi-check-circle me-2"></i> Register
                                                </button>
                                            <?php endif; ?>
                                        </div>
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