<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once '../connectDB.php';
require_once '../models/user.php';

$connectDB = new connectDB();
$user = new user($connectDB->getConnectionDB());

$data = json_decode(file_get_contents("php://input"));

$img_filename = "";
$allowed_file_types = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];

if ($data->imageName != "") {
    // ตรวจสอบประเภทของไฟล์
    $image_data = base64_decode($data->imageName);
    $finfo = finfo_open();
    $mime_type = finfo_buffer($finfo, $image_data, FILEINFO_MIME_TYPE);
    finfo_close($finfo);

    if (in_array($mime_type, $allowed_file_types)) {
        // สร้างชื่อไฟล์ใหม่โดยใช้นามสกุลของไฟล์
        $img_extension = ($mime_type == 'image/png') ? '.png' : (($mime_type == 'image/jpeg' || $mime_type == 'image/jpg') ? '.jpg' : '.gif');
        $img_filename = "user_" . $data->userName . "_" . uniqid() . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT) . "_" . time() . $img_extension;

        file_put_contents("./../images/" . $img_filename, $image_data);
    }
}

$result = $user->updateUser(
    $data->userID,
    $data->username ?? null,
    $data->password ?? null,
    $data->email ?? null,
    $data->phone ?? null,
    $data->location ?? null,
    $img_filename
);

if ($result == true) {
    $resultData = array();
    $resultArray = array(
        "message" => "1"
    );
    array_push($resultData, $resultArray);
    echo json_encode($resultData);
} else {
    $resultData = array();
    $resultArray = array(
        "message" => "0"
    );
    array_push($resultData, $resultArray);
    echo json_encode($resultData);
}
