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

$id = 0;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
} elseif (isset($_GET['work'])) {
    $id = (int)$_GET['work'];
}

if ($id <= 0) {
    respond(false, 'ไม่พบผลงานที่ต้องการ', [], 'invalid_id', 400);
}

try {
    $stmt = $conn->prepare(
        "SELECT id, title, category, location, description, duration_days, spaces, cover_image, detail_url, video_url " .
        "FROM portfolio_items WHERE id = ? AND is_active = 1 LIMIT 1"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    if (!$item) {
        respond(false, 'ไม่พบผลงานที่ต้องการ', [], 'not_found', 404);
    }

    $images = [];
    $imgStmt = $conn->prepare(
        "SELECT image_url FROM portfolio_images WHERE portfolio_id = ? ORDER BY sort_order ASC, id ASC"
    );
    $imgStmt->bind_param('i', $id);
    $imgStmt->execute();
    $imgRes = $imgStmt->get_result();
    while ($row = $imgRes->fetch_assoc()) {
        $images[] = $row['image_url'];
    }

    if (empty($images)) {
        $images[] = $item['cover_image'] ?: 'images/h01.jpg';
    }

    $data = [
        'item' => [
            'id' => (int)$item['id'],
            'title' => $item['title'],
            'category' => $item['category'],
            'location' => $item['location'],
            'description' => $item['description'],
            'duration_days' => $item['duration_days'],
            'spaces' => $item['spaces'],
            'cover_image' => $item['cover_image'],
            'detail_url' => $item['detail_url'],
            'video_url' => $item['video_url']
        ],
        'images' => $images
    ];

    respond(true, 'ok', $data);
} catch (mysqli_sql_exception $e) {
    respond(false, 'ยังไม่มีข้อมูลผลงาน', ['item' => null, 'images' => []], 'table_missing');
}