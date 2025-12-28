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
											<div class="card-body">
												<span class="text-muted fw-bold">Jumlah Event</span>
												<h2 class="fw-bold mb-1"><?php echo number_format($stats['totalEvents']); ?></h2>
												<p class="text-muted mb-0">Keseluruhan event tersenarai</p>
											</div>
										</div>
									</div>
									<!-- Second column data -->
									<div class="col-md-4">
										<div class="card shadow-sm border-0 h-100">	
											<div class></div>
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

				<!--begin::Footer-->
				<?php include "include/footer.php"; ?>
				<!--end::Footer-->
			</div>
			<!--end::Wrapper-->
		</div>
		<!--end::Page-->
	</div>
	<!--end::Root-->

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