<?php
require 'configregis.php'; // เรียกใช้การตั้งค่าฐานข้อมูลจาก config.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // ตรวจสอบว่าชื่อผู้ใช้หรือรหัสผ่านไม่ว่างเปล่า
    if (empty($username) || empty($password)) {
        die("Username or password cannot be empty.");
    }

    // แฮชรหัสผ่าน
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // ตรวจสอบว่าชื่อผู้ใช้ซ้ำหรือไม่
    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        die("Username already exists. Please choose another one.");
    }

    // เพิ่มผู้ใช้ใหม่ลงในฐานข้อมูล
    $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute(['username' => $username, 'password' => $hashedPassword])) {
        echo "User registered successfully!";
    } else {
        echo "There was an error registering the user.";
    }
}
?>