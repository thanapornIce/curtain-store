<?php
session_start();
require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query("\n        SELECT DATE_FORMAT(booking_date, '%Y-%m-%d') AS booking_day, time_slot\n        FROM bookings\n    ");
    $bookings = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $dateKey = $row['booking_day'];
            $slot = strtolower(trim((string) $row['time_slot']));

            if (!isset($bookings[$dateKey])) {
                $bookings[$dateKey] = [];
            }

            if (!in_array($slot, $bookings[$dateKey], true)) {
                $bookings[$dateKey][] = $slot;
            }
        }
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($bookings);

    $conn->close();
    exit;
}

header('Content-Type: text/plain; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "unauthorized";
    $conn->close();
    exit;
}

$userId = (int) $_SESSION['user_id'];
$date = trim((string)($_POST['date'] ?? ''));
$slot = strtolower(trim((string)($_POST['slot'] ?? '')));
$note = trim((string)($_POST['note'] ?? ''));

if ($date === '' || $slot === '') {
    echo "error";
    $conn->close();
    exit;
}

if (!in_array($slot, ['morning', 'afternoon'], true)) {
    echo "invalid_slot";
    $conn->close();
    exit;
}

$bookingDate = DateTime::createFromFormat('Y-m-d', $date);
$today = new DateTime('today');
$minBookingDate = clone $today;

if (!$bookingDate || $bookingDate->format('Y-m-d') !== $date) {
    echo "invalid_date";
    $conn->close();
    exit;
}

if ($bookingDate < $minBookingDate) {
    echo "booking_must_be_today_or_future";
    $conn->close();
    exit;
}

$userStmt = $conn->prepare("SELECT COALESCE(NULLIF(name, ''), fullname) AS customer_name, phone FROM users WHERE id = ? LIMIT 1");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

$customerName = trim((string)($user['customer_name'] ?? ''));
$customerTel = trim((string)($user['phone'] ?? ''));

if ($customerName === '' || $customerTel === '') {
    echo "missing_profile";
    $conn->close();
    exit;
}

$checkStmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE DATE(booking_date) = ? AND time_slot = ?");
$checkStmt->bind_param("ss", $date, $slot);
$checkStmt->execute();
$checkStmt->bind_result($exists);
$checkStmt->fetch();
$checkStmt->close();

if ($exists > 0) {
    echo "ช่วงเวลานี้ถูกจองแล้ว";
    $conn->close();
    exit;
}

$stmt = $conn->prepare("\n    INSERT INTO bookings (booking_date, time_slot, customer_name, customer_tel, note)\n    VALUES (?, ?, ?, ?, ?)\n");

$stmt->bind_param("sssss", $date, $slot, $customerName, $customerTel, $note);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}

$stmt->close();
$conn->close();
?>
