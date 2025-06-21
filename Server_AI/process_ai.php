<?php
// กำหนดการแสดงข้อผิดพลาด
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Bangkok');

// รันโมเดล AI
try {
    $randomForestResponse = file_get_contents("http://119.59.117.37/myProjact/random_forest.php");
    $KNN_Response = file_get_contents("http://119.59.117.37/myProjact/knn_model.php");
    $naiveBayesResponse = file_get_contents("http://119.59.117.37/myProjact/naive_bayes_model.php");
    $decisionTreeResponse = file_get_contents("http://119.59.117.37/myProjact/decision_tree_model.php");
    $gbr_Response = file_get_contents("http://119.59.117.37/myProjact/gbr_model.php");

    // บันทึกผลลัพธ์ลงไฟล์ (เช็คว่าประมวลผลเสร็จ)
    file_put_contents("ai_result_log.txt", date("Y-m-d H:i:s") . " AI Processing Completed\n", FILE_APPEND);

    // จัดการผลลัพธ์ที่ได้จากโมเดล AI
    $responseData = [];
    $responseData["gbr_Response"] = json_decode($gbr_Response, true);
    $responseData["randomForestResponse"] = json_decode($randomForestResponse, true);
    $responseData["KNN_Response"] = json_decode($KNN_Response, true);
    $responseData["decisionTreeResponse"] = json_decode($decisionTreeResponse, true);
    $responseData["naiveBayesResponse"] = json_decode($naiveBayesResponse, true);

    // ส่งข้อมูลผลลัพธ์ไปยัง API อื่น
    $respondDataToText = json_encode($responseData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $result = file_get_contents("https://solarpanelsauproject.com/PHP/apis/aiResult.php", false, stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\n",
            'content' => $respondDataToText
        ]
    ]));

    // ตรวจสอบการตอบกลับจาก API
    if ($result === FALSE) {
        // บันทึกข้อผิดพลาดหากมี
        file_put_contents("ai_result_log.txt", date("Y-m-d H:i:s") . " Error sending result to API\n", FILE_APPEND);
    } else {
        // บันทึกผลลัพธ์ที่ได้รับจาก API
        file_put_contents("ai_result_log.txt", date("Y-m-d H:i:s") . " Result sent to API successfully: " . $result . "\n", FILE_APPEND);
    }
} catch (Exception $e) {
    // บันทึกข้อผิดพลาด
    file_put_contents("ai_result_log.txt", date("Y-m-d H:i:s") . " Error: " . $e->getMessage() . "\n", FILE_APPEND);
}
?>
