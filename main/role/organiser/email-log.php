<?php
session_start();
include "../../../include/config.php";
/** @var mysqli $conn */
include "../../../include/permissions.php";

require_manage_events();

$eventId = trim($_GET['event_id'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');
$search = trim($_GET['q'] ?? '');

// Optional: scope to a specific event
$eventName = '';
if ($eventId !== '') {
    $evStmt = $conn->prepare("SELECT event_name FROM att_event WHERE event_id = ? LIMIT 1");
    if ($evStmt) {
        $evStmt->bind_param('s', $eventId);
        $evStmt->execute();
        $evRow = $evStmt->get_result()->fetch_assoc();
        $evStmt->close();
        $eventName = $evRow['event_name'] ?? '';
    }
}

// Build query
$where  = ['1'];
$params = [];
$types  = '';

if ($eventId !== '') {
    $where[]  = 'l.event_id = ?';
    $params[] = $eventId;
    $types   .= 's';
}
if ($filterStatus !== '') {
    $where[]  = 'l.status = ?';
    $params[] = $filterStatus;
    $types   .= 's';
}
if ($search !== '') {
    $like     = '%' . $search . '%';
    $where[]  = '(l.recipient_email LIKE ? OR l.recipient_name LIKE ? OR l.registration_id LIKE ?)';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= 'sss';
}

$whereSql = implode(' AND ', $where);

$logs = [];
$sql = "
    SELECT l.log_id, l.event_id, l.registration_id,
           l.recipient_email, l.recipient_name,
           l.subject, l.status, l.fail_reason, l.sent_at,
           e.event_name
    FROM att_email_log l
    LEFT JOIN att_event e ON e.event_id = l.event_id COLLATE utf8mb4_general_ci
    WHERE {$whereSql}
    ORDER BY l.sent_at DESC
    LIMIT 1000
";

$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $logs[] = $row;
    }
    $stmt->close();
}

$totalSent   = 0;
$totalFailed = 0;
foreach ($logs as $l) {
    if ($l['status'] === 'sent') $totalSent++;
    else $totalFailed++;
}

$pageTitle = $eventId !== '' ? 'Email Log — ' . htmlspecialchars($eventName) : 'Email Log';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <base href="">
    <meta charset="utf-8" />
    <title><?= $pageTitle ?> | ATTENDANCE SYSTEM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="../../../assets/media/logos/ace.png" />

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <link href="../../../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../../../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" />
    <style>
        .badge-sent   { background:#d1fae5; color:#065f46; }
        .badge-failed { background:#fee2e2; color:#991b1b; }
        .table thead th { white-space: nowrap; }
        .stat-card { border-radius: 12px; }
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
                    <div id="kt_content_container" class="container-fluid py-4">

                        <!-- Page heading -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h2 class="fw-bold mb-1"><?= $pageTitle ?></h2>
                                <?php if ($eventId !== ''): ?>
                                    <div class="text-muted small">
                                        <a href="event-registration.php?id=<?= urlencode($eventId) ?>" class="text-decoration-none">
                                            <i class="bi bi-arrow-left me-1"></i>Back to registrations
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="row g-3 mb-4">
                            <div class="col-sm-4">
                                <div class="card stat-card border-0 shadow-sm p-3 text-center">
                                    <div class="fs-2 fw-bold text-dark"><?= count($logs) ?></div>
                                    <div class="text-muted small">Total Emails</div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="card stat-card border-0 shadow-sm p-3 text-center">
                                    <div class="fs-2 fw-bold text-success"><?= $totalSent ?></div>
                                    <div class="text-muted small">Sent</div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="card stat-card border-0 shadow-sm p-3 text-center">
                                    <div class="fs-2 fw-bold text-danger"><?= $totalFailed ?></div>
                                    <div class="text-muted small">Failed</div>
                                </div>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <form method="get" class="row g-2 align-items-end">
                                    <?php if ($eventId !== ''): ?>
                                        <input type="hidden" name="event_id" value="<?= htmlspecialchars($eventId) ?>">
                                    <?php endif; ?>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small mb-1">Search</label>
                                        <input type="text" name="q" class="form-control form-control-sm"
                                               placeholder="Name, email or registration ID…"
                                               value="<?= htmlspecialchars($search) ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold small mb-1">Status</label>
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="">All</option>
                                            <option value="sent"   <?= $filterStatus === 'sent'   ? 'selected' : '' ?>>Sent</option>
                                            <option value="failed" <?= $filterStatus === 'failed' ? 'selected' : '' ?>>Failed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-auto">
                                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                                        <a href="email-log.php<?= $eventId !== '' ? '?event_id=' . urlencode($eventId) : '' ?>"
                                           class="btn btn-sm btn-light ms-1">Reset</a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table id="emailLogTable" class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <?php if ($eventId === ''): ?>
                                                    <th>Event</th>
                                                <?php endif; ?>
                                                <th>Registration ID</th>
                                                <th>Recipient</th>
                                                <th>Subject</th>
                                                <th>Status</th>
                                                <th>Sent At</th>
                                                <th>Fail Reason</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (empty($logs)): ?>
                                            <tr>
                                                <td colspan="<?= $eventId === '' ? 8 : 7 ?>" class="text-center text-muted py-4">
                                                    No email records found.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($logs as $i => $l): ?>
                                            <tr>
                                                <td class="text-muted small"><?= $i + 1 ?></td>
                                                <?php if ($eventId === ''): ?>
                                                    <td>
                                                        <a href="email-log.php?event_id=<?= urlencode($l['event_id']) ?>" class="text-decoration-none fw-semibold">
                                                            <?= htmlspecialchars($l['event_name'] ?? $l['event_id']) ?>
                                                        </a>
                                                        <div class="text-muted small"><?= htmlspecialchars($l['event_id']) ?></div>
                                                    </td>
                                                <?php endif; ?>
                                                <td class="font-monospace small"><?= htmlspecialchars($l['registration_id']) ?></td>
                                                <td>
                                                    <div class="fw-semibold"><?= htmlspecialchars($l['recipient_name'] ?: '—') ?></div>
                                                    <div class="text-muted small"><?= htmlspecialchars($l['recipient_email']) ?></div>
                                                </td>
                                                <td class="small"><?= htmlspecialchars($l['subject']) ?></td>
                                                <td>
                                                    <span class="badge rounded-pill px-2 py-1 <?= $l['status'] === 'sent' ? 'badge-sent' : 'badge-failed' ?>">
                                                        <?= $l['status'] === 'sent' ? '✓ Sent' : '✗ Failed' ?>
                                                    </span>
                                                </td>
                                                <td class="small text-nowrap"><?= htmlspecialchars($l['sent_at']) ?></td>
                                                <td class="small text-danger"><?= $l['fail_reason'] ? htmlspecialchars($l['fail_reason']) : '<span class="text-muted">—</span>' ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <?php include "../../../include/footer.php"; ?>
        </div>
    </div>
</div>

<script src="../../../assets/plugins/global/plugins.bundle.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        $('#emailLogTable').DataTable({
            order: [[<?= $eventId === '' ? 6 : 5 ?>,'desc']],
            pageLength: 25,
            columnDefs: [{ orderable: false, targets: [-1] }],
            language: { search: 'Quick search:' }
        });
    });
</script>
</body>
</html>
