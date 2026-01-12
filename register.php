<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_SESSION['userId'])) {
    header('Location: home.php');
    exit;
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <!--end::Global Stylesheets Bundle-->
    <style>
        .toggle-password-btn {
            background: transparent;
            /* Remove background */
            border: none;
            /* Remove border */
            padding: 0;
            /* Remove extra padding */
        }

        .toggle-password-btn:hover,
        .toggle-password-btn:focus {
            background: transparent;
            /* Keep transparent on hover/focus */
            box-shadow: none;
            /* Remove outline shadow */
        }
    </style>
</head>
<!--end::Head-->

<!--begin::Body-->

<body class="d-flex flex-column min-vh-100 bg-white py-2">
    <!--begin::Main content-->
    <main class="flex-grow-1 d-flex flex-column justify-content-center align-items-center p-10">
        <!--begin::Logo-->
        <section class="p-3 p-md-4 p-xl-5">
            <div class="container">
                <div class="card border-light-subtle shadow-sm">
                    <div class="row g-0 py-2">
                        <div class="col-12 col-md-6 text-bg">
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <div class="col-10 col-xl-8 py-3">
                                    <div class="text-center">
                                        <a href="index.php" class="d-inline-flex align-items-center justify-content-center">
                                            <img alt="Logo Attendance"
                                                src="assets/media/logos/atendance.png"
                                                class="h-150px me-4" />

                                            <img alt="Logo SIRIM"
                                                src="assets/media/logos/sirim.png"
                                                class="h-150px" />
                                        </a>
                                    </div>
                                    <hr class="border-primary-subtle mb-4">
                                    <h2 class="text-dark mb-5 text-center">
                                        ATTENDANCE | Advanced Event and Attendance Coordination Engine
                                    </h2><br>
                                    <!-- <p class="lead m-0">We write words, take photos, make videos, and interact with artificial intelligence.</p> -->
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card-body p-3 p-md-4 p-xl-5">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-5">
                                            <h2 class="h3">Pendaftaran Akaun</h2>
                                        </div>
                                    </div>
                                </div>
                                <form action="registerCode.php" method="POST">
                                    <div class="row gy-3 gy-md-4 overflow-hidden">
                                        <div class="col-12">
                                            <label for="name" class="form-label">Nama Penuh <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="name" id="name" placeholder="Isikan Nama Penuh Anda" required>
                                        </div>
                                        <div class="col-12">
                                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="email" id="email" placeholder="Isikan Email Anda" required>
                                        </div>
                                        <div class="col-12 ">
                                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="password" id="password" placeholder="Masukkan Password Anda" value="" required>
                                            <button type="button" class="btn btn-sm toggle-password-btn position-absolute top-50 end-0 translate-middle-y me-2" onclick="toggleRegisterPassword()">
                                                <i class="fa fa-eye-slash" id="togglePasswordIcon"></i>
                                            </button>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Jawatan<span class="text-danger">*</span></label><br>
                                            <select name="roleId" required>
                                                <option value="1">Pentadbir (Organiser) -- will update</option>
                                                <option value="2">Penguatkuasa (Participant) -- will update</option>
                                            </select><br><br>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" name="iAgree" id="iAgree" required>
                                                <label class="form-check-label text-dark" for="iAgree">
                                                    Saya bersetuju dengan <a href="#!" class="link-primary text-decoration-none">Terma dan Syarat</a>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-grid">
                                                <button class="btn bsb-btn-xl btn-primary" type="submit">Daftar</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="row">
                                    <div class="col-12">
                                        <hr class="mt-5 mb-4 border-secondary-subtle">
                                        <p class="m-0 text-dark text-center">Sudah mempunyai akaun? <a href="/attendance/" class="link-primary text-decoration-none">Log Masuk</a></p>
                                    </div>
                                </div>
                                <!-- <div class="row">
                                    <div class="col-12">
                                        <p class="mt-5 mb-4">Log Masuk Melalui:</p>
                                        <div class="d-flex gap-3 flex-column flex-xl-row">
                                            <a href="#!" class="btn bsb-btn-xl btn-outline-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-google" viewBox="0 0 16 16">
                                                    <path d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352 2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z" />
                                                </svg>
                                                <span class="ms-2 fs-6">Google</span>
                                            </a>
                                            <a href="#!" class="btn bsb-btn-xl btn-outline-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16">
                                                    <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z" />
                                                </svg>
                                                <span class="ms-2 fs-6">Facebook</span>
                                            </a>
                                            <a href="#!" class="btn bsb-btn-xl btn-outline-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-twitter" viewBox="0 0 16 16">
                                                    <path d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334 0-.14 0-.282-.006-.422A6.685 6.685 0 0 0 16 3.542a6.658 6.658 0 0 1-1.889.518 3.301 3.301 0 0 0 1.447-1.817 6.533 6.533 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.325 9.325 0 0 1-6.767-3.429 3.289 3.289 0 0 0 1.018 4.382A3.323 3.323 0 0 1 .64 6.575v.045a3.288 3.288 0 0 0 2.632 3.218 3.203 3.203 0 0 1-.865.115 3.23 3.23 0 0 1-.614-.057 3.283 3.283 0 0 0 3.067 2.277A6.588 6.588 0 0 1 .78 13.58a6.32 6.32 0 0 1-.78-.045A9.344 9.344 0 0 0 5.026 15z" />
                                                </svg>
                                                <span class="ms-2 fs-6">Twitter</span>
                                            </a>
                                        </div>
                                    </div>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>
    <!--begin::Footer-->
    <footer class="d-flex flex-center mt-auto p-4">
        <!--begin::Links-->
        <div class="d-flex flex-center flex-column-auto p-10 mt-auto">
            <!--begin::Links-->
            <div class="d-flex align-items-center fw-bold fs-6">
                <a class="text-muted text-hover-primary px-2">&copy;2024 - <?php echo date("Y"); ?>, ATTENDANCE. Hakcipta Terpelihara. Dibangun dan diselenggarakan oleh SIRIM Berhad. | Version 1.0</a>
            </div>
            <!--end::Links-->
        </div>
        <!--end::Links-->
    </footer>
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
            function toggleRegisterPassword() {
                const password = document.getElementById("password");
                const icon = document.getElementById("togglePasswordIcon");

                if (password.type === "password") {
                    password.type = "text";
                    icon.classList.remove("fa-eye-slash");
                    icon.classList.add("fa-eye");
                } else {
                    password.type = "password";
                    icon.classList.remove("fa-eye");
                    icon.classList.add("fa-eye-slash");
                }
            }
        </script>
    </div>
    <!--end::Javascript-->
</body>
<!--end::Body-->

</html>