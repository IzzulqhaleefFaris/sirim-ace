<?php
session_start();
include "../../../include/config.php";
/** @var mysqli $conn */
include "../../../include/updateEventStatus.php";
include "../../../include/permissions.php";

require_manage_events();

// Update event statuses before displaying
updateEventStatuses($conn);

// 1. Validate ID
if (!isset($_GET['id']) || trim($_GET['id']) === '') {
    die("Invalid event ID.");
}

$event_id = $_GET['id'];

require_event_owner_or_admin($conn, $event_id, 'event-list.php');

// 2. Load event data
$sql = "
        SELECT e.*, l.location_name, l.location_buildingName, l.address_line1,
            l.address_line2, l.address_city, l.address_postcode,
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
    <title>Edit Event | sirimace SYSTEM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="../../../assets/media/logos/ace.png" />

    <!-- Global Javascript -->
    <script src="/sirimace/assets/plugins/global/plugins.bundle.js"></script>
    <script src="/sirimace/assets/js/scripts.bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


    <!--begin::Page Custom Javascript(used by this page)-->
    <script src="assets/js/custom/widgets.js"></script>
    <script src="assets/js/custom/apps/chat/chat.js"></script>
    <script src="assets/js/custom/modals/create-app.js"></script>
    <script src="assets/js/custom/modals/upgrade-plan.js"></script>
    <!--end::Page Custom Javascript-->

    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->

    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="../../../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../../../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unpkg.com/antd@5/dist/reset.css" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <!--end::Global Stylesheets Bundle-->

    <style>
        body {
            background: radial-gradient(circle at 10% -10%, #ffffff 0%, #f3f7fb 40%, #eef2f8 100%);
        }

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
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 16px;
            border: 1px solid #e7ecf3;
        }

        .event-img-detail {
            width: 100%;
            max-width: 680px;
            aspect-ratio: 16 / 9;
            object-fit: cover;
            border-radius: 14px;
            border: 1px solid #e7ecf3;
            display: block;
            margin: 0 auto;
            box-shadow: 0 8px 24px rgba(16, 33, 55, 0.1);
        }

        .picker-mount .form-control {
            min-height: 48px;
        }

        .picker-mount .ant-picker {
            width: 100%;
            min-height: 48px;
            border-radius: 10px;
            border-color: #d6dfeb;
        }

        .picker-mount .ant-picker:hover,
        .picker-mount .ant-picker-focused {
            border-color: rgba(15, 108, 191, 0.5);
            box-shadow: 0 0 0 0.2rem rgba(15, 108, 191, 0.12);
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
                <?php include "../../../include/header.php"; ?>
                <!--end::Header-->
                <!--begin::Content-->
                <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                    <!--begin::Toolbar-->
                    <?php include "../../../include/toolbar.php"; ?>
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
                                                Edit Event
                                            </h2>
                                            <p class="text-muted mb-0">Update event information</p>
                                        </div>
                                        <a href="event-list.php" class="btn btn-light border">
                                            <i class="bi bi-arrow-left me-1"></i>Back
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
                                            <form action="update-event.php" method="POST" id="editEventForm" enctype="multipart/form-data" novalidate>

                                                <!-- Hidden Fields -->
                                                <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['event_id']) ?>">
                                                <input type="hidden" name="location_id" value="<?= htmlspecialchars($event['location_id'] ?? '') ?>">

                                                <!-- Event Information Section -->
                                                <h5 class="section-header">
                                                    <i class="bi bi-info-circle me-2 text-primary"></i>Event Information
                                                </h5>

                                                <div class="row mb-4">
                                                    <div class="col-12 mb-3">
                                                        <label for="event_name" class="form-label">
                                                            Event Name
                                                        </label>
                                                        <input type="text"
                                                            id="event_name"
                                                            name="event_name"
                                                            class="form-control form-control-lg"
                                                            value="<?= htmlspecialchars($event['event_name']) ?>"
                                                            required
                                                            placeholder="Enter event name">
                                                        <div class="invalid-feedback">Please enter event name.</div>
                                                    </div>

                                                    <div class="col-12 mb-3">
                                                        <label for="event_type_id" class="form-label">
                                                            Event Type
                                                        </label>
                                                        <select id="event_type_id" name="event_type_id" class="form-select form-select-lg" required>
                                                            <option value="">-- Select Event Type --</option>
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
                                                        <div class="invalid-feedback">Please select event type.</div>
                                                    </div>

                                                    <div class="col-12 mb-3">
                                                        <label for="event_image" class="form-label">Event Image</label>

                                                        <div class="mb-2 text-center">
                                                            <img id="eventImagePreview"
                                                                src="<?= !empty($event['event_image']) ? '/sirimace/' . htmlspecialchars($event['event_image']) : '/sirimace/images/custom/no_image.jpg' ?>"
                                                                data-default-src="<?= !empty($event['event_image']) ? '/sirimace/' . htmlspecialchars($event['event_image']) : '/sirimace/images/custom/no_image.jpg' ?>"
                                                                alt="Event Image Preview"
                                                                class="event-img-detail">
                                                        </div>

                                                        <input type="file"
                                                            class="form-control form-control-lg"
                                                            name="event_image"
                                                            id="event_image"
                                                            accept="image/*">
                                                        <small class="form-text text-muted">Optional. Supported: JPG/PNG, max 2 MB.</small>
                                                    </div>
                                                </div>

                                                <!-- Event Dates Section -->
                                                <h5 class="section-header mt-4">
                                                    <i class="bi bi-calendar-range me-2 text-primary"></i>Event Dates
                                                </h5>

                                                <div class="row mb-4">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="event_startDate" class="form-label ">
                                                            Event Start
                                                        </label>
                                                        <div id="startDateMount" class="picker-mount"></div>
                                                        <input type="hidden"
                                                            id="event_startDate"
                                                            name="event_startDate"
                                                            value="<?= htmlspecialchars($event['event_startDate']) ?>"
                                                            required>
                                                        <div class="invalid-feedback">Please select event start date.</div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="event_endDate" class="form-label">
                                                            Event End
                                                        </label>
                                                        <div id="endDateMount" class="picker-mount"></div>
                                                        <input type="hidden"
                                                            id="event_endDate"
                                                            name="event_endDate"
                                                            value="<?= htmlspecialchars($event['event_endDate']) ?>"
                                                            required>
                                                        <div class="invalid-feedback">Please select event end date.</div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="event_startTime" class="form-label">Start Time</label>
                                                        <div id="startTimeMount" class="picker-mount"></div>
                                                        <input type="hidden"
                                                            id="event_startTime"
                                                            name="event_startTime"
                                                            value="<?= htmlspecialchars($_POST['event_startTime'] ?? ($event['event_startTime'] ?? '09:00')) ?>">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="event_endTime" class="form-label">End Time</label>
                                                        <div id="endTimeMount" class="picker-mount"></div>
                                                        <input type="hidden"
                                                            id="event_endTime"
                                                            name="event_endTime"
                                                            value="<?= htmlspecialchars($_POST['event_endTime'] ?? ($event['event_endTime'] ?? '17:00')) ?>">
                                                    </div>
                                                </div>

                                                <!-- Registration Dates Section -->
                                                <h5 class="section-header mt-4">
                                                    <i class="bi bi-person-plus me-2 text-primary"></i>Registration Dates
                                                </h5>

                                                <div class="row mb-4">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="event_openRegistration" class="form-label">
                                                            Registration Open
                                                        </label>
                                                        <div id="openRegMount" class="picker-mount"></div>
                                                        <input type="hidden"
                                                            id="event_openRegistration"
                                                            name="event_openRegistration"
                                                            value="<?= htmlspecialchars($event['event_openRegistration'] ?? '') ?>"
                                                            placeholder="Optional">
                                                        <small class="form-text text-muted">Leave empty if not specified</small>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="event_closeRegistration" class="form-label">
                                                            Registration Close
                                                        </label>
                                                        <div id="closeRegMount" class="picker-mount"></div>
                                                        <input type="hidden"
                                                            id="event_closeRegistration"
                                                            name="event_closeRegistration"
                                                            value="<?= htmlspecialchars($event['event_closeRegistration'] ?? '') ?>"
                                                            placeholder="Optional">
                                                        <small class="form-text text-muted">Leave empty if not specified</small>
                                                    </div>
                                                </div>

                                                <!-- Location Section -->
                                                <h5 class="section-header mt-4">
                                                    <i class="bi bi-geo-alt me-2 text-primary"></i>Location Information
                                                </h5>

                                                <div class="row mb-4">
                                                    <div class="col-12 mb-3">
                                                        <label for="location_name" class="form-label">
                                                            <i class="bi bi-building me-1"></i> &nbsp;Location Name
                                                        </label>
                                                        <input type="text"
                                                            id="location_name"
                                                            name="location_name"
                                                            class="form-control form-control-lg"
                                                            value="<?= htmlspecialchars($event['location_name'] ?? '') ?>"
                                                            required
                                                            placeholder="Contoh: Dewan Serbaguna">
                                                        <div class="invalid-feedback">Please enter location name.</div>
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label for="location_buildingName" class="form-label">
                                                            <i class="bi bi-building-check me-1"></i>Building Name
                                                        </label>
                                                        <input type="text"
                                                            id="location_buildingName"
                                                            name="location_buildingName"
                                                            class="form-control form-control-lg"
                                                            value="<?= htmlspecialchars($event['location_buildingName'] ?? '') ?>"
                                                                placeholder="Example: Main Building">
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label for="state_id" class="form-label">
                                                            State
                                                        </label>
                                                        <select id="state_id" name="state_id" class="form-select form-select-lg" required>
                                                            <option value="">-- Select State --</option>
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
                                                        <div class="invalid-feedback">Please select state.</div>
                                                    </div>

                                                    <div class="col-12 mb-3">
                                                        <label for="address_line1" class="form-label">
                                                            Address Line 1
                                                        </label>
                                                        <input type="text"
                                                            id="address_line1"
                                                            name="address_line1"
                                                            class="form-control form-control-lg"
                                                            value="<?= htmlspecialchars($event['address_line1'] ?? '') ?>"
                                                                placeholder="Enter address line 1">
                                                    </div>

                                                    <div class="col-12 mb-3">
                                                        <label for="address_line2" class="form-label">
                                                            Address Line 2
                                                        </label>
                                                        <input type="text"
                                                            id="address_line2"
                                                            name="address_line2"
                                                            class="form-control form-control-lg"
                                                            value="<?= htmlspecialchars($event['address_line2'] ?? '') ?>"
                                                                placeholder="Enter address line 2">
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label for="address_city" class="form-label">
                                                            City
                                                        </label>
                                                        <input type="text"
                                                            id="address_city"
                                                            name="address_city"
                                                            class="form-control form-control-lg"
                                                            value="<?= htmlspecialchars($event['address_city'] ?? '') ?>"
                                                                placeholder="Enter city">
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label for="address_postcode" class="form-label">
                                                            Postcode
                                                        </label>
                                                        <input type="text"
                                                            id="address_postcode"
                                                            name="address_postcode"
                                                            class="form-control form-control-lg"
                                                            value="<?= htmlspecialchars($event['address_postcode'] ?? '') ?>"
                                                            placeholder="Enter postcode">
                                                    </div>
                                                </div>

                                                <!-- Form Actions -->
                                                <div class="d-flex justify-content-between align-items-center mt-5 pt-4 border-top">
                                                    <a href="event-list.php" class="btn btn-secondary btn-lg">
                                                        <i class="bi bi-x-circle me-1"></i>Cancel
                                                    </a>
                                                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                                        <i class="bi bi-check-circle me-1"></i>Update Event
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
        <?php include "../../../include/footer.php"; ?>
    </footer>
    </div>
    <!--end::Page-->
    </div>
    <!--end::Root-->

    <!--begin::Javascript-->
    <script src="assets/plugins/global/plugins.bundle.js"></script>
    <script src="assets/js/scripts.bundle.js"></script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/dayjs@1/dayjs.min.js"></script>
    <script src="https://unpkg.com/antd@5/dist/antd.min.js"></script>
    <!--end::Global Javascript Bundle-->

    <script>
        // Form validation
        (function() {
            'use strict';
            const form = document.getElementById('editEventForm');
            const submitBtn = document.getElementById('submitBtn');
            const eventInput = document.getElementById('event_image');
            const previewImg = document.getElementById('eventImagePreview');
            const defaultSrc = previewImg?.dataset?.defaultSrc || previewImg?.getAttribute('src') || '';

            function mountNativeDateInput(mountId, hiddenId, defaultValue) {
                const el = document.getElementById(mountId);
                if (!el) return;

                el.innerHTML = '';
                const input = document.createElement('input');
                input.type = 'date';
                input.className = 'form-control form-control-lg';
                input.value = defaultValue || '';
                input.addEventListener('change', () => {
                    document.getElementById(hiddenId).value = input.value || '';
                });
                el.appendChild(input);
                document.getElementById(hiddenId).value = defaultValue || '';
            }

            function mountNativeTimeInput(mountId, hiddenId, defaultValue) {
                const el = document.getElementById(mountId);
                if (!el) return;

                el.innerHTML = '';
                const input = document.createElement('input');
                input.type = 'time';
                input.className = 'form-control form-control-lg';
                input.value = defaultValue || '';
                input.addEventListener('change', () => {
                    document.getElementById(hiddenId).value = input.value || '';
                });
                el.appendChild(input);
                document.getElementById(hiddenId).value = defaultValue || '';
            }

            function mountPickers() {
                const defaults = {
                    startDate: <?= json_encode($event['event_startDate'] ?? '') ?>,
                    endDate: <?= json_encode($event['event_endDate'] ?? '') ?>,
                    openReg: <?= json_encode($event['event_openRegistration'] ?? '') ?>,
                    closeReg: <?= json_encode($event['event_closeRegistration'] ?? '') ?>,
                    startTime: <?= json_encode($_POST['event_startTime'] ?? ($event['event_startTime'] ?? '09:00')) ?>,
                    endTime: <?= json_encode($_POST['event_endTime'] ?? ($event['event_endTime'] ?? '17:00')) ?>
                };

                const hasAntdStack = !!(window.React && window.ReactDOM && window.dayjs && window.antd && window.ReactDOM.createRoot);
                if (!hasAntdStack) {
                    mountNativeDateInput('startDateMount', 'event_startDate', defaults.startDate);
                    mountNativeDateInput('endDateMount', 'event_endDate', defaults.endDate);
                    mountNativeDateInput('openRegMount', 'event_openRegistration', defaults.openReg);
                    mountNativeDateInput('closeRegMount', 'event_closeRegistration', defaults.closeReg);
                    mountNativeTimeInput('startTimeMount', 'event_startTime', defaults.startTime);
                    mountNativeTimeInput('endTimeMount', 'event_endTime', defaults.endTime);
                    return;
                }

                const { DatePicker, TimePicker } = antd;
                const h = React.createElement;

                function mountDatePicker(mountId, hiddenId, defaultValue) {
                    const el = document.getElementById(mountId);
                    if (!el) return;
                    const defaultVal = defaultValue ? dayjs(defaultValue) : undefined;
                    const root = ReactDOM.createRoot(el);
                    root.render(h(DatePicker, {
                        defaultValue: defaultVal,
                        style: { width: '100%' },
                        format: 'DD/MM/YYYY',
                        onChange: function(dayjsObj) {
                            document.getElementById(hiddenId).value = dayjsObj ? dayjsObj.format('YYYY-MM-DD') : '';
                        },
                        placeholder: 'Select date'
                    }));

                    document.getElementById(hiddenId).value = defaultValue || '';
                }

                function mountTimePicker(mountId, hiddenId, defaultValue) {
                    const el = document.getElementById(mountId);
                    if (!el) return;
                    const defaultVal = defaultValue ? dayjs(defaultValue, 'HH:mm') : undefined;
                    const root = ReactDOM.createRoot(el);
                    root.render(h(TimePicker, {
                        defaultValue: defaultVal,
                        style: { width: '100%' },
                        format: 'hh:mm A',
                        showSecond: false,
                        use12Hours: true,
                        needConfirm: false,
                        onChange: function(dayjsObj) {
                            document.getElementById(hiddenId).value = dayjsObj ? dayjsObj.format('HH:mm') : '';
                        },
                        placeholder: 'Select time'
                    }));

                    document.getElementById(hiddenId).value = defaultValue || '';
                }

                mountDatePicker('startDateMount', 'event_startDate', defaults.startDate);
                mountDatePicker('endDateMount', 'event_endDate', defaults.endDate);
                mountDatePicker('openRegMount', 'event_openRegistration', defaults.openReg);
                mountDatePicker('closeRegMount', 'event_closeRegistration', defaults.closeReg);
                mountTimePicker('startTimeMount', 'event_startTime', defaults.startTime);
                mountTimePicker('endTimeMount', 'event_endTime', defaults.endTime);
            }

            mountPickers();

            if (eventInput && previewImg) {
                eventInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImg.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    } else if (defaultSrc) {
                        previewImg.src = defaultSrc;
                    }
                });
            }

            form.addEventListener('submit', function(event) {
                if (!validateDates() || !form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    // Disable submit button and show loading
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';
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
                        alert('End date must be after start date.');
                        return false;
                    }
                }

                if (openReg.value && closeReg.value) {
                    if (new Date(closeReg.value) < new Date(openReg.value)) {
                        alert('Registration close date must be after open date.');
                        return false;
                    }
                }

                if (openReg.value && startDate.value) {
                    if (new Date(openReg.value) > new Date(startDate.value)) {
                        alert('Registration open date cannot be after event start date.');
                        return false;
                    }
                }

                if (closeReg.value && endDate.value) {
                    if (new Date(closeReg.value) > new Date(endDate.value)) {
                        alert('Registration close date cannot be after event end date.');
                        return false;
                    }
                }

                return true;
            }

            if (startDate) startDate.addEventListener('change', validateDates);
            if (endDate) endDate.addEventListener('change', validateDates);
            if (openReg) openReg.addEventListener('change', validateDates);
            if (closeReg) closeReg.addEventListener('change', validateDates);
        })();
    </script>
</body>
<!--end::Body-->

</html>