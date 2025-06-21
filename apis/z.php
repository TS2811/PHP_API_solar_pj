<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
date_default_timezone_set('Asia/Bangkok');

require_once '../connectDB.php';
require_once '../models/sensor.php';

$connectDB = new connectDB();
$sensor = new sensor($connectDB->getConnectionDB());

$result = $sensor->getOneData();

$resultArray = array();
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    extract($row);
    $resultArray[] = array(
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

// ฟังก์ชันสำหรับการส่งอีเมล
function sendEmail($to, $cc, $subject, $message)
{
    $subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
    $message = wordwrap($message, 1200, "\r\n");

    $headers = "From: ESP32 System <solarpanelsauproject2024@gmail.com>\r\n" .
        "Reply-To: solarpanelsauproject2024@gmail.com\r\n" .
        "Cc: " . implode(',', $cc) . "\r\n" .
        "MIME-Version: 1.0\r\n" .
        "Content-Type: text/html; charset=UTF-8\r\n" .
        "X-Mailer: PHP/" . phpversion();

    if (mail($to, $subject, $message, $headers)) {
        echo json_encode(["message" => "Email sent successfully"]);
    } else {
        echo json_encode(["message" => "Failed to send email"]);
    }
}

// ข้อมูลสำหรับส่งอีเมล
$to = 'S6419410003@sau.ac.th';
$cc = ['S6419410007@sau.ac.th', 'S6419410026@sau.ac.th', 'S6419410028@sau.ac.th'];
$subject = "Test Email";
$msg = "<html><body>";
$msg .= "<h2 style='color: red;'>test email</h2>";
$msg .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
$msg .= "<tr><th>Current</th><th>Voltage</th><th>Temperature</th><th>Humidity</th><th>Light1</th><th>Light2</th><th>Light3</th><th>sendDateTime</th></tr>";
foreach ($resultArray as $data) {
    $msg .= "<tr><td>{$data['current']}</td><td>{$data['voltage']}</td><td>{$data['Temperature']}</td><td>{$data['Humidity']}</td><td>{$data['Light1']}</td><td>{$data['Light2']}</td><td>{$data['Light3']}</td><td>{$data['sendDateTime']}</td></tr>";
}
$msg .= "</table>";
$msg .= "</body></html>";

// ส่งอีเมล
sendEmail($to, $cc, $subject, $msg);
