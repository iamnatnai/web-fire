<?php
session_start();
include('../config.php');

// Ensure user is admin
if ($_SESSION['role'] !== 'Admin') {
    echo 'unauthorized';
    exit();
}

// Validate and sanitize input
$user_id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
$active = filter_input(INPUT_POST, 'active', FILTER_SANITIZE_NUMBER_INT);

if ($user_id && ($active === '0' || $active === '1')) {
    $stmt = $conn->prepare("UPDATE users SET active = ? WHERE id = ?");
    $stmt->bind_param('ii', $active, $user_id);
    
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }

    $stmt->close();
} else {
    echo 'invalid';
}

$conn->close();
?>
