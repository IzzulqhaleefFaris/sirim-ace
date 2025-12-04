<!-- ADDED -->

<!DOCTYPE html>
<html lang="en">

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

<body>

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

                        <!--begin:Content -->
                        <div class="container px-4 text-center overflow: auto">
                            <div class="row gx-5">
                                <div class="col">
                                    <div class="p-3">
                                        <div class="card shadow-lg border-0 my-rounded p-5 text-center bg-white">
                                            <div class="card-body" style="height: 440px; overflow: auto;">
                                                <h1 class="card-title display-5" style="font-size: 2.5rem;">Manage Your SIRIM Events Effortlessly</h1>&nbsp;
                                                <h6 class="card-subtitle mb-4 text-muted fs-3">Seamless Event Registration & Tracking for SIRIM Programs</h6>
                                                <br>
                                                <p class="card-text text-start fs-3">Register, monitor, and manage events in one secure platform.</p>
                                                <ul class="text-start fs-3">
                                                    <li>Simple event registration</li>
                                                    <li>Real-time participant tracking</li>
                                                    <li>Secure and reliable platform</li>
                                                </ul><br>

                                                <a href="#" class="btn btn-primary btn-lg mt-3 rounded-pill shadow">Get Started</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3">
                                        <div class="card shadow-lg border-0 my-rounded p-5 text-center bg-white">
                                            <div class="card-body" style="height: 440px;">
                                                <div id="carouselExampleCaptions" class="carousel carousel-dark slide">
                                                    <div class="carousel-inner">
                                                        <!-- Slide 1 -->
                                                        <div class="carousel-item active text-center">
                                                            <img src="https://upload.wikimedia.org/wikipedia/commons/c/c2/EWM_shop_2007.jpg" class="d-block w-100 carousel-img" alt="..." style="height:300px; object-fit:cover;">
                                                            <div class="mt-3">
                                                                <h5>Workshop</h5>
                                                                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s</p>
                                                            </div>
                                                        </div>

                                                        <!-- Slide 2 -->
                                                        <div class="carousel-item text-center">
                                                            <img src="https://cdn.prod.website-files.com/634681057b887c6f4830fae2/67dd4edf967ba7ce775bc488_8%20Effective%20Ways%20to%20Run%20a%20Successful%20Business%20Meeting.png" class="d-block w-100 carousel-img" alt="..." style="height:300px; object-fit:cover;">
                                                            <div class="mt-3">
                                                                <h5>Meeting</h5>
                                                                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s</p>
                                                            </div>
                                                        </div>

                                                        <!-- Slide 3 -->
                                                        <div class="carousel-item text-center">
                                                            <img src="https://media.istockphoto.com/id/182138517/photo/project-manager-visiting-construction-site.jpg?s=612x612&w=0&k=20&c=hsXn1moWTuCMZceQexnVlIv_Wtvr49umCQ224UCNuLI=" class="d-block w-100 carousel-img" alt="..." style="height:300px; object-fit:cover;">
                                                            <div class="mt-3">
                                                                <h5>Demonstration</h5>
                                                                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s</p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                        <span class="visually-hidden">Previous</span>
                                                    </button>
                                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
                                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                        <span class="visually-hidden">Next</span>
                                                    </button>

                                                    <div class="carousel-indicators position-static text-center">
                                                        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                                        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1" aria-label="Slide 2"></button>
                                                        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2" aria-label="Slide 3"></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><br>

                            <div class="container text-center">
                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <div class="card shadow-sm border-0 rounded-3 h-100">
                                            <div class="card-body">
                                                <h5 class="card-title">Total Events</h5>
                                                <h2 class="display-6 fw-bold text-primary">25</h2>
                                                <p class="card-text text-muted">Events created this month</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card shadow-sm border-0 rounded-3 h-100">
                                            <div class="card-body">
                                                <h5 class="card-title">Participants</h5>
                                                <h2 class="display-6 fw-bold text-success">180</h2>
                                                <p class="card-text text-muted">Active participants registered</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card shadow-sm border-0 rounded-3 h-100">
                                            <div class="card-body">
                                                <h5 class="card-title">Upcoming Events</h5>
                                                <h2 class="display-6 fw-bold text-warning">6</h2>
                                                <p class="card-text text-muted">Scheduled for next week</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><br>

                            <div class="container-fluid" style="background-color:#ffcc00;">
                                
                            </div>
                        </div>
                    </div>
                </div>
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

</html>
<!-- END ADDED -->