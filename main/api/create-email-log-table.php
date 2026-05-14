<?php
/**
 * One-time migration: creates the att_email_log table.
 * Run once via browser then delete (or leave — it is idempotent).
 */
session_start();
require_once __DIR__ . '/../../include/config.php';
require_once __DIR__ . '/../../include/permissions.php';

// Admin only
if (($_SESSION['roleId'] ?? null) != 1) {
    http_response_code(403);
    exit('Forbidden.');
}

$sql = "
CREATE TABLE IF NOT EXISTS att_email_log (
    log_id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id        VARCHAR(50)  NOT NULL,
    registration_id VARCHAR(50)  NOT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    recipient_name  VARCHAR(255) NOT NULL DEFAULT '',
    subject         VARCHAR(255) NOT NULL DEFAULT '',
    status          ENUM('sent','failed') NOT NULL DEFAULT 'sent',
    fail_reason     TEXT         NULL,
    sent_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event  (event_id),
    INDEX idx_reg    (registration_id),
    INDEX idx_sentAt (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

if ($conn->query($sql)) {
    echo '<p style="font-family:monospace;color:green;">att_email_log table created (or already exists). You may delete this file.</p>';
} else {
    echo '<p style="font-family:monospace;color:red;">Error: ' . htmlspecialchars($conn->error) . '</p>';
}
