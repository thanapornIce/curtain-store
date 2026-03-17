<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

require '../config/db.php';

$email = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'อีเมลไม่ถูกต้อง']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO subscribe_emails (email, created_at) VALUES (?, NOW())");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true, 'message' => 'สมัครรับข่าวสารเรียบร้อย']);
