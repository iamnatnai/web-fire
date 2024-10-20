<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../config.php'); // Ensure this file correctly sets up $conn

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? $_POST['username'] : null;
    $firstName = isset($_POST['firstName']) ? $_POST['firstName'] : null;
    $lastName = isset($_POST['lastName']) ? $_POST['lastName'] : null;
    $password = isset($_POST['password']) ? $_POST['password'] : null;
    $role = isset($_POST['role']) ? $_POST['role'] : null;

    if ($username && $firstName && $lastName && $password && $role) {
        // Check for duplicate username
        $checkUsername = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $checkUsername->bind_param('s', $username);
        $checkUsername->execute();
        $checkUsername->store_result();

        if ($checkUsername->num_rows > 0) {
            echo 'Username is already taken.';
            exit();
        }

        $checkUsername->close();

        // Prepare SQL statement
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, first_name, last_name, password, role_id) VALUES (?, ?, ?, ?, (SELECT id FROM roles WHERE role_name = ?))";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssss', $username, $firstName, $lastName, $hashedPassword, $role);

        // Execute query
        if ($stmt->execute()) {
            echo 'User added successfully.';
        } else {
            echo 'Error adding user: ' . $stmt->error;
        }

        $stmt->close();
    } else {
        echo 'Error: Missing required fields.';
    }
} else {
    echo 'Invalid request method.';
}

$conn->close();
?>
