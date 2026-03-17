<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$DB_HOST = 'sql100.infinityfree.com';
$DB_PORT = 3306;
$DB_USER = 'if0_41412052';
$DB_PASS = 'Nx73PLKk21J';
$DB_NAME = 'curtain_db';

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
