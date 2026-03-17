<?php
session_start();
require '../config/db.php';

header('Content-Type: application/json; charset=utf-8');

function respond($success, $message, $data = [], $code = null, $status = 200)
{
    http_response_code($status);
    $payload = ['success' => $success, 'message' => $message];
    if ($code !== null) {
        $payload['code'] = $code;
    }
    if (!empty($data)) {
        $payload['data'] = $data;
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    respond(false, 'unauthorized', [], 'unauthorized', 401);
}

$role = $_SESSION['role'] ?? '';
if ($role !== 'admin') {
    respond(false, 'forbidden', [], 'forbidden', 403);
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    respond(false, 'upload_failed', [], 'upload_failed', 400);
}

$file = $_FILES['file'];
$maxSize = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $maxSize) {
    respond(false, 'file_too_large', [], 'file_too_large', 400);
}

$allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt, true)) {
    respond(false, 'invalid_type', [], 'invalid_type', 400);
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
$allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($mime, $allowedMime, true)) {
    respond(false, 'invalid_type', [], 'invalid_type', 400);
}

$uploadDir = dirname(__DIR__) . '/images/portfolio';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = 'portfolio_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$target = $uploadDir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $target)) {
    respond(false, 'upload_failed', [], 'upload_failed', 500);
}

$relative = 'images/portfolio/' . $filename;
respond(true, 'uploaded', ['url' => $relative]);