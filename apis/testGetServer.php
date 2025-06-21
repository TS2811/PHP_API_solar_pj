<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
date_default_timezone_set('Asia/Bangkok');

$data = json_decode(file_get_contents("php://input"));

// $aiResponse = file_get_contents("http://119.59.117.37/myProjact/collect_data.php", false, stream_context_create([
//     'http' => [
//         'method'  => 'POST',
//         'header'  => "Content-Type: application/json\r\n",
//         'content' => json_encode($data)
//     ]
// ]));

$aiResponse = file_get_contents("https://http://119.59.117.37/myProjact/random_forest.php");

$aiResponseData = json_decode($aiResponse, true);
$aiResponseData["message"] == "1" ? $responseData["message"] = "success" : $responseData["message"] = "fail";
echo json_encode([$responseData]);
