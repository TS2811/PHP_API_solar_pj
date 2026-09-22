<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
date_default_timezone_set('Asia/Bangkok');

require_once '../connectDB.php';
require_once '../models/sensor.php';
require_once '../models/report_log.php';

$connectDB = new connectDB();
$sensor = new sensor($connectDB->getConnectionDB());
$report_log = new report_log($connectDB->getConnectionDB());

$result = $sensor->getOneData();

if (!$result || $result->rowCount() === 0) {
    echo json_encode(["message" => "No data found"]);
    exit;
}

$resultsensor = array();
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    extract($row);
    $resultsensor = array(
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
}

function sendDiscordWebhook($message)
{
    $webhookURL = "";
    $data = ["content" => $message, "allowed_mentions" => ["parse" => ["everyone"]]];

    $options = [
        "http" => [
            "header" => "Content-Type: application/json\r\n",
            "method" => "POST",
            "content" => json_encode($data),
        ],
    ];

    $context = stream_context_create($options);
    file_get_contents($webhookURL, false, $context);
}


$DateTime = date("Y-m-d H:i:s");

$dmsg = "**♻ แสดงค่าล่าสุด ♻**\n" .
    "- ⏳ อัปเดตล่าสุดเมื่อ **" . floor((strtotime($DateTime) - strtotime($resultsensor['sendDateTime'])) / 60) . " นาทีที่ผ่านมา**\n" .
    "- 📡 Current: {$resultsensor['current']} A\n" .
    "- ⚡ Voltage: {$resultsensor['voltage']} V\n" .
    "- 🌡 Temperature: {$resultsensor['Temperature']} °C\n" .
    "- 💧 Humidity: {$resultsensor['Humidity']} %\n" .
    "- ☀️ Light1: {$resultsensor['Light1']} lux\n" .
    "- ☀️ Light2: {$resultsensor['Light2']} lux\n" .
    "- ☀️ Light3: {$resultsensor['Light3']} lux\n" .
    "- 📅 Timestamp: {$resultsensor['sendDateTime']}\n\n";

sendDiscordWebhook($dmsg);
