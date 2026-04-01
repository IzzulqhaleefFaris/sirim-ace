<?php
session_start();
include "../../../include/config.php";
include "../../../include/updateEventStatus.php";

// Update event statuses based on current date
updateEventStatuses($conn);

// Redirect to login if not logged in
if (!isset($_SESSION['userId'])) {
    header('Location: /sirimace');
    exit;
}

// Filter inputs
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : 'All';
$allowedStatus = ['All', 'Upcoming', 'Current', 'Completed'];

if (!in_array($statusFilter, $allowedStatus, true)) {
    $statusFilter = 'All';
}

// SQL Query with filters
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
        WHERE 1=1";

$params = [];
$types = '';

if ($statusFilter !== 'All') {
    $sql .= " AND e.event_status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if ($searchTerm !== '') {
    $sql .= " AND (e.event_name LIKE ? OR l.location_name LIKE ? OR s.state_name LIKE ?)";
    $searchWildcard = '%' . $searchTerm . '%';
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $types .= 'sss';
}

$sql .= " ORDER BY e.event_startDate ASC";

$stmt = $conn->prepare($sql);
if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$res = false;
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
}

// Badge style for event status
$statusBadgeClasses = [
    'Upcoming'  => 'badge-light-warning text-warning',
    'Completed' => 'badge-light-success text-success',
    'Current'   => 'badge-light-primary text-primary',
];

$activeFilterCount = 0;
if ($statusFilter !== 'All') {
    $activeFilterCount++;
}
if ($searchTerm !== '') {
    $activeFilterCount++;
}
?>



<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
    <base href="">
	<meta charset="utf-8" />
    <title>Browse Events | ATTENDANCE SYSTEM</title>
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
        .event-page-header {
            border-radius: 1rem;
        }

        .event-filter-card {
            border-radius: 1rem;
        }

        .event-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }

        .event-card {
            border-radius: 20px;
            overflow: hidden;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--kt-card-box-shadow);
        }

        .event-meta {
            min-height: 46px;
        }

        .event-title {
            min-height: 56px;
        }

        .filter-chip {
            min-width: 90px;
            text-align: center;
        }

        .event-empty-state {
            border: 1px dashed var(--kt-border-color);
            border-radius: 1rem;
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
                    <div class="container px-4 px-lg-5 mt-5">
                        <div class="card mb-6 event-page-header">
                            <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 py-5">
                                <div>
                                    <h2 class="mb-1">Explore Events</h2>
                                    <div class="text-muted">Find available events and open each card for details.</div>
                                </div>
                            </div>
                        </div>

                        <?php
                        $statusOptions = ['All', 'Upcoming', 'Current', 'Completed'];
                        ?>
                        <div class="card mb-7 event-filter-card">
                            <div class="card-body py-4">
                                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                                    <form method="GET" class="w-100 w-lg-50">
                                        <div class="input-group input-group-solid">
                                            <input
                                                type="text"
                                                name="q"
                                                class="form-control"
                                                placeholder="Search event, location, or state"
                                                value="<?= htmlspecialchars($searchTerm) ?>">
                                            <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
                                            <button class="btn btn-dark" type="submit">Search</button>
                                        </div>
                                    </form>

                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($statusOptions as $statusOption): ?>
                                            <?php
                                            $isActive = $statusFilter === $statusOption;
                                            $queryString = http_build_query([
                                                'status' => $statusOption,
                                                'q' => $searchTerm,
                                            ]);
                                            ?>
                                            <a
                                                href="?<?= $queryString ?>"
                                                class="btn <?= $isActive ? 'btn-light border border-dark text-dark fw-semibold' : 'btn-dark' ?> rounded-pill px-5 filter-chip">
                                                <?= htmlspecialchars($statusOption) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <?php if ($activeFilterCount > 0): ?>
                                    <div class="mt-4">
                                        <a href="browse-event-list.php" class="btn btn-sm btn-light-danger rounded-pill px-5">Clear Filters</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row gx-4 gx-lg-4 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-start">
                            <?php if ($res && $res->num_rows > 0): ?>
                                <?php while ($event = $res->fetch_assoc()): ?>
                                    <div class="col mb-5">
                                        <div class="card h-100 event-card">
                                            <!-- Event image-->
                                            <?php
                                            $rawImagePath = ltrim((string)($event['event_image'] ?? ''), '/');
                                            $eventImage = '/sirimace/' . $rawImagePath;

                                            // Cache-buster: refresh image when file content changes on disk
                                            $imageVersion = (string)($event['event_id'] ?? '0');
                                            if ($rawImagePath !== '') {
                                                $physicalImagePath = realpath(__DIR__ . '/../../../' . $rawImagePath);
                                                if ($physicalImagePath && is_file($physicalImagePath)) {
                                                    $mtime = @filemtime($physicalImagePath);
                                                    if ($mtime !== false) {
                                                        $imageVersion = (string)$mtime;
                                                    }
                                                }
                                            }

                                            $eventImageWithVersion = $eventImage . '?v=' . urlencode($imageVersion);
                                            $status = $event['event_status'];
                                            $badgeClass = $statusBadgeClasses[$status] ?? 'badge-light-dark text-dark';
                                            ?>
                                            <img class="card-img-top event-img" src="<?= htmlspecialchars($eventImageWithVersion) ?>"
                                                alt="Event Image" onerror="this.onerror=null;this.src='/sirimace/images/custom/no_image.jpg';" />

                                            <!-- Event details-->
                                            <div class="card-body p-4">
                                                <div>
                                                    <!-- Event name-->
                                                    <h4 class="fw-bolder mb-2 event-title"><?= htmlspecialchars($event['event_name']) ?></h4>
                                                    <!-- Event location-->
                                                    <div class="text-muted fw-semibold event-meta"><?= htmlspecialchars($event['location_name']) ?> - <?= htmlspecialchars($event['state_name']) ?></div>
                                                    <!-- Event date -->
                                                    <div class="small mt-2 text-gray-700">
                                                        <?= date('d M Y', strtotime($event['event_startDate'])) ?>
                                                        –
                                                        <?= date('d M Y', strtotime($event['event_endDate'])) ?>
                                                    </div>
                                                </div>
                                                <!-- Event status badge -->
                                                <div class="mt-2">
                                                    <span class="badge px-4 py-2 <?= $badgeClass ?>">
                                                        <?= htmlspecialchars($status) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <!-- Event See Details Button -->
                                            <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                                <div class="text-center">
                                                    <a class="btn btn-light-dark mt-auto rounded-pill px-10" href="event-view.php?id=<?= $event['event_id'] ?>">
                                                        See Details
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="event-empty-state text-center p-10 bg-light">
                                        <h4 class="mb-2">No events found</h4>
                                        <p class="text-muted mb-4">Try changing your search or status filter.</p>
                                        <a href="browse-event-list.php" class="btn btn-light-primary rounded-pill px-6">Reset View</a>
                                    </div>
                                </div>
                            <?php endif; ?>
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