<?php
session_start();

include "../../../include/config.php";
include "../../../include/permissions.php";

require_manage_events();
?>

<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
    <base href="">
    <meta charset="utf-8" />
    <title>Scan Attendance | ATTENDANCE SYSTEM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="../../../assets/media/logos/soljar_ico.ico" />
    <!-- Global Javascript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <!--begin::Page Custom Javascript(used by this page)-->
    <script src="../../../assets/js/custom/widgets.js"></script>
    <script src="../../../assets/js/custom/apps/chat/chat.js"></script>
    <script src="../../../assets/js/custom/modals/create-app.js"></script>
    <script src="../../../assets/js/custom/modals/upgrade-plan.js"></script>
    <!--end::Page Custom Javascript-->

    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->

    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="../../../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../../../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="/attendance/instascan/instascan.min.js"></script>
    <!--end::Global Stylesheets Bundle-->

</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed toolbar-tablet-and-mobile-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
    <!--begin::Main-->
    <div class="d-flex flex-column flex-root">
        <div class="page d-flex flex-row flex-column-fluid">
            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                <?php include "../../../include/header.php"; ?>
                <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                    <?php include "../../../include/toolbar.php"; ?>

                    <div class="container py-6">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="fw-bold mb-0">Scan Attendance</h2>
                            <a href="home.php" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i>Back</a>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Kamera</h5>
                                    </div>
                                    <div class="card-body">
                                        <video id="preview" class="w-100 border rounded" style="min-height:240px;"></video>
                                        <div class="mt-3 d-flex gap-2">
                                            <button class="btn btn-primary" id="startFront">Front Cam</button>
                                            <button class="btn btn-secondary" id="startBack">Back Cam</button>
                                        </div>
                                        <div id="cameraStatus" class="alert alert-info mt-3" role="alert">
                                            <i class="bi bi-info-circle me-2"></i>Memulakan kamera...
                                        </div>
                                        <div class="small text-muted mt-2">Pastikan QR jelas sebelum imbas.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Manual / Status</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="manualForm" class="mb-3">
                                            <label class="form-label fw-bold">Registration ID</label>
                                            <div class="input-group mb-2">
                                                <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                                <input type="text"
                                                    class="form-control form-control-lg"
                                                    id="manualCode"
                                                    placeholder="Contoh: REG0001"
                                                    autocomplete="off" />
                                                <button class="btn btn-primary btn-lg" type="submit">
                                                    <i class="bi bi-check-circle me-1"></i>Submit
                                                </button>
                                            </div>
                                            <small class="text-muted">
                                                Imbas QR kod peserta atau masukkan Registration ID secara manual
                                            </small>
                                        </form>
                                        <div id="resultBox" class="alert d-none mb-0" role="alert"></div>
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

    <div>
        <script src="../../../assets/plugins/global/plugins.bundle.js"></script>
        <script src="../../../assets/js/scripts.bundle.js"></script>
    </div>

    <script>
        const resultBox = document.getElementById('resultBox');
        const cameraStatus = document.getElementById('cameraStatus');
        let scanner = null;
        let activeCameraIndex = 0;

        function updateCameraStatus(type, message) {
            cameraStatus.className = 'alert alert-' + type + ' mt-3';
            cameraStatus.innerHTML = '<i class="bi bi-' + 
                (type === 'success' ? 'check-circle' : 
                 type === 'warning' ? 'exclamation-triangle' : 
                 'info-circle') + ' me-2"></i>' + message;
        }

        function showResult(type, message, details = null) {
            resultBox.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger');
            resultBox.classList.remove('d-none');

            let html = '<strong>' + (type === 'success' ? '✓ ' : '✗ ') + message + '</strong>';
            if (details) {
                html += '<hr class="my-2">';
                if (details.event_name) {
                    html += '<div class="small"><strong>Event:</strong> ' + details.event_name + '</div>';
                }
                if (details.attendance_id) {
                    html += '<div class="small"><strong>Attendance ID:</strong> ' + details.attendance_id + '</div>';
                }
                if (details.check_in_time) {
                    html += '<div class="small"><strong>Check-in Time:</strong> ' + details.check_in_time + '</div>';
                }
            }

            resultBox.innerHTML = html;

            // Auto-hide after 10 seconds for success
            if (type === 'success') {
                setTimeout(() => {
                    resultBox.classList.add('d-none');
                }, 10000);
                // Clear manual input after successful scan
                document.getElementById('manualCode').value = '';
            }

            // Scroll to result
            resultBox.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }

        async function submitCode(code) {
            if (!code || code.trim() === '') {
                showResult('error', 'Sila masukkan Registration ID');
                return;
            }

            code = code.trim();
            resultBox.classList.add('d-none');

            // Show loading
            resultBox.className = 'alert alert-info';
            resultBox.classList.remove('d-none');
            resultBox.innerHTML = '<div class="spinner-border spinner-border-sm me-2" role="status"></div>Memproses...';

            try {
                const res = await fetch('../../api/api_attendance_validate.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        registration_id: code
                    })
                });

                const raw = await res.text();
                let data = null;

                try {
                    data = JSON.parse(raw);
                } catch (parseError) {
                    console.error('Invalid API response:', raw);
                    showResult('error', 'Ralat pelayan: respons tidak sah. Sila semak konfigurasi API.');
                    return;
                }

                if (!res.ok) {
                    showResult('error', data.message || ('Ralat pelayan (HTTP ' + res.status + ').'));
                    return;
                }

                if (data.status === 'success') {
                    // Play success sound if available
                    try {
                        const audio = new Audio('beep-07a.mp3');
                        audio.play().catch(() => {});
                    } catch (e) {}

                    showResult('success', data.message || 'Kehadiran berjaya direkodkan!', {
                        event_name: data.event_name,
                        attendance_id: data.attendance_id,
                        check_in_time: data.check_in_time
                    });
                } else {
                    showResult('error', data.message || 'Ralat semasa memproses. Sila cuba lagi.');
                }
            } catch (e) {
                console.error('Error:', e);
                showResult('error', 'Ralat sambungan. Sila pastikan sambungan internet anda aktif.');
            }
        }

        document.getElementById('manualForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitCode(document.getElementById('manualCode').value.trim());
        });

        // Initialize camera scanner
        function initScanner(cameraIndex) {
            return Instascan.Camera.getCameras().then(function(cameras) {
                console.log('Cameras found:', cameras.length);
                
                if (cameras.length === 0) {
                    updateCameraStatus('danger', 'Tiada kamera ditemui. Sila gunakan manual entry.');
                    document.getElementById('preview').style.display = 'none';
                    document.getElementById('startFront').disabled = true;
                    document.getElementById('startBack').disabled = true;
                    return;
                }

                // Stop existing scanner if running
                if (scanner) {
                    scanner.stop();
                }

                scanner = new Instascan.Scanner({
                    video: document.getElementById('preview'),
                    mirror: false,
                    scanPeriod: 5
                });

                scanner.addListener('scan', function(content) {
                    console.log('Scanned:', content);
                    submitCode(content.trim());
                });

                // Start camera
                const selectedCamera = cameras[cameraIndex] || cameras[0];
                scanner.start(selectedCamera).then(() => {
                    updateCameraStatus('success', 'Kamera aktif. Sila imbas QR kod.');
                    activeCameraIndex = cameraIndex;
                }).catch(err => {
                    console.error('Start camera error:', err);
                    updateCameraStatus('danger', 'Ralat: ' + err.message + '. Sila benarkan akses kamera.');
                });

                // Enable/disable buttons based on available cameras
                document.getElementById('startFront').disabled = false;
                document.getElementById('startBack').disabled = cameras.length < 2;
                if (cameras.length < 2) {
                    document.getElementById('startBack').classList.add('opacity-50');
                }

                return cameras;
            }).catch(function(e) {
                console.error('Camera initialization error:', e);
                updateCameraStatus('danger', 
                    'Ralat mengakses kamera: ' + e.message + 
                    '. Sila pastikan anda memberikan kebenaran akses kamera.');
                document.getElementById('preview').style.display = 'none';
                document.getElementById('startFront').disabled = true;
                document.getElementById('startBack').disabled = true;
            });
        }

        // Initialize on page load
        initScanner(0);

        // Front camera button
        document.getElementById('startFront').addEventListener('click', function() {
            updateCameraStatus('info', 'Menukar ke kamera hadapan...');
            initScanner(0);
        });

        // Back camera button
        document.getElementById('startBack').addEventListener('click', function() {
            updateCameraStatus('info', 'Menukar ke kamera belakang...');
            initScanner(1);
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (scanner) {
                scanner.stop();
            }
        });
    </script>
</body>

</html>