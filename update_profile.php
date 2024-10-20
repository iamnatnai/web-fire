<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$userId = $_SESSION['user_id'];
$username = $_POST['username'];
$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$currentPassword = $_POST['currentPassword'];
$newPassword = $_POST['newPassword'];

// ตรวจสอบรหัสผ่านเดิม
$query = "SELECT password FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!password_verify($currentPassword, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
    exit();
}

// อัปเดตโปรไฟล์
$updateQuery = "UPDATE users SET username = ?, first_name = ?, last_name = ?";
$params = [$username, $firstName, $lastName];
$types = 'sss';

if (!empty($newPassword)) {
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateQuery .= ", password = ?";
    $params[] = $newPasswordHash;
    $types .= 's';
}

$updateQuery .= " WHERE id = ?";
$params[] = $userId;
$types .= 'i';

$stmt = $conn->prepare($updateQuery);
$stmt->bind_param($types, ...$params);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'บัญชีคุณถูกอัปเดตแล้ว']);
} else {
    echo json_encode(['success' => false, 'message' => 'คุณไม่ได้ทำการเปลี่ยนแปลงค่าใดๆหรือค่าไม่ได้บันทึก']);
}

$stmt->close();
$conn->close();
?>
