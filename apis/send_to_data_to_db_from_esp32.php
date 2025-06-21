<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
date_default_timezone_set('Asia/Bangkok');

require_once '../connectDB.php';
require_once '../models/sensor.php';

$connectDB = new connectDB();
$sensor = new sensor($connectDB->getConnectionDB());
$data = json_decode(file_get_contents("php://input"));
$DateTime;
try {
    if (!isset($data->current, $data->voltage, $data->Temp, $data->Humi, $data->lux1, $data->lux2, $data->lux3, $data->eqpID)) {
        throw new Exception("Missing required data fields.");
    }

    $DateTime = isset($data->DateTime) ? $data->DateTime : date("Y-m-d H:i:s");

    $result = $sensor->sendData(
        $data->current,
        $data->voltage,
        $data->Temp,
        $data->Humi,
        $data->lux1,
        $data->lux2,
        $data->lux3,
        $DateTime,
        $data->eqpID
    );

    $responseData = ["message" => $result ? "1" : "0"];
} catch (Exception $e) {
    $responseData = ["error" => $e->getMessage()];
}

$responseData["DataTime"] = $DateTime;

$collectData = file_get_contents("http://119.59.117.37/myProjact/collect_data1.php", false, stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n",
        'content' => json_encode($data)
    ]
]));

$collectResponseData = json_decode($collectData, true);

if ($collectResponseData["message"] == "1") {
    $responseData["collectData"] = "1";

    $randomForestResponse = file_get_contents("http://119.59.117.37/myProjact/random_forest.php");
    $KNN_Response = file_get_contents("http://119.59.117.37/myProjact/knn_model.php");
    $naiveBayesResponse = file_get_contents("http://119.59.117.37/myProjact/naive_bayes_model.php");
    $decisionTreeResponse = file_get_contents("http://119.59.117.37/myProjact/decision_tree_model.php");
    $gbr_Response = file_get_contents("http://119.59.117.37/myProjact/gbr_model.php");

    $responseData["gbr_Response"] = json_decode($gbr_Response, true);
    $responseData["randomForestResponse"] = json_decode($randomForestResponse, true);
    $responseData["KNN_Response"] = json_decode($KNN_Response, true);
    $responseData["decisionTreeResponse"] = json_decode($decisionTreeResponse, true);
    $responseData["naiveBayesResponse"] = json_decode($naiveBayesResponse, true);
} elseif ($collectResponseData["message"] == "0") {
    $responseData["collectData"] = "0";
}

$respondDataToText = json_encode($responseData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
file_put_contents("respondDataTo32.json", $respondDataToText . "\n");
echo json_encode([$responseData]);
