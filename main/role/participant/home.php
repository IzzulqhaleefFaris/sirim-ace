<?php
session_start();
include "../../../include/config.php";


// Redirect to login if not logged in
if (!isset($_SESSION['userId'])) {
	header('Location: /attendance');
	exit;
}

$stats = [
	'totalEvents' => 0,
	'eventsThisMonth' => 0,
	'upcomingEventsCount' => 0,
];

$upcomingEvents = [];
$eventsError = null;

if (isset($conn) && $conn instanceof mysqli) {
	$totalEventsRes = $conn->query("SELECT COUNT(*) AS total FROM att_event");
	if ($totalEventsRes && ($row = $totalEventsRes->fetch_assoc())) {
		$stats['totalEvents'] = (int) $row['total'];
	}

	$thisMonthRes = $conn->query("SELECT COUNT(*) AS total FROM att_event WHERE YEAR(event_startDate) = YEAR(CURDATE()) AND MONTH(event_startDate) = MONTH(CURDATE())");
	if ($thisMonthRes && ($row = $thisMonthRes->fetch_assoc())) {
		$stats['eventsThisMonth'] = (int) $row['total'];
	}

	$upcomingCountRes = $conn->query("SELECT COUNT(*) AS total FROM att_event WHERE event_startDate >= CURDATE()");
	if ($upcomingCountRes && ($row = $upcomingCountRes->fetch_assoc())) {
		$stats['upcomingEventsCount'] = (int) $row['total'];
	}

	$upcomingSql = "
		SELECT e.event_id,
			   e.event_name,
			   e.event_startDate,
			   e.event_endDate,
			   l.location_name,
			   t.event_type_name
		FROM att_event e
		LEFT JOIN att_location l ON e.location_id = l.location_id
		LEFT JOIN att_event_type t ON e.event_type_id = t.event_type_id
		WHERE e.event_startDate >= CURDATE()
		ORDER BY e.event_startDate ASC
		LIMIT 5";

	$upcomingRes = $conn->query($upcomingSql);
	if ($upcomingRes) {
		while ($event = $upcomingRes->fetch_assoc()) {
			$upcomingEvents[] = $event;
		}
	} else {
		$eventsError = "Tidak dapat memuatkan senarai event.";
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
<!--begin::Head-->
<head>
	<base href="">
	<meta charset="utf-8" />
	<title>ATTENDANCE SYSTEM</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="shortcut icon" href="../../../assets/media/logos/soljar_ico.ico" />

	<!--begin::Fonts-->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
	<!--end::Fonts-->

	<!--begin::Global Stylesheets Bundle(used by all pages)-->
	<link href="../../../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
	<link href="../../../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<!--end::Global Stylesheets Bundle-->
	<style>
		.hero-card {
			background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
			color: #fff;
			border: 0;
			box-shadow: 0 20px 45px rgba(79, 70, 229, 0.25);
		}

		.hero-card .badge {
			background: rgba(255, 255, 255, 0.2);
			color: #fff;
		}

		.hero-stat {
			background: rgba(255, 255, 255, 0.15);
			border: 1px solid rgba(255, 255, 255, 0.2);
			border-radius: 12px;
		}

		.feature-card {
			border: 1px solid #eef2f7;
			transition: transform .2s ease, box-shadow .2s ease;
		}

		.feature-card:hover {
			transform: translateY(-15px);
			box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
		}

		.feature-icon {
			width: 48px;
			height: 48px;
			border-radius: 14px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			background: #f3f4ff;
			color: #4f46e5;
		}

		.upcoming-card {
			border: 1px solid #eef2f7;
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
					<div class="post d-flex flex-column-fluid" id="kt_post">


						<div id="kt_content_container" class="container">
							<!--begin::Dashboard Section-->
							<div class="py-6 py-lg-10">
								<?php $nextEvent = $upcomingEvents[0] ?? null; ?>
								<div class="row g-5 mb-5">
									<div class="col-xl-7">
										<div class="card hero-card h-100">
											<div class="card-body p-5 p-lg-7">
												<span class="badge mb-3">Dashboard Peserta</span>
												<h1 class="fw-bold mb-3">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama'] ?? 'Peserta'); ?>.</h1>
												<p class="fs-6 mb-4">Terokai event yang menarik, semak status pendaftaran, dan simpan QR anda di satu tempat.</p>
												<div class="d-flex flex-wrap gap-2 mb-4">
													<a href="event-list.php" class="btn btn-light btn-sm">Lihat Semua Event</a>
													<a href="my-events.php" class="btn btn-outline-light btn-sm">My Events & QR</a>
												</div>
												<div class="row g-3">
													<div class="col-sm-4">
														<div class="hero-stat p-3 text-center">
															<div class="fw-bold fs-4"><?php echo number_format($stats['totalEvents']); ?></div>
															<div class="small">Jumlah Event</div>
														</div>
													</div>
													<div class="col-sm-4">
														<div class="hero-stat p-3 text-center">
															<div class="fw-bold fs-4"><?php echo number_format($stats['eventsThisMonth']); ?></div>
															<div class="small">Event Bulan Ini</div>
														</div>
													</div>
													<div class="col-sm-4">
														<div class="hero-stat p-3 text-center">
															<div class="fw-bold fs-4"><?php echo number_format($stats['upcomingEventsCount']); ?></div>
															<div class="small">Event Akan Datang</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-xl-5">
										<div class="card shadow-sm h-100">
											<div class="card-body p-4 p-lg-5">
												<h3 class="fw-bold mb-3">Event Terdekat</h3>
												<?php if ($eventsError): ?>
													<div class="alert alert-danger" role="alert">
														<?php echo htmlspecialchars($eventsError); ?>
													</div>
												<?php elseif (!$nextEvent): ?>
													<div class="text-muted">Tiada event akan datang buat masa ini.</div>
													<a href="event-list.php" class="btn btn-light btn-sm border mt-3">Semak Event Lain</a>
												<?php else: ?>
													<?php $nextDay = !empty($nextEvent['event_startDate']) ? date('d', strtotime($nextEvent['event_startDate'])) : '--'; ?>
													<div class="d-flex gap-3 align-items-center mb-3">
														<div class="date-pill d-flex align-items-center justify-content-center fs-5">
															<?php echo htmlspecialchars($nextDay); ?>
														</div>
														<div>
															<div class="fw-bold fs-5"><?php echo htmlspecialchars($nextEvent['event_name']); ?></div>
															<div class="text-muted small">
																<?php echo htmlspecialchars(formatEventDateRange($nextEvent['event_startDate'], $nextEvent['event_endDate'])); ?>
																<?php if (!empty($nextEvent['location_name'])): ?>
																	· <?php echo htmlspecialchars($nextEvent['location_name']); ?>
																<?php endif; ?>
															</div>
														</div>
													</div>
													<div class="d-flex flex-wrap gap-2">
														<a href="event-view.php?id=<?php echo urlencode($nextEvent['event_id']); ?>" class="btn btn-primary btn-sm">Lihat Butiran</a>
														<a href="event-list.php" class="btn btn-light btn-sm border">Semua Event</a>
													</div>
												<?php endif; ?>
											</div>
										</div>
									</div>
								</div>

								<div class="row g-4 mb-5">
									<div class="col-md-4">
										<div class="card feature-card h-100">
											<div class="card-body">
												<div class="feature-icon mb-3"><i class="bi bi-search fs-4"></i></div>
												<h5 class="fw-bold">Cari Event</h5>
												<p class="text-muted small mb-0">Gunakan carian dan penapis untuk jumpa event yang sesuai.</p>
											</div>
										</div>
									</div>
									<div class="col-md-4">
										<div class="card feature-card h-100">
											<div class="card-body">
												<div class="feature-icon mb-3"><i class="bi bi-check2-circle fs-4"></i></div>
												<h5 class="fw-bold">Daftar Pantas</h5>
												<p class="text-muted small mb-0">Daftar event dengan satu klik dan semak status anda segera.</p>
											</div>
										</div>
									</div>
									<div class="col-md-4">
										<div class="card feature-card h-100">
											<div class="card-body">
												<div class="feature-icon mb-3"><i class="bi bi-qr-code-scan fs-4"></i></div>
												<h5 class="fw-bold">Simpan QR</h5>
												<p class="text-muted small mb-0">QR anda sentiasa tersedia untuk imbasan kehadiran.</p>
											</div>
										</div>
									</div>
								</div>

								<div class="card upcoming-card shadow-sm">
									<div class="card-header border-0 pt-4">
										
									</div>
								</div>
							</div>
						</div>
						<!--end::Dashboard Section-->
					</div>
					<!--end::Container-->
				</div>
				<!--end::Post-->
			</div>
			<!--end::Content-->
		</div>
		<!--end::Wrapper-->
	</div>
	<!--end::Page-->
	</div>
	<!--end::Root-->

	<!--begin::Footer-->
	<?php include "../../../include/footer.php"; ?>
	<!--end::Footer-->

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
		<!--begin::Page Custom Javascript(used by this page)-->
		<script src="../../../assets/js/custom/widgets.js"></script>
		<script src="../../../assets/js/custom/apps/chat/chat.js"></script>
		<script src="../../../assets/js/custom/modals/create-app.js"></script>
		<script src="../../../assets/js/custom/modals/upgrade-plan.js"></script>
		<!--end::Page Custom Javascript-->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
	</div>
	<!--end::Javascript-->
</body>
<!--end::Body-->

</html>