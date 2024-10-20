<?php
include 'config.php';

// รับข้อมูลที่ส่งมาจากฟอร์ม
$qrData = $_POST['qrData'];

// ป้องกัน SQL Injection
$qrData = $conn->real_escape_string($qrData);

// ค้นหาข้อมูลในฐานข้อมูลตามค่า QR Code ที่ได้
$sql = "SELECT * FROM fire_extinguisher WHERE FCODE='$qrData'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // แสดงผลข้อมูลที่พบ
    while($row = $result->fetch_assoc()) {
        echo "FCODE: " . $row["FCODE"] . "<br>";
        echo "F_water: " . $row["F_water"] . "<br>";
        echo "F_located: " . $row["F_located"] . "<br>";
        echo '<img src="' . $row["image_path"] . '" alt="Image"><br>';
    }
} else {
    echo "No results found.";
}

$conn->close();
?>
