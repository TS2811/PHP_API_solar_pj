<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

$pythonOutput = shell_exec("python C:/xampp/htdocs/myProjact/gbr_model.py");

if (!$pythonOutput) {
    echo json_encode(["message" => "0", "error" => "Python script execution failed"]);
    exit();
}

$AIresult = json_decode($pythonOutput, true);

if (!$AIresult || !isset($AIresult["predicted_output"])) {
    echo json_encode(["message" => "0", "error" => $AIresult['error'] ?? "AI processing failed"]);
    exit();
}

$responseData["message"] = "1";
$responseData["predicted_output"] = $AIresult["predicted_output"];

$file_path = "C:/xampp/htdocs/myProjact/pythonInput.json";
$jsonContent = file_get_contents($file_path);

$data = json_decode($jsonContent, true); // แปลง JSON เป็น array

$resultGBR = (($AIresult["predicted_output"] - ($data["current"] * $data["voltage"])) /
    (($AIresult["predicted_output"] + ($data["current"] * $data["voltage"])) / 2)) * 100;

// ทำให้เป็นทศนิยม 3 ตำแหน่ง และค่าต่ำกว่าศูนย์เป็น 0
$resultGBR = max(0, round($resultGBR, 3));

$responseData["resultGBR"] = $resultGBR;

// เรียก API บันทึกข้อมูล
$result = file_get_contents("https://solarpanelsauproject.com/PHP/apis/addAiReport.php", false, stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n",
        'content' => json_encode([
            "report" => $resultGBR,
            "modelAIname" => "GBR",
            "senserTime" => $data["DateTime"] ?? "N/A"
        ])
    ]
]));

$decodedResult = json_decode($result, true);

if (is_array($decodedResult) && isset($decodedResult["message"])) {
    $responseData["AIresultToDB"] = $decodedResult["message"];
} else {
    $responseData["AIresultToDB"] = "Error: Invalid API Response";
}

$result = file_get_contents("https://solarpanelsauproject.com/PHP/apis/addAiReport.php", false, stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n",
        'content' => json_encode([
            "report" => $AIresult["predicted_output"],
            "modelAIname" => "GBR_Power",
            "senserTime" => $data["DateTime"] ?? "N/A"
        ])
    ]
]));

$decodedResult = json_decode($result, true);

if (is_array($decodedResult) && isset($decodedResult["message"])) {
    $responseData["AIresultToDB_P"] = $decodedResult["message"];
} else {
    $responseData["AIresultToDB_P"] = "Error: Invalid API Response";
}

echo json_encode([$responseData]);
