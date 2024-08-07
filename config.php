<?php
// config.php
// $servername = "localhost";
// $username = "root"; // ชื่อผู้ใช้ฐานข้อมูล
// $password = ""; // รหัสผ่านฐานข้อมูล
// $dbname = "fired_data"; // ชื่อฐานข้อมูล

$servername = "localhost";
$username = "kasemra2_dcc"; // ชื่อผู้ใช้ฐานข้อมูล
$password = "123456"; // รหัสผ่านฐานข้อมูล
$dbname = "kasemra2_dcc"; // ชื่อฐานข้อมูล

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>
