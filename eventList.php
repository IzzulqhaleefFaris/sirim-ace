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
                                <div class="col-12">
                                    <div class="card shadow-sm">
                                        <div class="card-header d-flex align-items-center justify-content-between">
                                            <h5 class="card-title fs-1" style="font-weight: 700">Senarai Event</h5>
                                            <div>
                                                <a href="createEvent.php" class="btn btn-sm btn-primary"><i class="bi bi-plus-square-fill"></i> Tambah Event</a>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table id="eventTable" class="table table-striped table-hover align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width:70px">No</th>
                                                            <th>Nama Event</th>
                                                            <th>Description</th>
                                                            <th>Jenis </th>
                                                            <th>Tarikh Mula</th>
                                                            <th>Tarikh Tamat</th>
                                                            <th>Lokasi</th>
                                                            <th style="width:170px" class="text-end">Tindakan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        include 'include/config.php';

                                                        $sql = "SELECT e.*, t.event_type_name, l.location_name
                                                                FROM att_event e
                                                                LEFT JOIN att_event_type t ON e.event_type_id = t.event_type_id
                                                                LEFT JOIN att_location l ON e.location_id = l.location_id
                                                                ORDER BY e.event_startDate DESC";

                                                        $res = $conn->query($sql);

                                                        if (!$res) {
                                                            echo '<tr><td colspan="8" class="text-danger">Ralat membaca data event: ' . htmlspecialchars($conn->error) . '</td></tr>';
                                                        } elseif ($res->num_rows === 0) {
                                                            echo '<tr><td colspan="8" class="text-center text-muted py-4">Tiada event ditemui.</td></tr>';
                                                        } else {
                                                            $i = 1;
                                                            while ($row = $res->fetch_assoc()) {
                                                                echo "<tr>
                                                                        <td>{$i}</td>
                                                                        <td>" . htmlspecialchars($row['event_name']) . "</td>
                                                                        <td>" . htmlspecialchars($row['event_description']) . "</td>
                                                                        <td class='text-end'>" . htmlspecialchars($row['event_type_name']) . "</td>
                                                                        <td>" . htmlspecialchars($row['event_startDate']) . "</td>
                                                                        <td>" . htmlspecialchars($row['event_endDate']) . "</td>
                                                                        <td>" . htmlspecialchars($row['location_name']) . "</td>
                                                                        <td>                                                 
                                                                            <button 
                                                                                type='button' 
                                                                                class='btn btn-sm btn-light me-1 btn-view' 
                                                                                data-bs-toggle='modal' 
                                                                                data-bs-target='#viewEventModal'
                                                                                data-name='" . htmlspecialchars($row['event_name']) . "'
                                                                                data-description='" . htmlspecialchars($row['event_description']) . "'
                                                                                data-type='" . htmlspecialchars($row['event_type_name']) . "'
                                                                                data-start='" . htmlspecialchars($row['event_startDate']) . "'
                                                                                data-end='" . htmlspecialchars($row['event_endDate']) . "'
                                                                                data-location='" . htmlspecialchars($row['location_name']) . "'
                                                                                title='View'>
                                                                                <i class='bi bi-eye'></i>
                                                                            </button>

                                                                            <a href='editEvent.php?id=" . $row['event_id'] . "' class='btn btn-sm btn-warning text-white me-1 ' title='Edit'><i class='bi bi-pencil'></i></a>

                                                                            <button type='button' class='btn btn-sm btn-danger btn-delete' data-id='" . $row['event_id'] . "' title='Delete'><i class='bi bi-trash'></i></button>
                                                                        </td>
                                                                    </tr>";
                                                                $i++;
                                                            }
                                                        }
                                                        $res->free();

                                                        //utk table
                                                        /*<div class='d-inline-flex align-items-center justify-content-center gap-1'>
                                                                                <a href='viewEvent.php?id=<?=" . $row["event_id"] . "?> 
                                                                                class='btn btn-sm btn-light d-flex align-items-center justify-content-center' 
                                                                                title='View'>
                                                                                    <i class='bi bi-eye'></i>
                                                                                </a>

                                                                                <a href='editEvent.php?id=<?=" . $row["event_id"] ." ?>' 
                                                                                class='btn btn-sm btn-warning text-white d-flex align-items-center justify-content-center' 
                                                                                title='Edit'>
                                                                                    <i class='bi bi-pencil'></i>
                                                                                </a>

                                                                                <button type='button' 
                                                                                        class='btn btn-sm btn-danger btn-delete d-flex align-items-center justify-content-center' 
                                                                                        data-id='<?=" . $row["event_id"] . "?>' 
                                                                                        title='Delete'>
                                                                                    <i class='bi bi-trash'></i>
                                                                                </button>
                                                                            </div>
                                                        */
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <small class="text-muted">Jumlah event: <?php if (isset($res) && isset($res->num_rows)) echo $res->num_rows;
                                                                                    elseif (isset($i)) echo max(0, $i - 1);
                                                                                    else echo '—'; ?></small>
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

        <div class="modal fade" id="viewEventModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">View Event Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <!-- <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>Nama Event</th>
                                    <td id="viewName"></td>
                                </tr>
                                <tr>
                                    <th>Deskripsi</th>
                                    <td id="viewDesc"></td>
                                </tr>
                                <tr>
                                    <th>Jenis Event</th>
                                    <td id="viewType"></td>
                                </tr>
                                <tr>
                                    <th>Tarikh Mula</th>
                                    <td id="viewStart"></td>
                                </tr>
                                <tr>
                                    <th>Tarikh Tamat</th>
                                    <td id="viewEnd"></td>
                                </tr>
                                <tr>
                                    <th>Tarikh Buka Daftar</th>
                                    <td id="viewRegisterDate"></td>
                                </tr>
                                <tr>
                                    <th>Tarikh Tutup Daftar</th>
                                    <td id="viewCloseRegisterDate"></td>
                                </tr>
                                <tr>
                                    <th>Alamat Penuh</th>
                                    <td id="viewAddress"></td>
                                </tr>
                                <tr>
                                    <th>Negeri</th>
                                    <td id="viewStates"></td>
                                </tr>
                                <tr>
                                    <th>Nama Lokasi</th>
                                    <td id="viewLocation"></td>
                                </tr>
                                <tr>
                                    <th>Bangunan</th>
                                    <td id="viewBuilding"></td>
                                </tr>
                                <tr>
                                    <th>Bilik</th>
                                    <td id="viewRoom"></td>
                                </tr>
                                <tr>
                                    <th>Aras</th>
                                    <td id="viewLevel"></td>
                                </tr>
                            </tbody>
                        </table> -->
                        Pending
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>

                </div>
            </div>
        </div>
        <footer>
            <?php include "include/footer.php"; ?>
        </footer>
    </div>
    <!--end::Page-->
    </div>
    <!--end::Root-->

    <!--begin::Scrolltop-->
    <?php include "include/scrolltop.php"; ?>
    <!--end::Scrolltop-->
    <!--end::Main-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


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

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const viewButtons = document.querySelectorAll(".btn-view");

            viewButtons.forEach(btn => {
                btn.addEventListener("click", function() {
                    document.getElementById("viewName").textContent = this.dataset.name;
                    document.getElementById("viewDesc").textContent = this.dataset.description;
                    document.getElementById("viewType").textContent = this.dataset.type;
                    document.getElementById("viewStart").textContent = this.dataset.start;
                    document.getElementById("viewEnd").textContent = this.dataset.end;
                    document.getElementById("viewOpenRegistration").textContent = this.dataset.x;
                    document.getElementById("viewCloseRegistration").textContent = this.dataset.x;
                    document.getElementById("viewAddress").textContent = this.dataset.address;
                    document.getElementById("viewx").textContent = this.dataset.x;
                    document.getElementById("viewLocation").textContent = this.dataset.location;
                });
            });
        });
    </script>
    <script>
        document.addEventListener("click", function(e) {
            const btn = e.target.closest(".btn-delete");
            if (!btn) return;

            let id = btn.dataset.id;
            console.log("Deleting ID:", id)

            if (!id) {
                alert("Error: missing event ID");
                return;
            }

            if (confirm("Are you sure want to delete this event?")) {
                fetch("deleteEvent.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: "id=" + encodeURIComponent(id),
                    })
                    .then(res => res.text())
                    .then(data => {
                        if (data.trim() === "success") {
                            location.reload();
                        } else {
                            alert("Error: " + data);
                        }
                    });
            }
        });
    </script>
</body>

</html>