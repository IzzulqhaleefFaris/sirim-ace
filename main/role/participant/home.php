<?php
session_start();
include "../../../include/config.php";
include "../../../include/updateEventStatus.php";
include "../../../include/permissions.php";

require_login();

updateEventStatuses($conn);

$participantId = $_SESSION['userId'] ?? '';

$stats = [
	'totalEvents' => 0,
	'eventsThisMonth' => 0,
	'upcomingEventsCount' => 0,
	'myRegistrations' => 0,
	'myCheckedIn' => 0,
];

$upcomingEvents = [];
$eventsError = null;

if (isset($conn) && $conn instanceof mysqli) {
	$totalEventsRes = $conn->query("SELECT COUNT(*) AS total FROM att_event");
	if ($totalEventsRes && ($row = $totalEventsRes->fetch_assoc())) {
		$stats['totalEvents'] = (int)($row['total'] ?? 0);
	}

	$thisMonthRes = $conn->query("SELECT COUNT(*) AS total FROM att_event WHERE YEAR(event_startDate) = YEAR(CURDATE()) AND MONTH(event_startDate) = MONTH(CURDATE())");
	if ($thisMonthRes && ($row = $thisMonthRes->fetch_assoc())) {
		$stats['eventsThisMonth'] = (int)($row['total'] ?? 0);
	}

	$upcomingCountRes = $conn->query("SELECT COUNT(*) AS total FROM att_event WHERE event_startDate >= CURDATE()");
	if ($upcomingCountRes && ($row = $upcomingCountRes->fetch_assoc())) {
		$stats['upcomingEventsCount'] = (int)($row['total'] ?? 0);
	}

	if ($participantId !== '') {
		$myStatsSql = "
			SELECT
				COUNT(r.registration_id) AS myRegistrations,
				SUM(CASE WHEN a.attendance_id IS NOT NULL THEN 1 ELSE 0 END) AS myCheckedIn
			FROM att_registration r
			LEFT JOIN att_attendance a ON a.registration_id = r.registration_id
			WHERE r.participant_id = ?";

		$myStatsStmt = $conn->prepare($myStatsSql);
		if ($myStatsStmt) {
			$myStatsStmt->bind_param("s", $participantId);
			$myStatsStmt->execute();
			$myStatsRes = $myStatsStmt->get_result();
			if ($myStatsRes && ($row = $myStatsRes->fetch_assoc())) {
				$stats['myRegistrations'] = (int)($row['myRegistrations'] ?? 0);
				$stats['myCheckedIn'] = (int)($row['myCheckedIn'] ?? 0);
			}
			$myStatsStmt->close();
		}
	}

	$upcomingSql = "
		SELECT
			e.event_id,
			e.event_name,
			e.event_startDate,
			e.event_endDate,
			e.event_status,
			l.location_name,
			t.event_type_name,
			COUNT(r.registration_id) AS total_registrations,
			SUM(CASE WHEN r.participant_id = ? THEN 1 ELSE 0 END) AS is_registered
		FROM att_event e
		LEFT JOIN att_location l ON e.location_id = l.location_id
		LEFT JOIN att_event_type t ON e.event_type_id = t.event_type_id
		LEFT JOIN att_registration r ON r.event_id = e.event_id
		WHERE e.event_startDate >= CURDATE()
		GROUP BY e.event_id, e.event_name, e.event_startDate, e.event_endDate, e.event_status, l.location_name, t.event_type_name
		ORDER BY e.event_startDate ASC
		LIMIT 6";

	$upcomingStmt = $conn->prepare($upcomingSql);
	if ($upcomingStmt) {
		$upcomingStmt->bind_param("s", $participantId);
		$upcomingStmt->execute();
		$upcomingRes = $upcomingStmt->get_result();
		if ($upcomingRes) {
			while ($event = $upcomingRes->fetch_assoc()) {
				$upcomingEvents[] = $event;
			}
		} else {
			$eventsError = "Tidak dapat memuatkan senarai upcoming events.";
		}
		$upcomingStmt->close();
	} else {
		$eventsError = "Tidak dapat memuatkan senarai upcoming events.";
	}
} else {
	$eventsError = "Sambungan pangkalan data tidak tersedia.";
}

if (!function_exists('formatEventDateRange')) {
	function formatEventDateRange(?string $start, ?string $end): string
	{
		if (!$start) {
			return '-';
		}

		$startDate = date_create($start);
		$endDate = $end ? date_create($end) : null;
		if (!$startDate) {
			return htmlspecialchars($start);
		}

		$startFmt = $startDate->format('d M Y');
		if ($endDate && $endDate->format('Y-m-d') !== $startDate->format('Y-m-d')) {
			return $startFmt . ' - ' . $endDate->format('d M Y');
		}

		return $startFmt;
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
	<link rel="shortcut icon" href="../../../assets/media/logos/soljar_ico.ico" />

	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />

	<link href="../../../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
	<link href="../../../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

	<style>
		.home-hero {
			border-radius: 1rem;
		}

		.quick-action {
			border-radius: 1rem;
			transition: transform .2s ease, box-shadow .2s ease;
		}

		.quick-action:hover {
			transform: translateY(-3px);
			box-shadow: var(--kt-card-box-shadow);
		}

		.action-icon {
			width: 48px;
			height: 48px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			border-radius: 50%;
		}

		.stat-card {
			border-radius: 1rem;
		}

		.upcoming-item {
			border: 1px solid #f1f5f9;
			border-radius: 14px;
			padding: 16px;
			margin-bottom: 12px;
			background: #fff;
		}

		.date-pill {
			min-width: 52px;
			height: 52px;
			border-radius: 14px;
			background: #eef2ff;
			color: #4f46e5;
			font-weight: 700;
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
								<?php $nextEvent = $upcomingEvents[0] ?? null; ?>

								<div class="card shadow-sm mb-5 home-hero">
									<div class="card-body py-6 px-6">
										<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
											<div>
												<h2 class="fw-bolder mb-1">Participant Dashboard</h2>
												<div class="text-muted">Terokai event, daftar dengan cepat, dan semak status kehadiran anda.</div>
											</div>
											<div class="d-flex gap-2">
												<a href="event-list.php" class="btn btn-primary btn-sm">Browse Events</a>
												<a href="my-events.php" class="btn btn-light btn-sm border">My Events & QR</a>
											</div>
										</div>
									</div>
								</div>

								<div class="row g-4 mb-5">
									<div class="col-md-6 col-xl-3">
										<div class="card stat-card shadow-sm border-0 h-100 border-start border-3 border-dark">
											<div class="card-body">
												<div class="text-muted small mb-1">Jumlah Event</div>
												<div class="h3 fw-bold mb-0"><?php echo number_format($stats['totalEvents']); ?></div>
											</div>
										</div>
									</div>
									<div class="col-md-6 col-xl-3">
										<div class="card stat-card shadow-sm border-0 h-100 border-start border-3 border-primary">
											<div class="card-body">
												<div class="text-muted small mb-1">Event Bulan Ini</div>
												<div class="h3 fw-bold mb-0 text-primary"><?php echo number_format($stats['eventsThisMonth']); ?></div>
											</div>
										</div>
									</div>
									<div class="col-md-6 col-xl-3">
										<div class="card stat-card shadow-sm border-0 h-100 border-start border-3 border-info">
											<div class="card-body">
												<div class="text-muted small mb-1">Upcoming Events</div>
												<div class="h3 fw-bold mb-0 text-info"><?php echo number_format($stats['upcomingEventsCount']); ?></div>
											</div>
										</div>
									</div>
									<div class="col-md-6 col-xl-3">
										<div class="card stat-card shadow-sm border-0 h-100 border-start border-3 border-success">
											<div class="card-body">
												<div class="text-muted small mb-1">My Registrations</div>
												<div class="h3 fw-bold mb-0 text-success"><?php echo number_format($stats['myRegistrations']); ?></div>
											</div>
										</div>
									</div>
								</div>

								<div class="row g-4 mb-5">
									<div class="col-md-6 col-xl-4">
										<!-- <a href="event-list.php" class="card quick-action h-100 text-decoration-none text-dark shadow-sm"> -->
										<div class="card quick-action h-100 text-decoration-none text-dark shadow-sm">
											<div class="card-body d-flex flex-column gap-3">
												<span class="action-icon bg-light-primary"><i class="bi bi-search fs-2 text-primary"></i></span>
												<div class="fw-bolder fs-5">Browse Events</div>
												<div class="text-muted small">Cari event mengikut kategori, tarikh dan lokasi.</div>
											</div>
										</div>
										<!-- </a> -->
									</div>
									<div class="col-md-6 col-xl-4">
										<!-- <a href="my-events.php" class="card quick-action h-100 text-decoration-none text-dark shadow-sm"> -->
										<div class="card quick-action h-100 text-decoration-none text-dark shadow-sm">
											<div class="card-body d-flex flex-column gap-3">
												<span class="action-icon bg-light-success"><i class="bi bi-calendar-check fs-2 text-success"></i></span>
												<div class="fw-bolder fs-5">My Events & QR</div>
												<div class="text-muted small">Semak pendaftaran anda dan tunjukkan QR semasa check-in.</div>
											</div>
										</div>
										<!-- </a> -->
									</div>
									<div class="col-md-6 col-xl-4">
										<div class="card quick-action h-100 shadow-sm">
											<div class="card-body d-flex flex-column gap-3">
												<span class="action-icon bg-light-warning"><i class="bi bi-check2-circle fs-2 text-warning"></i></span>
												<div class="fw-bolder fs-5">Kehadiran Direkod</div>
												<div class="text-muted small"><?php echo number_format($stats['myCheckedIn']); ?> event telah berjaya anda hadiri.</div>
											</div>
										</div>
									</div>
								</div>

								<div class="row g-5">
									<div class="col-xl-7">
										<div class="card shadow-sm h-100">
											<div class="card-header border-0 pt-4">
												<h3 class="card-title fw-bold">Senarai Event Akan Datang</h3>
											</div>
											<div class="card-body pt-0">
												<?php if ($eventsError): ?>
													<div class="alert alert-danger mb-0"><?php echo htmlspecialchars($eventsError); ?></div>
												<?php elseif (empty($upcomingEvents)): ?>
													<div class="text-muted">Tiada future events untuk dipaparkan.</div>
												<?php else: ?>
													<?php foreach ($upcomingEvents as $event): ?>
														<div class="upcoming-item d-flex justify-content-between flex-wrap gap-3 align-items-center">
															<div>
																<div class="fw-bold"><?php echo htmlspecialchars($event['event_name']); ?></div>
																<div class="text-muted small">
																	<?php echo htmlspecialchars(formatEventDateRange($event['event_startDate'], $event['event_endDate'])); ?>
																	<?php if (!empty($event['location_name'])): ?> · <?php echo htmlspecialchars($event['location_name']); ?><?php endif; ?>
																		<?php if (!empty($event['event_type_name'])): ?> · <?php echo htmlspecialchars($event['event_type_name']); ?><?php endif; ?>
																</div>
															</div>
															<div class="d-flex align-items-center gap-2">
																<?php if ((int)($event['is_registered'] ?? 0) > 0): ?>
																	<span class="badge badge-light-success">Registered</span>
																<?php else: ?>
																	<span class="badge badge-light-secondary"><?php echo number_format((int)($event['total_registrations'] ?? 0)); ?> registered</span>
																<?php endif; ?>
																<a href="event-view.php?id=<?php echo urlencode($event['event_id']); ?>" class="btn btn-primary btn-sm border">Lihat Event</a>
															</div>
														</div>
													<?php endforeach; ?>
												<?php endif; ?>
											</div>
										</div>
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

	<?php include "../../../include/scrolltop.php"; ?>

	<div>
		<script src="../../../assets/plugins/global/plugins.bundle.js"></script>
		<script src="../../../assets/js/scripts.bundle.js"></script>
		<script src="../../../assets/js/custom/widgets.js"></script>
		<script src="../../../assets/js/custom/apps/chat/chat.js"></script>
		<script src="../../../assets/js/custom/modals/create-app.js"></script>
		<script src="../../../assets/js/custom/modals/upgrade-plan.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
	</div>
</body>

</html>