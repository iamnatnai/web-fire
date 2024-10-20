<?php
session_start();
include('config.php');
include('navbar.php');
// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่และเป็นเจ้าของบัญชีที่ต้องการแก้ไข
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Include SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>


<h1>Edit Profile</h1>
<form id="edit-profile-form">
            <h2>เมื่อต้องการเปลี่ยนแปลงข้อมูล ต้องกรอกรหัสผ่านเดิมทุกครั้ง</h2>
            <input type="hidden" id="user-id" value="<?php echo htmlspecialchars($user['id']); ?>">
            <label for="username"><i class="fas fa-user"></i> Username:</label>
            <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label for="first-name"><i class="fas fa-id-card"></i> ชื่อจริง:</label>
            <input type="text" id="first-name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>

            <label for="last-name"><i class="fas fa-id-card-alt"></i> แผนก:</label>
            <input type="text" id="last-name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>

            <label for="current-password"><i class="fas fa-lock"></i> รหัสผ่านเดิม:</label>
            <input type="password" id="current-password" required>

            <label for="new-password"><i class="fas fa-key"></i> รหัสผ่านใหม่:</label>
            <h3>*กรอกเมื่อต้องการเปลี่ยนรหัสผ่านใหม่เท่านั้น*</h3>
            <input type="password" id="new-password">
            <br></br>
            <button type="submit"><i class="fas fa-save"></i> Update</button>
</form>
<script src="edit_profile.js"></script>

</body>
</html>

<?php $conn->close(); ?>
