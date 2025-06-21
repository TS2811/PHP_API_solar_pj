<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once '../connectDB.php';
require_once '../models/latest_prediction_results.php';

$connectDB = new connectDB();
$AiPredict = new LatestPredictionResults($connectDB->getConnectionDB());

$data = json_decode(file_get_contents("php://input"));

$result = $AiPredict->getAllReport();

if ($result->rowCount() > 0) {
    $resultData = array();
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $resultArray = array(
            "RandomForest" => $RandomForest,
            "GBR" => $GBR,
            "KNN" => $KNN,
            "NaiveBayes" => $NaiveBayes,
            "DecisionTreet" => $DecisionTreet,
            "senserTime" => $senserTime
        );
        array_push($resultData, $resultArray);
    }
    echo json_encode($resultData);
} else {
    $resultData = array();
    $resultArray = array(
        "message" => "0"
    );
    array_push($resultData, $resultArray);
    echo json_encode($resultData);
}