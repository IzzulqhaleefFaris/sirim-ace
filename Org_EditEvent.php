<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header("Location: /attendance");
    exit;
}

include "include/config.php";
include "include/updateEventStatus.php";

// Update event statuses before displaying
updateEventStatuses($conn);

// 1. Validate ID
if (!isset($_GET['id']) || trim($_GET['id']) === '') {
    die("Invalid event ID.");
}

$event_id = $_GET['id'];

// 2. Load event data
$sql = "
    SELECT e.*, l.location_name, l.building_name, l.location_address, 
           l.location_room, l.location_level, l.state_id AS loc_state
    FROM att_event e
    LEFT JOIN att_location l ON e.location_id = l.location_id
    WHERE e.event_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Event not found.");
}

$event = $result->fetch_assoc();

// 3. Load dropdown options
$types = $conn->query("SELECT * FROM att_event_type");
$states = $conn->query("SELECT * FROM att_state");
?>

<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
    <base href="">
    <meta charset="utf-8" />
    <title>Edit Event | ATTENDANCE SYSTEM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="assets/media/logos/soljar_ico.ico" />
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/custom.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <!--end::Global Stylesheets Bundle-->
    <style>
        .form-label {
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }
        .form-label.required::after {
            content: " *";
            color: red;
        }
        .section-header {
            color: #1a1a1a;
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e0e0e0;
        }
        .card {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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

                    <!--begin::Post-->
                    <div class="post d-flex flex-column-fluid" id="kt_post">
                        <div id="kt_content_container" class="container-fluid py-5">
                            <div class="row justify-content-center">
                                <div class="col-12 col-lg-10 col-xl-8">
                                    <!-- Header -->
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div>
                                            <h2 class="fw-bold mb-1">
                                                <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Event
                                            </h2>
                                            <p class="text-muted mb-0">Kemaskini maklumat event</p>
                                        </div>
                                        <a href="Org_EventList.php" class="btn btn-light border">
                                            <i class="bi bi-arrow-left me-1"></i>Kembali
                                        </a>
                                    </div>

                                    <!-- Alert Messages -->
                                    <?php if (!empty($_SESSION['msg'])): ?>
                                        <div class="alert alert-<?= htmlspecialchars($_SESSION['msg']['type']) ?> alert-dismissible fade show" role="alert">
                                            <i class="bi bi-<?= $_SESSION['msg']['type'] === 'success' ? 'check-circle' : ($_SESSION['msg']['type'] === 'danger' ? 'exclamation-triangle' : 'info-circle') ?> me-2"></i>
                                            <?= htmlspecialchars($_SESSION['msg']['text']) ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                        <?php unset($_SESSION['msg']); ?>
                                    <?php endif; ?>

                                    <!-- Form Card -->
                                    <div class="card shadow-sm">
                                        <div class="card-body p-4">
                                            <form action="Org_UpdateEvent.php" method="POST" id="editEventForm" novalidate>

                                                <!-- Hidden Fields -->
                                                <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['event_id']) ?>">
                                                <input type="hidden" name="location_id" value="<?= htmlspecialchars($event['location_id'] ?? '') ?>">

                                                <!-- Event Information Section -->
                                                <h5 class="section-header">
                                                    <i class="bi bi-info-circle me-2 text-primary"></i>Maklumat Event
                                                </h5>

                                                <div class="row mb-4">
                                                    <div class="col-12 mb-3">
                                                        <label for="event_name" class="form-label required">
                                                            <i class="bi bi-calendar-event me-1"></i>Nama Event
                                                        </label>
                                                        <input type="text" 
                                                               id="event_name"
                                                               name="event_name" 
                                                               class="form-control form-control-lg"
                                                               value="<?= htmlspecialchars($event['event_name']) ?>" 
                                                               required
                                                               placeholder="Masukkan nama event">
                                                        <div class="invalid-feedback">Sila masukkan nama event.</div>
                                                    </div>

                                                    <div class="col-12 mb-3">
                                                        <label for="event_type_id" class="form-label required">
                                                            <i class="bi bi-tag me-1"></i>Jenis Event
                                                        </label>
                                                        <select id="event_type_id" name="event_type_id" class="form-select form-select-lg" required>
                                                            <option value="">-- Pilih Jenis Event --</option>
                                                            <?php 
                                                            $types->data_seek(0); // Reset pointer
                                                            while ($t = $types->fetch_assoc()): 
                                                            ?>
                                                                <option value="<?= htmlspecialchars($t['event_type_id']) ?>"
                                                                    <?= $event['event_type_id'] == $t['event_type_id'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($t['event_type_name']) ?>
                                                                </option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                        <div class="invalid-feedback">Sila pilih jenis event.</div>
                                                    </div>
                                                </div>

                                                <!-- Event Dates Section -->
                                                <h5 class="section-header mt-4">
                                                    <i class="bi bi-calendar-range me-2 text-primary"></i>Tarikh Event
                                                </h5>

                                                <div class="row mb-4">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="event_startDate" class="form-label required">
                                                            <i class="bi bi-calendar-check me-1"></i>Event Mula
                                                        </label>
                                                        <input type="date" 
                                                               id="event_startDate"
                                                               name="event_startDate" 
                                                               class="form-control form-control-lg"
                                                               value="<?= htmlspecialchars($event['event_startDate']) ?>" 
                                                               required>
                                                        <div class="invalid-feedback">Sila pilih tarikh mula event.</div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="event_endDate" class="form-label required">
                                                            <i class="bi bi-calendar-x me-1"></i>Event Tamat
                                                        </label>
                                                        <input type="date" 
                                                               id="event_endDate"
                                                               name="event_endDate" 
                                                               class="form-control form-control-lg"
                                                               value="<?= htmlspecialchars($event['event_endDate']) ?>" 
                                                               required>
                                                        <div class="invalid-feedback">Sila pilih tarikh tamat event.</div>
                                                    </div>
                                                </div>

                                                <!-- Registration Dates Section -->
                                                <h5 class="section-header mt-4">
                                                    <i class="bi bi-person-plus me-2 text-primary"></i>Tarikh Pendaftaran
                                                </h5>

                                                <div class="row mb-4">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="event_openRegistration" class="form-label">
                                                            <i class="bi bi-unlock me-1"></i>Pendaftaran Dibuka
                                                        </label>
                                                        <input type="date" 
                                                               id="event_openRegistration"
                                                               name="event_openRegistration" 
                                                               class="form-control form-control-lg"
                                                               value="<?= htmlspecialchars($event['event_openRegistration'] ?? '') ?>"
                                                               placeholder="Pilihan">
                                                        <small class="form-text text-muted">Kosongkan jika tidak dinyatakan</small>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="event_closeRegistration" class="form-label">
                                                            <i class="bi bi-lock me-1"></i>Pendaftaran Ditutup
                                                        </label>
                                                        <input type="date" 
                                                               id="event_closeRegistration"
                                                               name="event_closeRegistration" 
                                                               class="form-control form-control-lg"
                                                               value="<?= htmlspecialchars($event['event_closeRegistration'] ?? '') ?>"
                                                               placeholder="Pilihan">
                                                        <small class="form-text text-muted">Kosongkan jika tidak dinyatakan</small>
                                                    </div>
                                                </div>

                                                <!-- Location Section -->
                                                <h5 class="section-header mt-4">
                                                    <i class="bi bi-geo-alt me-2 text-primary"></i>Maklumat Lokasi
                                                </h5>

                                                <div class="row mb-4">
                                                    <div class="col-12 mb-3">
                                                        <label for="location_name" class="form-label required">
                                                            <i class="bi bi-building me-1"></i>Nama Lokasi
                                                        </label>
                                                        <input type="text" 
                                                               id="location_name"
                                                               name="location_name" 
                                                               class="form-control form-control-lg"
                                                               value="<?= htmlspecialchars($event['location_name'] ?? '') ?>" 
                                                               required
                                                               placeholder="Contoh: Dewan Serbaguna">
                                                        <div class="invalid-feedback">Sila masukkan nama lokasi.</div>
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label for="building_name" class="form-label">
                                                            <i class="bi bi-building-check me-1"></i>Nama Bangunan
                                                        </label>
                                                        <input type="text" 
                                                               id="building_name"
                                                               name="building_name" 
                                                               class="form-control form-control-lg"
                                                               value="<?= htmlspecialchars($event['building_name'] ?? '') ?>"
                                                               placeholder="Contoh: Bangunan Utama">
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label for="state_id" class="form-label required">
                                                            <i class="bi bi-map me-1"></i>Negeri
                                                        </label>
                                                        <select id="state_id" name="state_id" class="form-select form-select-lg" required>
                                                            <option value="">-- Pilih Negeri --</option>
                                                            <?php 
                                                            $states->data_seek(0); // Reset pointer
                                                            while ($s = $states->fetch_assoc()): 
                                                            ?>
                                                                <option value="<?= htmlspecialchars($s['state_id']) ?>"
                                                                    <?= ($event['loc_state'] ?? '') == $s['state_id'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($s['state_name']) ?>
                                                                </option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                        <div class="invalid-feedback">Sila pilih negeri.</div>
                                                    </div>

                                                    <div class="col-12 mb-3">
                                                        <label for="location_address" class="form-label">
                                                            <i class="bi bi-geo-alt-fill me-1"></i>Alamat Lengkap
                                                        </label>
                                                        <textarea id="location_address"
                                                                  name="location_address" 
                                                                  class="form-control"
                                                                  rows="3"
                                                                  placeholder="Masukkan alamat lengkap lokasi"><?= htmlspecialchars($event['location_address'] ?? '') ?></textarea>
                                                    </div>
                                                </div>

                                                <!-- Form Actions -->
                                                <div class="d-flex justify-content-between align-items-center mt-5 pt-4 border-top">
                                                    <a href="Org_EventList.php" class="btn btn-secondary btn-lg">
                                                        <i class="bi bi-x-circle me-1"></i>Batal
                                                    </a>
                                                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                                        <i class="bi bi-check-circle me-1"></i>Kemaskini Event
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Section-->
                </div>
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
    </div>
    <!--end::Root-->

    <!--begin::Javascript-->
    <script src="assets/plugins/global/plugins.bundle.js"></script>
    <script src="assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->

    <script>
        // Form validation
        (function() {
            'use strict';
            const form = document.getElementById('editEventForm');
            const submitBtn = document.getElementById('submitBtn');

            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    // Disable submit button and show loading
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';
                }
                form.classList.add('was-validated');
            }, false);

            // Date validation
            const startDate = document.getElementById('event_startDate');
            const endDate = document.getElementById('event_endDate');
            const openReg = document.getElementById('event_openRegistration');
            const closeReg = document.getElementById('event_closeRegistration');

            function validateDates() {
                if (startDate.value && endDate.value) {
                    if (new Date(endDate.value) < new Date(startDate.value)) {
                        endDate.setCustomValidity('Tarikh tamat mesti selepas tarikh mula');
                        endDate.reportValidity();
                        return false;
                    } else {
                        endDate.setCustomValidity('');
                    }
                }

                if (openReg.value && closeReg.value) {
                    if (new Date(closeReg.value) < new Date(openReg.value)) {
                        closeReg.setCustomValidity('Tarikh tutup pendaftaran mesti selepas tarikh buka');
                        closeReg.reportValidity();
                        return false;
                    } else {
                        closeReg.setCustomValidity('');
                    }
                }

                if (openReg.value && startDate.value) {
                    if (new Date(openReg.value) > new Date(startDate.value)) {
                        openReg.setCustomValidity('Tarikh buka pendaftaran tidak boleh selepas tarikh mula event');
                        openReg.reportValidity();
                        return false;
                    } else {
                        openReg.setCustomValidity('');
                    }
                }

                if (closeReg.value && endDate.value) {
                    if (new Date(closeReg.value) > new Date(endDate.value)) {
                        closeReg.setCustomValidity('Tarikh tutup pendaftaran tidak boleh selepas tarikh tamat event');
                        closeReg.reportValidity();
                        return false;
                    } else {
                        closeReg.setCustomValidity('');
                    }
                }

                return true;
            }

            startDate.addEventListener('change', validateDates);
            endDate.addEventListener('change', validateDates);
            openReg.addEventListener('change', validateDates);
            closeReg.addEventListener('change', validateDates);
        })();
    </script>
</body>
<!--end::Body-->

</html>