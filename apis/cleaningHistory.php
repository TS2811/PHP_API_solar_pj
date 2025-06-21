<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

require_once '../connectDB.php';
require_once '../models/cleaning_schedule.php';
require_once '../models/sensor.php';
require_once '../models/user.php';

$connectDB = new connectDB();
$cleaning_schedule = new cleaning_schedule($connectDB->getConnectionDB());
$sensor = new sensor($connectDB->getConnectionDB());
$user = new user($connectDB->getConnectionDB());


$data = json_decode(file_get_contents("php://input"));

$result = $cleaning_schedule->getAllCleaningSchedule();

if ($result->rowCount() > 0) {
    $resultData = array();
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $resultArray = array(
            "message" => "1",
            "cleaningDate" => $cleaningDate,
        );

        if ($OrderedBy == 0) {
            $resultArray["OrderedBy"] = "AI";
        } else {
            $User = $user->getByUserID($OrderedBy)->fetch(PDO::FETCH_ASSOC);
            $resultArray["OrderedBy"] = $User["username"];
        }
        $B = $sensor->getBefore($cleaningDate)->fetch(PDO::FETCH_ASSOC);
        $A = $sensor->getAfter($cleaningDate)->fetch(PDO::FETCH_ASSOC);

        $resultArray["PowerBefore"] = ($B) ? ($B["current"] * $B["voltage"]) : null;
        $resultArray["PowerAfter"] = ($A) ? ($A["current"] * $A["voltage"]) : null;
        array_push($resultData, $resultArray);
    }
    echo json_encode($resultData);
} else {
    $resultData = array();
    $resultArray = array(
        "message" => "0"
    );
    array_push($resultData, $resultArray);
    echo json_encode($resultData);
}
