<?php
header('Content-Type: application/json; charset=utf-8');

function respond($success, $message, $status = 200)
{
    http_response_code($status);
    echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method Not Allowed', 405);
}

require '../config/db.php';

$email = trim($_POST['email'] ?? '');
$msg = trim($_POST['msg'] ?? '');

if ($email === '' || $msg === '') {
    respond(false, 'ข้อมูลไม่ครบถ้วน', 200);
}

$stmt = $conn->prepare("INSERT INTO contact_messages (email, message, created_at) VALUES (?, ?, NOW())");
$stmt->bind_param("ss", $email, $msg);
$stmt->execute();
$stmt->close();

respond(true, 'ส่งข้อความเรียบร้อย', 200);