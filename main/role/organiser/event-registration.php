<?php
session_start();
include "../../../include/config.php";
include "../../../include/updateEventStatus.php";
include "../../../include/permissions.php";

require_manage_events();

updateEventStatuses($conn);

$eventId = trim($_GET['id'] ?? '');
if ($eventId === '') {
    $_SESSION['msg'] = ['type' => 'warning', 'text' => 'Invalid event ID.'];
    header("Location: Org_EventList.php");
    exit;
}

require_event_owner_or_admin($conn, $eventId, 'event-list.php');

// Load event info
$eventStmt = $conn->prepare("
    SELECT e.event_id, e.event_name, e.event_startDate, e.event_endDate, e.event_status
    FROM att_event e
    WHERE e.event_id = ?
    LIMIT 1
");
$eventStmt->bind_param("s", $eventId);
$eventStmt->execute();
$eventRes = $eventStmt->get_result();
if (!$eventRes || $eventRes->num_rows === 0) {
    $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Event not found.'];
    header("Location: Org_EventList.php");
    exit;
}
$event = $eventRes->fetch_assoc();
$eventStmt->close();

// Load registrations + attendance + participant name/contact
$sql = "
    SELECT
        r.registration_id,
        r.participant_id,
        IFNULL(NULLIF(r.registration_source, ''), 'account') AS registration_source,
        COALESCE(NULLIF(u.nama, ''), NULLIF(r.walkin_name, ''), '-') AS participant_name,
        COALESCE(NULLIF(u.email, ''), NULLIF(r.walkin_email, ''), '-') AS participant_email,
        COALESCE(NULLIF(p.participant_phone, ''), NULLIF(r.walkin_phone, ''), '-') AS participant_phone,
        COALESCE(NULLIF(p.participant_company, ''), NULLIF(r.walkin_company, ''), '-') AS participant_company,
        a.attendance_id,
        a.check_in_time,
        e.event_status
    FROM att_registration r
    JOIN att_event e ON e.event_id = r.event_id
    LEFT JOIN user u ON u.userId = r.participant_id
    LEFT JOIN att_participant p ON p.participant_id = r.participant_id
    LEFT JOIN att_attendance a ON a.registration_id = r.registration_id
    WHERE r.event_id = ?
    ORDER BY participant_name ASC, r.registration_id ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $eventId);
$stmt->execute();
$rows = $stmt->get_result();

// Stats
$counts = ['registered' => 0, 'present' => 0, 'absent' => 0];
if ($rows) {
    $all = [];
    while ($r = $rows->fetch_assoc()) {
        $isPresent = !empty($r['attendance_id']);
        $isAbsent = (!$isPresent && ($r['event_status'] ?? '') === 'Completed');
        $status = $isPresent ? 'Present' : ($isAbsent ? 'Absent' : 'Registered');
        $r['_status'] = $status;
        $all[] = $r;
        $counts['registered']++;
        if ($status === 'Present') $counts['present']++;
        if ($status === 'Absent') $counts['absent']++;
    }
} else {
    $all = [];
}
$stmt->close();

$flashMsg = $_SESSION['msg'] ?? null;
unset($_SESSION['msg']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <base href="">
    <meta charset="utf-8" />
    <title>Event Registrations | ATTENDANCE SYSTEM</title>
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
        .stat-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15) !important;
        }

        .table thead th {
            white-space: nowrap;
        }
    </style>
</head>

<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed toolbar-tablet-and-mobile-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
    <div class="d-flex flex-column flex-root">
        <div class="page d-flex flex-row flex-column-fluid min-vh-100">
            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                <?php include "../../../include/header.php"; ?>

                <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                    <?php include "../../../include/toolbar.php"; ?>

                    <div class="post d-flex flex-column-fluid" id="kt_post">
                        <div id="kt_content_container" class="container-fluid py-2">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h2 class="fw-bold mb-1"><?= htmlspecialchars($event['event_name']) ?></h2>
                                    <div class="text-muted">
                                        <?= htmlspecialchars($event['event_startDate']) ?> – <?= htmlspecialchars($event['event_endDate']) ?>
                                        · <span class="badge bg-primary"><?= htmlspecialchars($event['event_status']) ?></span>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="event-list.php" class="btn btn-light border">
                                        <i class="bi bi-arrow-left me-1"></i>Back
                                    </a>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#organiserRegisterModal">
                                        <i class="bi bi-person-plus me-1"></i>Register
                                    </button>
                                    <?php
                                    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                                    $walkInUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/sirimace/walkin-register.php?event=' . urlencode($eventId);
                                    $walkInQr = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . urlencode($walkInUrl);
                                    ?>
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#walkInQrModal">
                                        <i class="bi bi-qr-code me-1"></i>Walk-in QR
                                    </button>
                                    <a class="btn btn-success"
                                        href="event-registrations-export.php?id=<?= urlencode($eventId) ?>">
                                        <i class="bi bi-download me-1"></i>Export CSV (Excel)
                                    </a>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="card stat-card shadow-sm border-0 h-100">
                                        <div class="card-body">
                                            <div class="text-muted small mb-1">Registered</div>
                                            <div class="h3 fw-bold mb-0"><?= number_format($counts['registered']) ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card stat-card shadow-sm border-0 h-100 border-start border-3 border-success">
                                        <div class="card-body">
                                            <div class="text-muted small mb-1">Present</div>
                                            <div class="h3 fw-bold mb-0 text-success"><?= number_format($counts['present']) ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card stat-card shadow-sm border-0 h-100 border-start border-3 border-danger">
                                        <div class="card-body">
                                            <div class="text-muted small mb-1">Absent (after Completed)</div>
                                            <div class="h3 fw-bold mb-0 text-danger"><?= number_format($counts['absent']) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card shadow-sm">
                                <?php if ($flashMsg && !empty($flashMsg['text'])): ?>
                                    <div class="card-body pb-0">
                                        <div class="alert alert-<?= htmlspecialchars($flashMsg['type'] ?? 'info') ?> mb-0">
                                            <?= htmlspecialchars($flashMsg['text']) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <div class="fw-bold">Registrations & Attendance</div>
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="text-muted small mb-0">Filter:</label>
                                        <select id="statusFilter" class="form-select form-select-sm" style="width: 180px;">
                                            <option value="">All</option>
                                            <option value="Registered">Registered</option>
                                            <option value="Present">Present</option>
                                            <option value="Absent">Absent</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($all)): ?>
                                        <div class="table-responsive">
                                            <table id="regTable" class="table table-hover align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Registration ID</th>
                                                        <th>Participant ID</th>
                                                        <th>Source</th>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>Phone</th>
                                                        <th>Company</th>
                                                        <th>Status</th>
                                                        <th>Check-in Time</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $i = 1; ?>
                                                    <?php foreach ($all as $r): ?>
                                                        <tr>
                                                            <td class="text-center"><?= $i++ ?></td>
                                                            <td><?= htmlspecialchars($r['registration_id']) ?></td>
                                                            <td><?= htmlspecialchars($r['participant_id']) ?></td>
                                                            <td>
                                                                <?php $source = strtolower($r['registration_source'] ?? 'account'); ?>
                                                                <span class="badge <?= $source === 'walk_in' ? 'bg-info' : 'bg-dark' ?>">
                                                                    <?= $source === 'walk_in' ? 'Walk-in' : 'Account' ?>
                                                                </span>
                                                            </td>
                                                            <td><?= htmlspecialchars($r['participant_name'] ?? '-') ?></td>
                                                            <td><?= htmlspecialchars($r['participant_email'] ?? '-') ?></td>
                                                            <td><?= htmlspecialchars($r['participant_phone'] ?? '-') ?></td>
                                                            <td><?= htmlspecialchars($r['participant_company'] ?? '-') ?></td>
                                                            <td>
                                                                <?php
                                                                $badgeClass = 'bg-secondary';
                                                                if ($r['_status'] === 'Present') $badgeClass = 'bg-success';
                                                                if ($r['_status'] === 'Absent') $badgeClass = 'bg-danger';
                                                                if ($r['_status'] === 'Registered') $badgeClass = 'bg-warning text-dark';
                                                                ?>
                                                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($r['_status']) ?></span>
                                                            </td>
                                                            <td><?= htmlspecialchars($r['check_in_time'] ?? '-') ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-light border text-center mb-0">No registrations found for this event</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="modal fade" id="walkInQrModal" tabindex="-1" aria-labelledby="walkInQrModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="walkInQrModalLabel">Walk-in Registration QR</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img src="<?= htmlspecialchars($walkInQr) ?>" alt="Walk-in QR" class="img-fluid border rounded p-2 bg-white" style="max-width: 280px;">
                                            <p class="text-muted small mt-3 mb-2">Scan to open public walk-in registration page.</p>
                                            <a href="<?= htmlspecialchars($walkInUrl) ?>" target="_blank" class="btn btn-sm btn-light-primary">Open Link</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="organiserRegisterModal" tabindex="-1" aria-labelledby="organiserRegisterModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <form method="POST" action="event-assist-register.php">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="organiserRegisterModalLabel">Register Participant</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="event_id" value="<?= htmlspecialchars($eventId) ?>">

                                                <div class="alert alert-light border mb-4">
                                                    If the user email exists, participant will be registered using existing account.<br>
                                                    If the user email does not exist, participant will be registered as walk-in only.
                                                </div>

                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="participant_name" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Email</label>
                                                        <input type="email" name="participant_email" id="participantEmailField" class="form-control" placeholder="example@email.com">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Phone</label>
                                                        <input type="text" name="participant_phone" class="form-control">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Company</label>
                                                        <input type="text" name="participant_company" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Register</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <footer>
                    <?php include "../../../include/footer.php"; ?>
                </footer>
            </div>
        </div>
    </div>

    <script src="assets/plugins/global/plugins.bundle.js"></script>
    <script src="assets/js/scripts.bundle.js"></script>

    <script>
        $(document).ready(function() {
            if (!$('#regTable').length) {
                return;
            }

            const table = $('#regTable').DataTable({
                pageLength: 25,
                order: [
                    [0, 'asc']
                ],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ records",
                    info: "Showing _START_ to _END_ of _TOTAL_ records",
                    infoEmpty: "Showing 0 to 0 of 0 records",
                    infoFiltered: "(filtered from _MAX_ total records)",
                    zeroRecords: "No matching records found",
                    emptyTable: "No data available in table",
                }
            });

            $('#statusFilter').on('change', function() {
                const val = $(this).val();
                // Status column index = 8
                table.column(8).search(val ? val : '', true, false).draw();
            });
        });
    </script>
</body>

</html>

