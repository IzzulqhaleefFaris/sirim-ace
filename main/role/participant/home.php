<?php
session_start();
include "../../../include/config.php";
/** @var mysqli $conn */
include "../../../include/updateEventStatus.php";
include "../../../include/permissions.php";

require_login();
updateEventStatuses($conn);

$searchTerm = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$stateFilter = isset($_GET['state']) ? trim((string)$_GET['state']) : '';
$dateFilter = isset($_GET['date']) ? trim((string)$_GET['date']) : '';
$statusFilter = isset($_GET['status']) ? trim((string)$_GET['status']) : 'All';
$allowedStatus = ['All', 'Upcoming', 'Current', 'Completed'];
if (!in_array($statusFilter, $allowedStatus, true)) {
    $statusFilter = 'All';
}

$states = [];
$latestEvents = [];
$events = [];
$eventsError = null;

if (isset($conn) && $conn instanceof mysqli) {
    $stateRes = $conn->query("SELECT state_id, state_name FROM att_state ORDER BY state_name ASC");
    if ($stateRes) {
        while ($state = $stateRes->fetch_assoc()) {
            $states[] = $state;
        }
    }

    $latestSql = "
        SELECT
            e.event_id,
            e.event_name,
            e.event_image,
            e.event_startDate,
            t.event_type_name
        FROM att_event e
        LEFT JOIN att_event_type t ON t.event_type_id = e.event_type_id
        ORDER BY e.event_startDate DESC
        LIMIT 5";

    $latestRes = $conn->query($latestSql);
    if ($latestRes) {
        while ($row = $latestRes->fetch_assoc()) {
            $latestEvents[] = $row;
        }
    }

    $eventsSql = "
        SELECT
            e.event_id,
            e.event_name,
            e.event_startDate,
            e.event_startTime,
            e.event_image,
            e.event_status,
            t.event_type_name,
            l.location_name,
            s.state_name,
            l.state_id
        FROM att_event e
        LEFT JOIN att_event_type t ON t.event_type_id = e.event_type_id
        LEFT JOIN att_location l ON l.location_id = e.location_id
        LEFT JOIN att_state s ON s.state_id = l.state_id
        WHERE 1=1";

    $params = [];
    $types = '';

    if ($statusFilter !== 'All') {
        $eventsSql .= " AND e.event_status = ?";
        $params[] = $statusFilter;
        $types .= 's';
    }

    if ($searchTerm !== '') {
        $eventsSql .= " AND (e.event_name LIKE ? OR l.location_name LIKE ? OR s.state_name LIKE ?)";
        $searchWildcard = '%' . $searchTerm . '%';
        $params[] = $searchWildcard;
        $params[] = $searchWildcard;
        $params[] = $searchWildcard;
        $types .= 'sss';
    }

    if ($stateFilter !== '') {
        $eventsSql .= " AND l.state_id = ?";
        $params[] = $stateFilter;
        $types .= 's';
    }

    if ($dateFilter !== '') {
        $eventsSql .= " AND DATE(e.event_startDate) = ?";
        $params[] = $dateFilter;
        $types .= 's';
    }

    $eventsSql .= " ORDER BY e.event_startDate DESC, e.event_startTime DESC";

    $eventsStmt = $conn->prepare($eventsSql);
    if ($eventsStmt) {
        if (!empty($params)) {
            $eventsStmt->bind_param($types, ...$params);
        }

        $eventsStmt->execute();
        $eventsRes = $eventsStmt->get_result();
        if ($eventsRes) {
            while ($row = $eventsRes->fetch_assoc()) {
                $events[] = $row;
            }
        }
        $eventsStmt->close();
    } else {
        $eventsError = "Unable to load events list.";
    }
} else {
    $eventsError = "Database connection is not available.";
}

if (!function_exists('formatSimpleDate')) {
    function formatSimpleDate(?string $value): string
    {
        if (!$value) {
            return '-';
        }

        $date = date_create($value);
        return $date ? $date->format('d M Y') : htmlspecialchars($value);
    }
}

if (!function_exists('formatSimpleTime')) {
    function formatSimpleTime(?string $value): string
    {
        if (!$value) {
            return '-';
        }

        $time = date_create($value);
        return $time ? $time->format('h:i A') : htmlspecialchars($value);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <base href="">
    <meta charset="utf-8" />
    <title>Home | ATTENDANCE SYSTEM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="../../../assets/media/logos/ace.png" />

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />

    <link href="../../../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../../../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <style>
        :root {
            --ace-primary: #273a90;
            --ace-primary-hover: #1a7fe0;
            --ace-light-bg: #f9fbfd;
            --ace-badge-bg: #e6f4ff;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
        }

        /* ===== SECTION COMMON ===== */
        .section-badge {
            display: inline-block;
            background: var(--ace-badge-bg);
            color: var(--ace-primary);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .section-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 0.25rem;
        }

        .section-subtitle {
            color: #777;
            font-size: 0.95rem;
        }

        /* ===== CAROUSEL ===== */
        .home-section-card {
            border-radius: 14px;
            border: 1px solid #eee;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        }

        .carousel-image {
            width: 100%;
            height: 360px;
            object-fit: cover;
            border-radius: 12px;
        }

        .carousel-caption-box {
            background: rgba(0, 0, 0, 0.58);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            display: inline-block;
            max-width: min(640px, 92%);
        }

        #latestEventsCarousel .carousel-caption {
            left: 1.25rem;
            right: 1.25rem;
            bottom: 3.4rem;
        }

        #latestEventsCarousel .carousel-indicators {
            margin-bottom: 1rem;
        }

        #latestEventsCarousel .carousel-indicators [data-bs-target] {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        @media (max-width: 768px) {
            #latestEventsCarousel .carousel-caption {
                bottom: 2.9rem;
            }

            .carousel-caption-box {
                padding: 0.6rem 0.8rem;
            }
        }

        /* ===== SEARCH ===== */
        .search-wrapper {
            border-radius: 14px;
            border: 1px solid #eee;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        }

        .search-wrapper .form-control,
        .search-wrapper .form-select {
            background-color: #f5f8fa;
            border: 1px solid #e4e6ef;
            border-radius: 8px;
        }

        .search-wrapper .form-control:focus,
        .search-wrapper .form-select:focus {
            border-color: var(--ace-primary);
            box-shadow: none;
        }

        .btn-ace {
            background: var(--ace-primary);
            color: #fff;
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-ace:hover {
            background: var(--ace-primary-hover);
            color: #fff;
        }

        /* ===== EVENT CARDS ===== */
        .event-grid-card {
            border: 1px solid #eee;
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
            transition: box-shadow 0.3s, transform 0.2s;
            height: 100%;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        }

        .event-grid-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .event-grid-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .event-meta {
            min-height: 66px;
        }

        .event-meta i {
            color: var(--ace-primary);
            margin-right: 4px;
        }

        .badge-ace {
            background: var(--ace-badge-bg);
            color: var(--ace-primary);
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
        }

        .btn-ace-sm {
            background: var(--ace-primary);
            color: #fff;
            border: 2px solid var(--ace-primary);
            border-radius: 8px;
            padding: 0.35rem 1rem;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .btn-ace-sm:hover {
            background: var(--ace-primary-hover);
            border-color: var(--ace-primary-hover);
            color: #fff;
        }

        .btn-ace-outline-sm {
            background: transparent;
            color: var(--ace-primary);
            border: 2px solid var(--ace-primary);
            border-radius: 8px;
            padding: 0.35rem 1rem;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .btn-ace-outline-sm:hover {
            background: var(--ace-badge-bg);
        }

        .btn-outline-ace {
            background: transparent;
            color: var(--ace-primary);
            border: 2px solid var(--ace-primary);
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-outline-ace:hover {
            background: var(--ace-badge-bg);
            color: var(--ace-primary);
        }

        .status-badge {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.3rem 0.65rem;
            border-radius: 20px;
        }

        .status-upcoming { background: #fff8dd; color: #f5a623; }
        .status-current { background: #e6f4ff; color: var(--ace-primary); }
        .status-completed { background: #e8fff3; color: #50cd89; }

        .placeholder-section {
            border: 2px dashed #d1d5db;
            border-radius: 14px;
            background: #f8fafc;
        }
    </style>
</head>

<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed toolbar-tablet-and-mobile-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
    <div class="d-flex flex-column flex-root">
        <div class="page d-flex flex-row flex-column-fluid">
            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                <?php include "../../../include/header.php"; ?>

                <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                    <?php include "../../../include/toolbar.php"; ?>

                    <div class="post d-flex flex-column-fluid" id="kt_post">
                        <div id="kt_content_container" class="container">
                            <div class="py-4 py-lg-8">
                                <div class="card mb-6 home-section-card">
                                    <div class="card-header border-0 pt-5 pb-0">
                                        <div>
                                            <span class="section-badge">Latest</span>
                                            <h3 class="section-title">Upcoming Events</h3>
                                        </div>
                                    </div>
                                    <div class="card-body pt-4">
                                        <?php if (empty($latestEvents)): ?>
                                            <div class="text-muted">No event available for the carousel yet.</div>
                                        <?php else: ?>
                                            <div id="latestEventsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4500">
                                                <div class="carousel-indicators">
                                                    <?php foreach ($latestEvents as $index => $event): ?>
                                                        <button type="button" data-bs-target="#latestEventsCarousel" data-bs-slide-to="<?php echo (int)$index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-label="Slide <?php echo (int)($index + 1); ?>"></button>
                                                    <?php endforeach; ?>
                                                </div>

                                                <div class="carousel-inner">
                                                    <?php foreach ($latestEvents as $index => $event): ?>
                                                        <?php
                                                        $rawImagePath = ltrim((string)($event['event_image'] ?? ''), '/');
                                                        $eventImage = '/sirimace/images/custom/no_image.jpg';
                                                        $imgVersion = time();
                                                        if ($rawImagePath !== '') {
                                                            $physPath = __DIR__ . '/../../../' . $rawImagePath;
                                                            if (is_file($physPath)) {
                                                                $eventImage = '/sirimace/' . $rawImagePath;
                                                                $imgVersion = (string)filemtime($physPath);
                                                            }
                                                        }
                                                        $eventImageUrl = $eventImage . '?v=' . urlencode((string)$imgVersion);
                                                        ?>
                                                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                                            <a href="event-view.php?id=<?php echo urlencode((string)$event['event_id']); ?>" class="d-block text-white text-decoration-none">
                                                                <img src="<?php echo htmlspecialchars($eventImageUrl); ?>" class="carousel-image" alt="Event image" onerror="this.onerror=null;this.src='/sirimace/images/custom/no_image.jpg';">
                                                                <div class="carousel-caption text-start">
                                                                    <div class="carousel-caption-box">
                                                                        <h5 class="mb-1 text-white"><?php echo htmlspecialchars((string)($event['event_name'] ?? '-')); ?></h5>
                                                                        <div class="small text-light">
                                                                            <?php echo htmlspecialchars(formatSimpleDate((string)($event['event_startDate'] ?? ''))); ?>
                                                                            <?php if (!empty($event['event_type_name'])): ?>
                                                                                | <?php echo htmlspecialchars((string)$event['event_type_name']); ?>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>

                                                <button class="carousel-control-prev" type="button" data-bs-target="#latestEventsCarousel" data-bs-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Previous</span>
                                                </button>
                                                <button class="carousel-control-next" type="button" data-bs-target="#latestEventsCarousel" data-bs-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Next</span>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="card mb-6 search-wrapper">
                                    <div class="card-body py-4">
                                        <form method="GET" class="row g-3 align-items-end">
                                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                                            <div class="col-lg-5">
                                                <label class="form-label mb-1 fw-semibold">Search</label>
                                                <input type="text" name="q" class="form-control" placeholder="Search event, location, or state" value="<?php echo htmlspecialchars($searchTerm); ?>">
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label mb-1 fw-semibold">State</label>
                                                <select name="state" class="form-select">
                                                    <option value="">All State</option>
                                                    <?php foreach ($states as $state): ?>
                                                        <option value="<?php echo htmlspecialchars((string)$state['state_id']); ?>" <?php echo $stateFilter === (string)$state['state_id'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars((string)$state['state_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label mb-1 fw-semibold">Date</label>
                                                <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($dateFilter); ?>">
                                            </div>
                                            <div class="col-lg-3 d-grid">
                                                <button type="submit" class="btn btn-ace">Search</button>
                                            </div>
                                        </form>

                                        <!-- Status filter chips -->
                                        <div id="filters" class="d-flex flex-wrap gap-2 mt-4">
                                            <?php
                                            $statusOptions = ['All', 'Upcoming', 'Current', 'Completed'];
                                            foreach ($statusOptions as $opt):
                                                $isActive = ($statusFilter === $opt);
                                                $chipQuery = http_build_query(['status' => $opt, 'q' => $searchTerm, 'state' => $stateFilter, 'date' => $dateFilter]);
                                            ?>
                                                <a href="?<?php echo $chipQuery; ?>" onclick="sessionStorage.setItem('scrollY',window.scrollY)" class="btn btn-sm rounded-pill px-4 <?php echo $isActive ? 'btn-ace' : 'btn-outline-ace'; ?>">
                                                    <?php echo htmlspecialchars($opt); ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php if ($searchTerm !== '' || $stateFilter !== '' || $dateFilter !== '' || $statusFilter !== 'All'): ?>
                                            <div class="mt-3">
                                                <a href="?" onclick="sessionStorage.setItem('scrollY',window.scrollY)" class="btn btn-danger btn-sm rounded-pill px-4"><i class="fas fa-times me-1"></i>Reset Filter</a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <span class="section-badge">Browse</span>
                                    <h2 class="section-title mb-4">Events by SIRIM</h2>
                                    <?php if ($eventsError): ?>
                                        <div class="alert alert-danger mb-0"><?php echo htmlspecialchars($eventsError); ?></div>
                                    <?php elseif (empty($events)): ?>
                                        <div class="alert alert-light border mb-0">No events found for your filter.</div>
                                    <?php else: ?>
                                        <div class="row g-4">
                                            <?php foreach ($events as $event): ?>
                                                <?php
                                                $rawImagePath = ltrim((string)($event['event_image'] ?? ''), '/');
                                                $eventImage = '/sirimace/images/custom/no_image.jpg';
                                                $imgVersion = time();
                                                if ($rawImagePath !== '') {
                                                    $physPath = __DIR__ . '/../../../' . $rawImagePath;
                                                    if (is_file($physPath)) {
                                                        $eventImage = '/sirimace/' . $rawImagePath;
                                                        $imgVersion = (string)filemtime($physPath);
                                                    }
                                                }
                                                $eventImageUrl = $eventImage . '?v=' . urlencode((string)$imgVersion);
                                                ?>
                                                <div class="col-md-6 col-xl-4">
                                                    <div class="card event-grid-card">
                                                        <img src="<?php echo htmlspecialchars($eventImageUrl); ?>" class="event-grid-image" alt="Event image" onerror="this.onerror=null;this.src='/sirimace/images/custom/no_image.jpg';">
                                                        <div class="card-body d-flex flex-column">
                                                            <h5 class="fw-bold mb-3"><?php echo htmlspecialchars((string)($event['event_name'] ?? '-')); ?></h5>
                                                            <div class="event-meta text-muted small mb-3">
                                                                <div><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars((string)($event['location_name'] ?? '-')); ?><?php echo !empty($event['state_name']) ? ' - ' . htmlspecialchars((string)$event['state_name']) : ''; ?></div>
                                                                <div><i class="fas fa-calendar"></i> <?php echo htmlspecialchars(formatSimpleDate((string)($event['event_startDate'] ?? ''))); ?></div>
                                                                <div><i class="fas fa-clock"></i> <?php echo htmlspecialchars(formatSimpleTime((string)($event['event_startTime'] ?? ''))); ?></div>
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <span class="badge-ace"><?php echo htmlspecialchars((string)($event['event_type_name'] ?? 'General')); ?></span>
                                                                    <?php
                                                                    $evStatus = $event['event_status'] ?? '';
                                                                    $statusClass = 'status-upcoming';
                                                                    if ($evStatus === 'Current') $statusClass = 'status-current';
                                                                    elseif ($evStatus === 'Completed') $statusClass = 'status-completed';
                                                                    ?>
                                                                    <span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($evStatus); ?></span>
                                                                </div>
                                                                <a href="event-view.php?id=<?php echo urlencode((string)$event['event_id']); ?>" class="btn-ace-sm">View</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="section p-7 text-center">
                                    <!-- Added Section -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php include "../../../include/footer.php"; ?>
            </div>
        </div>
    </div>

    <?php include "../../../include/scrolltop.php"; ?>

    <div>
        <script src="../../../assets/plugins/global/plugins.bundle.js"></script>
        <script src="../../../assets/js/scripts.bundle.js"></script>
        <script src="../../../assets/js/custom/widgets.js"></script>
        <script src="../../../assets/js/custom/apps/chat/chat.js"></script>
        <script src="../../../assets/js/custom/modals/create-app.js"></script>
        <script src="../../../assets/js/custom/modals/upgrade-plan.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script>
            (function() {
                var savedY = sessionStorage.getItem('scrollY');
                if (savedY !== null) {
                    sessionStorage.removeItem('scrollY');
                    window.scrollTo(0, parseInt(savedY, 10));
                }
            })();
        </script>
    </div>
</body>

</html>
