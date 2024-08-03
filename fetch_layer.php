<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fired_data";

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// รับข้อมูลจากฐานข้อมูล
$sql = "SELECT layer_code, image_path, description FROM layerforfire";
$result = $conn->query($sql);

$data = [];

if ($result->num_rows > 0) {
    // ดึงข้อมูลจากแต่ละแถว
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode(["status" => "success", "data" => $data]);

$conn->close();
?>
