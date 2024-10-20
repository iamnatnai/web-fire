<?php
// เชื่อมต่อกับฐานข้อมูล
include 'config.php'; // เพิ่มเครื่องหมาย ;

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// รับข้อมูลที่ค้นหา
$q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';

// คำสั่ง SQL ค้นหาจากฐานข้อมูล
$sql = "SELECT FCODE, F_located FROM fire_extinguisher WHERE FCODE LIKE '%$q%' OR F_located LIKE '%$q%'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div onclick="selectTank(\'' . $row['FCODE'] . '\')">';
        echo $row['FCODE'] . ' - ' . $row['F_located'];
        echo '</div>';
    }
} else {
    echo '<div>ไม่พบข้อมูล</div>';
}

$conn->close();
?>
