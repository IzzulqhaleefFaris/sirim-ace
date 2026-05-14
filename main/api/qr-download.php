<?php
/**
 * qr-download.php — Proxy endpoint to download a QR code image.
 * Fetches the QR from the external API server-side and streams it to the browser.
 *
 * GET params:
 *   data  (string) — the registration ID to encode
 */
session_start();
require_once __DIR__ . '/../../include/permissions.php';

if (empty($_SESSION['userId'])) {
    http_response_code(401);
    exit;
}

$data = trim($_GET['data'] ?? '');
if ($data === '') {
    http_response_code(400);
    exit;
}

$url = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&format=png&data=' . urlencode($data);

$ctx = stream_context_create([
    'http' => [
        'timeout'     => 10,
        'user_agent'  => 'SIRIM-ACE/1.0',
    ],
    'ssl' => [
        'verify_peer'      => true,
        'verify_peer_name' => true,
    ],
]);

$image = @file_get_contents($url, false, $ctx);
if ($image === false || strlen($image) === 0) {
    http_response_code(502);
    exit('Could not fetch QR image.');
}

$filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $data) . '-qr.png';

header('Content-Type: image/png');
header('Content-Length: ' . strlen($image));
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store');
echo $image;
