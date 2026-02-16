<?php
//include("auth.php"); //include auth.php file on all secure pages
include __DIR__ . "/config.php";
ini_set('max_execution_time', 300);
error_reporting(0);
if (!isset($_SESSION)) {
    session_start();
    $user = $_SESSION["userId"];
    $role = $_SESSION["roleId"];
}
?>

<?php $role = $_SESSION["roleId"];

if ($_SESSION["roleId"] == '2') { ?>
    <!-- ======== Participant ======== -->
    <div class="menu menu-lg-rounded menu-column menu-lg-row menu-state-bg menu-title-gray-700 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-400 fw-bold my-5 my-lg-0 align-items-stretch" id="" data-kt-menu="true">
        <!--begin::Home-->
        <div class="menu-item me-lg-1">
            <a class="menu-link py-3" href="/attendance/main/role/participant/home.php" style="text-decoration: none;">
                <span class="menu-icon"><i class="bi bi-house fs-3"></i></span>
                <span class="menu-title">Utama</span>
            </a>
        </div>
        <!--end::Home-->

        <!--begin::Senarai Event-->
        <div class="menu-item me-lg-1">
            <a class="menu-link py-3" href="/attendance/main/role/participant/event-list.php" style="text-decoration: none;">
                <span class="menu-icon"><i class="bi bi-list-task fs-3"></i></span>
                <span class="menu-title">Senarai Event</span>
            </a>
        </div>
        <!--end::Senarai Event-->

        <!--begin::My Events & QR-->
        <div class="menu-item me-lg-1">
            <a class="menu-link py-3" href="/attendance/main/role/participant/my-events.php" style="text-decoration: none;">
                <span class="menu-icon"><i class="bi bi-calendar-check fs-3"></i></span>
                <span class="menu-title">My Events & QR</span>
            </a>
        </div>
        <!--end::My Events & QR-->
    </div>
<?php } elseif ($_SESSION["roleId"] == '1') { ?>
    <!-- ======== Organiser ======== -->
    <div class="menu menu-lg-rounded menu-column menu-lg-row menu-state-bg menu-title-gray-700 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-400 fw-bold my-5 my-lg-0 align-items-stretch" id="" data-kt-menu="true">
        <!--begin::Home-->
        <div class="menu-item me-lg-1">
            <a class="menu-link py-3" href="/attendance/main/role/organiser/home.php">
                <span class="menu-icon"><i class="bi bi-calendar-event fs-3"></i></span>
                <span class="menu-title">Utama</span>
            </a>
        </div>
        <!--end::Home-->

        <!--begin::Senarai Event (Participant)-->
        <div class="menu-item me-lg-1">
            <a class="menu-link py-3" href="/attendance/main/role/participant/event-list.php" style="text-decoration: none;">
                <span class="menu-icon"><i class="bi bi-list-task fs-3"></i></span>
                <span class="menu-title">Senarai Event</span>
            </a>
        </div>
        <!--end::Senarai Event (Participant)-->

        <!--begin::My Events & QR (Participant)-->
        <div class="menu-item me-lg-1">
            <a class="menu-link py-3" href="/attendance/main/role/participant/my-events.php" style="text-decoration: none;">
                <span class="menu-icon"><i class="bi bi-calendar-check fs-3"></i></span>
                <span class="menu-title">My Events & QR</span>
            </a>
        </div>
        <!--end::My Events & QR (Participant)-->

        <!--begin::Pengguna-->
        <div class="menu-item me-lg-1">
            <a class="menu-link py-3" href="/attendance/main/role/organiser/event-list.php">
                <span class="menu-icon"><i class="bi bi-people fs-3"></i></span>
                <span class="menu-title">Event</span><!--CHANGED: Event-->
            </a>
        </div>
        <!--end::Pengguna-->

        <!--begin::Pengguna-->
        <div class="menu-item me-lg-1">
            <a class="menu-link py-3" href="/attendance/main/role/organiser/dashboard.php" style="text-decoration: none;">
                <span class="menu-icon"><i class="bi bi-newspaper fs-3"></i></span>
                <span class="menu-title">Dashboard</span><!--CHANGED: Dashboard-->
            </a>
        </div>
        <!--end::Pengguna-->

        <!--begin::Pengguna-->
        <div class="menu-item me-lg-1">
            <a class="menu-link py-3" href="/attendance/main/role/organiser/scanner.php" style="text-decoration: none;">
                <span class="menu-icon"><i class="bi bi-upc-scan fs-3"></i></span>
                <span class="menu-title">Scanner</span><!--CHANGED: Kehadiran-->
            </a>
        </div>
        <!--end::Pengguna-->
    </div>
    <!--end::Menu-->
<?php } else { ?>
    <!-- ======== ADMIN ======== -->
    <div class="menu menu-lg-rounded menu-column menu-lg-row menu-state-bg menu-title-gray-700 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-400 fw-bold my-5 my-lg-0 align-items-stretch" id="kt_header_menu" data-kt-menu="true">
        <!--begin::Home-->
        <div class="menu-item me-lg-1">
            <a class="menu-link py-3" href="/attendance/main/role/admin/home.php" style="text-decoration: none;">
                <span class="menu-icon"><i class="bi bi-house fs-3"></i></span>
                <span class="menu-title">Utama</span>
            </a>
        </div>
        <!--end::Home-->

        <!--begin::Event Management-->
        <div class="menu-item me-lg-1">
            <a class="menu-link py-3" href="/attendance/main/role/admin/event-list.php" style="text-decoration: none;">
                <span class="menu-icon"><i class="bi bi-calendar-event fs-3"></i></span>
                <span class="menu-title">Urus Event</span>
            </a>
        </div>
        <!--end::Event Management-->

        <!--begin::User Management-->
        <div class="menu-item me-lg-1">
            <a class="menu-link py-3" href="/attendance/main/role/admin/users.php" style="text-decoration: none;">
                <span class="menu-icon"><i class="bi bi-people fs-3"></i></span>
                <span class="menu-title">Urus Pengguna</span>
            </a>
        </div>
        <!--end::User Management-->
    </div>
<?php } ?>