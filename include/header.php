<?php
//include("auth.php"); //include auth.php file on all secure pages
include "include/config.php";
ini_set('max_execution_time', 300);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user = $_SESSION['userId'] ?? null;
$role = $_SESSION['roleId'] ?? null;
$nama = $_SESSION['nama'] ?? null;
?>

<?php
include 'include/config.php';
$userId = $_SESSION["userId"];
$sql = "SELECT * FROM user WHERE userId = '$userId'";
$query = mysqli_query($conn, $sql);
$rows = mysqli_fetch_array($query, MYSQLI_ASSOC);

// get userId from session
$userId = $_SESSION['userId'] ?? null;
$userName = $_SESSION['nama'] ?? null;
$roleId = $_SESSION['roleId'] ?? null;

// fetch name + role if not stored in session
if ($userId && empty($userName)) {
    $stmt = $conn->prepare("SELECT nama, roleId FROM user WHERE userId = ?");
    if ($stmt) {
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && ($row = $res->fetch_assoc())) {
            $userName = $row['nama'];
            $roleId = $row['roleId'];
            // cache into session to avoid repeated queries
            $_SESSION['nama'] = $userName;
            $_SESSION['roleId'] = $roleId;
        }
        $stmt->close();
    } else {
        error_log("Prepare failed: " . $conn->error);
    }
}
?>

<!--begin::Header-->
<div id="kt_header" class="header align-items-stretch">
    <!--begin::Container-->
    <div class="container-fluid d-flex align-items-stretch justify-content-between">

        <!--begin::Logo-->
        <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0 me-lg-10">
            <a><img alt="Logo" src="assets/media/logos/atendance.png" class="h-55px" /></a>
        </div>
        <!--end::Logo-->

        <!--begin::Wrapper-->
        <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1">
            <!--begin::Navbar-->
            <div class="d-flex align-items-stretch" id="kt_header_nav">
                <!--begin::Menu wrapper-->
                <div class="header-menu align-items-stretch" data-kt-drawer="true" data-kt-drawer-name="header-menu" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_header_menu_mobile_toggle" data-kt-place="true" data-kt-place-mode="prepend" data-kt-place-parent="{default: '#kt_body', lg: '#kt_header_nav'}">
                    <!--<div class="fw-bolder text-light d-flex align-items-center fs-2">SOLJAR | Security Operations Logistical Journey and Reporting</div>-->
                    <?php include 'include/navbar.php'; ?> <!--Tukar Navbar-->
                </div>
                <!--end::Menu wrapper-->
            </div>
            <!--end::Navbar-->

            <!--begin::Topbar-->
            <div class="d-flex align-items-stretch flex-shrink-0">
                <!--begin::Toolbar wrapper-->
                <div class="topbar d-flex align-items-stretch flex-shrink-0">
                    <!--begin::User-->
                    <div class="d-flex align-items-stretch" id="kt_header_user_menu_toggle">
                        <!--begin::Menu wrapper-->
                        <div class="topbar-item cursor-pointer symbol px-3 px-lg-5 me-n3 me-lg-n5 symbol-30px symbol-md-35px" data-kt-menu-trigger="click" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end" data-kt-menu-flip="bottom">
                            <!-- <img src="assets/media/avatars/blank.png" alt="metronic" /> -->
                            <i class="bi bi-person fs-4x"></i>
                            &nbsp;&nbsp;
                            <div class="d-flex flex-column">
                                <div class="fw-bolder text-light d-flex align-items-center fs-5"><?php echo $user; ?></div>
                                <?php if ($_SESSION["roleId"] == '1') { ?>
                                    <a href="#" class="fw-bold text-muted fs-7">
                                        <?php echo htmlspecialchars($userName); ?>
                                    </a>
                                <?php } else if ($_SESSION["roleId"] == '2') { ?>
                                    <a href="#" class="fw-bold text-muted fs-7">
                                        <?php echo htmlspecialchars($userName); ?>
                                    </a>
                                <?php } else { ?>
                                    <a href="#" class="fw-bold text-muted fs-7">Operator</a>
                                <?php } ?>
                            </div>
                        </div>
                        <!--end::Menu wrapper-->

                        <!--begin::Menu-->
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold py-4 fs-6 w-225px" data-kt-menu="true">

                            <!--begin::Menu item-->
                            <div class="menu-item px-5">
                                <a type="button" class="menu-link px-5" data-bs-toggle="modal" data-bs-target="#modalProfile">
                                    <span class="menu-icon"><i class="bi bi-person-fill fs-3"></i></span>
                                    <span class="menu-title">Profil Saya</span>
                                </a>
                            </div>

                            <!--end::Menu item-->
                            <!--begin::Menu item-->
                            <div class="menu-item px-5">
                                <a type="button" class="menu-link px-5" data-bs-toggle="modal" data-bs-target="#modalCp">
                                    <span class="menu-icon"><i class="bi bi-shield-lock-fill fs-3"></i></span>
                                    <span class="menu-title">Tukar Kata Laluan</span>
                                </a>
                            </div>
                            <!--end::Menu item-->

                            <!--start::Menu separator-->
                            <div class="separator my-2"></div>
                            <!--end::Menu separator-->


                            <!--begin::Menu item-->
                            <div class="menu-item px-5 text-center">
                                <?php
                                include 'include/config.php';
                                $sql = "SELECT * FROM useraccess WHERE userId = '$user' AND tarikhKeluar IS NULL ORDER BY tarikhMasuk DESC LIMIT 1;";
                                $query = mysqli_query($conn, $sql);
                                $rows = mysqli_fetch_array($query, MYSQLI_ASSOC);
                                ?>
                                <div>
                                    <input id="tarikhMasuk" name="tarikhMasuk" value="<?php echo $rows['tarikhMasuk']; ?>" hidden readonly />
                                    <input id="tarikhKeluar" name="tarikhKeluar" value="<?php echo $rows['tarikhKeluar']; ?>" hidden readonly />
                                </div>
                                <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
                                <form method="POST" action="/attendance/include/logout.php" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-light-dark px-10"><i class="bi bi-box-arrow-right fs-1"></i>&nbsp;Log Keluar</button>
                                </form>
                            </div>
                            <!--end::Menu item-->
                        </div>
                        <!--end::Menu-->

                    </div>
                    <!--end::User -->
                    <!--begin::Heaeder menu toggle-->
                    <div class="d-flex align-items-stretch d-lg-none px-3 me-n3" title="Show header menu">
                        <div class="topbar-item" id="kt_header_menu_mobile_toggle">
                            <i class="bi bi-text-left fs-1"></i>
                        </div>
                    </div>
                    <!--end::Heaeder menu toggle-->
                </div>
                <!--end::Toolbar wrapper-->
            </div>
            <!--end::Topbar-->
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Container-->
</div>
<!--end::Header-->

<!--begin::Modal-->
<div>
    <?php
    include 'include/config.php';
    $userId = $_SESSION["userId"];
    $sqlp = "SELECT u.userId, u.password, u.stafId, u.nama, u.roleId, u.email, u.status, r.roleName FROM user AS u, role AS r WHERE u.userId = '$userId' AND u.roleId = r.roleId";
    $queryp = mysqli_query($conn, $sqlp);
    $rowsp = mysqli_fetch_array($queryp, MYSQLI_ASSOC);
    ?>
    <!--begin::My Profile-->
    <form id="profileForm" class="form" method="post" name="profileForm" action="profileCode.php?userId=<?php echo $rowsp['userId']; ?>">
        <div class="modal fade" tabindex="-1" id="modalProfile" role="dialog" aria-labelledby="modalProfileLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fs-2x" id="modalProfileLabel"><i class="bi bi-person-fill fs-3x text-dark"></i>&nbsp;Profil Saya</h5>

                        <!--begin::Close-->
                        <div class="btn btn-icon btn-sm btn-active-light-danger ms-2" data-bs-dismiss="modal" aria-label="Close">
                            <span class="svg-icon svg-icon-2x"><i class="bi bi-x fs-2"></i></span>
                        </div>
                        <!--end::Close-->
                    </div>

                    <div class="modal-body">
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">ID Pengguna </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm form-control-solid" id="userId" name="userId" value="<?php echo $rowsp['userId']; ?>" readonly />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">Kata Laluan </label>
                            <div class="col-sm-8">
                                <input type="password" class="form-control form-control-sm form-control-solid" id="password" name="password" value="<?php echo $rowsp['password']; ?>" readonly />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">Name Penuh </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="nama" name="nama" value="<?php echo $rowsp['nama']; ?>" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">No. Pekerja</label>
                            <div class="col-sm-8">
                                <input
                                    type="text"
                                    class="form-control form-control-sm"
                                    id="stafId"
                                    name="stafId"
                                    value="<?php echo $rowsp['stafId']; ?>" />
                                <div class="form-text text-muted">
                                    (For SIRIM staffs only)
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">E-mel </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="email" name="email" placeholder="eg: contoh@mpsepang.gov.my" value="<?php echo $rowsp['email']; ?>" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">No. Telefon</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="telNo" name="telNo" value="<?php echo $rowsp['email']; ?>" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">E-mel </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" id="email" name="email" placeholder="eg: contoh@mpsepang.gov.my" value="<?php echo $rowsp['email']; ?>" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">Peranan </label>
                            <div class="col-sm-8">
                                <?php if ($rowsp['roleId'] == 1) { ?>
                                    <input type="text" class="form-control form-control-sm form-control-solid" id="status" name="status" value="Pentadbir" readonly />
                                <?php } elseif ($rowsp['roleId'] == '2') { ?>
                                    <input type="text" class="form-control form-control-sm form-control-solid" id="status" name="status" value="Penguatkuasa" readonly />
                                <?php } elseif ($rowsp['roleId'] == '3') { ?>
                                    <input type="text" class="form-control form-control-sm form-control-solid" id="status" name="status" value="Operator" readonly />
                                <?php } else { ?>
                                    <input type="roleId" class="form-control form-control-sm form-control-solid" id="status" name="status" value="Rekod Tiada" readonly />
                                <?php } ?>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">Status </label>
                            <div class="col-sm-8">
                                <?php if ($rowsp['status'] == 'A') { ?>
                                    <input type="text" class="form-control form-control-sm form-control-solid" id="status" name="status" value="Aktif" readonly />
                                <?php } else { ?>
                                    <input type="text" class="form-control form-control-sm form-control-solid" id="status" name="status" value="Tidak Aktif" readonly />
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-white btn-active-light-dark" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary">Kemaskini</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!--end::My Profile-->

    <!--begin::Change Password-->
    <form class="form theme-form" method="post" name="cpForm" action="cpCode.php?userId=<?php echo $rowsp['userId']; ?>">
        <div class="modal fade" tabindex="-1" id="modalCp" role="dialog" aria-labelledby="modalCpLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fs-2x" id="modalCpLabel"><i class="bi bi-shield-lock-fill fs-2x text-dark"></i>&nbsp;Tukar Kata Laluan</h5>
                        <!--begin::Close-->
                        <div class="btn btn-icon btn-sm btn-active-light-danger ms-2" data-bs-dismiss="modal" aria-label="Close">
                            <span class="svg-icon svg-icon-2x"><i class="bi bi-x fs-2"></i></span>
                        </div>
                        <!--end::Close-->
                    </div>

                    <div class="modal-body">
                        <div class="mb-3 row">
                            <p>
                                <span class="text-danger">Adakah anda pasti untuk set semula kata laluan anda?</span><br>
                                <span class="text-danger"> Jika Ya, sila masukkan kata laluan baru :</span>
                            </p>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label">Kata Laluan Baru </label>
                            <div class="col-sm-8 position-relative mb-3" id="show_hide_password">
                                <input class="form-control form-control-sm" type="password" name="password" id="password" autocomplete="off" placeholder="password" />
                                <div class="input-group-append">
                                    <span>
                                        <a class="btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n8" type="button">
                                            <i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-white btn-active-light-dark" data-bs-dismiss="modal">Tidak</button>
                        <button type="submit" class="btn btn-sm btn-primary">Ya</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!--end::Change Password-->
</div>
<!--end::Modal-->