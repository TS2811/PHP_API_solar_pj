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
        $resultArray = array(
            "message" => "1",
            "userID" => strval($userID),
            "username" => $username,
            "password" => $password,
            "email" => $email,
            "phone" => $phone,
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
