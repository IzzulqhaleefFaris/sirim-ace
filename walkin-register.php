<?php
session_start();
include "include/config.php";
/** @var mysqli $conn */
include "include/updateEventStatus.php";
updateEventStatuses($conn);

$eventId = trim($_GET['event'] ?? $_POST['event'] ?? '');
$event = null;
$error = null;
$success = null;
$registrationId = null;
if ($eventId === '') {
    $error = 'Invalid event.';
} else {
    $stmtEvent = $conn->prepare("SELECT event_id, event_name, event_startDate, event_endDate, event_status FROM att_event WHERE event_id = ? LIMIT 1");
    if ($stmtEvent) {
        $stmtEvent->bind_param("s", $eventId);
        $stmtEvent->execute();
        $resEvent = $stmtEvent->get_result();
        if ($resEvent && $resEvent->num_rows > 0) {
            $event = $resEvent->fetch_assoc();
        } else {
            $error = 'Event not found.';
        }
        $stmtEvent->close();
    } else {
        $error = 'Server error while loading event.';
    }
}

$isEventCurrent = $event && (($event['event_status'] ?? '') === 'Current');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    if (!$isEventCurrent) {
        $error = 'Walk-in is only allowed while the event is ongoing.';
    } else {
        $walkinName = trim($_POST['walkin_name'] ?? '');
        $walkinEmail = trim($_POST['walkin_email'] ?? '');
        $walkinPhone = trim($_POST['walkin_phone'] ?? '');
        $walkinCompany = trim($_POST['walkin_company'] ?? '');

        if ($walkinName === '') {
            $error = 'Walk-in name is required.';
        } else {
            if ($walkinEmail !== '' && !filter_var($walkinEmail, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email format.';
            }
        }

        if (!$error) {
            // Prevent duplicate walk-in per event by email/phone when provided
            if ($walkinEmail !== '' || $walkinPhone !== '') {
                $dupSql = "
                    SELECT registration_id
                    FROM att_registration
                    WHERE event_id = ?
                      AND registration_source = 'walk_in'
                      AND (
                        (? <> '' AND walkin_email = ?)
                        OR
                        (? <> '' AND walkin_phone = ?)
                      )
                    LIMIT 1
                ";

                $dupStmt = $conn->prepare($dupSql);
                if ($dupStmt) {
                    $dupStmt->bind_param("sssss", $eventId, $walkinEmail, $walkinEmail, $walkinPhone, $walkinPhone);
                    $dupStmt->execute();
                    $dupRes = $dupStmt->get_result();
                    if ($dupRes && $dupRes->num_rows > 0) {
                        $existing = $dupRes->fetch_assoc();
                        $registrationId = $existing['registration_id'];
                        $success = 'Anda sudah berdaftar sebagai walk-in untuk event ini.';
                    }
                    $dupStmt->close();
                }
            }
        }

        if (!$error && !$success) {
            // Generate registration ID
            $newCode = 'REG0001';
            $codeSql = "SELECT registration_id FROM att_registration WHERE registration_id LIKE 'REG%' ORDER BY registration_id DESC LIMIT 1";
            $codeRes = $conn->query($codeSql);
            if ($codeRes && $row = $codeRes->fetch_assoc()) {
                $lastNumber = (int) substr($row['registration_id'], 3);
                $newCode = 'REG' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            }

            // Generate pseudo participant id for walk-in (length 10)
            $walkinParticipantId = 'WKI0000001';
            $pidSql = "SELECT participant_id FROM att_registration WHERE participant_id LIKE 'WKI%' ORDER BY participant_id DESC LIMIT 1";
            $pidRes = $conn->query($pidSql);
            if ($pidRes && $rowPid = $pidRes->fetch_assoc()) {
                $lastWalkinNum = (int) substr($rowPid['participant_id'], 3);
                $walkinParticipantId = 'WKI' . str_pad($lastWalkinNum + 1, 7, '0', STR_PAD_LEFT);
            }

            $insertSql = "
                INSERT INTO att_registration
                    (registration_id, event_id, participant_id, registration_source, walkin_name, walkin_email, walkin_phone, walkin_company)
                VALUES (?, ?, ?, 'walk_in', ?, ?, ?, ?)
            ";

            $insertStmt = $conn->prepare($insertSql);
            if (!$insertStmt) {
                $error = 'Server error while registering walk-in.';
            } else {
                $insertStmt->bind_param(
                    "sssssss",
                    $newCode,
                    $eventId,
                    $walkinParticipantId,
                    $walkinName,
                    $walkinEmail,
                    $walkinPhone,
                    $walkinCompany
                );

                if ($insertStmt->execute()) {
                    $registrationId = $newCode;
                    $success = 'Walk-in registration successful.';
                    // Mark attendance as Present immediately
                    $attNextId = 'ATT0001';
                    $attLast = $conn->query("SELECT attendance_id FROM att_attendance ORDER BY attendance_id DESC LIMIT 1");
                    if ($attLast && $attRow = $attLast->fetch_assoc()) {
                        $attLastNum = (int) substr($attRow['attendance_id'], 3);
                        $attNextId = 'ATT' . str_pad($attLastNum + 1, 4, '0', STR_PAD_LEFT);
                    }
                    $attInsert = $conn->prepare("
                        INSERT INTO att_attendance
                            (attendance_id, registration_id, event_id, participant_id, check_in_time, attendance_status)
                        VALUES (?, ?, ?, ?, NOW(), 'Present')
                    ");
                    if ($attInsert) {
                        $attInsert->bind_param("ssss", $attNextId, $registrationId, $eventId, $walkinParticipantId);
                        $attInsert->execute();
                        $attInsert->close();
                    }
                } else {
                    $error = 'Registration failed. Please try again.';
                }

                $insertStmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Walk-in Registration | ATTENDANCE SYSTEM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="/sirimace/assets/media/logos/ace.png" />
    <link href="/sirimace/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/sirimace/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
</head>

<body class="bg-light">
    <div class="container py-8" style="max-width: 780px;">
        <div class="card shadow-sm">
            <div class="card-body p-6">
                <h2 class="fw-bold mb-2">Walk-in Registration</h2>
                <br>
                <?php if ($event): ?>
                    <div class="text-muted mb-4">
                        <div class="fw-semibold text-gray-800 fs-3"><?= htmlspecialchars($event['event_name']) ?></div>
                        <div>
                            Event Date:
                            <?php
                                // Format event dates as dd/mm/yyyy and show single date when start==end
                                $rawStart = $event['event_startDate'] ?? '';
                                $rawEnd = $event['event_endDate'] ?? '';

                                function formatDateDMY($raw) {
                                    if (empty($raw)) return '';
                                    try {
                                        $dt = new DateTime($raw);
                                        return $dt->format('d/m/Y');
                                    } catch (Exception $e) {
                                        return $raw;
                                    }
                                }

                                $startOnly = '';
                                $endOnly = '';
                                try {
                                    if (!empty($rawStart)) $startOnly = (new DateTime($rawStart))->format('Y-m-d');
                                    if (!empty($rawEnd)) $endOnly = (new DateTime($rawEnd))->format('Y-m-d');
                                } catch (Exception $e) {
                                    // ignore and fall back to raw values
                                }

                                if ($startOnly && $endOnly && $startOnly === $endOnly) {
                                    echo htmlspecialchars(formatDateDMY($rawStart));
                                } else {
                                    $outStart = formatDateDMY($rawStart);
                                    $outEnd = formatDateDMY($rawEnd);
                                    echo htmlspecialchars(trim($outStart . ($outStart && $outEnd ? ' - ' : '') . $outEnd));
                                }
                            ?>
                        </div>
                        <div>Status: <span class="badge <?= ($event['event_status'] === 'Current') ? 'bg-success' : 'bg-secondary' ?>"><?= htmlspecialchars($event['event_status']) ?></span></div>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if ($registrationId): ?>
                    <div class="text-center mb-5">
                        <div class="fw-bold mb-2">Registration ID: <?= htmlspecialchars($registrationId) ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($event && $isEventCurrent): ?>
                    <form method="POST" class="row g-3">
                        <input type="hidden" name="event" value="<?= htmlspecialchars($eventId) ?>">
                        <div class="col-12">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="walkin_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="walkin_email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="walkin_phone" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Company</label>
                            <input type="text" name="walkin_company" class="form-control">
                        </div>
                        <div class="col-12 d-flex gap-2 mt-2">
                            <button type="submit" class="btn btn-primary">Register Walk-in</button>
                            <a href="/sirimace" class="btn btn-light border">Back</a>
                        </div>
                    </form>
                <?php elseif ($event): ?>
                    <div class="alert alert-warning mb-0">Walk-in is only available when the event is started.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
