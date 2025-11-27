<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'include/eventCreation.php';
$eventCreated = $_SESSION['event_created'] ?? null;
unset($_SESSION['event_created']);
?>

<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['msg'])):
	$msgType = $_SESSION['msg']['type'] ?? 'info';
	$msgText = $_SESSION['msg']['text'] ?? '';
?>
	<div class="alert alert-<?= htmlspecialchars($msgType) ?> alert-dismissible fade show m-4" role="alert">
		<?= $msgText ?>
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	</div>
<?php
	unset($_SESSION['msg']); // clear message after display
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
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
	<!--end::Fonts-->
	<!--begin::Global Stylesheets Bundle(used by all pages)-->
	<link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/custom.css" rel="stylesheet" type="text/css" />
	<!--end::Global Stylesheets Bundle-->
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed toolbar-tablet-and-mobile-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
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
										<form id="eventForm" method="POST" action="Org_CreateEvent.php">
											<div class="card shadow-sm">
												<div class="card-header">
													<h5 class="card-title fs-1" style="font-weight: 700">Pendaftaran Event</h5>
												</div>
												<div class="card-body">

													<div class="container">
														<!--begin::Info Asas-->
														<h5 class="card-title fs-2" style="font-weight: 800">Info Asas</h5>
														<?php include('userErrors.php'); ?>

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
																value="<?= htmlspecialchars($_POST['event_description'] ?? '') ?>"
																placeholder="Isikan maklumat event berkenaan"
																style="height: 100px;"></textarea>
														</div>

														<div class="row g-2 py-2">
															<label class="form-label form-label-sm required">Jenis Event :</label>
															<select class="form-select form-select-sm w-auto" name="event_type_id" id="jenisEvent">
																<option selected disabled>Pilih Jenis</option>
																<?php if ($eventTypes): ?>
																	<?php while ($r = $eventTypes->fetch_assoc()): ?>
																		<option value="<?= htmlspecialchars($r['event_type_id']) ?>">
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
																	name="event_startTime" />
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
																	name="event_endTime" />
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
			// Auto-hide alert after 4 seconds
			setTimeout(() => {
				const alert = document.querySelector('.alert');
				if (alert) {
					const bsAlert = new bootstrap.Alert(alert);
					bsAlert.close();
				}
			}, 4000);

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

				// ✅ Validation check before showing modal
				if (!eventName || !startDate || !endDate || !state || !locationName) {
					alert('Please fill all required fields before confirming.');
					return;
				}

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
	</div>
	<!--end::Javascript-->
</body>
<!--end::Body-->

</html>