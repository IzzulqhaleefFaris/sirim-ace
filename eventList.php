<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['userId'])) {
    header('Location: /attendance');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!--end::Global Stylesheets Bundle-->

    <style>
        .table thead th,
        .table tbody td {
            text-align: center !important;
            vertical-align: middle !important;
        }
    </style>
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed toolbar-tablet-and-mobile-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
    <!--begin::Main-->
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Page-->
        <div class="page d-flex flex-row flex-column-fluid min-vh-100">
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

                    <!--begin::Content-->
                    <div class="post d-flex flex-column-fluid" id="kt_post">
                        <div id="kt_content_container" class="container-fluid">
                            <div class="row justify-content-center">
                                <div class="card shadow-sm">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="card-title fs-1" style="font-weight: 700">Senarai Event</h5>
                                        <a href="createEvent.php" class="btn btn-sm btn-primary d-flex align-items-center">
                                            <i class="bi bi-plus-square-fill me-1"></i> Tambah Event
                                        </a>
                                    </div>

                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="eventTable" class="table table-striped table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Nama Event</th>
                                                        <th>Lokasi</th>
                                                        <th>Jenis</th>
                                                        <th>Event Mula</th>
                                                        <th>Event Tamat</th>
                                                        <th>Tindakan</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    <?php
                                                    $sql = "SELECT
                                                                e.event_id,
                                                                e.event_name,
                                                                e.event_startDate,
                                                                e.event_endDate,
                                                                t.event_type_name,
                                                                l.location_name
                                                                FROM att_event e
                                                                LEFT JOIN att_event_type t ON e.event_type_id = t.event_type_id
                                                                LEFT JOIN att_location l ON e.location_id = l.location_id
                                                                ORDER BY e.event_startDate DESC";

                                                    $res = $conn->query($sql);

                                                    //Error handling
                                                    if (!$res) {
                                                        echo "<tr><td colspan= '7' class='text-danger'> Database Error: " . htmlspecialchars($conn->error) . "</td></tr>";
                                                    } elseif ($res->num_rows == 0) {
                                                        echo "<tr><td colspan='7' class='text-center text-muted py-3'>Tiada event ditemui.</td></tr>";
                                                    } else {
                                                        $i = 1;
                                                        while ($row = $res->fetch_assoc()) {
                                                            echo "
                                                                <tr>
                                                                    <td>{$i}</td>
                                                                    <td>" . htmlspecialchars($row['event_name']) . "</td>
                                                                    <td>" . htmlspecialchars($row['location_name']) . "</td>
                                                                    <td>" . htmlspecialchars($row['event_type_name']) . "</td>
                                                                    <td>" . htmlspecialchars($row['event_startDate']) . "</td>
                                                                    <td>" . htmlspecialchars($row['event_endDate']) . "</td>
                                                                    
                                                                    <td>
                                                                        <a href='editEvent.php?id={$row['event_id']}' class='btn btn-warning btn-sm me-1'>Edit</a>
                                                                        <button class='btn btn-danger btn-sm btn-delete' data-id='{$row['event_id']}'>Delete</button>
                                                                    </td>
                                                                </tr>
                                                                ";
                                                            $i++;
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <!--End::Content-->
                <!--end::Container-->
            </div>
            <!--end::Post-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Wrapper-->
    <footer>
        <?php include "include/footer.php"; ?>
    </footer>
    </div>
    <!--end::Page-->

    <!-- JS Script -->
    <script>
        document.addEventListener('click',
            function(e) {
                const btn = e.target.closest('.btn-delete');
                if (!btn) return;

                const idAttr = btn.dataset.id ?? btn.getAttribute('data-id');
                const id = String(idAttr ?? '').trim();
                console.log('delete idAttr:', idAttr, 'parsed id: ', id);

                if (!id || !/^[A-Za-z0-9_-]+$/.test(id)) {
                    alert('Error: missing or invalid event ID');
                    return;
                }

                if (!confirm('Adakah anda pasti mahu memadam event ini?')) return;

                fetch('deleteEvent.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'id=' + encodeURIComponent(id)
                    })
                    .then(res => res.text())
                    .then(text => {
                        if (text.trim() === 'success') {
                            location.reload();
                        } else {
                            alert('Gagal memadam event: ' + text);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Ralat rangkaian. Sila cuba semula.');
                    });
            });
    </script>

    <script>
        $('#eventTable').DataTable({
            language: {
                search: "Cari:",
                lengthMenu: "Papar _MENU_ rekod",
                info: "Rekod _START_ - _END_ daripada _TOTAL_ jumlah rekod",
                infoEmpty: "Tiada rekod",
                infoFiltered: "(Tapis dari _MAX_ rekod)",
                zeroRecords: "Tiada padanan",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "▶",
                    previous: "◀"
                }
            }
        });
    </script>
</body>