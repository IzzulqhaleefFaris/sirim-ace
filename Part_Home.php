<?php
session_start();
include "include/config.php";

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
	<link rel="shortcut icon" href="assets/media/logos/soljar_ico.ico" />
	<!--begin::Fonts-->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
	<!--end::Fonts-->
	<!--begin::Global Stylesheets Bundle(used by all pages)-->
	<link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
	<!--end::Global Stylesheets Bundle-->
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
				<?php include "include/header.php"; ?>
				<!--end::Header-->
				<!--begin::Content-->
				<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
					<!--begin::Toolbar-->
					<?php include "include/toolbar.php"; ?>
					<!--end::Toolbar-->

					<!--begin::Post-->
					<div class="post d-flex flex-column-fluid" id="kt_post">


						<div id="kt_content_container" class="container">
							<!--begin::Dashboard Section-->
							<div class="py-6 py-lg-10">
								<div class="row g-5 g-xl-10 mb-5 mb-xl-10">
									<!-- First column data -->
									<div class="col-md-4">
										<div class="card shadow-sm border-0 h-100">
											<div class="card-body row align-items-center">
												<div class="col-auto me-3">
													<i class="bi bi-calendar-event-fill fs-1 "></i>
												</div>
												<div class="col">
													<span class="text fw-bold">Jumlah Event</span>
													<h2 class="fw-bold mb-1"><?php echo number_format($stats['totalEvents']); ?></h2>
													<p class="text-muted mb-0">Keseluruhan event tersenarai</p>
												</div>
											</div>
										</div>
									</div>
									<!-- Second column data -->
									<div class="col-md-4">
										<div class="card shadow-sm border-0 h-100">
											<div class="card-body row align-items-center">
												<div class="col-auto me-3">
													<i class="bi bi-calendar-event-fill fs-1 "></i>
												</div>
												<div class="col">
													<span class="text fw-bold">Event Bulan Ini</span>
													<h2 class="fw-bold mb-1"><?php echo number_format($stats['eventsThisMonth']); ?></h2>
													<p class="text-muted mb-0">Jadual sepanjang bulan semasa</p>
												</div>
											</div>
										</div>
									</div>
									<!-- Third column data -->
									<div class="col-md-4">
										<div class="card shadow-sm border-0 h-100">
											<div class="card-body row align-items-center">
												<div class="col-auto me-3">
													<i class="bi bi-calendar-event-fill fs-1 "></i>
												</div>
												<div class="col">
													<span class="text fw-bold">Event Akan Datang</span>
													<h2 class="fw-bold mb-1"><?php echo number_format($stats['upcomingEventsCount']); ?></h2>
													<p class="text-muted mb-0">Event selepas tarikh hari ini</p>
												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="row g-5 g-xl-10 mb-5 mb-xl-10">
									<div class="col-xl-5">
										<div class="card shadow-sm border-0 h-100">
											<div class="card-body d-flex flex-column justify-content-center">
												<p class="text mb-2">Selamat Datang!</p>
												<h2 class="fw-bold mb-4">Lihat maklumat events yang dikendalikan oleh pihak SIRIM</h2>
												<p class="text-muted fs-6 mb-6">Jejak status pendaftaran dan lihat semua event yang bakal berlangsung secara pantas di sini.</p>
												<a href="Part_EventList.php" class="btn btn-primary btn-sm align-self-start">Lihat Semua Event</a>
											</div>
										</div>
									</div>
									<div class="col-xl-7">
										<div class="card shadow-sm border-0 h-100">
											<div class="card-header border-0 pt-5">
												<h3 class="card-title align-items-start flex-column">
													<span class="card-label fw-bold fs-3 mb-1">Event Akan Datang</span>
													<span class="text-muted fw-semibold fs-7">Senarai 5 event terdekat</span>
												</h3>
											</div>
											<div class="card-body pt-3">
												<?php if ($eventsError): ?>
													<div class="alert alert-danger" role="alert">
														<?php echo htmlspecialchars($eventsError); ?>
													</div>
												<?php elseif (empty($upcomingEvents)): ?>
													<div class="text-muted text-center py-5">
														--Tiada event akan datang buat masa ini.--
													</div>
												<?php else: ?>
													<div class="timeline">
														<?php foreach ($upcomingEvents as $event): ?>
															<div class="timeline-item align-items-center mb-6">
																<!-- <div class="timeline-line w-20p"></div> -->
																<div class="timeline-icon symbol symbol-40px me-4">
																	<?php $eventDay = !empty($event['event_startDate']) ? date('d', strtotime($event['event_startDate'])) : '--'; ?>
																	<span class="symbol-label bg-light-primary text-primary fw-bold">
																		<?php echo htmlspecialchars($eventDay); ?>
																	</span>
																</div>
															</div>
															<div class="timeline-content d-flex justify-content-between flex-grow-1">
																<div class="me-5">
																	<div class="fw-bold fs-5 text-dark"><?php echo htmlspecialchars($event['event_name']); ?></div>
																	<div class="text-muted fs-7">
																		<?php echo htmlspecialchars(formatEventDateRange($event['event_startDate'], $event['event_endDate'])); ?>
																		<?php if (!empty($event['location_name'])): ?>
																			· <?php echo htmlspecialchars($event['location_name']); ?>
																		<?php endif; ?>
																	</div>
																</div>
																<span class="badge badge-light-primary align-self-start"><?php echo htmlspecialchars($event['event_type_name'] ?? 'Event'); ?></span>
															</div>
														<?php endforeach; ?>
													</div>
											</div>
										<?php endif; ?>
										</div>
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
	<?php include "include/footer.php"; ?>
	<!--end::Footer-->

	<!--begin::Scrolltop-->
	<?php include "include/scrolltop.php"; ?>
	<!--end::Scrolltop-->
	<!--end::Main-->

	<!--begin::Javascript-->
	<div>
		<!--begin::Global Javascript Bundle(used by all pages)-->
		<script src="assets/plugins/global/plugins.bundle.js"></script>
		<script src="assets/js/scripts.bundle.js"></script>
		<!--end::Global Javascript Bundle-->
		<!--begin::Page Custom Javascript(used by this page)-->
		<script src="assets/js/custom/widgets.js"></script>
		<script src="assets/js/custom/apps/chat/chat.js"></script>
		<script src="assets/js/custom/modals/create-app.js"></script>
		<script src="assets/js/custom/modals/upgrade-plan.js"></script>
		<!--end::Page Custom Javascript-->
		<script>
			$(document).ready(function() {
				$("#show_hide_password a").on('click', function(event) {
					event.preventDefault();
					if ($('#show_hide_password input').attr("type") == "text") {
						$('#show_hide_password input').attr('type', 'password');
						$('#show_hide_password i').addClass("fa-eye-slash");
						$('#show_hide_password i').removeClass("fa-eye");
					} else if ($('#show_hide_password input').attr("type") == "password") {
						$('#show_hide_password input').attr('type', 'text');
						$('#show_hide_password i').removeClass("fa-eye-slash");
						$('#show_hide_password i').addClass("fa-eye");
					}
				});
			});
		</script>
	</div>
	<!--end::Javascript-->
</body>
<!--end::Body-->

</html>