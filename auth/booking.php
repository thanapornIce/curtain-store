<?php
session_start();
require '../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'unauthorized'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = (int)$_SESSION['user_id'];
if ($userId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'invalid_user'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$userStmt = $conn->prepare("SELECT COALESCE(NULLIF(name,''), fullname) AS name, phone FROM users WHERE id = ? LIMIT 1");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

$phone = trim((string)($user['phone'] ?? ''));
$name = trim((string)($user['name'] ?? ''));

if ($phone === '' && $name === '') {
    echo json_encode([
        'success' => true,
        'booking' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "SELECT booking_date, time_slot, customer_name, customer_tel, created_at
        FROM bookings
        WHERE (customer_tel <> '' AND customer_tel = ?) OR (customer_name <> '' AND customer_name = ?)
        ORDER BY booking_date DESC, created_at DESC
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $phone, $name);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

echo json_encode([
    'success' => true,
    'booking' => $booking ?: null
], JSON_UNESCAPED_UNICODE);
