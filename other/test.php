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
    <link href="assets/css/test.css" rel="stylesheet" type="text/css" />
    <link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/fbootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css" />
    <script
        src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js">
    </script>
    <!--end::Global Stylesheets Bundle-->
</head>

<body>
    <div class="container">
        <!--begin::Content-->
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">Senarai Event</h5>
                                <div>
                                    <a href="createEvent.php" class="btn btn-sm btn-primary">Tambah Event</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:60px">#</th>
                                                <th>Nama Event</th>
                                                <th>Jenis</th>
                                                <th>Tarikh Mula</th>
                                                <th>Tarikh Tamat</th>
                                                <th>Lokasi</th>
                                                <th style="width:170px" class="text-end">Tindakan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Attempt to fetch events from DB if connection ($conn or $con) exists.
                                            // Adjust variable name to match your config (commonly $conn or $con).
                                            $db = null;
                                            if (isset($conn)) $db = $conn;
                                            elseif (isset($con)) $db = $con;

                                            if ($db) {
                                                $sql = "SELECT e.*, t.event_type_name
                                                                    FROM events e
                                                                    LEFT JOIN event_types t ON e.event_type_id = t.event_type_id
                                                                    ORDER BY e.event_startDate DESC";
                                                if ($res = $db->query($sql)) {
                                                    $i = 1;
                                                    if ($res->num_rows === 0) {
                                                        echo '<tr><td colspan="7" class="text-center text-muted py-4">Tiada event ditemui.</td></tr>';
                                                    } else {
                                                        while ($row = $res->fetch_assoc()) {
                                                            $id = htmlspecialchars($row['event_id'] ?? $row['id'] ?? '');
                                                            $name = htmlspecialchars($row['event_name'] ?? '');
                                                            $type = htmlspecialchars($row['event_type_name'] ?? '');
                                                            $start = htmlspecialchars($row['event_startDate'] ?? $row['start_date'] ?? '');
                                                            $end = htmlspecialchars($row['event_endDate'] ?? $row['end_date'] ?? '');
                                                            $location = htmlspecialchars($row['location_name'] ?? $row['lokasi'] ?? '');
                                                            echo "<tr>";
                                                            echo "<td>{$i}</td>";
                                                            echo "<td>{$name}</td>";
                                                            echo "<td>{$type}</td>";
                                                            echo "<td>{$start}</td>";
                                                            echo "<td>{$end}</td>";
                                                            echo "<td>{$location}</td>";
                                                            echo '<td class="text-end">
                                                                                <a href="viewEvent.php?id=' . $id . '" class="btn btn-sm btn-light me-1" title="View"><i class="bi bi-eye"></i></a>
                                                                                <a href="editEvent.php?id=' . $id . '" class="btn btn-sm btn-warning text-white me-1" title="Edit"><i class="bi bi-pencil"></i></a>
                                                                                <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $id . '" title="Hapus"><i class="bi bi-trash"></i></button>
                                                                            </td>';
                                                            echo "</tr>";
                                                            $i++;
                                                        }
                                                    }
                                                    $res->free();
                                                } else {
                                                    echo '<tr><td colspan="7" class="text-danger">Ralat membaca data event: ' . htmlspecialchars($db->error) . '</td></tr>';
                                                }
                                            } else {
                                                // No DB connection available — show placeholder rows
                                                for ($i = 1; $i <= 5; $i++) {
                                                    echo '<tr>
                                                                        <td>' . $i . '</td>
                                                                        <td>Contoh Event ' . $i . '</td>
                                                                        <td>Jenis A</td>
                                                                        <td>2025-10-01</td>
                                                                        <td>2025-10-02</td>
                                                                        <td>Kuala Lumpur</td>
                                                                        <td class="text-end">
                                                                            <a href="#" class="btn btn-sm btn-light me-1"><i class="bi bi-eye"></i></a>
                                                                            <a href="#" class="btn btn-sm btn-warning text-white me-1"><i class="bi bi-pencil"></i></a>
                                                                            <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $i . '"><i class="bi bi-trash"></i></button>
                                                                        </td>
                                                                      </tr>';
                                                }
                                            }
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
    </div>

</body>

</html>