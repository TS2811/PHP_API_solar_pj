<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

$file = "CleanStatus.json";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"));
    if (isset($data->Clean)) {
        file_put_contents($file, json_encode(["message" => (string) $data->Clean], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $resultData = array();
        $resultArray = array(
            "status" => "updated",
            "messageToESP32" => $data->Clean,
        );
        array_push($resultData, $resultArray);
        echo json_encode($resultData);
    } else {
        $resultData = array();
        $resultArray = array(
            "status" => "error",
        );
        array_push($resultData, $resultArray);
        echo json_encode($resultData);
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (file_exists($file)) {
        echo file_get_contents($file, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); // คืนค่า JSON Object ตรงๆ
    } else {
        echo json_encode(["message" => "0"]);
    }
}