<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once '../connectDB.php';
require_once '../models/user.php';

$connectDB = new connectDB();
$user = new user($connectDB->getConnectionDB());

$data = json_decode(file_get_contents("php://input"));

$result = $user->login($data->username, $data->password);

if ($result->rowCount() > 0) {
    $resultData = array();
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $base64Image = null;
        if (!empty($imageName)) {
            $imagePath = __DIR__ . "/../images/" . $imageName; // เปลี่ยน path ตามโครงสร้างของคุณ
            if (file_exists($imagePath)) {
                $imageData = file_get_contents($imagePath);
                $base64Image = base64_encode($imageData);
            }
        }

        $resultArray = array(
            "message" => "1",
            "userID" => strval($userID),
            "username" => $username,
            "password" => $password,
            "email" => $email,
            "phone" => $phone,
            "location" => $location,
            "imageName" => $base64Image,
            "updated_at" => $updated_at,
            "rank" => $rank
        );
        array_push($resultData, $resultArray);
    }
    echo json_encode($resultData);
} else {
    $resultData = array();
    $resultArray = array(
        "massage" => "0"
    );
    array_push($resultData, $resultArray);
    echo json_encode($resultData);
}