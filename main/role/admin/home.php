<?php
session_start();
include "../../../include/config.php";
include "../../../include/permissions.php";

require_admin();

$stats = [
    'users' => 0,
    'events' => 0,
    'registrations' => 0,
];

if (isset($conn) && $conn instanceof mysqli) {
    $resUsers = $conn->query("SELECT COUNT(*) AS total FROM user");
    if ($resUsers && ($row = $resUsers->fetch_assoc())) {
        $stats['users'] = (int)$row['total'];
    }

    $resEvents = $conn->query("SELECT COUNT(*) AS total FROM att_event");
    if ($resEvents && ($row = $resEvents->fetch_assoc())) {
        $stats['events'] = (int)$row['total'];
    }

    $resRegs = $conn->query("SELECT COUNT(*) AS total FROM att_registration");
    if ($resRegs && ($row = $resRegs->fetch_assoc())) {
        $stats['registrations'] = (int)$row['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <base href="">
    <meta charset="utf-8" />
    <title>Admin Dashboard | ATTENDANCE SYSTEM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="../../../assets/media/logos/soljar_ico.ico" />

    <!-- Global Javascript -->
    <script src="../../../assets/plugins/global/plugins.bundle.js"></script>
    <script src="../../../assets/js/scripts.bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />

    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="../../../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../../../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!--end::Global Stylesheets Bundle-->
</head>

<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed toolbar-tablet-and-mobile-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
    <div class="d-flex flex-column flex-root">
        <div class="page d-flex flex-row flex-column-fluid">
            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                <?php include "../../../include/header.php"; ?>

                <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                    <?php include "../../../include/toolbar.php"; ?>

                    <div class="post d-flex flex-column-fluid" id="kt_post">
                        <div id="kt_content_container" class="container py-6">
                            <div class="row g-5 g-xl-10 mb-5">
                                <div class="col-md-4">
                                    <div class="card shadow-sm border-0 h-100">
                                        <div class="card-body">
                                            <div class="text-muted mb-2">Jumlah Pengguna</div>
                                            <h2 class="fw-bold mb-0"><?php echo number_format($stats['users']); ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card shadow-sm border-0 h-100">
                                        <div class="card-body">
                                            <div class="text-muted mb-2">Jumlah Event</div>
                                            <h2 class="fw-bold mb-0"><?php echo number_format($stats['events']); ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card shadow-sm border-0 h-100">
                                        <div class="card-body">
                                            <div class="text-muted mb-2">Jumlah Pendaftaran</div>
                                            <h2 class="fw-bold mb-0"><?php echo number_format($stats['registrations']); ?></h2>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-5">
                                <div class="col-md-6">
                                    <div class="card shadow-sm border-0 h-100">
                                        <div class="card-body">
                                            <h4 class="fw-bold mb-3">Urus Event</h4>
                                            <p class="text-muted">Lihat, kemaskini, dan pantau semua event.</p>
                                            <a class="btn btn-primary btn-sm" href="event-list.php">Buka Senarai Event</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card shadow-sm border-0 h-100">
                                        <div class="card-body">
                                            <h4 class="fw-bold mb-3">Urus Pengguna</h4>
                                            <p class="text-muted">Kemaskini peranan dan status pengguna.</p>
                                            <a class="btn btn-primary btn-sm" href="users.php">Buka Pengurusan Pengguna</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php include "../../../include/footer.php"; ?>
            </div>
        </div>
    </div>

    <?php include "../../../include/scrolltop.php"; ?>
</body>
</html>
