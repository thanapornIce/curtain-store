<?php
// รับข้อมูลจากฟอร์มใบเสนอราคา
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

require '../config/db.php';

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');
$curtainType = trim($_POST['curtain-type'] ?? '');
$fabricType = trim($_POST['fabric-type'] ?? '');
$width = (int)($_POST['width'] ?? 0);
$height = (int)($_POST['height'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 0);

// ตรวจสอบข้อมูล
if ($name === '' || $phone === '' || $curtainType === '' || $fabricType === '' || $width <= 0 || $height <= 0 || $quantity <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

// จัดการไฟล์รูปภาพ
$photoPath = '';
if (isset($_FILES['window-photo']) && $_FILES['window-photo']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png'];
    $fileType = $_FILES['window-photo']['type'];
    $fileSize = $_FILES['window-photo']['size'];
    if (!in_array($fileType, $allowedTypes) || $fileSize > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ไฟล์รูปไม่ถูกต้อง']);
        exit;
    }
    $ext = $fileType === 'image/png' ? '.png' : '.jpg';
    $photoPath = '../images/quotation/' . uniqid('photo_', true) . $ext;
    if (!move_uploaded_file($_FILES['window-photo']['tmp_name'], $photoPath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'อัพโหลดรูปภาพไม่สำเร็จ']);
        exit;
    }
}

// บันทึกข้อมูลลงฐานข้อมูล
$stmt = $conn->prepare("INSERT INTO quotations (name, phone, message, curtain_type, fabric_type, width, height, quantity, photo_path, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sssssiiss", $name, $phone, $message, $curtainType, $fabricType, $width, $height, $quantity, $photoPath);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true, 'message' => 'ส่งใบเสนอราคาเรียบร้อย']);
