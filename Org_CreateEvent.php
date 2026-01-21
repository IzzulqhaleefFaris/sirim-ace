<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "include/config.php";
include "include/updateEventStatus.php";

// Update event statuses based on current date
updateEventStatuses($conn);

// Redirect to login if not logged in
if (!isset($_SESSION['userId'])) {
	header('Location: /attendance');
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
	<title>ATTENDANCE SYSTEM</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="shortcut icon" href="assets/media/logos/soljar_ico.ico" />

	<!--begin::Fonts-->
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<!--end::Fonts-->

	<!--begin::Global Stylesheets Bundle(used by all pages)-->
	<link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/custom.css" rel="stylesheet" type="text/css" />
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
	<!--end::Global Stylesheets Bundle-->

	<!-- Custom Styling -->
	<style>
		.event-img-detail {
			width: 100%;
			max-width: 640px;
			aspect-ratio: 16 / 9;
			object-fit: cover;
			border-radius: 15px;
			display: block;
			margin: 0 auto;
		}

		/* Make alerts more prominent */
		.alert {
			position: fixed !important;
			/* fix it to the top of viewport */
			top: 60px;
			/* adjust to below header height */
			left: 50%;
			transform: translateX(-50%);
			z-index: 11000 !important;
			/* higher than header */
			width: auto;
			/* or 90% if you want */
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
				<?php include "include/header.php"; ?>
				<!--end::Header-->
				<!--begin::Content-->
				<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
					<!--begin::Toolbar-->
					<?php include "include/toolbar.php"; ?>
					<!--end::Toolbar-->

					<!--begin::Post-->
					<div class="post d-flex flex-column-fluid" id="kt_post">
						<div id="kt_content_container" class="container-fluid">
							<div class="row justify-content-center">
								<div class="col-md-8 col-lg-6">
									<!--begin::Section-->
									<div class="py-0">
										<form id="eventForm" method="POST" action="Org_EventCreation.php" enctype="multipart/form-data">
											<div class="card shadow-sm">
												<div class="card-header">
													<h5 class="card-title fs-1" style="font-weight: 800">Pendaftaran Event</h5>
												</div>
												<div class="card-body">

													<div class="container">
														<!--begin::Info Asas-->
														<h5 class="card-title fs-2" style="font-weight: 800">Info Asas</h5>

														<h3 class="card-title"><small class="text-danger fs-8"> Sila isikan ruang yang bertanda (<i class="text-danger">*</i>&nbsp;)</small></h3>

														<div class="row g-2 py-2">
															<label class="form-label form-label-sm required">Nama Event :</label>
															<input
																type="text"
																class="form-control form-control-sm w-100"
																id="event_name"
																name="event_name"
																value="<?= htmlspecialchars($_POST['event_name'] ?? '') ?>"
																placeholder="Nama event"
																required />
														</div>

														<div class="row g-2 py-2">
															<label class="form-label form-label-sm ">Maklumat Event :</label>
															<textarea
																class="form-control form-control-sm w-60"
																id="event_description"
																name="event_description"
																placeholder="Isikan maklumat event berkenaan"
																style="height: 100px;"><?= htmlspecialchars($_POST['event_description'] ?? '') ?></textarea>
														</div>

														<div class="row g-2 py-2">
															<label for="event_image" class="form-label form-label-sm mb-1">Event Image</label>

															<!-- Image Preview -->
															<div class="mb-2 text-center">
																<img id="eventImagePreview"
																	src="<?= !empty($event['event_image']) ? '/attendance/' . htmlspecialchars($event['event_image']) : '/attendance/images/custom/no_image.jpg' ?>"
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
															<label class="form-label form-label-sm required">Jenis Event :</label>
															<select class="form-select form-select-sm w-auto" name="event_type_id" id="jenisEvent">
																<option selected disabled>Pilih Jenis</option>
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


														<div class="row py-2 align-items-center">
															<label for="organiserInput" class="form-label form-label-sm required mb-0">Nama Pengurus :</label>
															<div class="col-auto">
																<select class="form-select form-select-sm w-auto" id="organiserInput" name="organiser" required>
																	<option value="" selected disabled>Pilih Nama Pengurus</option>
																	<option value="Staf 1">Staf 1</option>
																	<option value="Staf 2">Staf 2</option>
																	<option value="Staf 3">Staf 3</option>
																</select>
															</div>
														</div>
														<!-- end: Info Asas -->
													</div>
													<div class="separator my-7 separator-dotted border-muted"></div>
													<div class="container">
														<!--begin::Tarikh & Masa-->
														<h5 class="card-title fs-2" style="font-weight: 800">Tarikh & Masa</h5>
														<?php include('userErrors.php'); ?>

														<div class="mb-4">
															<div class="mb-4 pt-4 d-flex gap-2">
																<button type="button"
																	class="btn btn-outline-dark active"
																	id="singleEventBtn" style="border: 1px solid black">
																	Single Event
																</button>
																<button type="button"
																	class="btn btn-outline-dark"
																	id="recurringEventBtn" style="border: 1px solid black">
																	Recurring Event
																</button>
															</div>
															<input type="hidden" name="eventType" id="eventTypeInput" value="single">
														</div>

														<p style="color: #aaa5b7;">Single event can happens once and can last multiple days</p>

														<div class="row g-2 py-2">
															<!-- Start Date -->
															<div class="col-md-6">
																<label class="form-label form-label-sm required">Start Date :</label>
																<input
																	id="startDate"
																	class="form-control"
																	type="date"
																	name="event_startDate"
																	value="<?= htmlspecialchars($_POST['event_startDate'] ?? '') ?>"
																	required />
															</div>

															<!-- Start Time -->
															<div class="col-md-6">
																<label class="form-label form-label-sm required">Start Time :</label>
																<input
																	id="startTime"
																	class="form-control"
																	type="time"
																	name="event_startTime"
																	value="<?= htmlspecialchars($_POST['event_startTime'] ?? '09:00') ?>"
																	required />
															</div>
														</div>

														<div class="row g-2 py-2">
															<!-- End Date -->
															<div class="col-md-6">
																<label class="form-label form-label-sm required">End Date :</label>
																<input
																	id="endDate"
																	class="form-control"
																	type="date"
																	name="event_endDate"
																	value="<?= htmlspecialchars($_POST['event_endDate'] ?? '') ?>"
																	required />
															</div>

															<!-- End Time -->
															<div class="col-md-6">
																<label class="form-label form-label-sm required">End Time :</label>
																<input
																	id="endTime"
																	class="form-control"
																	type="time"
																	name="event_endTime"
																	value="<?= htmlspecialchars($_POST['event_endTime'] ?? '17:00') ?>"
																	required />
															</div>
														</div><br><br>

														<h5 class="card-title fs-2" style="font-weight: 800">Tarikh Pembukaan & Penutupan Pendaftaran</h5>

														<div class="row g-2 py-2">
															<!-- Open Registration Date -->
															<div class="col-md-6">
																<label for="event_openRegistration" class="form-label">Tarikh Buka Pendaftaran <span class="text-danger">*</span></label>
																<input type="date" class="form-control" name="event_openRegistration" id="event_openRegistration" required>
															</div>

															<!-- Close Registration Date -->
															<div class="col-md-6">
																<label for="event_closeRegistration" class="form-label">Tarikh Tutup Pendaftaran <span class="text-danger">*</span></label>
																<input type="date" class="form-control" name="event_closeRegistration" id="event_closeRegistration" required>
															</div>
														</div>

														<!-- end: Tarikh & Masa -->
													</div>
													<div class="separator my-7 separator-dotted border-muted"></div>
													<div class="container">
														<h5 class="card-title fs-2" style="font-weight: 800">Lokasi</h5>
														<?php include('userErrors.php'); ?>

														<div class="row g-2 py-2">
															<div class="col-md-6">
																<label class="form-label form-label-sm required">Nama Lokasi :</label>
																<input type="text" class="form-control form-control-sm mb-2" id="location_name" name="location_name" placeholder="Nama lokasi" style="max-width: 250px;" />
																<label class="form-label form-label-sm">Bangunan :</label>
																<input type="text" class="form-control form-control-sm mb-2" id="building_name" name="building_name" placeholder="Bangunan" style="max-width: 250px;" />
																<label class="form-label form-label-sm">Bilik :</label>
																<input type="text" class="form-control form-control-sm mb-2" id="location_room" name="location_room" placeholder="Bilik" style="max-width: 250px;" />
																<label class="form-label form-label-sm">Aras :</label>
																<input type="text" class="form-control form-control-sm" id="location_level" name="location_level" placeholder="Aras" style="max-width: 250px;" />
															</div>
															<div class="col-md-6" rowspan="4">
																<label class="form-label form-label-sm required">Alamat Penuh :</label>
																<textarea class="form-control form-control-sm" id="location_address" name="location_address" placeholder="Isikan alamat penuh" style="height: 100px;"></textarea>
																<label class="form-label form-label-sm required pt-2">Negeri : </label><br>
																<select class="form-select form-select-sm" name="state_id" id="negeriSelect" required>
																	<option selected disabled>Pilih Negeri</option>
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

												<div class="d-flex justify-content-end ">
													<button type="reset" class="btn btn-sm btn-white fw-bolder btn-active-light-primary me-2">Set Semula</button>
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
														<div class="modal-body">
															<table class="table table-bordered">
																<tbody>
																	<tr>
																		<th>Nama Event</th>
																		<td id="confirmEventName"></td>
																	</tr>
																	<tr>
																		<th>Jenis Event</th>
																		<td id="confirmEventType"></td>
																	</tr>
																	<tr>
																		<th>Tarikh Mula</th>
																		<td id="confirmStartDate"></td>
																	</tr>
																	<tr>
																		<th>Tarikh Tamat</th>
																		<td id="confirmEndDate"></td>
																	</tr>
																	<tr>
																		<th>Tarikh Buka Pendaftaran</th>
																		<td id="confirmOpenRegistration"></td>
																	</tr>
																	<tr>
																		<th>Tarikh Tutup Pendaftaran</th>
																		<td id="confirmCloseRegistration"></td>
																	</tr>
																	<tr>
																		<th>Negeri</th>
																		<td id="confirmState"></td>
																	</tr>
																	<tr>
																		<th>Nama Lokasi</th>
																		<td id="confirmLocationName"></td>
																	</tr>
																	<tr>
																		<th>Alamat Event</th>
																		<td id="confirmAddress"></td>
																	</tr>
																	<tr>
																		<th>Bangunan</th>
																		<td id="confirmBuilding"></td>
																	</tr>
																	<tr>
																		<th>Bilik</th>
																		<td id="confirmRoom"></td>
																	</tr>
																	<tr>
																		<th>Aras</th>
																		<td id="confirmLevel"></td>
																	</tr>
																</tbody>
															</table>
														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
															<button type="submit" form="eventForm" class="btn btn-success">Submit</button>
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
													<p id="successMessage" class="fs-5 fw-bold"></p>
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
		<?php include "include/footer.php"; ?>
	</footer>
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
			// Log any alerts to console for debugging
			const alert = document.querySelector('.alert');
			if (alert) {
				const alertText = alert.textContent;
				console.log('🔴 ALERT MESSAGE:', alertText);

				// Only auto-hide SUCCESS alerts, keep ERROR/WARNING visible
				if (alert.classList.contains('alert-success')) {
					setTimeout(() => {
						const bsAlert = new bootstrap.Alert(alert);
						bsAlert.close();
					});
				}
			}

			// Jenis Event Dropdown
			document.querySelectorAll('.jenis-event-option').forEach(function(item) {
				item.addEventListener('click', function(e) {
					e.preventDefault();
					document.getElementById('jenisEventDropdown').textContent = this.textContent;
					document.getElementById('jenisEvent').value = this.getAttribute('data-value');
				});
			});

			// Nama Pengurus Dropdown
			document.querySelectorAll('.organiser-option').forEach(function(item) {
				item.addEventListener('click', function(e) {
					e.preventDefault();
					document.getElementById('organiserDropdown').textContent = this.textContent;
					document.getElementById('organiserInput').value = this.getAttribute('data-value');
				});
			});

			// Toggle active/inactive state and update hidden input
			document.getElementById('singleEventBtn').addEventListener('click', function() {
				this.classList.add('active');
				document.getElementById('recurringEventBtn').classList.remove('active');
				document.getElementById('eventTypeInput').value = 'single';
			});

			document.getElementById('recurringEventBtn').addEventListener('click', function() {
				this.classList.add('active');
				document.getElementById('singleEventBtn').classList.remove('active');
				document.getElementById('eventTypeInput').value = 'recurring';
			});

			//confirm button
			document.getElementById('confirmBtn').addEventListener('click', function() {
				// Collect input values
				const eventName = document.querySelector('[name="event_name"]').value;
				const eventType = document.querySelector('#jenisEvent').selectedOptions[0]?.text || '';
				const startDate = document.querySelector('[name="event_startDate"]').value;
				const endDate = document.querySelector('[name="event_endDate"]').value;
				const openRegistration = document.querySelector('[name="event_openRegistration"]').value;
				const closeRegistration = document.querySelector('[name="event_closeRegistration"]').value;
				const state = document.querySelector('#negeriSelect').selectedOptions[0]?.text || '';
				const locationName = document.querySelector('[name="location_name"]').value;
				const building = document.querySelector('[name="building_name"]').value;
				const address = document.querySelector('[name="location_address"]').value;
				const room = document.querySelector('[name="location_room"]').value;
				const level = document.querySelector('[name="location_level"]').value;

				// Debug log
				console.log('Form validation check:', {
					eventName,
					startDate,
					endDate,
					openRegistration,
					closeRegistration,
					state,
					locationName
				});

				// ✅ Validation check before showing modal
				if (!eventName || !startDate || !endDate || !state || !locationName) {
					const errorMsg = 'Please fill all required fields before confirming.';
					console.error('❌ VALIDATION FAILED:', errorMsg);
					alert('❌ ' + errorMsg);
					return;
				}

				// Additional client-side date validation
				const startTS = new Date(startDate).getTime();
				const endTS = new Date(endDate).getTime();
				const openRegTS = new Date(openRegistration).getTime();
				const closeRegTS = new Date(closeRegistration).getTime();

				if (endTS < startTS) {
					console.error('❌ End date before start date');
					alert('❌ Event end date cannot be earlier than start date.');
					return;
				}

				if (closeRegTS < openRegTS) {
					console.error('❌ Close registration before open registration');
					alert('❌ Registration closing date cannot be earlier than opening date.');
					return;
				}

				if (openRegTS > startTS) {
					console.error('❌ Registration opens after event starts');
					alert('❌ Registration cannot open after event starts.');
					return;
				}

				if (closeRegTS > endTS) {
					console.error('❌ Registration closes after event ends');
					alert('❌ Registration cannot close after event ends.');
					return;
				}

				console.log('✅ All validations passed, showing confirmation modal');

				// Fill modal content
				document.getElementById('confirmEventName').textContent = eventName;
				document.getElementById('confirmEventType').textContent = eventType;
				document.getElementById('confirmStartDate').textContent = startDate;
				document.getElementById('confirmEndDate').textContent = endDate;
				document.getElementById('confirmOpenRegistration').textContent = openRegistration;
				document.getElementById('confirmCloseRegistration').textContent = closeRegistration;
				document.getElementById('confirmState').textContent = state;
				document.getElementById('confirmLocationName').textContent = locationName;
				document.getElementById('confirmBuilding').textContent = building;
				document.getElementById('confirmAddress').textContent = address;
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

		<!-- Custom 2: Keyboard function -->
		<script>
			// Prevent pressing Enter from directly submitting the form
			document.getElementById("eventForm").addEventListener("keydown", function(e) {
				if (e.key === "Enter") {
					e.preventDefault();
				}
			});
		</script>

		<script>
			document.getElementById('startDate').addEventListener('change', function() {
				const [year, month, day] = this.value.split("-");
				document.getElementById('formattedDate').textContent = `${day}/${month}/${year}`;
			});
		</script>

		<!-- Live preview of image -->
		<script>
			const eventInput = document.getElementById('event_image');
			const previewImg = document.getElementById('eventImagePreview');

			eventInput.addEventListener('change', function() {
				const file = this.files[0];
				if (file) {
					const reader = new FileReader();
					reader.onload = function(e) {
						previewImg.src = e.target.result; // show selected image
					}
					reader.readAsDataURL(file);
				} else {
					previewImg.src = '/attendance/assets/img/no-image.png'; // fallback
				}
			});
		</script>

	</div>
	<!--end::Javascript-->
</body>
<!--end::Body-->

</html>