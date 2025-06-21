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

$Response = file_get_contents("http://119.59.117.37/myProjact/collect_data.php", false, stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n",
        'content' => json_encode($data)
    ]
]));

$responseData['sendStatus'] = json_decode($Response, true);

$respondDataToText = json_encode($responseData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
file_put_contents("respondDataTo32.json", $respondDataToText . "\n");
echo json_encode([$responseData]);
?>