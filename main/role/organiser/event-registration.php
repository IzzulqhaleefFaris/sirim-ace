<?php
session_start();
include "../../../include/config.php";
/** @var mysqli $conn */
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

// Load active staff choices for organiser-assisted registration
$staffUsers = [];
$staffStmt = $conn->prepare("SELECT userId, nama, email FROM user ORDER BY nama ASC");
if ($staffStmt) {
    $staffStmt->execute();
    $staffRes = $staffStmt->get_result();
    if ($staffRes) {
        while ($s = $staffRes->fetch_assoc()) {
            $staffUsers[] = $s;
        }
    }
    $staffStmt->close();
}

// Load already registered staff for this event (account registrations only)
$registeredStaffMap = [];
$registeredStaffStmt = $conn->prepare("SELECT DISTINCT r.participant_id, u.nama, u.email FROM att_registration r JOIN user u ON u.userId = r.participant_id WHERE r.event_id = ? AND IFNULL(NULLIF(r.registration_source, ''), 'account') = 'account' AND u.roleId IN (1,3) ORDER BY u.nama ASC");
if ($registeredStaffStmt) {
    $registeredStaffStmt->bind_param("s", $eventId);
    $registeredStaffStmt->execute();
    $registeredStaffRes = $registeredStaffStmt->get_result();
    if ($registeredStaffRes) {
        while ($rs = $registeredStaffRes->fetch_assoc()) {
            $rid = (string)($rs['participant_id'] ?? '');
            $rname = trim((string)($rs['nama'] ?? ''));
            $remail = trim((string)($rs['email'] ?? ''));
            $registeredStaffMap[$rid] = $rname . ($remail !== '' ? ' (' . $remail . ')' : '');
        }
    }
    $registeredStaffStmt->close();
}

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
    <link rel="shortcut icon" href="../../../assets/media/logos/ace.png" />
    
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" />
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

        .choices__inner {
            min-height: 38px;
            border-radius: 8px;
        }

        .selected-staff-title {
            font-size: 0.82rem;
            color: #6c757d;
            margin-top: 0.6rem;
        }

        .selected-staff-list {
            margin-top: 0.35rem;
        }

        .selected-staff-list .list-group-item {
            padding: 0.45rem 0.75rem;
        }

        .register-modal .modal-content {
            border: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(20, 35, 55, 0.18);
        }

        .register-modal .modal-header {
            border-bottom: 1px solid #e9edf3;
            background: linear-gradient(180deg, #f9fbff 0%, #f3f7ff 100%);
            padding: 1rem 1.25rem;
        }

        .register-modal .modal-title {
            font-weight: 700;
            color: #16324f;
        }

        .register-modal .modal-body {
            background: #fcfdff;
            padding: 1.15rem 1.25rem;
        }

        .register-modal .modal-footer {
            border-top: 1px solid #e9edf3;
            background: #f9fbff;
        }

        .register-mode-switch {
            display: inline-flex;
            gap: 0.45rem;
            padding: 0.3rem;
            border-radius: 999px;
            border: 1px solid #dbe7f5;
            background: #f1f6ff;
        }

        .register-mode-switch .btn {
            border-radius: 999px !important;
            padding: 0.35rem 0.9rem;
            font-weight: 600;
        }

        .register-section-card {
            background: #fff;
            border: 1px solid #e8edf4;
            border-radius: 12px;
            padding: 0.9rem;
        }

        .register-section-title {
            font-size: 0.84rem;
            font-weight: 700;
            color: #5a6c82;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 0.55rem;
        }

        .register-modal .form-control,
        .register-modal .form-select,
        .register-modal .choices__inner {
            border-color: #dce5f1;
        }

        .register-modal .form-control:focus,
        .register-modal .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.18rem rgba(13, 110, 253, 0.12);
        }

        .selected-staff-list {
            max-height: 180px;
            overflow: auto;
        }

        .selected-staff-list:empty {
            max-height: none;
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
                                    <a class="btn btn-secondary"
                                        href="email-log.php?event_id=<?= urlencode($eventId) ?>">
                                        <i class="bi bi-envelope-open me-1"></i>Email Log
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
                                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div class="fw-bold">Registrations & Attendance</div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <input type="text" id="tableSearch" class="form-control form-control-sm" placeholder="Search..." style="width:180px;">
                                        <select id="rowsPerPage" class="form-select form-select-sm" style="width:100px;">
                                            <option value="10" selected>10 rows</option>
                                            <option value="20">20 rows</option>
                                            <option value="-1">All</option>
                                        </select>
                                        <button id="sendQrBlastBtn" type="button" class="btn btn-sm btn-warning d-none">
                                            <i class="bi bi-envelope-fill me-1"></i>Send QR Email (<span id="blastCount">0</span>)
                                        </button>
                                        <button id="deleteSelectedBtn" type="button" class="btn btn-sm btn-danger d-none">
                                            <i class="bi bi-trash me-1"></i>Delete Selected (<span id="deleteCount">0</span>)
                                        </button>
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
                                                        <th style="width:36px;"><input type="checkbox" id="selectAllChk" title="Select all"></th>
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
                                                        <th>QR</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $i = 1; ?>
                                                    <?php foreach ($all as $r): ?>
                                                        <tr data-status="<?= htmlspecialchars($r['_status']) ?>">
                                                            <td class="text-center">
                                                                <input type="checkbox" class="reg-chk"
                                                                    data-id="<?= htmlspecialchars($r['registration_id']) ?>"
                                                                    data-email="<?= htmlspecialchars($r['participant_email'] ?? '') ?>"
                                                                    data-name="<?= htmlspecialchars($r['participant_name'] ?? '') ?>">
                                                            </td>
                                                            <td class="text-center"><?= $i++ ?></td>
                                                            <td><?= htmlspecialchars($r['registration_id']) ?></td>
                                                            <td><?= htmlspecialchars($r['participant_id']) ?></td>
                                                            <td>
                                                                <?php $source = strtolower($r['registration_source'] ?? 'account'); ?>
                                                                <span class="badge <?= $source === 'walk_in' ? 'bg-primary' : 'bg-secondary' ?>">
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
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 show-qr-btn"
                                                                    data-reg-id="<?= htmlspecialchars($r['registration_id']) ?>"
                                                                    data-name="<?= htmlspecialchars($r['participant_name'] ?? '') ?>"
                                                                    title="Show QR Code">
                                                                    <i class="bi bi-qr-code"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-light border text-center mb-0">No registrations found for this event</div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-white border-top-0 pt-0 pb-3 px-4">
                                    <small class="text-muted me-3 fw-semibold">Status:</small>
                                    <span class="badge bg-warning text-dark me-2">Registered</span>
                                    <span class="badge bg-success me-2">Present</span>
                                    <span class="badge bg-danger me-2">Absent</span>
                                    <span class="text-muted mx-2">|</span>
                                    <small class="text-muted fw-semibold me-2">Source:</small>
                                    <span class="badge bg-secondary me-2">Account</span>
                                    <span class="badge bg-primary me-2">Walk-in</span>
                                </div>
                            </div>

                            <!-- Delete Participants Confirmation Modal -->
            <div class="modal fade" id="deleteParticipantsModal" tabindex="-1" aria-labelledby="deleteParticipantsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteParticipantsModalLabel"><i class="bi bi-trash me-2"></i>Delete Participants</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger py-2 small mb-3">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                This will permanently remove the selected participant(s) from the list. This action cannot be undone.
                            </div>
                            <p class="mb-0">Are you sure you want to delete <strong><span id="deleteModalCount">0</span> participant(s)</strong>?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="confirmDeleteBtn" class="btn btn-danger fw-semibold">
                                <i class="bi bi-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
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

                            <div class="modal fade register-modal" id="organiserRegisterModal" tabindex="-1" aria-labelledby="organiserRegisterModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <form method="POST" action="event-assist-register.php">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="organiserRegisterModalLabel">Register Participant</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="event_id" value="<?= htmlspecialchars($eventId) ?>">
                                                <input type="hidden" name="register_mode" id="registerModeInput" value="staff">

                                                <div class="mb-3">
                                                    <label class="form-label d-block mb-2">Choose Registration Type</label>
                                                    <div class="register-mode-switch" role="group" aria-label="Registration type switch">
                                                        <button type="button" class="btn btn-primary" id="staffModeBtn">Staff</button>
                                                        <button type="button" class="btn btn-outline-primary" id="externalModeBtn">External Participant</button>
                                                    </div>
                                                </div>

                                                <div id="staffRegisterSection" class="register-section-card">
                                                    <div class="register-section-title">Staff Registration</div>
                                                    <div class="row g-3 align-items-end">
                                                        <div class="col-md-9">
                                                            <label class="form-label">Select Staff</label>
                                                            <select id="staffPicker" class="form-select" aria-label="Search and select staff">
                                                                <option value="">Search name/email and select</option>
                                                                <?php foreach ($staffUsers as $staff): ?>
                                                                    <?php
                                                                    $staffId = (string)($staff['userId'] ?? '');
                                                                    $staffName = trim((string)($staff['nama'] ?? ''));
                                                                    $staffEmail = trim((string)($staff['email'] ?? ''));
                                                                    $label = $staffName . ($staffEmail !== '' ? ' (' . $staffEmail . ')' : '');
                                                                    $isAlreadyRegistered = isset($registeredStaffMap[$staffId]);
                                                                    ?>
                                                                    <option value="<?= htmlspecialchars($staffId) ?>" <?= $isAlreadyRegistered ? 'disabled' : '' ?>>
                                                                        <?= htmlspecialchars($label) ?><?= $isAlreadyRegistered ? ' (Registered)' : '' ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <button type="button" class="btn btn-outline-primary w-100" id="addStaffBtn">Select</button>
                                                        </div>
                                                    </div>
                                                    <div class="small text-muted mt-2">You can select one or more staff.</div>
                                                    <div class="selected-staff-title">Selected staff list</div>
                                                    <div id="selectedStaffList" class="selected-staff-list"></div><br>
                                                    <label class="form-label">Registered Staff</label>
                                                    <div id="alreadyRegisteredStaffList" class="selected-staff-list">
                                                        <?php if (!empty($registeredStaffMap)): ?>
                                                            <ul class="list-group">
                                                                <?php foreach ($registeredStaffMap as $registeredLabel): ?>
                                                                    <li class="list-group-item"><?= htmlspecialchars($registeredLabel) ?></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div id="selectedStaffInputs"></div>
                                                </div>

                                                <div id="externalRegisterSection" class="d-none register-section-card">
                                                    <div class="register-section-title">External Participant</div>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Name <span class="text-danger">*</span></label>
                                                            <input type="text" name="participant_name" id="externalNameField" class="form-control">
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
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary fw-semibold">Register</button>
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
	<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // DataTable
            let table = null;
            if (typeof jQuery !== 'undefined' && jQuery.fn && typeof jQuery.fn.DataTable === 'function' && jQuery('#regTable').length) {
                table = jQuery('#regTable').DataTable({
                    pageLength: 10,
                    order: [[1, 'desc']],
                    columnDefs: [
                        { orderable: false, targets: [0, 10, 11] }
                    ],
                    dom: 'tip',
                    language: {
                        info: 'Showing _START_ to _END_ of _TOTAL_ records',
                        infoEmpty: 'Showing 0 to 0 of 0 records',
                        infoFiltered: '(filtered from _MAX_ total records)',
                        zeroRecords: 'No matching records found',
                        emptyTable: 'No data available in table',
                        paginate: { previous: '&laquo;', next: '&raquo;' }
                    }
                });

                // Wire search input
                const tableSearch = document.getElementById('tableSearch');
                if (tableSearch) {
                    tableSearch.addEventListener('input', function () {
                        table.search(this.value).draw();
                    });
                }

                // Wire rows-per-page dropdown
                const rowsPerPage = document.getElementById('rowsPerPage');
                if (rowsPerPage) {
                    rowsPerPage.addEventListener('change', function () {
                        table.page.len(parseInt(this.value, 10)).draw();
                    });
                }
            }

            const statusFilter = document.getElementById('statusFilter');
            if (statusFilter) {
                statusFilter.addEventListener('change', function () {
                    if (!table) {
                        // Fallback: plain row show/hide when DataTables not active
                        const val = this.value.toLowerCase();
                        document.querySelectorAll('#regTable tbody tr').forEach(function (row) {
                            row.style.display = (!val || (row.dataset.status || '').toLowerCase() === val) ? '' : 'none';
                        });
                        return;
                    }
                    // DataTables: filter on Status column (index 9)
                    table.column(9).search(this.value ? '^' + this.value + '$' : '', true, false).draw();
                });
            }

            const registerForm = document.querySelector('#organiserRegisterModal form');
            const registerModeInput = document.getElementById('registerModeInput');
            const staffModeBtn = document.getElementById('staffModeBtn');
            const externalModeBtn = document.getElementById('externalModeBtn');
            const staffSection = document.getElementById('staffRegisterSection');
            const externalSection = document.getElementById('externalRegisterSection');
            const staffPicker = document.getElementById('staffPicker');
            const addStaffBtn = document.getElementById('addStaffBtn');
            const selectedStaffList = document.getElementById('selectedStaffList');
            const selectedStaffInputs = document.getElementById('selectedStaffInputs');
            const externalNameField = document.getElementById('externalNameField');

            if (!registerForm || !registerModeInput || !staffModeBtn || !externalModeBtn || !staffSection || !externalSection || !staffPicker || !addStaffBtn || !selectedStaffList || !selectedStaffInputs || !externalNameField) {
                return;
            }

            const selectedStaffMap = new Map();

            let staffChoices = null;
            if (typeof Choices !== 'undefined') {
                staffChoices = new Choices(staffPicker, {
                    searchEnabled: true,
                    itemSelectText: '',
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: 'Search staff name or email'
                });
            }

            function setMode(mode) {
                const isStaff = mode === 'staff';
                registerModeInput.value = isStaff ? 'staff' : 'external';

                staffSection.classList.toggle('d-none', !isStaff);
                externalSection.classList.toggle('d-none', isStaff);

                staffModeBtn.classList.toggle('btn-primary', isStaff);
                staffModeBtn.classList.toggle('btn-outline-primary', !isStaff);
                externalModeBtn.classList.toggle('btn-primary', !isStaff);
                externalModeBtn.classList.toggle('btn-outline-primary', isStaff);

                externalNameField.required = !isStaff;
            }

            function renderSelectedStaff() {
                selectedStaffList.innerHTML = '';
                selectedStaffInputs.innerHTML = '';

                if (selectedStaffMap.size === 0) {
                    return;
                }

                const list = document.createElement('ul');
                list.className = 'list-group';

                selectedStaffMap.forEach(function(label, userId) {
                    const item = document.createElement('li');
                    item.className = 'list-group-item d-flex justify-content-between align-items-center';

                    const labelSpan = document.createElement('span');
                    labelSpan.textContent = label;
                    item.appendChild(labelSpan);

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn btn-sm btn-light-danger';
                    removeBtn.setAttribute('data-remove', userId);
                    removeBtn.setAttribute('aria-label', 'Remove');
                    removeBtn.textContent = 'Remove';
                    item.appendChild(removeBtn);

                    list.appendChild(item);

                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'staff_ids[]';
                    hidden.value = userId;
                    selectedStaffInputs.appendChild(hidden);
                });

                selectedStaffList.appendChild(list);
            }

            function getSelectedStaffLabel(userId) {
                const options = staffPicker.options || [];
                for (let i = 0; i < options.length; i++) {
                    if (options[i].value === userId) {
                        return options[i].text;
                    }
                }
                return userId;
            }

            addStaffBtn.addEventListener('click', function() {
                if (!staffPicker.value) {
                    return;
                }

                const userId = staffPicker.value;
                const selectedOption = Array.from(staffPicker.options).find(function(opt) {
                    return opt.value === userId;
                });
                if (selectedOption && selectedOption.disabled) {
                    return;
                }
                const label = getSelectedStaffLabel(userId);

                if (!selectedStaffMap.has(userId)) {
                    selectedStaffMap.set(userId, label);
                    renderSelectedStaff();
                }

                if (staffChoices) {
                    staffChoices.removeActiveItems();
                } else {
                    staffPicker.value = '';
                }
            });

            selectedStaffList.addEventListener('click', function(e) {
                const btn = e.target.closest('button[data-remove]');
                if (!btn) return;

                const userId = btn.getAttribute('data-remove');
                selectedStaffMap.delete(userId);
                renderSelectedStaff();
            });

            staffModeBtn.addEventListener('click', function() {
                setMode('staff');
            });

            externalModeBtn.addEventListener('click', function() {
                setMode('external');
            });

            registerForm.addEventListener('submit', function(e) {
                if (registerModeInput.value === 'staff' && selectedStaffMap.size === 0) {
                    e.preventDefault();
                    alert('Please select at least one staff member.');
                    return;
                }

                if (registerModeInput.value === 'external' && !externalNameField.value.trim()) {
                    e.preventDefault();
                    alert('Please fill in external participant name.');
                }
            });

            setMode('staff');

            // ── QR Email Blast ───────────────────────────────────────────
            const selectAllChk   = document.getElementById('selectAllChk');
            const sendQrBlastBtn = document.getElementById('sendQrBlastBtn');
            const blastCountEl   = document.getElementById('blastCount');

            function getCheckedBoxes() {
                // Must collect from ALL pages, not just the currently visible page
                const source = table
                    ? Array.from(table.rows().nodes()).map(function(n){ return n.querySelector('.reg-chk'); }).filter(Boolean)
                    : Array.from(document.querySelectorAll('.reg-chk'));
                return source.filter(function(c){ return c.checked; });
            }

            function updateBlastToolbar() {
                const checked = getCheckedBoxes();
                if (checked.length > 0) {
                    sendQrBlastBtn.classList.remove('d-none');
                    blastCountEl.textContent = checked.length;
                } else {
                    sendQrBlastBtn.classList.add('d-none');
                    blastCountEl.textContent = '0';
                }
            }

            if (selectAllChk) {
                selectAllChk.addEventListener('change', function () {
                    // Check/uncheck ALL rows in ALL pages, not just visible ones
                    document.querySelectorAll('.reg-chk').forEach(function (chk) {
                        chk.checked = selectAllChk.checked;
                    });
                    // If DataTables is active, also update rows on other pages via API
                    if (table) {
                        table.rows().nodes().each(function (node) {
                            const chk = node.querySelector('.reg-chk');
                            if (chk) chk.checked = selectAllChk.checked;
                        });
                    }
                    updateBlastToolbar();
                    const checked2 = getCheckedBoxes();
                    deleteSelectedBtn && deleteSelectedBtn.classList.toggle('d-none', checked2.length === 0);
                    if (deleteCountEl) deleteCountEl.textContent = checked2.length;
                });
            }

            document.addEventListener('change', function (e) {
                if (!e.target.classList.contains('reg-chk')) return;
                // Count ALL rows across all pages
                const allChks     = table
                    ? Array.from(table.rows().nodes()).map(function(n){ return n.querySelector('.reg-chk'); }).filter(Boolean)
                    : Array.from(document.querySelectorAll('.reg-chk'));
                const checkedChks = allChks.filter(function(c){ return c.checked; });
                if (selectAllChk) {
                    selectAllChk.checked       = allChks.length > 0 && allChks.length === checkedChks.length;
                    selectAllChk.indeterminate = checkedChks.length > 0 && checkedChks.length < allChks.length;
                }
                updateBlastToolbar();
                deleteSelectedBtn && deleteSelectedBtn.classList.toggle('d-none', checkedChks.length === 0);
                if (deleteCountEl) deleteCountEl.textContent = checkedChks.length;
            });

            // ── Delete Participants ──────────────────────────────────────────
            const deleteSelectedBtn   = document.getElementById('deleteSelectedBtn');
            const deleteCountEl       = document.getElementById('deleteCount');
            const deleteParticipantsModal = document.getElementById('deleteParticipantsModal') ? new bootstrap.Modal(document.getElementById('deleteParticipantsModal')) : null;
            const deleteModalCountEl  = document.getElementById('deleteModalCount');
            const confirmDeleteBtn    = document.getElementById('confirmDeleteBtn');

            const origUpdateBlastToolbar = updateBlastToolbar;
            function updateAllToolbars() {
                origUpdateBlastToolbar();
                const checked = getCheckedBoxes();
                if (checked.length > 0) {
                    deleteSelectedBtn && deleteSelectedBtn.classList.remove('d-none');
                    if (deleteCountEl) deleteCountEl.textContent = checked.length;
                } else {
                    deleteSelectedBtn && deleteSelectedBtn.classList.add('d-none');
                    if (deleteCountEl) deleteCountEl.textContent = '0';
                }
            }

            if (deleteSelectedBtn) {
                deleteSelectedBtn.addEventListener('click', function () {
                    const checked = getCheckedBoxes();
                    if (checked.length === 0) return;
                    if (deleteModalCountEl) deleteModalCountEl.textContent = checked.length;
                    if (deleteParticipantsModal) deleteParticipantsModal.show();
                });
            }

            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function () {
                    const checked = getCheckedBoxes();
                    if (checked.length === 0) return;

                    confirmDeleteBtn.disabled = true;
                    confirmDeleteBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Deleting…';

                    const formData = new FormData();
                    formData.append('event_id', <?= json_encode($eventId) ?>);
                    checked.forEach(function (chk) {
                        formData.append('registration_ids[]', chk.dataset.id);
                    });

                    fetch('../../api/delete-participants.php', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (deleteParticipantsModal) deleteParticipantsModal.hide();
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert('Delete failed: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(function (err) {
                        alert('Request failed: ' + err.message);
                    })
                    .finally(function () {
                        confirmDeleteBtn.disabled = false;
                        confirmDeleteBtn.innerHTML = '<i class="bi bi-trash me-1"></i>Delete';
                    });
                });
            }

            const qrBlastModal    = document.getElementById('qrBlastModal')    ? new bootstrap.Modal(document.getElementById('qrBlastModal'))    : null;
            const qrBlastSendBtn  = document.getElementById('qrBlastSendBtn');
            const blastRecipient  = document.getElementById('blastRecipientCount');
            const blastWarning    = document.getElementById('blastNoEmailWarning');
            const blastInstructions = document.getElementById('blastInstructions');

            if (sendQrBlastBtn) {
                sendQrBlastBtn.addEventListener('click', function () {
                    const checked = getCheckedBoxes();
                    if (checked.length === 0) return;

                    const withoutEmail = checked.filter(function (c) { return !c.dataset.email || c.dataset.email === '-' || c.dataset.email === ''; }).length;

                    if (blastRecipient) blastRecipient.textContent = checked.length;
                    if (blastWarning) {
                        if (withoutEmail > 0) {
                            blastWarning.textContent = withoutEmail + ' participant(s) have no email address and will be skipped.';
                            blastWarning.style.display = '';
                        } else {
                            blastWarning.style.display = 'none';
                        }
                    }
                    if (blastInstructions) blastInstructions.value = '';
                    const blastAgenda = document.getElementById('blastAgenda');
                    if (blastAgenda) blastAgenda.value = '';
                    const blastAgendaError = document.getElementById('blastAgendaError');
                    if (blastAgendaError) blastAgendaError.style.display = 'none';

                    if (qrBlastModal) qrBlastModal.show();
                });
            }

            if (qrBlastSendBtn) {
                qrBlastSendBtn.addEventListener('click', function () {
                    const checked = getCheckedBoxes();
                    if (checked.length === 0) return;

                    qrBlastSendBtn.disabled = true;
                    qrBlastSendBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Sending…';

                    const formData = new FormData();
                    formData.append('event_id', <?= json_encode($eventId) ?>);
                    formData.append('instructions', blastInstructions ? blastInstructions.value : '');

                    const blastAgendaFile = document.getElementById('blastAgenda');
                    if (blastAgendaFile && blastAgendaFile.files[0]) {
                        const agendaFile = blastAgendaFile.files[0];
                        const agendaError = document.getElementById('blastAgendaError');
                        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                        if (!allowedTypes.includes(agendaFile.type)) {
                            if (agendaError) { agendaError.textContent = 'Agenda must be an image (JPEG, PNG, GIF, WEBP).'; agendaError.style.display = ''; }
                            qrBlastSendBtn.disabled = false;
                            qrBlastSendBtn.innerHTML = '<i class="bi bi-send me-1"></i>Send Email';
                            return;
                        }
                        if (agendaFile.size > 10 * 1024 * 1024) {
                            if (agendaError) { agendaError.textContent = 'Agenda image must be under 10 MB.'; agendaError.style.display = ''; }
                            qrBlastSendBtn.disabled = false;
                            qrBlastSendBtn.innerHTML = '<i class="bi bi-send me-1"></i>Send Email';
                            return;
                        }
                        if (agendaError) agendaError.style.display = 'none';
                        formData.append('agenda', agendaFile);
                    }

                    checked.forEach(function (chk) {
                        formData.append('registration_ids[]', chk.dataset.id);
                    });

                    fetch('../../api/send-qr-blast.php', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (qrBlastModal) qrBlastModal.hide();
                        let msg = 'Sent: ' + data.sent + '  |  Failed: ' + data.failed;
                        if (data.errors && data.errors.length > 0) {
                            msg += '\n\nDetails:\n' + data.errors.map(function (e) {
                                return '• ' + e.id + ': ' + e.reason;
                            }).join('\n');
                        }
                        alert(msg);
                    })
                    .catch(function (err) {
                        alert('Request failed: ' + err.message);
                    })
                    .finally(function () {
                        qrBlastSendBtn.disabled = false;
                        qrBlastSendBtn.innerHTML = '<i class="bi bi-send me-1"></i>Send Email';
                        sendQrBlastBtn.innerHTML = '<i class="bi bi-envelope-fill me-1"></i>Send QR Email (<span id="blastCount">' + getCheckedBoxes().length + '</span>)';
                        blastCountEl && (blastCountEl.textContent = getCheckedBoxes().length);
                    });
                });
            }
        });
    </script>
<!-- QR Preview Modal -->
<div class="modal fade" id="qrPreviewModal" tabindex="-1" aria-labelledby="qrPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:320px;">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold" id="qrPreviewModalLabel"><i class="bi bi-qr-code me-2"></i><span id="qrPreviewName"></span></h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-3">
                <p class="text-muted small mb-2" id="qrPreviewRegId"></p>
                <img id="qrPreviewImg" src="" alt="QR Code"
                     style="width:220px;height:220px;border:1px solid #ddd;border-radius:8px;">
            </div>
            <div class="modal-footer justify-content-center py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="qrPreviewDownload" href="#" class="btn btn-primary">
                    <i class="bi bi-download me-1"></i>Download PNG
                </a>
            </div>
        </div>
    </div>
</div>
<!-- QR Preview JS -->
<script>
    (function () {
        const qrPreviewModal = new bootstrap.Modal(document.getElementById('qrPreviewModal'));
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.show-qr-btn');
            if (!btn) return;
            const regId = btn.dataset.regId;
            const name  = btn.dataset.name || regId;
            const url   = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent(regId);
            document.getElementById('qrPreviewName').textContent  = name;
            document.getElementById('qrPreviewRegId').textContent = regId;
            document.getElementById('qrPreviewImg').src           = url;
            document.getElementById('qrPreviewDownload').dataset.regId = regId;
            qrPreviewModal.show();
        });

        document.getElementById('qrPreviewDownload').addEventListener('click', function (e) {
            e.preventDefault();
            const regId = this.dataset.regId;
            window.location.href = '../../api/qr-download.php?data=' + encodeURIComponent(regId);
        });
    })();
</script>

<!-- QR Blast Instructions Modal -->
<div class="modal fade" id="qrBlastModal" tabindex="-1" aria-labelledby="qrBlastModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrBlastModalLabel"><i class="bi bi-envelope-fill me-2"></i>Send QR Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Sending to <strong><span id="blastRecipientCount">0</span> participant(s)</strong>.</p>
                <div id="blastNoEmailWarning" class="alert alert-warning py-2 small mb-3" style="display:none;"></div>
                <div class="mb-3">
                    <label for="blastInstructions" class="form-label fw-semibold">Instructions <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea id="blastInstructions" class="form-control" rows="5"
                        placeholder="Enter any instructions to include in the email, e.g. parking info, dress code, items to bring..."></textarea>
                    <div class="form-text">This text will appear in an &ldquo;Instructions&rdquo; section inside the email.</div>
                </div>
                <div class="mb-3">
                    <label for="blastAgenda" class="form-label fw-semibold">Agenda <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="file" id="blastAgenda" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                    <div class="form-text">Upload an agenda image (JPEG, PNG, GIF or WEBP &mdash; max 10 MB). It will appear in the email below the instructions.</div>
                    <div id="blastAgendaError" class="text-danger small mt-1" style="display:none;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="qrBlastSendBtn" class="btn btn-warning fw-semibold">
                    <i class="bi bi-send me-1"></i>Send Email
                </button>
            </div>
        </div>
    </div>
</div>

</body>

</html>

