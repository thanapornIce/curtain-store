<?php
require '../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT id, ProductID, Name, Category, Color, Pattern, Price, Stock, Image FROM product ORDER BY id ASC";
$result = $conn->query($sql);

$items = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

echo json_encode([
    "success" => true,
    "items" => $items
], JSON_UNESCAPED_UNICODE);
