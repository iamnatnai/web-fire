<?php
// ข้อมูลการเชื่อมต่อกับฐานข้อมูล
$servername = "localhost";
$db_username = "root"; // ชื่อผู้ใช้ฐานข้อมูล
$db_password = ""; // รหัสผ่านฐานข้อมูล (ถ้ามี)
$dbname = "fired_data";

try {
    // สร้างการเชื่อมต่อกับฐานข้อมูล
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $db_username, $db_password);
    // ตั้งค่า PDO ให้รายงานข้อผิดพลาดแบบข้อยกเว้น (Exception)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // ตั้งค่าให้ใช้โหมดแบบคำสั่งที่เตรียมไว้ (Prepared Statements)
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // แสดงข้อผิดพลาดถ้าไม่สามารถเชื่อมต่อกับฐานข้อมูลได้
    die("Connection failed: " . $e->getMessage());
}
?>