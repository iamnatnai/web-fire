<?php
session_start();
include('config.php'); // เชื่อมต่อกับฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ตรวจสอบข้อมูลจากฐานข้อมูล
    $query = "SELECT users.*, roles.role_name FROM users 
              JOIN roles ON users.role_id = roles.id 
              WHERE username = ?"; // ปรับคำสั่ง SQL เพื่อรวม role
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // ตรวจสอบสถานะ active
        if ($user['active'] == 0) {
            echo "suspended"; // เพิ่ม response เป็น "suspended" หากบัญชีถูกระงับ
        } else {
            // ตรวจสอบรหัสผ่าน
            if (password_verify($password, $user['password'])) {
                // เก็บข้อมูลใน session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['firstname'] = $user['first_name'];
                $_SESSION['lastname'] = $user['last_name'];
                $_SESSION['role'] = $user['role_name']; // เก็บ role ของผู้ใช้ใน session
                echo "success";
            } else {
                echo "รหัสผ่านไม่ถูกต้อง";
            }
        }
    } else {
        echo "ไม่พบบัญชีนี้ในระบบ";
    }

    $stmt->close();
    $conn->close();
}
?>
