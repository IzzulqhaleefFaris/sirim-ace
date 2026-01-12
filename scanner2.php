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
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
	<!--end::Global Stylesheets Bundle-->
	<!--begin::Scanner(used by this page)-->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<!--<script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>-->
	<script src="instascan/instascan.js"></script>
	<script src="instascan/instascan.min.js"></script>
	<!--end::Scanner -->

	<!--begin::Scanner2(used by this page)-->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({
			google_ad_client: "ca-pub-6724419004010752",
			enable_page_level_ads: true
		});
	</script>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-131906273-1"></script>
	<script>
		window.dataLayer = window.dataLayer || [];

		function gtag() {
			dataLayer.push(arguments);
		}
		gtag('js', new Date());
		gtag('config', 'UA-131906273-1');
	</script>
	<!--end::Scanner2 -->
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
					<div class="toolbar" id="kt_toolbar">
						<?php include "include/toolbar.php"; ?>
						<!--begin::Container-->
						<div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
							<!--begin::Menu-->
							<h3>
								<div class="fw-bolder text-dark d-flex align-items-center fs-2"></div>
							</h3>
							<!--end::Menu-->
						</div>
						<!--end::Container-->
					</div>
					<!--end::Toolbar-->
					<!--begin::Post-->
					<div class="post d-flex flex-column-fluid" id="kt_post">
						<!--begin::Container-->
						<div id="kt_content_container" class="container">
							<!--begin::Section-->
							<div class="py-0">
								<div class="card shadow-sm">
									<div class="card-header">
										<h3 class="card-title">Imbas <?php echo $r = $_GET["r"]; ?><?php echo $rondaid = $_GET["rondaid"]; ?><input type="text" class="form-control form-control-sm" id="lokasi" value="" name="lokasi" readonly /></h3>
									</div>

									<div class="card">
										<div class="card-body">
											<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
											<div class="container-fluid">
												<div class="row">


													<div class="col">
														<script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
														<div class="col-sm-12">
															<video id="preview" class="p-1 border" style="width:100%;"></video>
														</div>
														<script type="text/javascript">
															// Function to get and display GPS location
															function displayLocation() {
																// Check if geolocation is supported
																if (navigator.geolocation) {
																	navigator.geolocation.getCurrentPosition(function(position) {
																		// Get coordinates
																		var latitude = position.coords.latitude;
																		var longitude = position.coords.longitude;

																		// Populate the var with coordinates
																		//var locationRonda = latitude + "," + longitude;
																		document.getElementById("lokasi").value = latitude + "," + longitude;
																	}, function(error) {
																		// Handle errors
																		switch (error.code) {
																			case error.PERMISSION_DENIED:
																				alert("User denied the request for Geolocation.");
																				break;
																			case error.POSITION_UNAVAILABLE:
																				alert("Location information is unavailable.");
																				break;
																			case error.TIMEOUT:
																				alert("The request to get user location timed out.");
																				break;
																			case error.UNKNOWN_ERROR:
																				alert("An unknown error occurred.");
																				break;
																		}
																	});
																} else {
																	alert("Geolocation is not supported by this browser.");
																}
															}

															// Call the function when the page loads
															window.onload = function() {
																displayLocation();
															};

															var scanner = new Instascan.Scanner({
																video: document.getElementById('preview'),
																scanPeriod: 5,
																mirror: false
															});
															scanner.addListener('scan', function(content) {
																//check valid URL
																var re = '<?php echo $r ?>';
																//if(content.substring(0, 38)=='https://soljar.sirimsense.com/i.php?b='){
																//alert(content.substring(0, 38));
																//alert(content.substring(content.indexOf('=') + 1));
																//check MULA

																if (re == 'MULA') {
																	if (content.substring(0, 5) == 'STUES') {
																		//alert(content.substring(content.indexOf('-') + 1) + ' Hadir.');
																		//window.location.href="rondaMulaCode.php?b="+content.substring(content.indexOf('=') + 1)+"&lokasi="+document.getElementById("lokasi").value;
																		window.location.href = "scannerCode.php?content=" + content;
																	} else {
																		alert('Ini bukan QRCode Attendance!!!');
																		window.location.href = "home.php?pg=OFCR";
																	}
																} else {
																	if (content.substring(content.indexOf('=') + 1) == 'hLI6Pp3CMiGfw/mlsUjd3Q==') {
																		alert('Tamat Rondaan.' + document.getElementById("lokasi").value);
																		window.location.href = "rondaMulaCode.php?b=" + content.substring(content.indexOf('=') + 1) + "&rondaid=" + '<?php echo $rondaid; ?>' + "&lokasi=" + document.getElementById("lokasi").value;
																	} else {
																		alert('Ini bukan QRCode TAMAT!!!');
																		history.back();
																	}
																}
																//alert(content);
																//window.location.href="rondaRemarks.php?b="+content.substring(content.indexOf('=') + 1);
																//}else{
																//	alert('Invalid URL!!!'+content);
																//    window.location.href="home.php?pg=OFCR";
																//}

															});
															Instascan.Camera.getCameras().then(function(cameras) {
																if (cameras.length > 0) {
																	scanner.start(cameras[0]);
																	$('[name="options"]').on('change', function() {
																		if ($(this).val() == 1) {
																			if (cameras[0] != "") {
																				scanner.start(cameras[0]);
																			} else {
																				alert('No Front camera found!');
																			}
																		} else if ($(this).val() == 2) {
																			if (cameras[1] != "") {
																				scanner.start(cameras[1]);
																			} else {
																				alert('No Back camera found!');
																			}
																		}
																	});
																} else {
																	console.error('No cameras found.');
																	alert('No cameras found.');
																}
															}).catch(function(e) {
																console.error(e);
																alert(e);
															});
														</script>
														<div class="btn-group btn-group-toggle mb-5" data-toggle="buttons">
															<label class="btn btn-primary active">
																<input type="radio" name="options" value="1" autocomplete="off" checked> Front Camera
															</label>
															<label class="btn btn-secondary">
																<input type="radio" name="options" value="2" autocomplete="off"> Back Camera
															</label>
														</div>
													</div>


													<div class="col-sm-3">
														<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
														<!-- demo left sidebar -->
														<ins class="adsbygoogle"
															style="display:block"
															data-ad-client="ca-pub-6724419004010752"
															data-ad-slot="7706376079"
															data-ad-format="auto"
															data-full-width-responsive="true"></ins>
														<script>
															(adsbygoogle = window.adsbygoogle || []).push({});
														</script>
													</div>

													<div class="col-sm-3">
														<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
														<!-- demo left sidebar -->
														<ins class="adsbygoogle"
															style="display:block"
															data-ad-client="ca-pub-6724419004010752"
															data-ad-slot="7706376079"
															data-ad-format="auto"
															data-full-width-responsive="true"></ins>
														<script>
															(adsbygoogle = window.adsbygoogle || []).push({});
														</script>
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
		<script src="assets/js/custom/apps/chat/chat.js"></script>
		<script src="instascan/instascan.js"></script>
		<script src="instascan/instascan.min.js"></script>
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