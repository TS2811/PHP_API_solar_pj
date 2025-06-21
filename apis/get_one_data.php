<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once '../connectDB.php';
require_once '../models/sensor.php';

$connectDB = new connectDB();
$sensor = new sensor($connectDB->getConnectionDB());

$data = json_decode(file_get_contents("php://input"));

$result = $sensor->getOneData($data->eqpID);

if ($result->rowCount() > 0) {
    $resultData = array();
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $resultArray = array(
            "message" => "1",
            "current" => $current,
            "voltage" => $voltage,
            "Temperature" => $Temperature,
            "Humidity" => $Humidity,
            "Light1" => $Light1,
            "Light2" => $Light2,
            "Light3" => $Light3,
            "sendDateTime" => $SendDateTime,
            "eqpID" => $eqpID
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
