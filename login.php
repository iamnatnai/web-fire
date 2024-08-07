<?php
session_start();
include('config.php'); // เชื่อมต่อกับฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ตรวจสอบข้อมูลจากฐานข้อมูล
    $query = "SELECT * FROM users WHERE username = ?"; // ปรับคำสั่ง SQL ตามฐานข้อมูลของคุณ
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            echo "success";
        } else {
            echo "Incorrect password";
        }
    } else {
        echo "Username not found";
    }

    $stmt->close();
    $conn->close();
}
?>
