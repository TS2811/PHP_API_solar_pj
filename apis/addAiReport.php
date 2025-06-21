<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../connectDB.php';
require_once '../models/aireport.php';

$connectDB = new connectDB();
$ai_report = new ai_report($connectDB->getConnectionDB());

$data = json_decode(file_get_contents("php://input"));

if (isset($data->report) && isset($data->modelAIname) && isset($data->senserTime)) {
    $result = $ai_report->addReport($data->report, $data->modelAIname, $data->senserTime);

    echo json_encode(["message" => $result ? "1" : "0"]);
} else {
    echo json_encode(["error" => "ข้อมูลไม่ครบถ้วน"]);
}

