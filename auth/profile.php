<?php
session_start();
require '../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(["success" => false, "error" => "unauthorized"]);
  exit;
}

$user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $stmt = $conn->prepare("SELECT id, COALESCE(NULLIF(name,''), fullname) AS name, email, phone, address FROM users WHERE id=?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result()->fetch_assoc();

  echo json_encode([
    "success" => true,
    "data" => $result
  ]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $address = trim($_POST['address'] ?? '');

  if ($name === '') {
    echo json_encode(["success" => false, "error" => "missing_name"]);
    exit;
  }

  $stmt = $conn->prepare("UPDATE users SET name = ?, fullname = ?, phone = ?, address = ? WHERE id = ?");
  $stmt->bind_param("ssssi", $name, $name, $phone, $address, $user_id);

  if ($stmt->execute()) {
    $_SESSION['user_name'] = $name;
    echo json_encode(["success" => true, "message" => "updated"]);
  } else {
    echo json_encode(["success" => false, "error" => "update_failed"]);
  }
  exit;
}

http_response_code(405);
echo json_encode(["success" => false, "error" => "method_not_allowed"]);
