
<?php
session_start();
include('../config.php');
include 'navbar.php';
// Ensure user is admin
if ($_SESSION['role'] !== 'Admin') {
    header('Location: ../index.php');
    exit();
}

$query = "SELECT users.*, roles.role_name AS role FROM users JOIN roles ON users.role_id = roles.id";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<!-- Include SweetAlert CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- Include SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<h1>Manage Users</h1>

<div>
    <input type="text" id="search-box" placeholder="Search by username or details" onkeyup="searchUsers()">
    <br></br>
</div>
<div>
    <button id="add-user-btn">Add User</button>
    <br></br>
</div>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>ชื่อจริง</th>
            <th>แผนก</th>
            <th>ตำแหน่ง</th>
            <th>สิทธิ์การใช้งาน</th>
            <th>แก้ไข</th>
        </tr>
    </thead>
    <tbody>
    <?php while($user = $result->fetch_assoc()): ?>
        <?php if ($user['username'] === 'IT') continue; ?>
        <tr>
            <td data-label="ID"><?php echo $user['id']; ?></td>
            <td data-label="Username"><?php echo $user['username']; ?></td>
            <td data-label="Firstname"><?php echo $user['first_name']; ?></td>
            <td data-label="Lastname"><?php echo $user['last_name']; ?></td>
            <td data-label="Role"><?php echo $user['role']; ?></td>
            <td data-label="Active">
                <label class="switch">
                    <input type="checkbox" class="toggle-active" data-user-id="<?php echo $user['id']; ?>" <?php echo $user['active'] ? 'checked' : ''; ?>>
                    <span class="slider round"></span>
                </label>
            </td>
            <td data-label="Actions">
                <button class="editBtn" data-user-id="<?php echo $user['id']; ?>" data-username="<?php echo $user['username']; ?>" data-first-name="<?php echo $user['first_name']; ?>" data-last-name="<?php echo $user['last_name']; ?>">Edit</button>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>
</table>

<script src="script.js"></script>
</body>
</html>

<?php $conn->close(); ?>
