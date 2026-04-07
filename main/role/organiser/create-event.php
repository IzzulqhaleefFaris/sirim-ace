<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "../../../include/config.php";
include "../../../include/updateEventStatus.php";
include "../../../include/permissions.php";

require_manage_events();

// Update event statuses based on current date
updateEventStatuses($conn);

// Redirect to login if not logged in
if (!isset($_SESSION['userId'])) {
	header('Location: /sirimace/index.php');
	exit;
}

// ✅ Dropdown: Event Types
$eventTypes = $conn->query("
    SELECT event_type_id, event_type_name
    FROM att_event_type
    ORDER BY event_type_name
");

// ✅ Dropdown: States
$states = $conn->query("
    SELECT state_id, state_name
    FROM att_state
    ORDER BY state_name
");

// Dropdown: PIC users
$picUsers = $conn->query("
	SELECT userId, nama, email
	FROM user
	WHERE nama IS NOT NULL AND nama <> ''
	ORDER BY nama
");

if (session_status() === PHP_SESSION_NONE) session_start();
$eventCreated = $_SESSION['event_created'] ?? null;
unset($_SESSION['event_created']);

$alertMessage = null;
if (!empty($_SESSION['msg'])):
	$alertMessage = $_SESSION['msg'];
	unset($_SESSION['msg']);
endif;

?>

<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
	<base href="">
	<meta charset="utf-8" />
	<title>Create Event | ATTENDANCE SYSTEM</title>
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
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" />
	<!--end::Fonts-->

	<!--begin::Global Stylesheets Bundle(used by all pages)-->
	<link href="https://cdn.jsdelivr.net/npm/@coreui/coreui@5.6.1/dist/css/coreui.min.css" rel="stylesheet" integrity="sha384-q/at3GHMpO8VjBXzZ+ymx89MhqKtK9AcxYuECgmRq1b2a3797G4sfEM/ylqgywpd" crossorigin="anonymous">
	<link href="../../../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
	<link href="../../../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<link rel="stylesheet" href="https://unpkg.com/antd@5/dist/reset.css" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
	<!--end::Global Stylesheets Bundle-->

	<!-- Custom Styling -->
	<style>
		:root {
			--page-bg-1: #f3f7fb;
			--page-bg-2: #eef2f8;
			--card-bg: #ffffff;
			--soft-border: #e7ecf3;
			--ink-900: #122033;
			--ink-700: #3d4e63;
			--ink-500: #73839a;
			--brand: #0f6cbf;
			--brand-deep: #0b4f8d;
		}

		body {
			font-family: "Manrope", sans-serif;
			background: radial-gradient(circle at 10% -10%, #ffffff 0%, var(--page-bg-1) 40%, var(--page-bg-2) 100%);
		}

		.modern-page {
			padding-top: 1rem;
			padding-bottom: 2rem;
		}

		.modern-event-card {
			background: var(--card-bg);
			border: 1px solid var(--soft-border);
			border-radius: 20px;
			overflow: hidden;
			box-shadow: 0 20px 45px rgba(16, 33, 55, 0.08);
		}

		.modern-card-header {
			padding: 1.5rem 1.75rem 1rem;
			border-bottom: 1px solid var(--soft-border);
			background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
		}

		.modern-card-title {
			margin: 0;
			font-size: 1.3rem;
			font-weight: 800;
			letter-spacing: -0.02em;
			color: var(--ink-900);
		}

		.modern-card-subtitle {
			margin-top: 0.35rem;
			font-size: 0.9rem;
			color: var(--ink-500);
		}

		.modern-card-body {
			padding: 1.5rem 1.75rem;
		}

		.modern-section {
			background: #fcfdff;
			border: 1px solid var(--soft-border);
			border-radius: 14px;
			padding: 1rem 1rem 0.5rem;
		}

		.modern-section-title {
			font-size: 1rem;
			font-weight: 800;
			color: var(--ink-900);
			margin-bottom: 0.25rem;
		}

		.modern-section-note {
			font-size: 0.82rem;
			color: var(--ink-500);
			margin-bottom: 0.75rem;
		}

		.form-control,
		.form-select,
		textarea.form-control {
			border: 1px solid var(--soft-border);
			border-radius: 10px;
			padding-top: 0.55rem;
			padding-bottom: 0.55rem;
			font-size: 0.93rem;
			color: var(--ink-900);
			box-shadow: none;
			transition: border-color 0.2s ease, box-shadow 0.2s ease;
		}

		.choices__inner {
			min-height: 38px;
			border: 1px solid var(--soft-border);
			border-radius: 10px;
			font-size: 0.93rem;
			padding: 0.32rem 0.55rem;
			background: #fff;
		}

		.is-focused .choices__inner,
		.is-open .choices__inner {
			border-color: rgba(15, 108, 191, 0.5);
			box-shadow: 0 0 0 0.2rem rgba(15, 108, 191, 0.12);
		}

		.choices__list--dropdown,
		.choices__list[aria-expanded] {
			border: 1px solid var(--soft-border);
			border-radius: 10px;
		}

		.form-control:focus,
		.form-select:focus,
		textarea.form-control:focus {
			border-color: rgba(15, 108, 191, 0.5);
			box-shadow: 0 0 0 0.2rem rgba(15, 108, 191, 0.12);
		}

		.form-label {
			font-weight: 600;
			color: var(--ink-700);
			margin-bottom: 0.35rem;
		}

		.event-mode-btn {
			border-radius: 999px !important;
			padding: 0.4rem 1rem;
			font-weight: 700;
		}

		.event-mode-btn.active {
			background-color: var(--brand);
			border-color: var(--brand) !important;
			color: #fff;
		}

		.event-img-detail {
			width: 100%;
			max-width: 640px;
			aspect-ratio: 16 / 9;
			object-fit: cover;
			border-radius: 14px;
			border: 1px solid var(--soft-border);
			display: block;
			margin: 0 auto;
			box-shadow: 0 8px 24px rgba(16, 33, 55, 0.1);
		}

		.alert {
			position: fixed !important;
			top: 60px;
			right: 18px;
			left: auto;
			transform: none;
			z-index: 11000 !important;
			min-width: 320px;
			max-width: 460px;
			border-radius: 12px;
			box-shadow: 0 12px 32px rgba(16, 33, 55, 0.2);
		}

		.separator {
			opacity: 0.8;
		}

		.modern-actions {
			padding: 1.2rem 1.5rem;
			border-top: 1px solid var(--soft-border);
			background: #fbfdff;
		}

		@media (max-width: 768px) {

			.modern-card-header,
			.modern-card-body,
			.modern-actions {
				padding-left: 1rem;
				padding-right: 1rem;
			}

			.alert {
				left: 12px;
				right: 12px;
				min-width: 0;
				max-width: none;
			}
		}

		.alert-danger {
			background-color: #f8d7da;
			border-color: #f5c6cb;
			color: #721c24;
		}

		.alert-warning {
			background-color: #fff3cd;
			border-color: #ffeeba;
			color: #856404;
		}

		.alert-success {
			background-color: #d4edda;
			border-color: #c3e6cb;
			color: #155724;
		}
	</style>
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed toolbar-tablet-and-mobile-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
	<?php if ($alertMessage): ?>
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<?= htmlspecialchars($alertMessage['text']) ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	<?php endif; ?>
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

					<!--begin::Post-->
					<div class="post d-flex flex-column-fluid" id="kt_post">
						<div id="kt_content_container" class="container-fluid modern-page">
							<div class="row justify-content-center">
								<div class="col-md-11 col-lg-10 col-xl-9">
									<!--begin::Section-->
									<div class="py-0">
										<form id="eventForm" method="POST" action="event-creation.php" enctype="multipart/form-data">
											<div class="card modern-event-card">
												<div class="modern-card-header">
													<h5 class="modern-card-title">Create Event</h5>
													<div class="modern-card-subtitle">Fill in the essential details to create your event.</div>
												</div>
												<div class="card-body modern-card-body">

													<div class="container modern-section">
														<!--begin::Info Asas-->
														<h5 class="modern-section-title">Basic Info</h5>

														<div class="modern-section-note">Please fill in all required fields (<span class="text-danger">*</span>).</div>

														<div class="row g-2 py-2">
															<label class="form-label form-label-sm required">Event Name :</label>
															<input
																type="text"
																class="form-control form-control-sm w-100"
																id="event_name"
																name="event_name"
																value="<?= htmlspecialchars($_POST['event_name'] ?? '') ?>"
																placeholder="Event name"
																required />
														</div>

														<div class="row g-2 py-2">
															<label class="form-label form-label-sm ">Event Details :</label>
															<textarea
																class="form-control form-control-sm w-60"
																id="event_description"
																name="event_description"
																placeholder="Enter event details"
																style="height: 100px;"><?= htmlspecialchars($_POST['event_description'] ?? '') ?></textarea>
														</div>

														<div class="row g-2 py-2">
															<label for="event_image" class="form-label form-label-sm mb-1">Event Image</label>

															<!-- Image Preview -->
															<div class="mb-2 text-center">
																<img id="eventImagePreview"
																	src="/sirimace/images/custom/no_image.jpg"
																	data-default-src="/sirimace/images/custom/no_image.jpg"
																	alt="Event Image Preview"
																	class="event-img-detail">
															</div>

															<div class="accordion accordion-flush" id="eventAccordion">
																<div class="accordion-item">
																	<h2 class="accordion-header" id="headingInfo">
																		<button class="accordion-button collapsed px-0 py-2 fw-semibold"
																			type="button"
																			data-bs-toggle="collapse"
																			data-bs-target="#collapseInfo"
																			aria-expanded="false"
																			aria-controls="collapseInfo">
																			<i class="bi bi-info-circle me-2 text-primary"></i>
																			Image Requirements
																		</button>
																	</h2>

																	<div id="collapseInfo"
																		class="accordion-collapse collapse"
																		aria-labelledby="headingInfo"
																		data-bs-parent="#eventAccordion">
																		<div class="accordion-body px-3 py-3 bg-light rounded-2">
																			<ul class="list-unstyled small mb-0">
																				<li>• Aspect ratio: <strong>16:9</strong></li>
																				<li>• Minimum size: <strong>1280 × 720 px</strong></li>
																				<li>• Recommended size: <strong>1600 × 900 px</strong></li>
																				<li>• Format: <strong>JPG / PNG</strong></li>
																				<li>• Max file size: <strong>2 MB</strong></li>
																			</ul>
																		</div>
																	</div>
																</div>
															</div>
															<input type="file"
																class="form-control form-control-sm"
																name="event_image"
																id="event_image"
																accept="image/*">
														</div>

														<div class="row g-2 py-2">
															<label class="form-label form-label-sm required">Event Type :</label>
															<select class="form-select form-select-sm" name="event_type_id" id="jenisEvent">
																<option selected disabled>Select Type</option>
																<?php if ($eventTypes): ?>
																	<?php while ($r = $eventTypes->fetch_assoc()): ?>
																		<option value="<?= htmlspecialchars($r['event_type_id']) ?>"
																			<?= (($_POST['event_type_id'] ?? '') == $r['event_type_id']) ? 'selected' : '' ?>>
																			<?= htmlspecialchars($r['event_type_name']) ?>
																		</option>
																	<?php endwhile; ?>
																<?php endif; ?>
															</select>
														</div>

														<div class="row g-2 py-2">
															<label for="organiserInput" class="form-label form-label-sm mb-0 required">Person In-Charge (PIC) :</label>
															<div class="col-12">
																<select class="form-select form-select-sm" id="organiserInput" name="organiser" required>
																	<option value="" selected disabled>Select PIC</option>
																	<?php if ($picUsers): ?>
																		<?php while ($u = $picUsers->fetch_assoc()): ?>
																			<?php
																			$userId = (string)($u['userId'] ?? '');
																			$userName = trim((string)($u['nama'] ?? ''));
																			$userEmail = trim((string)($u['email'] ?? ''));
																			$displayText = $userName . ($userEmail !== '' ? ' (' . $userEmail . ')' : '');
																			$selected = (($_POST['organiser'] ?? '') === $userId) ? 'selected' : '';
																			?>
																			<option value="<?= htmlspecialchars($userId) ?>" <?= $selected ?>>
																				<?= htmlspecialchars($displayText) ?>
																			</option>
																		<?php endwhile; ?>
																	<?php endif; ?>
																</select>
															</div>
														</div>
														<!-- end: Info Asas -->
													</div>
													<div class="separator my-7 separator-dotted border-muted"></div>
													<div class="container modern-section">
														<!--begin::Tarikh & Masa-->
														<h5 class="modern-section-title">Date & Time</h5>
														<?php include('userErrors.php'); ?>

														<div class="mb-4">
															<div class="mb-4 pt-4 d-flex gap-2">
																<button type="button"
																	class="btn btn-outline-dark event-mode-btn active"
																	id="singleEventBtn">
																	Single Event
																</button>
																<button type="button"
																	class="btn btn-outline-dark event-mode-btn"
																	id="recurringEventBtn">
																	Recurring Event
																</button>
															</div>
															<input type="hidden" name="eventType" id="eventTypeInput" value="single">
														</div>

														<p class="modern-section-note">Single event happens once and can span multiple days.</p>
														
														<div class="row g-2 py-2">
															<!-- Start Date -->
															<div class="col-md-6">
																<label class="form-label form-label-sm required">Start Date :</label>
																<div id="startDateMount"></div>
																<input type="hidden" id="startDate" name="event_startDate" value="<?= htmlspecialchars($_POST['event_startDate'] ?? '') ?>">
															</div>

															<!-- Start Time -->
															<div class="col-md-6">
																<label class="form-label form-label-sm required">Start Time :</label>
																<div id="startTimeMount"></div>
																<input type="hidden" id="startTime" name="event_startTime" value="<?= htmlspecialchars($_POST['event_startTime'] ?? '09:00') ?>">
															</div>
														</div>

														<div class="row g-2 py-2">
															<!-- End Date -->
															<div class="col-md-6">
																<label class="form-label form-label-sm required">End Date :</label>
																<div id="endDateMount"></div>
																<input type="hidden" id="endDate" name="event_endDate" value="<?= htmlspecialchars($_POST['event_endDate'] ?? '') ?>">
															</div>

															<!-- End Time -->
															<div class="col-md-6">
																<label class="form-label form-label-sm required">End Time :</label>
																<div id="endTimeMount"></div>
																<input type="hidden" id="endTime" name="event_endTime" value="<?= htmlspecialchars($_POST['event_endTime'] ?? '17:00') ?>">
															</div>
														</div><br><br>

														<h5 class="modern-section-title">Registration Date</h5>

														<p class="modern-section-note">Note: All dates take effect at exactly 12:00 AM local time.</p>

														<div class="row g-2 py-2">
															<!-- Open Registration Date -->
															<div class="col-md-6">
																<label for="event_openRegistration" class="form-label">Registration Open Date <span class="text-danger">*</span></label>
																<div id="openRegMount"></div>
																<input type="hidden" id="event_openRegistration" name="event_openRegistration">
															</div>

															<!-- Close Registration Date -->
															<div class="col-md-6">
																<label for="event_closeRegistration" class="form-label">Registration Close Date <span class="text-danger">*</span></label>
																<div id="closeRegMount"></div>
																<input type="hidden" id="event_closeRegistration" name="event_closeRegistration">
															</div>
														</div>

														<!-- end: Tarikh & Masa -->
													</div>
													<div class="separator my-7 separator-dotted border-muted"></div>
													<div class="container modern-section">
														<h5 class="modern-section-title">Location</h5>
														<?php include('userErrors.php'); ?>

														<div class="row g-2 py-2">
															<div class="col-md-6">
																<label class="form-label form-label-sm required">Location Name :</label>
																<input type="text" class="form-control form-control-sm mb-2" id="location_name" name="location_name" placeholder="Location name" />
																<label class="form-label form-label-sm">Building/Blok :</label>
																<input type="text" class="form-control form-control-sm mb-2" id="location_buildingName" name="location_buildingName" placeholder="Building" />
																<label class="form-label form-label-sm">Room/Hall :</label>
																<input type="text" class="form-control form-control-sm mb-2" id="location_room" name="location_room" placeholder="Room/Hall" />
																<label class="form-label form-label-sm">Level :</label>
																<input type="text" class="form-control form-control-sm" id="location_level" name="location_level" placeholder="Level/Floor" />
															</div>
															<div class="col-md-6" rowspan="4">
																<label class="form-label form-label-sm required">Address Line 1 :</label>
																<input type="text" class="form-control form-control-sm mb-2" id="address_line1" name="address_line1" placeholder="Building Name/Lot No." />
																<label class="form-label form-label-sm">Address Line 2 :</label>
																<input type="text" class="form-control form-control-sm mb-2" id="address_line2" name="address_line2" placeholder="Street Name" />
																<label class="form-label form-label-sm required">City :</label>
																<input type="text" class="form-control form-control-sm mb-2" id="address_city" name="address_city" placeholder="City" />
																<label class="form-label form-label-sm required">Postcode :</label>
																<input type="text" class="form-control form-control-sm" id="address_postcode" name="address_postcode" placeholder="Postcode" />
																<label class="form-label form-label-sm required pt-2">State : </label><br>
																<select class="form-select form-select-sm" name="state_id" id="negeriSelect" required>
																	<option selected disabled>Select State</option>
																	<?php if ($states): ?>
																		<?php while ($s = $states->fetch_assoc()): ?>
																			<?php $sel = (isset($_POST['state_id']) && $_POST['state_id'] === $s['state_id']) ? 'selected' : ''; ?>
																			<option value="<?= htmlspecialchars($s['state_id']) ?>" <?= $sel ?>>
																				<?= htmlspecialchars($s['state_name']) ?>
																			</option>
																		<?php endwhile; ?>
																	<?php endif; ?>
																</select>
															</div>
														</div>
													</div>
												</div>

												<div class="separator separator-dotted border-muted"></div>

												<div class="d-flex justify-content-end modern-actions">
													<button type="reset" class="btn btn-sm btn-white fw-bolder btn-active-light-primary me-2">Reset</button>
													<button type="button" class="btn btn-primary" id="confirmBtn">Confirm</button>
												</div>
											</div>
											<!-- Confirmation Modal -->
											<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
												<div class="modal-dialog modal-lg modal-dialog-centered">
													<div class="modal-content">
														<div class="modal-header bg-dark text-white">
															<h5 class="modal-title" id="confirmModalLabel">Confirm Event Details</h5>
															<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
														</div>
														<div class="modal-body my-2">
															<div class="bg-light rounded-3 p-3 mb-3 border">
																<div class="d-flex justify-content-between align-items-center">
																	<div>
																		<div class="text-muted small">Review details</div>
																		<div class="fw-semibold">Please ensure all information is correct</div>
																	</div>
																</div>
															</div>
															<table class="table table-sm table-hover align-middle mb-0">
																<tbody>
																	<tr>
																		<th class="text-muted w-25">Event Name</th>
																		<td id="confirmEventName" class="fw-semibold"></td>
																	</tr>
																	<tr>
																		<th class="text-muted">Event Type</th>
																		<td id="confirmEventType"></td>
																	</tr>
																	<tr>
																		<th class="text-muted">Person In-Charge (PIC)</th>
																		<td id="confirmPIC"></td>
																	</tr>
																	<tr>
																		<th class="text-muted">Event Date</th>
																		<td id="confirmEventDate"></td>
																	</tr>
																	<tr>
																		<th class="text-muted">Registration Date</th>
																		<td id="confirmRegistrationDate"></td>
																	</tr>
																	<tr>
																		<th class="text-muted">Time</th>
																		<td id="confirmEventTime"></td>
																	</tr>
																	<tr>
																		<th class="text-muted">State</th>
																		<td id="confirmState"></td>
																	</tr>
																	<tr>
																		<th class="text-muted">Location Name</th>
																		<td id="confirmLocationName"></td>
																	</tr>
																	<tr>
																		<th class="text-muted">Event Address</th>
																		<td id="confirmAddress"></td>
																	</tr>
																	<tr>
																		<th class="text-muted">Building</th>
																		<td id="confirmBuilding"></td>
																	</tr>
																	<tr>
																		<th class="text-muted">Bilik</th>
																		<td id="confirmRoom"></td>
																	</tr>
																	<tr>
																		<th class="text-muted">Aras</th>
																		<td id="confirmLevel"></td>
																	</tr>
																	<tr></tr>
																</tbody>
															</table>
														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
															<button type="submit" form="eventForm" id="submitEventBtn" class="btn btn-success">
																<span id="submitBtnText">Submit</span>
																<span id="submitBtnSpinner" class="d-none">
																	<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
																	Creating...
																</span>
															</button>
														</div>
													</div>
												</div>
											</div>
										</form>
									</div>
									<!-- Success Modal -->
									<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
										<div class="modal-dialog modal-dialog-centered">
											<div class="modal-content border-success">
												<div class="modal-header bg-success text-white">
													<h5 class="modal-title" id="successModalLabel">Event Created Successfully</h5>
													<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
												</div>
												<div class="modal-body text-center">
													<div class="mb-3">
														<i class="bi bi-check-circle-fill text-success" style="font-size:3rem;"></i>
													</div>
													<p id="successMessage" class="fs-5 fw-bold"></p>
													<p class="text-muted small mb-0">
														<i class="bi bi-envelope-check me-1"></i>
														Notification email has been sent to the organiser and PIC.
													</p>
												</div>
												<div class="modal-footer justify-content-center">
													<button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
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
	</div>
	<!--end::Wrapper-->
	<footer>
		<?php include "../../../include/footer.php"; ?>
	</footer>
	</div>
	<!--end::Page-->
	</div>
	<!--end::Root-->

	<!--begin::Scrolltop-->
	<?php include "../../../include/scrolltop.php"; ?>
	<!--end::Scrolltop-->
	<!--end::Main-->

	<!--begin::Javascript-->
	<div>
		<!-- Bootstrap -->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@5.6.1/dist/js/coreui.bundle.min.js" integrity="sha384-bfWw6UWgmz1820GDTev34DLTN/eDwwySOsuWriOxV32ZBfwFgiPaDzvOP+vG0Yur" crossorigin="anonymous"></script>

		<!-- Metronic -->
		<script src="../../../assets/plugins/global/plugins.bundle.js"></script>
		<script src="../../../assets/js/scripts.bundle.js"></script>

		<!-- Page custom JS -->
		<script src="../../../assets/js/custom/widgets.js"></script>
		<script src="../../../assets/js/custom/apps/chat/chat.js"></script>
		<script src="../../../assets/js/custom/modals/create-app.js"></script>
		<script src="../../../assets/js/custom/modals/upgrade-plan.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
		<script>
			// Log any alerts to console for debugging
			const alertBox = document.querySelector('.alert');
			if (alertBox) {
				const alertText = alertBox.textContent;
				console.log('🔴 ALERT MESSAGE:', alertText);

				if (alertBox.classList.contains('alert-success')) {
					setTimeout(() => {
						const bsAlert = new bootstrap.Alert(alertBox);
						bsAlert.close();
					});
				}
			}

			// Event Type Dropdown
			document.querySelectorAll('.jenis-event-option').forEach(function(item) {
				item.addEventListener('click', function(e) {
					e.preventDefault();
					document.getElementById('jenisEventDropdown').textContent = this.textContent;
					document.getElementById('jenisEvent').value = this.getAttribute('data-value');
				});
			});

			// Searchable PIC select
			document.addEventListener('DOMContentLoaded', function() {
				const picSelect = document.getElementById('organiserInput');
				if (!picSelect) return;

				new Choices(picSelect, {
					searchEnabled: true,
					itemSelectText: '',
					shouldSort: false,
					placeholder: true,
					placeholderValue: 'Search PIC name or email'
				});
			});

			// Toggle active/inactive state and update hidden input
			// document.getElementById('singleEventBtn').addEventListener('click', function() {
			// 	this.classList.add('active');
			// 	document.getElementById('recurringEventBtn').classList.remove('active');
			// 	document.getElementById('eventTypeInput').value = 'single';
			// });

			// document.getElementById('recurringEventBtn').addEventListener('click', function() {
			// 	this.classList.add('active');
			// 	document.getElementById('singleEventBtn').classList.remove('active');
			// 	document.getElementById('eventTypeInput').value = 'recurring';
			// });

			//confirm button
			document.getElementById('confirmBtn').addEventListener('click', function() {
				function formatDateToDMY(dateStr) {
					if (!dateStr || typeof dateStr !== 'string') return '';
					const parts = dateStr.split('-');
					if (parts.length !== 3) return dateStr;
					return `${parts[2]}/${parts[1]}/${parts[0]}`;
				}

				function formatTimeTo12H(timeStr) {
					if (!timeStr || typeof timeStr !== 'string') return '';
					const parts = timeStr.split(':');
					if (parts.length < 2) return timeStr;

					const hour24 = parseInt(parts[0], 10);
					const minute = parts[1];
					if (Number.isNaN(hour24)) return timeStr;

					const suffix = hour24 >= 12 ? 'PM' : 'AM';
					const hour12 = (hour24 % 12) || 12;
					return `${String(hour12).padStart(2, '0')}:${minute} ${suffix}`;
				}

				function formatDateRange(start, end) {
					const startFormatted = formatDateToDMY(start);
					const endFormatted = formatDateToDMY(end);
					if (!startFormatted && !endFormatted) return '';
					if (!startFormatted) return endFormatted;
					if (!endFormatted) return startFormatted;
					return start === end ? startFormatted : `${startFormatted} - ${endFormatted}`;
				}

				function formatTimeRange(start, end) {
					const startFormatted = formatTimeTo12H(start);
					const endFormatted = formatTimeTo12H(end);
					if (!startFormatted && !endFormatted) return '';
					if (!startFormatted) return endFormatted;
					if (!endFormatted) return startFormatted;
					return `${startFormatted} - ${endFormatted}`;
				}

				// Collect input values
				const eventName = document.querySelector('[name="event_name"]').value;
				const eventType = document.querySelector('#jenisEvent').selectedOptions[0]?.text || '';
				const pic = document.querySelector('#organiserInput').selectedOptions[0]?.text || '';
				const startDate = document.querySelector('[name="event_startDate"]').value;
				const endDate = document.querySelector('[name="event_endDate"]').value;
				const startTime = document.querySelector('[name="event_startTime"]').value;
				const endTime = document.querySelector('[name="event_endTime"]').value;
				const openRegistration = document.querySelector('[name="event_openRegistration"]').value;
				const closeRegistration = document.querySelector('[name="event_closeRegistration"]').value;
				const state = document.querySelector('#negeriSelect').selectedOptions[0]?.text || '';
				const locationName = document.querySelector('[name="location_name"]').value;
				const building = document.querySelector('[name="location_buildingName"]').value;
				const addressLine1 = document.querySelector('[name="address_line1"]').value;
				const addressLine2 = document.querySelector('[name="address_line2"]').value;
				const addressCity = document.querySelector('[name="address_city"]').value;
				const addressPostcode = document.querySelector('[name="address_postcode"]').value;
				const room = document.querySelector('[name="location_room"]').value;
				const level = document.querySelector('[name="location_level"]').value;

				// Debug log
				console.log('Form validation check:', {
					eventName,
					startDate,
					endDate,
					startTime,
					endTime,
					openRegistration,
					closeRegistration,
					state,
					locationName
				});

				// ✅ Validation check before showing modal
				if (!eventName || !pic || !startDate || !endDate || !state || !locationName) {
					console.error('❌ VALIDATION FAILED');
					showError('Please fill all required fields before confirming.');
					return; // 🔴 STOP execution here
				}

				// Additional client-side date validation
				const startTS = new Date(startDate).getTime();
				const endTS = new Date(endDate).getTime();
				const openRegTS = new Date(openRegistration).getTime();
				const closeRegTS = new Date(closeRegistration).getTime();

				if (endTS < startTS) {
					console.error('❌ End date before start date');
					showError('❌ Event end date cannot be earlier than start date.');
					return;
				}

				if (closeRegTS < openRegTS) {
					console.error('❌ Close registration before open registration');
					showError('❌ Registration closing date cannot be earlier than opening date.');
					return;
				}

				if (openRegTS > startTS) {
					console.error('❌ Registration opens after event starts');
					showError('❌ Registration cannot open after event starts.');
					return;
				}

				if (closeRegTS > endTS) {
					console.error('❌ Registration closes after event ends');
					showError('❌ Registration cannot close after event ends.');
					return;
				}

				console.log('✅ All validations passed, showing confirmation modal');

				// Fill modal content
				document.getElementById('confirmEventName').textContent = eventName;
				document.getElementById('confirmEventType').textContent = eventType;
				document.getElementById('confirmPIC').textContent = pic;
				document.getElementById('confirmEventDate').textContent = formatDateRange(startDate, endDate);
				document.getElementById('confirmRegistrationDate').textContent = formatDateRange(openRegistration, closeRegistration);
				document.getElementById('confirmEventTime').textContent = formatTimeRange(startTime, endTime);
				document.getElementById('confirmState').textContent = state;
				document.getElementById('confirmLocationName').textContent = locationName;
				document.getElementById('confirmBuilding').textContent = building;
				const addressParts = [addressLine1, addressLine2, addressCity, addressPostcode].filter(Boolean);
				document.getElementById('confirmAddress').textContent = addressParts.join(', ');
				document.getElementById('confirmRoom').textContent = room;
				document.getElementById('confirmLevel').textContent = level;

				// Show modal
				const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
				modal.show();
			});

			document.addEventListener("DOMContentLoaded", function() {
				<?php if ($eventCreated): ?>
					const successModal = new bootstrap.Modal(document.getElementById('successModal'));
					const messageContainer = document.getElementById('successMessage');

					// Create formatted HTML with centered text
					messageContainer.innerHTML = `
						<div style="text-align:center;">
							<strong>Event name:</strong> <?= htmlspecialchars($eventCreated['name']) ?><br>
							has been successfully created!
						</div>
					`;
					successModal.show();
				<?php endif; ?>
			});
		</script>

        <!-- Loading overlay on form submit -->
		<script>
			document.getElementById('eventForm').addEventListener('submit', function() {
				const btn = document.getElementById('submitEventBtn');
				const btnText = document.getElementById('submitBtnText');
				const btnSpinner = document.getElementById('submitBtnSpinner');
				btn.disabled = true;
				btnText.classList.add('d-none');
				btnSpinner.classList.remove('d-none');
			});
		</script>

		<!-- Custom 2: Keyboard function -->
		<script>
			// Prevent pressing Enter from directly submitting the form
			document.getElementById("eventForm").addEventListener("keydown", function(e) {
				if (e.key === "Enter") {
					e.preventDefault();
				}
			});
		</script>



		<!-- Live preview of image -->
		<script>
			const eventInput = document.getElementById('event_image');
			const previewImg = document.getElementById('eventImagePreview');
			const defaultSrc = previewImg?.dataset?.defaultSrc || previewImg?.getAttribute('src') || '';

			eventInput.addEventListener('change', function() {
				const file = this.files[0];
				if (file) {
					const reader = new FileReader();
					reader.onload = function(e) {
						previewImg.src = e.target.result; // show selected image
					}
					reader.readAsDataURL(file);
				} else if (defaultSrc) {
					previewImg.src = defaultSrc; // fallback to default
				}
			});

			const form = document.getElementById('eventForm');
			if (form) {
				form.addEventListener('reset', function() {
					if (defaultSrc) {
						previewImg.src = defaultSrc;
					}
				});
			}
		</script>

		<!-- React + antd DatePicker / TimePicker via CDN (no build tool needed) -->
		<script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
		<script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
		<script src="https://unpkg.com/dayjs@1/dayjs.min.js"></script>
		<script src="https://unpkg.com/antd@5/dist/antd.min.js"></script>
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				function mountNativeDateInput(mountId, hiddenId, defaultValue) {
					var el = document.getElementById(mountId);
					if (!el) return;

					el.innerHTML = '';
					var input = document.createElement('input');
					input.type = 'date';
					input.className = 'form-control form-control-sm';
					input.value = defaultValue || '';
					input.addEventListener('change', function() {
						document.getElementById(hiddenId).value = input.value || '';
					});
					el.appendChild(input);

					if (defaultValue) {
						document.getElementById(hiddenId).value = defaultValue;
					}
				}

				function mountNativeTimeInput(mountId, hiddenId, defaultValue) {
					var el = document.getElementById(mountId);
					if (!el) return;

					el.innerHTML = '';
					var input = document.createElement('input');
					input.type = 'time';
					input.className = 'form-control form-control-sm';
					input.value = defaultValue || '';
					input.addEventListener('change', function() {
						document.getElementById(hiddenId).value = input.value || '';
					});
					el.appendChild(input);

					if (defaultValue) {
						document.getElementById(hiddenId).value = defaultValue;
					}
				}

				function mountFallbackPickers(defaults) {
					mountNativeDateInput('startDateMount', 'startDate', defaults.startDate);
					mountNativeTimeInput('startTimeMount', 'startTime', defaults.startTime);
					mountNativeDateInput('endDateMount', 'endDate', defaults.endDate);
					mountNativeTimeInput('endTimeMount', 'endTime', defaults.endTime);
					mountNativeDateInput('openRegMount', 'event_openRegistration', '');
					mountNativeDateInput('closeRegMount', 'event_closeRegistration', '');
				}

				var hasAntdStack = !!(window.React && window.ReactDOM && window.dayjs && window.antd && window.ReactDOM.createRoot);
				if (!hasAntdStack) {
					console.warn('AntD picker dependencies missing. Falling back to native date/time inputs.');
					mountFallbackPickers({
						startDate: <?= json_encode($_POST['event_startDate'] ?? '') ?>,
						endDate: <?= json_encode($_POST['event_endDate']   ?? '') ?>,
						startTime: <?= json_encode($_POST['event_startTime'] ?? '09:00') ?>,
						endTime: <?= json_encode($_POST['event_endTime']   ?? '17:00') ?>
					});
					return;
				}

				var _a = antd, DatePicker = _a.DatePicker, TimePicker = _a.TimePicker;
				var h = React.createElement;

				var phpDefaults = {
					startDate:  <?= json_encode($_POST['event_startDate'] ?? '') ?>,
					endDate:    <?= json_encode($_POST['event_endDate']   ?? '') ?>,
					startTime:  <?= json_encode($_POST['event_startTime'] ?? '09:00') ?>,
					endTime:    <?= json_encode($_POST['event_endTime']   ?? '17:00') ?>
				};

				function mountDatePicker(mountId, hiddenId, defaultValue) {
					var el = document.getElementById(mountId);
					if (!el) return;
					var defaultVal = defaultValue ? dayjs(defaultValue) : undefined;
					var root = ReactDOM.createRoot(el);
					root.render(h(DatePicker, {
						defaultValue: defaultVal,
						style: { width: '100%' },
						format: 'DD/MM/YYYY',
						onChange: function(dayjsObj) {
							// Store as YYYY-MM-DD for PHP backend
							document.getElementById(hiddenId).value = dayjsObj ? dayjsObj.format('YYYY-MM-DD') : '';
						},
						placeholder: 'Select date'
					}));
				}

				function mountTimePicker(mountId, hiddenId, defaultValue) {
					var el = document.getElementById(mountId);
					if (!el) return;
					var defaultVal = defaultValue ? dayjs(defaultValue, 'HH:mm') : undefined;
					var root = ReactDOM.createRoot(el);
					root.render(h(TimePicker, {
						defaultValue: defaultVal,
						style: { width: '100%' },
						format: 'hh:mm A',
						showSecond: false,
						use12Hours: true,
						needConfirm: false,
						onChange: function(dayjsObj) {
							// Store as HH:mm (24h) for PHP backend
							document.getElementById(hiddenId).value = dayjsObj ? dayjsObj.format('HH:mm') : '';
						},
						placeholder: 'Select time'
					}));
				}

				mountDatePicker('startDateMount', 'startDate',            phpDefaults.startDate);
				mountTimePicker('startTimeMount', 'startTime',            phpDefaults.startTime);
				mountDatePicker('endDateMount',   'endDate',              phpDefaults.endDate);
				mountTimePicker('endTimeMount',   'endTime',              phpDefaults.endTime);
				mountDatePicker('openRegMount',   'event_openRegistration',  '');
				mountDatePicker('closeRegMount',  'event_closeRegistration', '');
			});
		</script>

		<!-- Error Alert For Confirm Button  -->
		<script>
			function showError(message) {
				// Remove existing error if any
				const existing = document.querySelector('.custom-error-alert');
				if (existing) existing.remove();

				const errorBox = document.createElement('div');
				errorBox.className = 'alert alert-danger alert-dismissible fade show custom-error-alert position-fixed top-0 start-50 translate-middle-x mt-5';
				errorBox.style.zIndex = '12000';
				errorBox.role = 'alert';

				errorBox.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

				document.body.appendChild(errorBox);

				// Auto remove after 4 seconds
				setTimeout(() => {
					const bsAlert = new bootstrap.Alert(errorBox);
					bsAlert.close();
				}, 4000);
			}
		</script>
	</div>
	<!--end::Javascript-->
</body>
<!--end::Body-->

</html>