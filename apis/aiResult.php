<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
date_default_timezone_set('Asia/Bangkok');

// รับค่า JSON จาก request
$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode(["message" => "0", "error" => "Invalid JSON"]);
    exit();
}

$pythonInput = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
// file_put_contents("pythonInput.json", $pythonInput . "\n");
file_put_contents("AIResult.json", $respondDataToText . "\n");

echo json_encode(["message" => "1"]);
?>