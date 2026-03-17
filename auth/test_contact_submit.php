<?php
// ทดสอบการส่งข้อความติดต่อผ่าน backend contact_submit.php

$url = 'http://localhost/curtain_store/auth/contact_submit.php';
$data = [
    'email' => 'test@example.com',
    'msg' => 'ข้อความทดสอบระบบติดต่อจาก PHP'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded",
        'method'  => 'POST',
        'content' => http_build_query($data),
    ]
];

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    echo "ส่งข้อความไม่สำเร็จ\n";
} else {
    echo "ผลลัพธ์จาก backend: $result\n";
}
