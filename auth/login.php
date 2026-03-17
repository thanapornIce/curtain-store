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

if (!isset($_POST['email'], $_POST['password'])) {
    respond(false, 'Missing fields', 'missing_fields');
}

$email = trim($_POST['email']);
$password = $_POST['password'];

$isEmail = strpos($email, '@') !== false;

if ($isEmail) {
    $sql = "SELECT id, name, fullname, password, role FROM users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
} else {
    $sql = "SELECT id, name, fullname, password, role FROM users WHERE name = ? OR fullname = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $email);
}
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {

    if (password_verify($password, $user['password'])) {
        session_regenerate_id(true);

        $displayName = trim((string)($user['name'] ?: $user['fullname']));
        if ($displayName === '') {
            $displayName = 'ลูกค้า';
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['user_name'] = $displayName;

        respond(true, 'เข้าสู่ระบบสำเร็จ', null, [
            'user' => [
                'id' => (int)$user['id'],
                'name' => $displayName,
                'role' => $user['role']
            ]
        ]);

    } else {
        respond(false, 'อีเมลหรือรหัสผ่านไม่ถูกต้อง', 'invalid_credentials');
    }

} else {
    respond(false, 'อีเมลหรือรหัสผ่านไม่ถูกต้อง', 'invalid_credentials');
}
