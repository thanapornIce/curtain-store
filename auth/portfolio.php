<?php
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

try {
    $sql = "SELECT id, title, category, location, cover_image, detail_url " .
           "FROM portfolio_items WHERE is_active = 1 " .
           "ORDER BY sort_order ASC, id DESC";
    $result = $conn->query($sql);

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'category' => $row['category'],
            'location' => $row['location'],
            'cover_image' => $row['cover_image'],
            'detail_url' => $row['detail_url']
        ];
    }

    respond(true, 'ok', ['items' => $items]);
} catch (mysqli_sql_exception $e) {
    respond(false, 'ยังไม่มีข้อมูลผลงาน', ['items' => []], 'table_missing');
}