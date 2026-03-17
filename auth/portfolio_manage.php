<?php
session_start();
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

if (!isset($_SESSION['user_id'])) {
    respond(false, 'unauthorized', [], 'unauthorized', 401);
}

$role = $_SESSION['role'] ?? '';
if ($role !== 'admin') {
    respond(false, 'forbidden', [], 'forbidden', 403);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

function normalize_images($raw)
{
    if ($raw === null || $raw === '') {
        return [];
    }

    $raw = trim((string)$raw);
    if ($raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $images = array_values(array_filter($decoded, function ($item) {
            return is_string($item) && trim($item) !== '';
        }));
        return $images;
    }

    $parts = preg_split('/\s*,\s*/', $raw);
    $images = array_values(array_filter($parts, function ($item) {
        return is_string($item) && trim($item) !== '';
    }));
    return $images;
}

if ($method === 'GET' && $action === 'list') {
    $result = $conn->query(
        "SELECT id, title, category, location, cover_image, detail_url, description, duration_days, spaces, video_url, sort_order, is_active " .
        "FROM portfolio_items ORDER BY sort_order ASC, id DESC"
    );
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'category' => $row['category'],
            'location' => $row['location'],
            'cover_image' => $row['cover_image'],
            'detail_url' => $row['detail_url'],
            'description' => $row['description'],
            'duration_days' => $row['duration_days'],
            'spaces' => $row['spaces'],
            'video_url' => $row['video_url'],
            'sort_order' => (int)$row['sort_order'],
            'is_active' => (int)$row['is_active']
        ];
    }
    respond(true, 'ok', ['items' => $items]);
}

if ($method === 'POST' && $action === 'create') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $cover = trim($_POST['cover_image'] ?? '');
    $detail = trim($_POST['detail_url'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $spaces = trim($_POST['spaces'] ?? '');
    $duration = (int)($_POST['duration_days'] ?? 0);
    $video = trim($_POST['video_url'] ?? '');
    $sort = (int)($_POST['sort_order'] ?? 0);
    $active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
    $images = normalize_images($_POST['images'] ?? '');

    if ($title === '' || $category === '' || $cover === '') {
        respond(false, 'missing_fields', [], 'missing_fields', 400);
    }

    $stmt = $conn->prepare(
        "INSERT INTO portfolio_items (title, category, location, cover_image, detail_url, description, duration_days, spaces, video_url, sort_order, is_active) " .
        "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        'ssssssissii',
        $title,
        $category,
        $location,
        $cover,
        $detail,
        $description,
        $duration,
        $spaces,
        $video,
        $sort,
        $active
    );
    $stmt->execute();
    $newId = $stmt->insert_id;

    if (!empty($images)) {
        $imgStmt = $conn->prepare(
            "INSERT INTO portfolio_images (portfolio_id, image_url, sort_order) VALUES (?, ?, ?)"
        );
        $order = 1;
        foreach ($images as $url) {
            $imgStmt->bind_param('isi', $newId, $url, $order);
            $imgStmt->execute();
            $order++;
        }
    }

    respond(true, 'created', ['id' => (int)$newId]);
}

if ($method === 'POST' && $action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        respond(false, 'invalid_id', [], 'invalid_id', 400);
    }

    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $cover = trim($_POST['cover_image'] ?? '');
    $detail = trim($_POST['detail_url'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $spaces = trim($_POST['spaces'] ?? '');
    $duration = (int)($_POST['duration_days'] ?? 0);
    $video = trim($_POST['video_url'] ?? '');
    $sort = (int)($_POST['sort_order'] ?? 0);
    $active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
    $images = normalize_images($_POST['images'] ?? '');

    $stmt = $conn->prepare(
        "UPDATE portfolio_items SET title=?, category=?, location=?, cover_image=?, detail_url=?, description=?, duration_days=?, spaces=?, video_url=?, sort_order=?, is_active=? WHERE id=?"
    );
    $stmt->bind_param(
        'ssssssissiii',
        $title,
        $category,
        $location,
        $cover,
        $detail,
        $description,
        $duration,
        $spaces,
        $video,
        $sort,
        $active,
        $id
    );
    $stmt->execute();

    if (!empty($images)) {
        $del = $conn->prepare("DELETE FROM portfolio_images WHERE portfolio_id = ?");
        $del->bind_param('i', $id);
        $del->execute();

        $imgStmt = $conn->prepare(
            "INSERT INTO portfolio_images (portfolio_id, image_url, sort_order) VALUES (?, ?, ?)"
        );
        $order = 1;
        foreach ($images as $url) {
            $imgStmt->bind_param('isi', $id, $url, $order);
            $imgStmt->execute();
            $order++;
        }
    }

    respond(true, 'updated', ['id' => $id]);
}

if ($method === 'POST' && $action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        respond(false, 'invalid_id', [], 'invalid_id', 400);
    }

    $delImgs = $conn->prepare("DELETE FROM portfolio_images WHERE portfolio_id = ?");
    $delImgs->bind_param('i', $id);
    $delImgs->execute();

    $delItem = $conn->prepare("DELETE FROM portfolio_items WHERE id = ?");
    $delItem->bind_param('i', $id);
    $delItem->execute();

    respond(true, 'deleted', ['id' => $id]);
}

respond(false, 'invalid_action', [], 'invalid_action', 400);