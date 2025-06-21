<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
date_default_timezone_set('Asia/Bangkok');

// รับค่า JSON จาก request
$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode(["message" => "0", "error" => "Invalid JSON"]);
    exit();
}

$pythonInput = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
file_put_contents("pythonInput.json", $pythonInput . "\n");

// เรียกใช้งาน AI เบื้องหลัง
$output = shell_exec("php C:\\xampp\\htdocs\\myProjact\\process_ai.php 2>&1"); // เปลี่ยน exec เป็น shell_exec

// บันทึกผลลัพธ์ของการเรียกใช้งาน
file_put_contents("exec_output_log.txt", date("Y-m-d H:i:s") . " Output: " . $output . "\n", FILE_APPEND);

// ส่งกลับข้อความสำเร็จ
echo json_encode(["message" => "1"]);
?>

<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
date_default_timezone_set('Asia/Bangkok');

// รับค่า JSON จาก request
$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode(["message" => "0", "error" => "Invalid JSON"]);
    exit();
}

$pythonInput = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
file_put_contents("pythonInput.json", $pythonInput . "\n");

// เรียกใช้งาน AI เบื้องหลัง
exec("C:\\xampp\\php\\php.exe C:\\xampp\\htdocs\\myProjact\\process_ai.php > NUL 2>&1 &");

// ส่งกลับข้อความสำเร็จ
echo json_encode(["message" => "1"]);
?>