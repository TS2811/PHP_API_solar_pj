<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST"); // เปลี่ยนเป็น POST
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once '../connectDB.php';
require_once '../models/user.php';

$connectDB = new connectDB();
$user = new user($connectDB->getConnectionDB());

$data = json_decode(file_get_contents("php://input"));

$result = $user->login($data->username, $data->password);

if ($result) {
    $base64Image = null;
    if (!empty($result['imageName'])) {
        $imagePath = __DIR__ . "/../images/" . $result['imageName'];
        if (file_exists($imagePath)) {
            $imageData = file_get_contents($imagePath);
            $base64Image = base64_encode($imageData);
        }
    }

    $resultArray = array(
        "message" => "1",
        "userID" => strval($result['userID']),
        "username" => $result['username'],
        "password" => "", // ไม่ควรส่ง password กลับไป
        "email" => $result['email'],
        "phone" => $result['phone'],
        "location" => $result['location'],
        "imageName" => $base64Image,
        "updated_at" => $result['updated_at'],
        "rank" => $result['rank']
    );

    echo json_encode([$resultArray]);
} else {
    echo json_encode([["message" => "0"]]);
}
