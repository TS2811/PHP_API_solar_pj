<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

$file = "CleanStatus.json";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (file_exists($file)) {
        $jsonData = json_decode(file_get_contents($file), true);
        echo json_encode([$jsonData]); // ส่งเป็น List เสมอ
    } else {
        echo json_encode([["message" => "0"]]); // ส่งเป็น List เสมอ
    }
}
