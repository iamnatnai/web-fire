<?php
require 'configregis.php'; // Include your database configuration

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first-name']);
    $lastName = trim($_POST['last-name']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Validate input fields
    if (empty($firstName) || empty($lastName) || empty($username) || empty($password)) {
        die("All fields are required.");
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if the username already exists
    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        die("Username already exists. Please choose another one.");
    }

    // Insert new user into the database
    $sql = "INSERT INTO users (first_name, last_name, username, password) VALUES (:first_name, :last_name, :username, :password)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute(['first_name' => $firstName, 'last_name' => $lastName, 'username' => $username, 'password' => $hashedPassword])) {
        echo "User registered successfully!";
    } else {
        echo "There was an error registering the user.";
    }
}
?>
