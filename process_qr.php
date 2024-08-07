<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "fired_data";

$servername = "localhost";
$username = "kasemra2_dcc"; // ชื่อผู้ใช้ฐานข้อมูล
$password = "123456"; // รหัสผ่านฐานข้อมูล
$dbname = "kasemra2_dcc"; // ชื่อฐานข้อมูล
// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// รับข้อมูลจาก POST
$data = $_POST['qrData'] ?? null;

if (!$data) {
    echo json_encode(["status" => "error", "message" => "QR Code data missing"]);
    $conn->close();
    exit();
}

// ป้องกัน SQL Injection
$data = $conn->real_escape_string($data);

// ค้นหาข้อมูลในฐานข้อมูล
$sql = "SELECT * FROM fire_extinguisher WHERE FCODE='$data'";
$result = $conn->query($sql);

$response = [];

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $response = [
        "status" => "found",
        "data" => [
            "FCODE" => $row["FCODE"],
            "F_water" => $row["F_water"],
            "F_located" => $row["F_located"],
            "image_path" => $row["image_path"]
        ]
    ];
} else {
    $response = [
        "status" => "not_found"
    ];
}

$conn->close();
echo json_encode($response);
?>
