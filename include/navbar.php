<?php
//include("auth.php"); //include auth.php file on all secure pages
include "include/config.php";
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
    <!--begin::Menu-->
    <div class="menu menu-lg-rounded menu-column menu-lg-row menu-state-bg menu-title-gray-700 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-400 fw-bold my-5 my-lg-0 align-items-stretch" id="" data-kt-menu="true">
        <!--begin::Home-->
        <div class="menu-item me-lg-1">
            <a class="menu-link py-3" href="Part_Home.php">
                <span class="menu-icon"><i class="bi bi-house fs-3"></i></span>
                <span class="menu-title">Utama</span>
            </a>
        </div>
        <!--end::Home-->

        <!--begin::Event-->
        <div data-kt-menu-trigger="hover" data-kt-menu-placement="bottom-start" class="menu-item menu-lg-down-accordion me-lg-1">
            <span class="menu-link py-3">
                <span class="menu-icon"><i class="bi bi-journals fs-3"></i></span>
                <span class="menu-title">Event</span>
                <span class="menu-arrow d-lg"></span>
            </span>
            <div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-rounded-0 py-lg-4 w-lg-225px">
                <div class="menu-item">
                    <a class="menu-link py-3" href="Part_EventList.php">
                        <span class="menu-icon"><i class="bi bi-list-task fs-3"></i></span>
                        <span class="menu-title">Senarai Event</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link py-3" href="Part_MyEvents.php">
                        <span class="menu-icon"><i class="bi bi-qr-code fs-3"></i></span>
                        <span class="menu-title">My Events & QR</span>
                    </a>
                </div>
            </div>
        </div>
        <!--end::Event-->
    </div>
    <!--end::Menu-->
<?php } elseif ($_SESSION["roleId"] == '1') { ?>
    <!--begin::Menu-->
    <div class="menu menu-lg-rounded menu-column menu-lg-row menu-state-bg menu-title-gray-700 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-400 fw-bold my-5 my-lg-0 align-items-stretch" id="" data-kt-menu="true">
        <!--begin::Home-->
        <div class="menu-item me-lg-1">
            <a class="menu-link py-3" href="Org_Home.php">
                <span class="menu-icon"><i class="bi bi-calendar-event"></i></span>
                <span class="menu-title">Utama</span>
            </a>
        </div>
        <!--end::Home-->

        <!--begin::Pengguna-->
        <div class="menu-item me-lg-1">
            <a class="menu-link py-3" href="home.php">
                <span class="menu-icon"><i class="bi bi-people fs-3"></i></span>
                <span class="menu-title">Event</span><!--CHANGED: Event-->
            </a>
        </div>
        <!--end::Pengguna-->

        <!--begin::Pengguna-->
        <div class="menu-item me-lg-1">
            <a class="menu-link py-3" href="rondaDailyMap.php">
                <span class="menu-icon"><i class="bi bi-newspaper"></i></span>
                <span class="menu-title">Dashboard</span><!--CHANGED: Dashboard-->
            </a>
        </div>
        <!--end::Pengguna-->

        <!--begin::Pengguna-->
        <div class="menu-item me-lg-1">
            <a class="menu-link py-3" href="Org_Scanner.php">
                <span class="menu-icon"><i class="bi bi-upc-scan"></i></span>
                <span class="menu-title">Kehadiran</span><!--CHANGED: Kehadiran-->
            </a>
        </div>
        <!--end::Pengguna-->
    </div>
    <!--end::Menu-->
<?php } else { ?>
    <!--begin::Menu-->
    <div class="menu menu-lg-rounded menu-column menu-lg-row menu-state-bg menu-title-gray-700 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-400 fw-bold my-5 my-lg-0 align-items-stretch" id="kt_header_menu" data-kt-menu="true">
        <div>

        </div>
        <!--begin::Home-->
        <div class="menu-item me-lg-1">
            <a class="menu-link py-3" href="dashboard.php">
                <span class="menu-icon"><i class="bi bi-house fs-3"></i></span>
                <span class="menu-title">Utama</span>
            </a>
        </div>
        <!--end::Home-->

        <!--begin::Sistem Maklumat-->
        <div data-kt-menu-trigger="hover" data-kt-menu-placement="bottom-start" class="menu-item menu-lg-down-accordion me-lg-1"> <!--tukar hover-->
            <span class="menu-link py-3">
                <span class="menu-icon"><i class="bi bi-archive fs-3"></i></span>
                <span class="menu-title">Event</span>
                <span class="menu-arrow d-lg"></span>
            </span>
            <div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-rounded-0 py-lg-4 w-lg-295px">
                <div class="menu-item">
                    <a class="menu-link py-3" href="pbt_kemaskininosiri.php">
                        <span class="menu-icon"><i class="bi bi-file-earmark-text fs-3"></i></span>
                        <span class="menu-title">Kemaskini No. Siri Iklan</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link py-3" href="pbt_padamnosiri.php">
                        <span class="menu-icon"><i class="bi bi-file-earmark-excel fs-3"></i></span>
                        <span class="menu-title">Padam No. Siri Iklan</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link py-3" href="pbt_kemaskininosiri_premis_carian.php">
                        <span class="menu-icon"><i class="bi bi-file-earmark-binary fs-3"></i></span>
                        <span class="menu-title">Kemaskini No. Siri Premis - Carian</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link py-3" href="pbt_kemaskininosiri_premis.php">
                        <span class="menu-icon"><i class="bi bi-file-earmark-binary-fill fs-3"></i></span>
                        <span class="menu-title">Kemaskini No. Siri Premis - Pilihan</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link py-3" href="pbt_padamnosiri_premis.php">
                        <span class="menu-icon"><i class="bi bi-file-earmark-excel-fill fs-3"></i></span>
                        <span class="menu-title">Padam No. Siri Premis</span>
                    </a>
                </div>
            </div>
        </div>
        <!--end::Sistem Maklumat-->

        <!--begin::Laporan-->
        <div data-kt-menu-trigger="hover" data-kt-menu-placement="bottom-start" class="menu-item menu-lg-down-accordion me-lg-1"> <!--tukar hover-->
            <span class="menu-link py-3">
                <span class="menu-icon"><i class="bi bi-journals fs-3"></i></span>
                <span class="menu-title">Laporan</span>
                <span class="menu-arrow d-lg"></span>
            </span>
            <div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-rounded-0 py-lg-4 w-lg-225px">
                <div class="menu-item">
                    <a class="menu-link py-3" href="pbt_laporanMonitoring.php">
                        <span class="menu-icon"><i class="bi bi-journal fs-3"></i></span>
                        <span class="menu-title">Laporan Pemantauan Iklan</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link py-3" href="pbt_laporanMonitoringPremis.php">
                        <span class="menu-icon"><i class="bi bi-journal-text fs-3"></i></span>
                        <span class="menu-title">Laporan Pemantauan Permis</span>
                    </a>
                </div>
                <!--<div class="menu-item">
                        <a class="menu-link py-3" href="pbt_laporanPelekatPremis.php">
                            <span class="menu-icon"><i class="bi bi-journal-album fs-3"></i></span>
                            <span class="menu-title">Laporan Pelekat Premis</span>
                        </a>
                    </div>-->
            </div>
        </div>
        <!--end::Laporan-->
    </div>
    <!--end::Menu-->
<?php } ?>