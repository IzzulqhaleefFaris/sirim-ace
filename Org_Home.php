<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['userId'])) {
	header('Location: /attendance');
	exit;
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
						<!-- begin::ADDED-->
						<!-- <div id="kt_content_container" class="container">
							<div class="py-0">
								<div class="card shadow-sm">
									<div class="card-body"></div>
								</div>
							</div>
						</div> -->

						<!--END OF ADDED -->
						<div id="kt_content_container" class="container">
							<!--begin::Section-->
							<div class="py-0">
								<div class="card shadow-sm">
									<div class="card-body">
										<br>
										<!--<a><img alt="Logo" src="assets/media/logos/sepang2.png" class="h-100px" /></a>-->
										<div class="d-grid gap-2">
											<a href="Org_CreateEvent.php" type="button" class="btn btn-lg btn-info" style="font-size: 20px;"><i class="bi bi-calendar-event"></i>&nbsp;Daftar Event</a>
											<a href="Org_EventList.php" type="button" class="btn btn-lg btn-primary" style="font-size: 20px;"><i class="bi bi-card-list"></i>&nbsp;Senarai Event</a>
											<a href="scanner2.php?r=MULA" type="button" class="btn btn-lg btn-info" style="font-size: 20px;"><i class="bi bi-upc-scan fs-2"></i>&nbsp;Imbas Kedatangan</a>

											<div class="btn-group">
												<!--<a href="attendanceReport.php" type="button" class="btn btn-lg btn-primary" style="font-size: 20px;"><i class="bi bi-upc-scan fs-2"></i>&nbsp;Laporan Kedatangan</a>-->
												<button class="btn btn-lg btn-primary dropdown-toggle" style="font-size: 20px;" type="button" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
													<i class="bi bi-journals fs-2"></i>&nbsp;Laporan Kedatangan
												</button>
												<ul class="dropdown-menu dropdown-menu-lg-end">
													<li><a class="dropdown-item" style="font-size: 16px;" href="attendanceReport.php?eventId=STUES2"><i class="bi bi-journal fs-3"></i>&emsp;STU Engagement Series 2 (Penang)</a></li>
													<li><a class="dropdown-item" style="font-size: 16px;" href="attendanceReport.php?eventId=STUES3"><i class="bi bi-journal fs-3"></i>&emsp;STU Engagement Series 3 (Pahang)</a></li>
													<li><a class="dropdown-item" style="font-size: 16px;" href="attendanceReport.php?eventId=STUES4"><i class="bi bi-journal fs-3"></i>&emsp;STU Engagement Series 4 (Shah Alam)</a></li>
													<li><a class="dropdown-item" style="font-size: 16px;" href="attendanceReport.php?eventId=STUES5"><i class="bi bi-journal fs-3"></i>&emsp;STU Engagement Series 5 (FI)</a></li>
													<li><a class="dropdown-item" style="font-size: 16px;" href="attendanceReport.php?eventId=STUES6"><i class="bi bi-journal fs-3"></i>&emsp;STU Engagement Series 6 (Johor)</a></li>
													<li><a class="dropdown-item" style="font-size: 16px;" href="attendanceReport.php?eventId=STUES7"><i class="bi bi-journal fs-3"></i>&emsp;STU Engagement Series 7 (MITI)</a></li>
													<!--<li><a class="dropdown-item" style="font-size: 16px;" href="rondaDailyMap.php"><i class="bi bi-journal-text fs-3"></i>&emsp;Peta Rondaan</a></li>-->
												</ul>
											</div>
										</div>
										<br>
									</div>
								</div>
							</div>
							<!--end::Section-->
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