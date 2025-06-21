<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
date_default_timezone_set('Asia/Bangkok');

// รับค่า JSON จาก request
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["message" => "0", "error" => "Invalid JSON"]);
    exit();
}

$pythonInput = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
// Debug: ตรวจสอบ JSON ที่จะส่งไปให้ Python
file_put_contents("pythonInput.txt", $pythonInput . "\n");
$pythonOutput = shell_exec("python C:/xampp/htdocs/myProjact/random_forest.py");

if (!$pythonOutput) {
    echo json_encode(["message" => "0", "error" => "Python script execution failed"]);
    exit();
}

// ตรวจสอบผลลัพธ์จาก Python
$AIresult = json_decode($pythonOutput, true);

if (!$AIresult || !isset($AIresult["predicted_output"])) {
    echo json_encode(["message" => "0", "error" => $AIresult['error'] ?? "AI processing failed"]);
    exit();
}

// ส่งค่าผลลัพธ์กลับไปให้ client
echo json_encode(["message" => "1", "predicted_output" => $AIresult["predicted_output"]]);
