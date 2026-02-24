<?php
session_start();
include "../../../include/permissions.php";

require_manage_events();
?>

<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
	<base href="">
	<meta charset="utf-8" />
	<title>Home| ATTENDANCE SYSTEM</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="shortcut icon" href="../../../assets/media/logos/soljar_ico.ico" />

	<!-- Global Javascript -->
	<script src="/attendance/assets/plugins/global/plugins.bundle.js"></script>
	<script src="/attendance/assets/js/scripts.bundle.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


	<!--begin::Page Custom Javascript(used by this page)-->
	<script src="assets/js/custom/widgets.js"></script>
	<script src="assets/js/custom/apps/chat/chat.js"></script>
	<script src="assets/js/custom/modals/create-app.js"></script>
	<script src="assets/js/custom/modals/upgrade-plan.js"></script>
	<!--end::Page Custom Javascript-->

	<!--begin::Fonts-->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
	<!--end::Fonts-->

	<!--begin::Global Stylesheets Bundle(used by all pages)-->
	<link href="../../../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
	<link href="../../../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
	<!--end::Global Stylesheets Bundle-->

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
							<!--begin::Section-->
							<div class="py-0">
								<div class="card shadow-sm mb-5 home-hero">
									<div class="card-body py-6 px-6">
										<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
											<div>
												<h2 class="fw-bolder mb-1">Organizer Dashboard</h2>
												<div class="text-muted">Manage events, review registrations, and scan attendance faster.</div>
											</div>
										</div>
									</div>
								</div>

								<div class="row g-4">
									<div class="col-md-6 col-xl-3">
										<a href="create-event.php" class="card quick-action h-100 text-decoration-none text-dark shadow-sm">
											<div class="card-body d-flex flex-column gap-3">
												<span class="action-icon bg-light-info"><i class="bi bi-calendar-event fs-2 text-info"></i></span>
												<div class="fw-bolder fs-5">Create Event</div>
												<div class="text-muted small">Create a new event and set registration details.</div>
											</div>
										</a>
									</div>

									<div class="col-md-6 col-xl-3">
										<a href="browse-event-list.php" class="card quick-action h-100 text-decoration-none text-dark shadow-sm">
											<div class="card-body d-flex flex-column gap-3">
												<span class="action-icon bg-light-primary"><i class="bi bi-search fs-2 text-primary"></i></span>
												<div class="fw-bolder fs-5">Browse Events</div>
												<div class="text-muted small">Browse events for registration and review.</div>
											</div>
										</a>
									</div>

									<div class="col-md-6 col-xl-3">
										<a href="event-list.php" class="card quick-action h-100 text-decoration-none text-dark shadow-sm">
											<div class="card-body d-flex flex-column gap-3">
												<span class="action-icon bg-light-warning"><i class="bi bi-gear fs-2 text-warning"></i></span>
												<div class="fw-bolder fs-5">Manage Events</div>
												<div class="text-muted small">Manage, update, and monitor organizer events.</div>
											</div>
										</a>
									</div>

									<div class="col-md-6 col-xl-3">
										<a href="my-events.php" class="card quick-action h-100 text-decoration-none text-dark shadow-sm">
											<div class="card-body d-flex flex-column gap-3">
												<span class="action-icon bg-light-dark"><i class="bi bi-calendar-check fs-2 text-dark"></i></span>
												<div class="fw-bolder fs-5">My Events & QR</div>
												<div class="text-muted small">View your registered events and registration QR.</div>
											</div>
										</a>
									</div>

									<div class="col-md-6 col-xl-3">
										<a href="scanner.php" class="card quick-action h-100 text-decoration-none text-dark shadow-sm">
											<div class="card-body d-flex flex-column gap-3">
												<span class="action-icon bg-light-success"><i class="bi bi-upc-scan fs-2 text-success"></i></span>
												<div class="fw-bolder fs-5">Scan Attendance</div>
												<div class="text-muted small">Scan participant QR codes to record attendance during events.</div>
											</div>
										</a>
									</div>

									<div class="col-md-6 col-xl-3">
										<div class="card quick-action h-100 shadow-sm">
											<div class="card-body d-flex flex-column gap-3">
												<span class="action-icon bg-light-warning"><i class="bi bi-journals fs-2 text-warning"></i></span>
												<div class="fw-bolder fs-5">Attendance Report (2025)</div>
												<div class="dropdown w-100 mt-auto">
													<button class="btn btn-light-primary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false" aria-haspopup="true">
														Select Event
													</button>
													<ul class="dropdown-menu w-100">
														<li><a class="dropdown-item" href="attendanceReport.php?eventId=STUES2">STU Engagement Series 2 (Penang)</a></li>
														<li><a class="dropdown-item" href="attendanceReport.php?eventId=STUES3">STU Engagement Series 3 (Pahang)</a></li>
														<li><a class="dropdown-item" href="attendanceReport.php?eventId=STUES4">STU Engagement Series 4 (Shah Alam)</a></li>
														<li><a class="dropdown-item" href="attendanceReport.php?eventId=STUES5">STU Engagement Series 5 (FI)</a></li>
														<li><a class="dropdown-item" href="attendanceReport.php?eventId=STUES6">STU Engagement Series 6 (Johor)</a></li>
														<li><a class="dropdown-item" href="attendanceReport.php?eventId=STUES7">STU Engagement Series 7 (MITI)</a></li>
													</ul>
												</div>
											</div>
										</div>
									</div>
								</div>
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
				<?php include "../../../include/footer.php"; ?>
				<!--end::Footer-->
			</div>
			<!--end::Wrapper-->
		</div>
		<!--end::Page-->
	</div>
	<!--end::Root-->

	<!--begin::Scrolltop-->
	<?php include "../../../include/scrolltop.php"; ?>
	<!--end::Scrolltop-->
	<!--end::Main-->

	<!--begin::Javascript-->
	<div></div>
	<!--end::Javascript-->
</body>
<!--end::Body-->

</html>