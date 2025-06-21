<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

date_default_timezone_set('Asia/Bangkok');

require_once '../connectDB.php';
require_once '../models/cleaning_schedule.php';

$connectDB = new connectDB();
$cleaning = new cleaning_schedule($connectDB->getConnectionDB());

$file = "CleanStatus.json";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->Clean) || !isset($data->user)) {
        echo json_encode(["status" => "error", "message" => "Missing parameters"]);
        exit();
    }

    $cleanValue = (string) $data->Clean;
    file_put_contents($file, json_encode(["message" => $cleanValue], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

    $resultArray = ["status" => "updated", "messageToESP32" => $cleanValue];

    if ($cleanValue === "1") {
        $result = $cleaning->addCleaningSchedule($data->DateTime, "1", $data->user);
        $resultArray["messageToDB"] = $result ? "1" : "0";
    }

    echo json_encode([$resultArray]);
}
