<?php
session_start();

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Redirect if already logged in — send to role-specific dashboard
if (isset($_SESSION['userId'])) {
	$roleId = $_SESSION['roleId'] ?? null;
	if ($roleId == 1) {
		header('Location: /sirimace/main/role/organiser/home.php?pg=OFCR');
		exit;
	} elseif ($roleId == 2) {
		header('Location: /sirimace/main/role/participant/home.php?pg=OFCR');
		exit;
	} elseif ($roleId == 3) {
		header('Location: /sirimace/main/role/admin/home.php?pg=ADMIN');
		exit;
	}
	// For unknown roles or no role, show login page
}

// Handle flash messages
$msg = $_SESSION['msg'] ?? null;
if ($msg) {
	$msgType = is_array($msg) ? ($msg['type'] ?? 'info') : 'info';
	$msgText = is_array($msg) ? ($msg['text'] ?? '') : $msg;
	unset($_SESSION['msg']);
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>

<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
	<base href="">
	<meta charset="utf-8" />
	<title>ATTENDANCE</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="shortcut icon" href="assets/media/logos/ace.png" />
	<!--begin::Fonts-->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
	<!--end::Fonts-->
	<!--begin::Global Stylesheets Bundle(used by all pages)-->
	<link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
	<!--end::Global Stylesheets Bundle-->
	<script language="javascript">
		function checkForm() {
			var emailField = document.loginForm.email;
			var passwordField = document.loginForm.user_pass;

			if (emailField.value.trim() === '') {
				alert('Please enter email.');
				emailField.focus();
				return false;
			} else if (passwordField.value.trim() === '') {
				alert('Please enter password.');
				passwordField.focus();
				return false;
			}
			return true;
		}
	</script>
	<style>
		html,
		body {
			height: 100%;
			margin: 0;
			overflow: hidden;
		}

		.login-split {
			display: flex;
			min-height: 100vh;
		}

		.login-image {
			flex: 1;
			position: relative;
			overflow: hidden;
			background-image: url("assets/media/logos/Sirim-50.jpg");
			background-size: cover;
			background-position: center;
			background-repeat: no-repeat;
			filter: none;
		}

		/* Blurred background layer to fill any gaps */
		.login-image::before {
			content: '';
			position: absolute;
			inset: -20px;
			background-image: url("assets/media/logos/Sirim-50.jpg");
			background-size: cover;
			background-position: center;
			filter: blur(20px);
			z-index: 0;
		}

		/* Sharp image on top, full width, centered vertically */
		.login-image::after {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background-image: url("assets/media/logos/Sirim-50.jpg");
			background-size: contain;
			background-position: center;
			background-repeat: no-repeat;
			z-index: 1;
		}

		.login-form-side {
			flex: 0 0 50%;
			max-width: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			background: #fff;
			padding: 2rem;
		}

		.login-form-wrapper {
			width: 100%;
			max-width: 420px;
		}

		.login-form-wrapper .form-control {
			background-color: #f5f8fa;
			border: 1px solid #e4e6ef;
			padding: 0.75rem 1rem;
			font-size: 1rem;
		}

		.login-form-wrapper .form-control:focus {
			border-color: #3699ff;
			box-shadow: none;
		}

		.btn-login {
			background-color: #3699ff;
			border-color: #3699ff;
			color: #fff;
			padding: 2rem 2rem;
			font-weight: 600;
			border-radius: 0.475rem;
		}

		.btn-login:hover {
			background-color: #1a7fe0;
			border-color: #1a7fe0;
			color: #fff;
		}

		@media (max-width: 991.98px) {
			.login-image {
				display: none;
			}

			.login-form-side {
				flex: 1;
				max-width: 100%;
			}
		}

		/* Loading overlay */
		.loading-overlay {
			display: none;
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(255, 255, 255, 0.85);
			z-index: 9999;
			justify-content: center;
			align-items: center;
			flex-direction: column;
		}

		.loading-overlay.active {
			display: flex;
		}

		.loading-spinner {
			width: 48px;
			height: 48px;
			border: 5px solid #e4e6ef;
			border-top: 5px solid #3699ff;
			border-radius: 50%;
			animation: spin 0.8s linear infinite;
		}

		@keyframes spin {
			0% { transform: rotate(0deg); }
			100% { transform: rotate(360deg); }
		}
	</style>
</head>
<!--end::Head-->

<!--begin::Body-->

<body id="kt_body" class="bg-white">
	<!-- Loading overlay -->
	<div class="loading-overlay" id="loadingOverlay">
		<div class="loading-spinner"></div>
		<p class="text-muted mt-3 fw-semibold" id="loadingText">Signing in...</p>
	</div>

	<?php if (!empty($msgText)): ?>
		<!-- Flash message modal -->
		<div class="modal fade" id="sessionMsgModal" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header bg-<?= htmlspecialchars($msgType) ?> text-white">
						<h5 class="modal-title"><?= ($msgType === 'danger' ? 'Error' : 'Notice') ?></h5>
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
					alert(modalEl.querySelector('.modal-body p').textContent);
				}
			});
		</script>
	<?php endif; ?>

	<!--begin::Split Layout-->
	<div class="login-split">
		<!--begin::Left Image-->
		<div class="login-image"></div>
		<!--end::Left Image-->

		<!--begin::Right Form-->
		<div class="login-form-side">
			<div class="login-form-wrapper">
				<!--begin::Logo-->
				<div class="text-center mb-6">
					<img alt="Logo" src="assets/media/logos/ace.png" class="h-100px" />&nbsp;&nbsp;&nbsp;
					<img alt="Logo" src="assets/media/logos/sirim.jpg" class="h-100px" />
				</div>
				<!--end::Logo-->

				<!--begin::Welcome Text-->
				<div class="text-center mb-6">
					<h2 class="fw-bold text-dark mb-2">Welcome to</h2>
					<h2 class="fw-bold text-dark mb-2">SIRIM Ace System</h2>
					<p class="text-muted fs-7">Sign in with your existing account to continue.</p>
				</div>
				<!--end::Welcome Text-->

				<!--begin::Form-->
				<form class="form w-100" novalidate="novalidate" id="loginForm" action="loginCode.php" method="post" name="loginForm" runat="server">
					<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

					<!--begin::Email-->
					<div class="mt-5 mb-4">
						<label class="form-label fw-semibold text-dark">Email</label>
						<input class="form-control form-control-lg" type="email" name="email" id="email" autocomplete="off" />
					</div>
					<!--end::Email-->

					<!--begin::Password-->
					<div class="mb-4">
						<label class="form-label fw-semibold text-dark">Password</label>
						<div class="position-relative" id="show_hide_password">
							<input class="form-control form-control-lg" type="password" name="user_pass" id="user_pass" autocomplete="off" />
							<a class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" type="button">
								<i class="fa fa-eye-slash" aria-hidden="true"></i>
							</a>
						</div>
					</div>
					<!--end::Password-->

					<!--begin::Remember & Forgot-->
					<div class="d-flex justify-content-between align-items-center mb-5">
						<button class="btn btn-login" type="submit" name="login">Login</button>
						<a href="#" class="text-primary fw-semibold fs-7">Forgot your password?</a>
					</div>
				</form>
				<!--end::Form-->

				<!--begin::Divider-->
				<div class="d-flex align-items-center mb-5">
					<div class="border-bottom flex-grow-1"></div>
					<span class="text-muted px-3 fs-7">or</span>
					<div class="border-bottom flex-grow-1"></div>
				</div>
				<!--end::Divider-->

				<!--begin::SSO & Google-->
				<div class="mb-3">
					<button class="btn btn-light w-100 border py-2" type="button">
						<img src="assets/media/logos/sirim2.png" alt="SIRIM" width="22" height="26">
						&nbsp;SIRIM Single Sign On
					</button>
				</div>

				<div class="mb-5">
					<a href="google-auth.php" class="btn btn-light w-100 border py-2" id="googleSignInBtn">
						<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 48 48">
							<path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z" />
							<path fill="#FF3D00" d="m6.306 14.691 6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z" />
							<path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z" />
							<path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z" />
						</svg>
						&nbsp;Sign in with Google
					</a>
				</div>
				<!--end::SSO & Google-->

				<!--begin::Register-->
				<div>
					<span class="text-muted">Don't have an account?</span>
					<a href="register.php" class="text-primary fw-semibold ms-1">Register now</a>
				</div>
				<!--end::Register-->
			</div>
		</div>
		<!--end::Right Form-->
	</div>
	<!--end::Split Layout-->

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
			document.addEventListener('DOMContentLoaded', function() {
				// Login form loading
				document.getElementById('loginForm').addEventListener('submit', function() {
					document.getElementById('loadingText').textContent = 'Logging in...';
					document.getElementById('loadingOverlay').classList.add('active');
				});

				// Google sign-in loading
				document.getElementById('googleSignInBtn').addEventListener('click', function() {
					document.getElementById('loadingText').textContent = 'Signing in with Google...';
					document.getElementById('loadingOverlay').classList.add('active');
				});

				const toggle = document.querySelector("#show_hide_password a");
				const input = document.querySelector("#show_hide_password input");
				const icon = document.querySelector("#show_hide_password i");

				toggle.addEventListener('click', function(e) {
					e.preventDefault();
					if (input.type === "password") {
						input.type = "text";
						icon.classList.remove("fa-eye-slash");
						icon.classList.add("fa-eye");
					} else {
						input.type = "password";
						icon.classList.add("fa-eye-slash");
						icon.classList.remove("fa-eye");
					}
				});
			});
		</script>

		<?php if (isset($_GET['register']) && $_GET['register'] == 'success'): ?>
			<script>
				alert("Registration successful! Please sign in.");
			</script>
		<?php endif; ?>
	</div>
	<!--end::Javascript-->
</body>
<!--end::Body-->

</html>