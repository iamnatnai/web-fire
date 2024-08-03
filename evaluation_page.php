<?php
header('Content-Type: application/json');
$servername = "localhost"; // ชื่อเซิร์ฟเวอร์ฐานข้อมูล
$username = "root"; // ชื่อผู้ใช้ฐานข้อมูล
$password = ""; // รหัสผ่านฐานข้อมูล
$dbname = "fired_data"; // ชื่อฐานข้อมูล

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// รับข้อมูลจากฟอร์ม
$data = json_decode(file_get_contents('php://input'), true);

// ป้องกันข้อมูลจากการโจมตี
$seal = $conn->real_escape_string($data['seal']);
$pressure = $conn->real_escape_string($data['pressure']);
$hose = $conn->real_escape_string($data['hose']);
$body = $conn->real_escape_string($data['body']);

// ตัวอย่างการบันทึกข้อมูลลงในฐานข้อมูล
$sql = "INSERT INTO evaluations (seal, pressure, hose, body) VALUES ('$seal', '$pressure', '$hose', '$body')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Evaluation recorded successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Error: " . $sql . "<br>" . $conn->error]);
}

$conn->close();
?>