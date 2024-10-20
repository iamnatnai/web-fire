<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../config.php'); // Ensure this file correctly sets up $conn

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if required fields are present
    $userId = isset($_POST['userId']) ? $_POST['userId'] : null;
    $newFirstName = isset($_POST['newFirstName']) ? $_POST['newFirstName'] : null;
    $newLastName = isset($_POST['newLastName']) ? $_POST['newLastName'] : null;
    $newUsername = isset($_POST['newUsername']) ? $_POST['newUsername'] : null;
    $newPassword = isset($_POST['newPassword']) ? $_POST['newPassword'] : '';
    $newRole = isset($_POST['newRole']) ? $_POST['newRole'] : null;

    if ($userId && $newFirstName && $newLastName && $newUsername && $newRole) {
        // Check for duplicate username
        $checkUsername = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $checkUsername->bind_param('si', $newUsername, $userId);
        $checkUsername->execute();
        $checkUsername->store_result();

        if ($checkUsername->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'ชื่อผู้ใช้นี้ถูกใช้แล้ว']);
            exit();
        }

        $checkUsername->close();

        // Prepare SQL statement
        $sql = "UPDATE users SET first_name = ?, last_name = ?, username = ?, role_id = (SELECT id FROM roles WHERE role_name = ?)" . ($newPassword ? ", password = ?" : "") . " WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($newPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt->bind_param('sssssi', $newFirstName, $newLastName, $newUsername, $newRole, $hashedPassword, $userId);
        } else {
            $stmt->bind_param('ssssi', $newFirstName, $newLastName, $newUsername, $newRole, $userId);
        }

        // Execute query
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating user: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: Missing required fields.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();

?>
