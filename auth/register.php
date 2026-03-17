<?php
session_start();
require '../config/db.php';

header('Content-Type: application/json; charset=utf-8');

function respond($success, $message, $code = null, $data = [], $status = 200)
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  respond(false, 'Method Not Allowed', 'method_not_allowed', [], 405);
}

$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$passwordRaw = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($fullname === '' || $email === '' || $passwordRaw === '') {
  respond(false, 'กรุณากรอกข้อมูลให้ครบ', 'missing_fields');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  respond(false, 'รูปแบบอีเมลไม่ถูกต้อง', 'invalid_email');
}

if ($confirmPassword !== '' && $passwordRaw !== $confirmPassword) {
  respond(false, 'รหัสผ่านไม่ตรงกัน', 'password_mismatch');
}

if (strlen($passwordRaw) < 8) {
  respond(false, 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร', 'password_too_short');
}

$password = password_hash($passwordRaw, PASSWORD_DEFAULT);

// เช็คอีเมลซ้ำ
$check = $conn->prepare("SELECT id FROM users WHERE email=?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
  respond(false, 'อีเมลนี้ถูกใช้แล้ว', 'email_exists');
}

$stmt = $conn->prepare(
  "INSERT INTO users (fullname,name,email,phone,password) VALUES (?,?,?,?,?)"
);
$stmt->bind_param("sssss", $fullname, $fullname, $email, $phone, $password);

if ($stmt->execute()) {
  $newId = $stmt->insert_id;
  session_regenerate_id(true);
  $_SESSION['user_id'] = $newId;
  $_SESSION['role'] = 'customer';
  $_SESSION['user_name'] = $fullname !== '' ? $fullname : 'ลูกค้า';
  respond(true, 'สมัครสมาชิกสำเร็จ', null, [
    'user' => [
      'id' => (int)$newId,
      'name' => $_SESSION['user_name'],
      'role' => 'customer'
    ]
  ]);
} else {
  respond(false, 'สมัครสมาชิกไม่สำเร็จ', 'error');
}
