<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_SESSION['userId'])) {
	header('Location: home.php');
	exit;
}

$msg = $_SESSION['msg'] ?? null;
if ($msg) {
	$msgType = is_array($msg) ? ($msg['type'] ?? 'info') : 'info';
	$msgText = is_array($msg) ? ($msg['text'] ?? '') : $msg;
	unset($_SESSION['msg']);
}
?>

<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
	<base href="">
	<meta charset="utf-8" />
	<title>ATTENDANCE</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="shortcut icon" href="assets/media/logos/soljar_ico.ico" />
	<!--begin::Fonts-->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
	<!--end::Fonts-->
	<!--begin::Global Stylesheets Bundle(used by all pages)-->
	<link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
	<!--end::Global Stylesheets Bundle-->
	<script language="javascript">
		function checkForm() {
			var uname, upass;
			with(window.document.loginForm) {
				uname = user_name;
				upass = user_pass;
			}

			if (trim(uname.value) == '') {
				alert('Please enter your username.');
				uname.focus();
				return false;
			} else if (trim(upass.value) == '') {
				alert('Please enter your password.');
				upass.focus();
				return false;
			} else {
				uname.value = trim(uname.value);
				upass.value = trim(upass.value);
				return true;
			}
		}

		function trim(str) {
			return str.replace(/^\s+|\s+$/g, '');
		}

		function forgotPassword() {
			window.location = 'f_lupa_kataLaluan.php'
		}
	</script>
</head>
<!--end::Head-->

<!--begin::Body-->

<body id="kt_body" class="bg-white header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed toolbar-tablet-and-mobile-fixed aside-enabled aside-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
	<?php if (!empty($msgText)): ?>
		<!-- Flash message modal -->
		<div class="modal fade" id="sessionMsgModal" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header bg-<?= htmlspecialchars($msgType) ?> text-white">
						<h5 class="modal-title"><?= ($msgType === 'danger' ? 'Ralat' : 'Makluman') ?></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<p><?= htmlspecialchars($msgText) ?></p>
					</div>
				</div>
			</div>
		</div>

		<script>
			document.addEventListener('DOMContentLoaded', function() {
				var modalEl = document.getElementById('sessionMsgModal');
				if (modalEl && typeof bootstrap !== 'undefined') {
					new bootstrap.Modal(modalEl).show();
				} else if (modalEl) {
					// fallback: simple alert if bootstrap not available
					alert(modalEl.querySelector('.modal-body p').textContent);
				}
			});
		</script>
	<?php endif; ?>
	<!--begin::Main-->
	<div class="d-flex flex-column flex-root">
		<!--begin::Authentication - Sign-in -->
		<div class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-contain bgi-attachment-fixed">
			<!--begin::Content-->
			<div class="d-flex flex-center flex-column flex-column-fluid p-10 pb-lg-20">
				<!--begin::Logo-->
				<a href="index.html" class="mb-12">
					<img alt="Logo" src="assets/media/logos/atendance.png" class="h-150px" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img alt="Logo" src="assets/media/logos/sirim.png" class="h-150px" />
				</a>
				<h1 class="text-dark mb-5">ATTENDANCE | Advanced Event and Attendance Coordination Engine</h1>
				<!--end::Logo-->

				<!--begin::Wrapper-->
				<div class="w-lg-500px bg-white rounded shadow-sm p-10 p-lg-10 mx-auto">
					<!--begin::Form-->
					<form class="form w-100" novalidate="novalidate" id="loginForm" action="loginCode.php" method="post" name="loginForm" runat="server" style="clear: both;">

						<!--begin::Input group-->
						<div class="fv-row mb-5">
							<!--begin::Label-->
							<label class="form-label fs-6 fw-bolder text-dark">ID Pengguna</label>
							<!--end::Label-->
							<!--begin::Input-->
							<input class="form-control form-control-lg form-control-solid" type="text" name="user_name" id="user_name" autocomplete="off" />
							<!--end::Input-->
						</div>
						<!--end::Input group-->
						<!--begin::Input group-->
						<div class="fv-row mb-5">
							<!--begin::Wrapper-->
							<div class="d-flex flex-stack mb-2">
								<!--begin::Label-->
								<label class="form-label fw-bolder text-dark fs-6 mb-0">Kata Laluan</label>
								<!--end::Label-->
							</div>
							<!--end::Wrapper-->
							<!--begin::Input-->
							<div class="position-relative mb-3" id="show_hide_password">
								<input class="form-control form-control-lg form-control-solid" type="password" name="user_pass" id="user_pass" autocomplete="off" />
								<div class="input-group-append">
									<span>
										<a class="btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n8" type="button">
											<i class="fa fa-eye-slash" aria-hidden="true"></i></a>
									</span>
								</div>
							</div>
							<!--end::Input-->
						</div>
						<!--end::Input group-->

						<!--begin::Actions-->
						<div class="text-center">
							<!--begin::Submit button-->
							<!--<input class="btn btn-lg btn-info w-100 mb-5" name="login" type="submit" value="Log Masuk" />-->
							<button class="btn btn-lg btn-info w-100 mb-5" type="submit" name="login"><i class="bi bi-box-arrow-in-right fs-1"></i>&nbsp;Log Masuk</button>
							<!--end::Submit button-->
						</div>
						<!--end::Actions-->
					</form>
					<!--end::Form-->

					<!-- Start:: Register text -->
					<div class="text-end mt-3">
						<small class="text-muted">
							Belum ada akaun?
							<a href="register.php" class="link-primary">Daftar Sini!</a>
						</small>
					</div>
					<!-- End:: Register text -->
				</div>
				<!--end::Wrapper-->
			</div>
			<!--end::Content-->

			<!--begin::Footer-->
			<div class="d-flex flex-center flex-column-auto p-10">
				<!--begin::Links-->
				<div class="d-flex align-items-center fw-bold fs-6">
					<a class="text-muted text-hover-primary px-2">&copy;2024 - <?php echo date("Y"); ?>, ATTENDANCE. Hakcipta Terpelihara. Dibangun dan diselenggarakan oleh SIRIM Berhad. | Version 1.0</a>
				</div>
				<!--end::Links-->
			</div>
			<!--end::Footer-->
		</div>
		<!--end::Authentication - Sign-in-->
	</div>
	<!--end::Main-->

	<!--begin::Javascript-->
	<div>
		<!--begin::Global Javascript Bundle(used by all pages)-->
		<script src="assets/plugins/global/plugins.bundle.js"></script>
		<script src="assets/js/scripts.bundle.js"></script>
		<!--end::Global Javascript Bundle-->
		<!--begin::Page Custom Javascript(used by this page)-->
		<script src="assets/js/custom/authentication/sign-in/general.js"></script>
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
		<?php if (isset($_GET['register']) && $_GET['register'] == 'success'): ?>
			<script>
				alert("Pendaftaran berjaya! Sila log masuk.");
			</script>
		<?php endif; ?>
	</div>
	<!--end::Javascript-->
</body>
<!--end::Body-->

</html>