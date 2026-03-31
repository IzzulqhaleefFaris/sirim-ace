<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../../include/config.php';
include '../../../include/updateEventStatus.php';
include '../../../include/permissions.php';

require_manage_events();

// Update event statuses based on current date
updateEventStatuses($conn);

// Get statistics
$stats = [
    'total' => 0,
    'current' => 0,
    'upcoming' => 0,
    'completed' => 0
];

$hasOwnerColumn = has_event_owner_column($conn);

$statsSql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN event_status = 'Current' THEN 1 ELSE 0 END) as current,
    SUM(CASE WHEN event_status = 'Upcoming' THEN 1 ELSE 0 END) as upcoming,
    SUM(CASE WHEN event_status = 'Completed' THEN 1 ELSE 0 END) as completed
    FROM att_event";

if (!is_admin() && $hasOwnerColumn) {
    $statsSql .= " WHERE event_owner_id = ?";
    $statsStmt = $conn->prepare($statsSql);
    $statsStmt->bind_param("s", $_SESSION['userId']);
    $statsStmt->execute();
    $statsRes = $statsStmt->get_result();
} else {
    $statsRes = $conn->query($statsSql);
}

if ($statsRes && $row = $statsRes->fetch_assoc()) {
    $stats = [
        'total' => (int)$row['total'],
        'current' => (int)$row['current'],
        'upcoming' => (int)$row['upcoming'],
        'completed' => (int)$row['completed']
    ];
}

if (isset($statsStmt)) {
    $statsStmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
    <base href="">
    <meta charset="utf-8" />
    <title>Events | ATTENDANCE SYSTEM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="../../../assets/media/logos/soljar_ico.ico" />


    <!-- Global Javascript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

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
    <!--end::Global Stylesheets Bundle-->

    <style>
        .event-table {
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #e9ecef;
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .event-table thead th {
            white-space: nowrap;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #6c757d;
            font-weight: 700;
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
        }

        .event-table tbody td {
            vertical-align: middle !important;
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
            border-bottom: 1px solid #f1f3f5;
        }

        .event-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .event-table tbody tr:hover {
            background: #fcfdff;
        }

        .stat-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15) !important;
        }

        .event-name {
            font-weight: 600;
            color: #1a1a1a;
        }

        .badge-custom {
            padding: 6px 12px;
            font-weight: 500;
            font-size: 0.85em;
        }

        .action-buttons {
            white-space: nowrap;
        }

        .action-buttons .btn {
            margin: 2px;
        }

        .col-no,
        .col-status,
        .col-actions {
            text-align: center;
        }

        .col-no {
            width: 56px;
        }

        .col-actions {
            width: 210px;
        }
    </style>
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed toolbar-tablet-and-mobile-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">

    <?php
    if (!empty($_SESSION['msg'])):
        $msgType = $_SESSION['msg']['type'] ?? 'info';
        $msgText = $_SESSION['msg']['text'] ?? '';
    ?>
        <div class="alert alert-<?= htmlspecialchars($msgType) ?> alert-dismissible fade show m-4" role="alert">
            <?= $msgText ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php
        unset($_SESSION['msg']); // clear message after display
    endif;
    ?>

    <!--begin::Main-->
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Page-->
        <div class="page d-flex flex-row flex-column-fluid min-vh-100">
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

                    <!--begin::Content-->
                    <div class="post d-flex flex-column-fluid" id="kt_post">
                        <div id="kt_content_container" class="container-fluid">
                            <!-- Statistics Cards -->
                            <div class="row g-3 mb-4 px-20">
                                <div class="col-md-3">
                                    <div class="card stat-card shadow-sm border-0 h-100 border-start border-3 border-dark">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <div class="text-muted small mb-1 fs-5">Total Events</div>
                                                    <div class="h3 fw-bold mb-0"><?= number_format($stats['total']) ?></div>
                                                </div>
                                                <div class="fs-1 opacity-75">
                                                    <i class="bi bi-calendar-event " style="font-size: 2rem; color: #212529;"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card stat-card shadow-sm border-0 h-100 border-start border-3 border-primary">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <div class="text-muted small mb-1 fs-5">Current</div>
                                                    <div class="h3 fw-bold mb-0 text-primary"><?= number_format($stats['current']) ?></div>
                                                </div>
                                                <div class="fs-1 text-primary opacity-75">
                                                    <i class="bi bi-play-circle" style="font-size: 2rem; color: #0d6efd;"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card stat-card shadow-sm border-0 h-100 border-start border-3 border-secondary">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <div class="text-muted small mb-1 fs-5">Upcoming</div>
                                                    <div class="h3 fw-bold mb-0 text-secondary"><?= number_format($stats['upcoming']) ?></div>
                                                </div>
                                                <div class="fs-1 text-secondary opacity-75">
                                                    <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card stat-card shadow-sm border-0 h-100 border-start border-3 border-success">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <div class="text-muted small mb-1 fs-5">Completed</div>
                                                    <div class="h3 fw-bold mb-0 text-success"><?= number_format($stats['completed']) ?></div>
                                                </div>
                                                <div class="fs-1 text-success opacity-75">
                                                    <i class="bi bi-check-circle" style="font-size: 2rem; color: #198754;"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Main Card -->
                            <div class="card shadow-sm">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                    <div>
                                        <h5 class="card-title mb-0 fw-bold">
                                            <i class="bi bi-calendar3 me-2 text-primary"></i>Event List
                                        </h5>
                                        <small class="text-muted">Manage and monitor all your events</small>
                                    </div>
                                    <a href="create-event.php" class="btn btn-primary d-flex align-items-center">
                                        <i class="bi bi-plus-circle me-2"></i> Add Event
                                    </a>
                                </div>

                                <div class="card-body">
                                    <div class="card-body">
                                        <?php if (!empty($_SESSION['msg'])): ?>
                                            <div class="alert alert-<?= htmlspecialchars($_SESSION['msg']['type']) ?> alert-dismissible fade show mb-3" role="alert">
                                                <i class="bi bi-<?= $_SESSION['msg']['type'] === 'success' ? 'check-circle' : ($_SESSION['msg']['type'] === 'danger' ? 'exclamation-triangle' : 'info-circle') ?> me-2"></i>
                                                <?= htmlspecialchars($_SESSION['msg']['text']) ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                            <?php unset($_SESSION['msg']); ?>
                                        <?php endif; ?>

                                        <div class="table-responsive">
                                            <table id="eventTable" class="table table-hover event-table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="col-no">No</th>
                                                        <th>Event Name</th>
                                                        <th>Type</th>
                                                        <th>Event Date</th>
                                                        <th>Location</th>
                                                        <th>State</th>
                                                        <th>Time</th>
                                                        <th>Registration Date</th>
                                                        <th class="col-status">Status</th>
                                                        <th class="col-actions"><i class="bi bi-gear me-1"></i>Actions</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    <?php
                                                    $sql = "SELECT
                                                                e.event_id,
                                                                e.event_name,
                                                                e.event_openRegistration,
                                                                e.event_closeRegistration,
                                                                e.event_startDate,
                                                                e.event_endDate,
                                                                e.event_startTime,
                                                                e.event_endTime,
                                                                e.event_status,
                                                                t.event_type_name,
                                                                l.location_name,
                                                                s.state_name
                                                                FROM att_event e
                                                                LEFT JOIN att_event_type t ON e.event_type_id = t.event_type_id
                                                                LEFT JOIN att_location l ON e.location_id = l.location_id
                                                                LEFT JOIN att_state s ON l.state_id = s.state_id";

                                                    if (!is_admin() && $hasOwnerColumn) {
                                                        $sql .= " WHERE e.event_owner_id = ?";
                                                    }

                                                    $sql .= " ORDER BY e.event_startDate DESC";

                                                    if (!is_admin() && $hasOwnerColumn) {
                                                        $stmtEvents = $conn->prepare($sql);
                                                        $stmtEvents->bind_param("s", $_SESSION['userId']);
                                                        $stmtEvents->execute();
                                                        $res = $stmtEvents->get_result();
                                                    } else {
                                                        $res = $conn->query($sql);
                                                    }

                                                    //Error handling
                                                    if (!$res) {
                                                        echo "<tr><td colspan='10' class='text-danger'> Database Error: " . htmlspecialchars($conn->error) . "</td></tr>";
                                                    } elseif ($res->num_rows == 0) {
                                                        echo "<tr><td colspan='10' class='text-center text-muted py-3'>No events found.</td></tr>";
                                                    } else {
                                                        function getStatusBadge($status)
                                                        {
                                                            $badges = [
                                                                'Upcoming' => '<span class="badge bg-secondary badge-custom">Upcoming</span>',
                                                                'Completed' => '<span class="badge bg-success badge-custom">Completed</span>',
                                                                'Current' => '<span class="badge bg-primary badge-custom">Current</span>',
                                                            ];
                                                            return $badges[$status] ?? '<span class="badge bg-secondary badge-custom">' . htmlspecialchars($status) . '</span>';
                                                        }

                                                        function formatDate($dateStr)
                                                        {
                                                            if (empty($dateStr)) return '-';
                                                            $ts = strtotime($dateStr);
                                                            return $ts ? date('d/m/Y', $ts) : $dateStr;
                                                        }

                                                        function formatDateRange($start, $end)
                                                        {
                                                            $startFmt = formatDate($start);
                                                            $endFmt = formatDate($end);

                                                            if ($startFmt === '-' && $endFmt === '-') {
                                                                return '-';
                                                            }

                                                            if ($startFmt === $endFmt || $endFmt === '-') {
                                                                return $startFmt;
                                                            }

                                                            if ($startFmt === '-') {
                                                                return $endFmt;
                                                            }

                                                            return $startFmt . ' - ' . $endFmt;
                                                        }

                                                        function formatTimeCompact($timeStr)
                                                        {
                                                            if (empty($timeStr)) return '';
                                                            $ts = strtotime($timeStr);
                                                            return $ts ? date('Hi', $ts) : '';
                                                        }

                                                        function formatTimeRange($start, $end)
                                                        {
                                                            $startFmt = formatTimeCompact($start);
                                                            $endFmt = formatTimeCompact($end);

                                                            if ($startFmt !== '' && $endFmt !== '') {
                                                                return $startFmt . '-' . $endFmt;
                                                            }

                                                            if ($startFmt !== '') {
                                                                return $startFmt;
                                                            }

                                                            if ($endFmt !== '') {
                                                                return $endFmt;
                                                            }

                                                            return '-';
                                                        }

                                                        $i = 1;
                                                        while ($row = $res->fetch_assoc()) {
                                                            $eventName = htmlspecialchars($row['event_name']);
                                                            $eventType = htmlspecialchars($row['event_type_name'] ?? '-');
                                                            $eventDate = formatDateRange($row['event_startDate'] ?? '', $row['event_endDate'] ?? '');
                                                            $locationName = htmlspecialchars($row['location_name'] ?? '-');
                                                            $stateName = htmlspecialchars($row['state_name'] ?? '-');
                                                            $timeRange = formatTimeRange($row['event_startTime'] ?? '', $row['event_endTime'] ?? '');
                                                            $registrationDate = formatDateRange($row['event_openRegistration'] ?? '', $row['event_closeRegistration'] ?? '');
                                                            $eventId = htmlspecialchars($row['event_id']);

                                                            echo "
                                                                <tr>
                                                                    <td class='text-center'>{$i}</td>
                                                                    <td>
                                                                        <div class='event-name'>{$eventName}</div>
                                                                    </td>
                                                                    <td>{$eventType}</td>
                                                                    <td class='text-nowrap'>{$eventDate}</td>
                                                                    <td>{$locationName}</td>
                                                                    <td>{$stateName}</td>
                                                                    <td class='text-nowrap'>{$timeRange}</td>
                                                                    <td class='text-nowrap'>{$registrationDate}</td>
                                                                    <td class='text-center'>" . getStatusBadge($row['event_status']) . "</td>
                                                                    <td class='text-center action-buttons'>
                                                                        <a href='event-registration.php?id={$eventId}'
                                                                           class='btn btn-info btn-sm'
                                                                           title='View Registrations'>
                                                                            <i class='bi bi-people'></i>
                                                                        </a>
                                                                        <a href='edit-event.php?id={$eventId}' 
                                                                           class='btn btn-warning btn-sm' 
                                                                           title='Edit Event'>
                                                                            <i class='bi bi-pencil'></i>
                                                                        </a>
                                                                        <button class='btn btn-danger btn-sm btn-delete' 
                                                                                data-id='{$eventId}'
                                                                                data-name='{$eventName}'
                                                                                title='Delete Event'>
                                                                            <i class='bi bi-trash'></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            ";
                                                            $i++;
                                                        }

                                                        if (isset($stmtEvents)) {
                                                            $stmtEvents->close();
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <!--End::Content-->
                <!--end::Container-->
            </div>
            <!--end::Post-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Wrapper-->
    <footer>
        <?php include "../../../include/footer.php"; ?>
    </footer>
    </div>
    <!--end::Page-->

    <!-- JS Script -->

    <!--begin::Javascript-->
    <!--begin::Global Javascript Bundle(used by all pages)-->
    <script src="../../../assets/plugins/global/plugins.bundle.js"></script>
    <script src="../../../assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->
    <!--begin::Page Custom Javascript(used by this page)-->
    <script src="../../../assets/js/custom/widgets.js"></script>
    <script src="../../../assets/js/custom/apps/chat/chat.js"></script>
    <script src="../../../assets/js/custom/modals/create-app.js"></script>
    <script src="../../../assets/js/custom/modals/upgrade-plan.js"></script>
    <!--end::Page Custom Javascript-->
    <script>
        document.addEventListener('click',
            function(e) {
                const btn = e.target.closest('.btn-delete');
                if (!btn) return;

                const idAttr = btn.dataset.id ?? btn.getAttribute('data-id');
                const id = String(idAttr ?? '').trim();
                const eventName = btn.dataset.name || 'event ini';

                if (!id || !/^[A-Za-z0-9_-]+$/.test(id)) {
                    alert('Error: Invalid event ID');
                    return;
                }

                if (!confirm(`Are you sure you want to delete event "${eventName}"?\n\nThis action cannot be undone.`)) return;

                // Disable button to prevent double-click
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                fetch('delete-event.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'id=' + encodeURIComponent(id)
                    })
                    .then(res => res.text())
                    .then(text => {
                        if (text.trim() === 'success') {
                            // Show success message
                            const alertDiv = document.createElement('div');
                            alertDiv.className = 'alert alert-success alert-dismissible fade show m-3';
                            alertDiv.innerHTML = `
                                <i class="bi bi-check-circle me-2"></i>
                                Event deleted successfully.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            `;
                            document.body.insertBefore(alertDiv, document.body.firstChild);

                            // Reload after short delay
                            setTimeout(() => location.reload(), 500);
                        } else {
                            alert('Failed to delete event: ' + text);
                            btn.disabled = false;
                            btn.innerHTML = '<i class="bi bi-trash"></i>';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Network error. Please try again.');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-trash"></i>';
                    });
            });
    </script>

    <script>
        <?php if (!empty($_SESSION['msg'])): ?>
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        <?php endif; ?>
    </script>

    <!-- <script>
        // Auto-hide alert after 4 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                const bsAlert  = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 4000);
    </script> -->

    <script>
        $(document).ready(function() {
            $('#eventTable').DataTable({
                order: [
                    [0, 'desc']
                ],
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ records",
                    info: "Showing _START_ to _END_ of _TOTAL_ records",
                    infoEmpty: "Showing 0 to 0 of 0 records",
                    infoFiltered: "(filtered from _MAX_ total records)",
                    zeroRecords: "No matching records found",
                    emptyTable: "No data available in table",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                responsive: true
            });
        });
    </script>
</body>