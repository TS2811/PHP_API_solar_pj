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

$resultReport_log = $report_log->check_report($resultsensor['sendDateTime']);

$resultLog = ["note" => 0];
if ($resultReport_log && $resultReport_log->rowCount() > 0) {
    while ($row = $resultReport_log->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $resultLog = array(
            "message" => "1",
            "reportID" => $reportID,
            "reportContent" => $reportContent,
            "logDate" => $logDate,
            "note" => intval($note),
            "eqpID" => $eqpID,
        );
    }
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
        return "success";
    } else {
        return "fail";
    }
}

function sendDiscordWebhook($message)
{
    $webhookURL = "https://discordapp.com/api/webhooks/1352918426368213013/jnRnqCFrBWZlALNJaqLKGSHGy7FYzQWemkINAx7QKunMZhttdvmvZ_QRpEzHobufhe4G"; // ใส่ Webhook URL ของคุณ

    $data = ["content" => $message];

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

// ตรวจสอบกรณีที่ไม่มีข้อมูล
if (empty($resultsensor)) {
    echo json_encode(["message" => "No data found"]);
    exit;
}

// ตรวจสอบเวลาว่าผ่านไปเกิน 20 นาทีหรือไม่
if ((strtotime($DateTime) - strtotime($resultsensor['sendDateTime'])) > 1200) {
    try {
        if ($resultReport_log && $resultReport_log->rowCount() > 0) {
            if ((strtotime($DateTime) - strtotime($resultsensor['sendDateTime'])) > (300 + (900 * ($resultLog['note'] + 1)))) {
                // ข้อมูลสำหรับส่งอีเมล
                $to = 'S6419410003@sau.ac.th';
                $cc = ['S6419410007@sau.ac.th', 'S6419410026@sau.ac.th', 'S6419410028@sau.ac.th'];
                $subject = "รายงานปัญหาที่เกิดขึ้น";
                $msg = "<html><body>";
                $msg .= "<h1 style='color: red;'>ESP32 มีปัญหา ไม่สามารถส่งข้อมูลได้</h1>";
                $msg .= "<h2>เป็นเวลา <u>" . floor((strtotime($DateTime) - strtotime($resultsensor['sendDateTime'])) / 60) . "</u> นาที</h2>";
                $msg .= "<h3>ข้อมูลล่าสุด</h3>";
                $msg .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
                $msg .= "<tr><td>Current</td><td>{$resultsensor['current']}</td></tr>";
                $msg .= "<tr><td>Voltage</td><td>{$resultsensor['voltage']}</td></tr>";
                $msg .= "<tr><td>Temperature</td><td>{$resultsensor['Temperature']}</td></tr>";
                $msg .= "<tr><td>Humidity</td><td>{$resultsensor['Humidity']}</td></tr>";
                $msg .= "<tr><td>Light1</td><td>{$resultsensor['Light1']}</td></tr>";
                $msg .= "<tr><td>Light2</td><td>{$resultsensor['Light2']}</td></tr>";
                $msg .= "<tr><td>Light3</td><td>{$resultsensor['Light3']}</td></tr>";
                $msg .= "<tr><td>Send DateTime</td><td>{$resultsensor['sendDateTime']}</td></tr>";
                $msg .= "</table>";
                $msg .= "<p><strong>กรุณาดำเนินการแก้ไขโดย<b style='color: red; font-size: 30px;'>\"ด่วน\"</b>ที่สุด</strong></p>";
                $msg .= "</body></html>";
                $emaliStatus = sendEmail($to, $cc, $subject, $msg);

                $dmsg = "**⚠️ แจ้งเตือนระบบ ESP32 ⚠️**\n" .
                    "ESP32 มีปัญหา ไม่สามารถส่งข้อมูลได้เป็นเวลา **" . floor((strtotime($DateTime) - strtotime($resultsensor['sendDateTime'])) / 60) . " นาที**\n\n" .
                    "**ข้อมูลล่าสุด:**\n" .
                    "- 📡 Current: {$resultsensor['current']} A\n" .
                    "- ⚡ Voltage: {$resultsensor['voltage']} V\n" .
                    "- 🌡 Temperature: {$resultsensor['Temperature']} °C\n" .
                    "- 💧 Humidity: {$resultsensor['Humidity']} %\n" .
                    "- ☀️ Light1: {$resultsensor['Light1']} lux\n" .
                    "- ☀️ Light2: {$resultsensor['Light2']} lux\n" .
                    "- ☀️ Light3: {$resultsensor['Light3']} lux\n" .
                    "- 📅 Timestamp: {$resultsensor['sendDateTime']}\n\n" .
                    "**กรุณาดำเนินการแก้ไขโดยด่วน!** 🚨\n@everyone \n\n";

                sendDiscordWebhook($dmsg);

                if ($emaliStatus === "fail") {
                    error_log("Failed to send email");
                }

                $report_log->update_report($resultLog['reportID'], $resultLog['note'] + 1);
                echo json_encode(["message" => "Latest update : " . ((strtotime($DateTime) - strtotime($resultsensor['sendDateTime'])) / 60) . " minute", "data" => $resultsensor, "emaliStatus" => $emaliStatus]);
            } else {
                echo json_encode(["message" => "Latest update : " . ((strtotime($DateTime) - strtotime($resultsensor['sendDateTime'])) / 60) . " minute", "data" => $resultsensor]);
            }
        } else {
            $reportContent = "ไม่สามารถส่งข้อมูลไป DB ได้";
            $report_log->add_error_report($reportContent, $resultsensor['sendDateTime'], 1, $resultsensor['eqpID']);
            // ข้อมูลสำหรับส่งอีเมล
            $to = 'S6419410003@sau.ac.th';
            $cc = ['S6419410007@sau.ac.th', 'S6419410026@sau.ac.th', 'S6419410028@sau.ac.th'];
            $subject = "รายงานปัญหาที่เกิดขึ้น";
            $msg = "<html><body>";
            $msg .= "<h1 style='color: red;'>ESP32 มีปัญหา ไม่สามารถส่งข้อมูลได้</h1>";
            $msg .= "<h2>เป็นเวลา <u>" . floor((strtotime($DateTime) - strtotime($resultsensor['sendDateTime'])) / 60) . "</u> นาที</h2>";
            $msg .= "<h3>ข้อมูลล่าสุด</h3>";
            $msg .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
            $msg .= "<tr><td>Current</td><td>{$resultsensor['current']}</td></tr>";
            $msg .= "<tr><td>Voltage</td><td>{$resultsensor['voltage']}</td></tr>";
            $msg .= "<tr><td>Temperature</td><td>{$resultsensor['Temperature']}</td></tr>";
            $msg .= "<tr><td>Humidity</td><td>{$resultsensor['Humidity']}</td></tr>";
            $msg .= "<tr><td>Light1</td><td>{$resultsensor['Light1']}</td></tr>";
            $msg .= "<tr><td>Light2</td><td>{$resultsensor['Light2']}</td></tr>";
            $msg .= "<tr><td>Light3</td><td>{$resultsensor['Light3']}</td></tr>";
            $msg .= "<tr><td>Send DateTime</td><td>{$resultsensor['sendDateTime']}</td></tr>";
            $msg .= "</table>";
            $msg .= "<p><strong>กรุณาดำเนินการแก้ไขโดย<b style='color: red; font-size: 30px;'>\"ด่วน\"</b>ที่สุด</strong></p>";
            $msg .= "</body></html>";
            $emaliStatus = sendEmail($to, $cc, $subject, $msg);

            $dmsg = "**⚠️ แจ้งเตือนระบบ ESP32 ⚠️**\n" .
                "ESP32 มีปัญหา ไม่สามารถส่งข้อมูลได้เป็นเวลา **" . floor((strtotime($DateTime) - strtotime($resultsensor['sendDateTime'])) / 60) . " นาที**\n\n" .
                "**ข้อมูลล่าสุด:**\n" .
                "- 📡 Current: {$resultsensor['current']} A\n" .
                "- ⚡ Voltage: {$resultsensor['voltage']} V\n" .
                "- 🌡 Temperature: {$resultsensor['Temperature']} °C\n" .
                "- 💧 Humidity: {$resultsensor['Humidity']} %\n" .
                "- ☀️ Light1: {$resultsensor['Light1']} lux\n" .
                "- ☀️ Light2: {$resultsensor['Light2']} lux\n" .
                "- ☀️ Light3: {$resultsensor['Light3']} lux\n" .
                "- 📅 Timestamp: {$resultsensor['sendDateTime']}\n\n" .
                "**กรุณาดำเนินการแก้ไขโดยด่วน!** 🚨\n@everyone \n\n";

            sendDiscordWebhook($dmsg);

            if ($emaliStatus === "fail") {
                error_log("Failed to send email");
            }
            echo json_encode(["message" => "Latest update : " . ((strtotime($DateTime) - strtotime($resultsensor['sendDateTime'])) / 60) . " นาที", "data" => $resultsensor, "emaliStatus" => $emaliStatus]);
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo json_encode(["message" => "An error occurred", "error" => $e->getMessage()]);
    }
} else {
    echo json_encode(["message" => "Latest update : " . ((strtotime($DateTime) - strtotime($resultsensor['sendDateTime'])) / 60) . " minute ", "data" => $resultsensor]);
}