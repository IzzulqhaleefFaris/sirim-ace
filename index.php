<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<title>SIRIM Ace - Event Attendance System</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="shortcut icon" href="assets/media/logos/soljar_ico.ico" />
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
	<link rel="stylesheet" href="assets/css/landing.css" />
</head>

<body>

	<!-- ===== NAVBAR ===== -->
	<nav class="navbar-landing">
		<div class="container d-flex justify-content-between align-items-center">
			<a href="#" class="text-decoration-none d-flex align-items-center gap-2">
				<img src="assets/media/logos/sirim.jpg" alt="SIRIM" height="40" />
				<img src="assets/media/logos/ace.png" alt="Logo" height="40" />
				
			</a>

			<div class="d-none d-md-flex align-items-center">
				<a href="#hero" class="nav-link active">Home</a>
				<a href="#about" class="nav-link">About</a>
				<a href="#how-it-works" class="nav-link">How It Works</a>
				<!-- <a href="#events" class="nav-link">Events</a>
				<a href="#venues" class="nav-link">Venues</a> -->
			</div>

			<div class="d-flex gap-2">
				<a href="login.php" class="btn btn-signin">Sign In</a>
				<a href="register.php" class="btn btn-signup">Sign Up</a>
			</div>
		</div>
	</nav>

	<!-- ===== HERO ===== -->
	<section class="hero-section" id="hero">
		<div class="container">
			<h1>Discover &amp; Connect with <span>SIRIM Events,</span><br><span>Attendance</span> &amp; <span>Venues</span></h1>
			<p>Track events, manage attendance with QR codes, connect with venues, and sync your calendar &mdash; everything you need in one place to never miss a beat.</p>
			<div class="d-flex justify-content-center gap-3">
				<a href="login.php" class="btn btn-primary-landing">Get Started</a>
				<!-- <a href="#events" class="btn btn-outline-landing">Explore Events</a> -->
			</div>
		</div>
		<div class="hero-image-wrapper">
			<img src="images/custom/integrated.png" alt="Hero" class="hero-image" />
		</div>
	</section>

	<!-- ===== ABOUT ===== -->
	<section class="section-padding" id="about">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-lg-6 mb-4 mb-lg-0">
					<img src="assets/media/logos/ace-horizontal.png" alt="About" class="about-image" />
				</div>
				<div class="col-lg-6">
					<span class="section-badge">About Us</span>
					<h2 class="section-title">What is SIRIM Ace?</h2>
					<p class="text-muted mb-4" style="font-size:0.95rem;">SIRIM Ace is a comprehensive event attendance management system designed to streamline how organisations create, manage, and track events and participants.</p>

					<div class="about-card">
						<div class="about-icon" style="background:#3699ff;">
							<i class="fas fa-calendar-check"></i>
						</div>
						<div>
							<h5>Event Management</h5>
							<p>Create and organise events effortlessly with full control over details, schedules, and participant limits.</p>
						</div>
					</div>

					<div class="about-card">
						<div class="about-icon" style="background:#f5a623;">
							<i class="fas fa-qrcode"></i>
						</div>
						<div>
							<h5>QR Code Attendance</h5>
							<p>Scan QR codes for instant check-in — fast, accurate, and paperless attendance tracking.</p>
						</div>
					</div>

					<div class="about-card">
						<div class="about-icon" style="background:#50cd89;">
							<i class="fas fa-chart-bar"></i>
						</div>
						<div>
							<h5>Real-Time Analytics</h5>
							<p>Access live dashboards with attendance statistics, reports, and insights at your fingertips.</p>
						</div>
					</div>

					<div class="about-card">
						<div class="about-icon" style="background:#f1416c;">
							<i class="fas fa-users"></i>
						</div>
						<div>
							<h5>Role-Based Access</h5>
							<p>Admins, organisers, and participants each get a tailored experience with role-specific dashboards.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- ===== HOW IT WORKS ===== -->
	<section class="section-padding section-bg" id="how-it-works">
		<div class="container text-center">
			<span class="section-badge">Process</span>
			<h2 class="section-title">How It Works</h2>
			<p class="section-subtitle">Get started in just a few simple steps — from registration to attendance tracking.</p>

			<div class="row">
				<div class="col-md-3 step-connector">
					<div class="step-card">
						<div class="step-number">1</div>
						<h5>Create Account</h5>
						<p>Sign up with your email or Google account to get started in seconds.</p>
					</div>
				</div>
				<div class="col-md-3 step-connector">
					<div class="step-card">
						<div class="step-number">2</div>
						<h5>Browse Events</h5>
						<p>Explore upcoming events, workshops, and conferences available to you.</p>
					</div>
				</div>
				<div class="col-md-3 step-connector">
					<div class="step-card">
						<div class="step-number">3</div>
						<h5>Register &amp; Get QR</h5>
						<p>Register for events and receive a unique QR code for check-in.</p>
					</div>
				</div>
				<div class="col-md-3">
					<div class="step-card">
						<div class="step-number">4</div>
						<h5>Scan &amp; Attend</h5>
						<p>Show your QR code at the venue — attendance recorded instantly.</p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- ===== EVENTS ===== -->
	<!-- <section class="section-padding" id="events">
		<div class="container text-center">
			<span class="section-badge">Events</span>
			<h2 class="section-title">Explore Upcoming Events &amp; Workshops</h2>
			<p class="section-subtitle">Discover organised events across departments — join, register, and track your participation with ease.</p> -->

			<!-- <div class="row g-4"> -->
				<!-- Event Card 1 -->
				<!-- <div class="col-md-4">
					<div class="event-card">
						<img src="images/custom/no_image.jpg" alt="Event" />
						<div class="event-card-body">
							<h5>Technology Workshop</h5>
							<div class="event-meta"><i class="fas fa-map-marker-alt"></i> SIRIM Auditorium, Shah Alam</div>
							<div class="event-dates">
								<div class="date-item"><i class="fas fa-calendar"></i> Start: 15 Apr 2026</div>
								<div class="date-item"><i class="fas fa-calendar"></i> End: 15 Apr 2026</div>
							</div>
							<div class="event-attendees">
								<div class="avatar" style="background:#3699ff;"></div>
								<div class="avatar" style="background:#f5a623;"></div>
								<div class="avatar" style="background:#50cd89;"></div>
								<div class="avatar" style="background:#f1416c;"></div>
								<span class="ms-2 text-muted" style="font-size:0.8rem;">+20 attending</span>
							</div>
							<button class="btn-event-details">Event Details</button>
							<button class="btn-event-register">Register Now</button>
						</div>
					</div>
				</div> -->

				<!-- Event Card 2 -->
				<!-- <div class="col-md-4">
					<div class="event-card">
						<img src="images/custom/no_image.jpg" alt="Event" />
						<div class="event-card-body">
							<h5>Quality Assurance Seminar</h5>
							<div class="event-meta"><i class="fas fa-map-marker-alt"></i> SIRIM Conference Hall, KL</div>
							<div class="event-dates">
								<div class="date-item"><i class="fas fa-calendar"></i> Start: 22 Apr 2026</div>
								<div class="date-item"><i class="fas fa-calendar"></i> End: 22 Apr 2026</div>
							</div>
							<div class="event-attendees">
								<div class="avatar" style="background:#3699ff;"></div>
								<div class="avatar" style="background:#f5a623;"></div>
								<div class="avatar" style="background:#50cd89;"></div>
								<div class="avatar" style="background:#f1416c;"></div>
								<span class="ms-2 text-muted" style="font-size:0.8rem;">+15 attending</span>
							</div>
							<button class="btn-event-details">Event Details</button>
							<button class="btn-event-register">Register Now</button>
						</div>
					</div>
				</div> -->

				<!-- Event Card 3 -->
				<!-- <div class="col-md-4">
					<div class="event-card">
						<img src="images/custom/no_image.jpg" alt="Event" />
						<div class="event-card-body">
							<h5>Innovation Summit 2026</h5>
							<div class="event-meta"><i class="fas fa-map-marker-alt"></i> SIRIM Main Hall, Shah Alam</div>
							<div class="event-dates">
								<div class="date-item"><i class="fas fa-calendar"></i> Start: 5 May 2026</div>
								<div class="date-item"><i class="fas fa-calendar"></i> End: 6 May 2026</div>
							</div>
							<div class="event-attendees">
								<div class="avatar" style="background:#3699ff;"></div>
								<div class="avatar" style="background:#f5a623;"></div>
								<div class="avatar" style="background:#50cd89;"></div>
								<div class="avatar" style="background:#f1416c;"></div>
								<span class="ms-2 text-muted" style="font-size:0.8rem;">+32 attending</span>
							</div>
							<button class="btn-event-details">Event Details</button>
							<button class="btn-event-register">Register Now</button>
						</div>
					</div>
				</div>
			</div> -->

			<!-- <div class="mt-5">
				<a href="#" class="btn btn-outline-landing">Explore More Events</a>
			</div>
		</div>
	</section> -->

	<!-- ===== VENUES =====
	<section class="section-padding section-bg" id="venues">
		<div class="container text-center">
			<span class="section-badge">Venues</span>
			<h2 class="section-title">Popular Venues in Your Area</h2>
			<p class="section-subtitle">Explore top-rated venues where events and workshops are held — find the perfect space for your next gathering.</p>

			<div class="row g-4">
				<div class="col-md-4">
					<div class="venue-card">
						<img src="images/custom/no_image.jpg" alt="Venue" />
						<div class="venue-card-body">
							<h6>SIRIM Auditorium</h6>
							<div class="venue-rating"><i class="fas fa-star"></i> 4.8</div>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="venue-card">
						<img src="images/custom/no_image.jpg" alt="Venue" />
						<div class="venue-card-body">
							<h6>Conference Centre KL</h6>
							<div class="venue-rating"><i class="fas fa-star"></i> 4.6</div>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="venue-card">
						<img src="images/custom/no_image.jpg" alt="Venue" />
						<div class="venue-card-body">
							<h6>Innovation Hub</h6>
							<div class="venue-rating"><i class="fas fa-star"></i> 4.9</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section> -->

	<!-- ===== FOOTER ===== -->
	<footer class="footer-section">
		<div class="container">
			<div class="row">
				<div class="col-md-4 mb-4">
					<div class="d-flex align-items-center gap-2 mb-3">
						<img src="assets/media/logos/sirim.jpg" alt="SIRIM" height="36" />
						<img src="assets/media/logos/ace.png" alt="Logo" height="36" />
					</div>
					<p style="font-size:0.9rem;">SIRIM Ace is a comprehensive event attendance management platform designed for seamless event tracking and participation.</p>
				</div>
				<div class="col-md-2 mb-4">
					<h5>Quick Links</h5>
					<ul class="footer-links">
						<li><a href="#hero">Home</a></li>
						<li><a href="#about">About</a></li>
						<li><a href="#how-it-works">How It Works</a></li>
						<li><a href="#events">Events</a></li>
					</ul>
				</div>
				<div class="col-md-3 mb-4">
					<h5>Resources</h5>
					<ul class="footer-links">
						<li><a href="#">Help Centre</a></li>
						<li><a href="#">Privacy Policy</a></li>
						<li><a href="#">Terms of Service</a></li>
						<li><a href="#">Contact Us</a></li>
					</ul>
				</div>
				<div class="col-md-3 mb-4">
					<h5>Contact</h5>
					<ul class="footer-links">
						<li><i class="fas fa-envelope me-2" style="color:#3699ff;"></i> sirimace@sirim.my</li>
						<li><i class="fas fa-phone me-2" style="color:#3699ff;"></i> +603-5544 6000</li>
						<li><i class="fas fa-map-marker-alt me-2" style="color:#3699ff;"></i> Shah Alam, Selangor</li>
					</ul>
				</div>
			</div>
			<div class="footer-bottom">
				&copy; 2026 SIRIM Ace. All rights reserved.
			</div>
		</div>
	</footer>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>
