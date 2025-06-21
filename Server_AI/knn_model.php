<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

$pythonOutput = shell_exec("python C:/xampp/htdocs/myProjact/knn_model.py");

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

$result = file_get_contents("https://solarpanelsauproject.com/PHP/apis/addAiReport.php", false, stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n",
        'content' => json_encode([
            "report" => $AIresult["predicted_output"],
            "modelAIname" => "KNN",
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

// if ($AIresult["predicted_output"] > 0) {
//     $rClean = file_get_contents("https://solarpanelsauproject.com/PHP/apis/Ordered_cleanning_By_AI.php", false, stream_context_create([
//         'http' => [
//             'method'  => 'POST',
//             'header'  => "Content-Type: application/json\r\n",
//             'content' => json_encode([
//                 "DateTime" => $data["DateTime"] ?? date("Y-m-d H:i:s"),
//                 "Clean" => 1,
//                 "user" => 0
//             ])
//         ]
//     ]));
// }

// $decodedrClean = json_decode($rClean, true);

// if (is_array($decodedrClean) && isset($decodedrClean["message"])) {
//     $responseData["rClean"] = $decodedrClean["message"];
// } else {
//     $responseData["rClean"] = "Error: Invalid API Response";
// }

echo json_encode([$responseData]);
