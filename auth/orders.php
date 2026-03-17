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

function formatThaiDate($dateStr) {
    $ts = strtotime($dateStr);
    if ($ts === false) {
        return '';
    }

    $months = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
        5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
        9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
    ];

    $day = (int)date('j', $ts);
    $month = (int)date('n', $ts);
    $year = (int)date('Y', $ts) + 543;

    return $day . ' ' . ($months[$month] ?? '') . ' ' . $year;
}

function mapStatus($status) {
    switch ($status) {
        case 'paid':
            return ['label' => 'ยืนยันการชำระเงิน', 'class' => 'st-success'];
        case 'cancelled':
            return ['label' => 'ยกเลิก', 'class' => 'st-cancel'];
        case 'pending':
            return ['label' => 'กำลังจัดเตรียม', 'class' => 'st-waiting'];
        case 'cart':
        default:
            return ['label' => 'รอดำเนินการ', 'class' => 'st-waiting'];
    }
}

$orders = [];

if ($stmt = $conn->prepare("SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY id DESC")) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $statusInfo = mapStatus($row['status'] ?? '');
        $orders[] = [
            'code' => '#ORD-' . str_pad((string)$row['id'], 4, '0', STR_PAD_LEFT),
            'date' => formatThaiDate($row['created_at'] ?? ''),
            'status' => $statusInfo['label'],
            'statusClass' => $statusInfo['class'],
            'tracking' => ''
        ];
    }

    $stmt->close();
}

echo json_encode([
    'success' => true,
    'orders' => $orders
], JSON_UNESCAPED_UNICODE);
