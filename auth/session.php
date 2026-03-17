<?php
session_start();
require '../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "logged_in" => false
    ]);
    exit;
}

$userId = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, name, fullname, email, role FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_unset();
    session_destroy();
    http_response_code(401);
    echo json_encode([
        "logged_in" => false
    ]);
    exit;
}

$displayName = trim((string)($user['name'] ?: $user['fullname']));
if ($displayName === '') {
    $displayName = 'ลูกค้า';
}

$_SESSION['user_name'] = $displayName;
$_SESSION['role'] = $user['role'];

echo json_encode([
    "logged_in" => true,
    "user" => [
        "id" => (int)$user['id'],
        "name" => $displayName,
        "email" => $user['email'],
        "role" => $user['role']
    ]
]);