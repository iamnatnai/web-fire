<?php
// evaluation_page.php

header('Content-Type: application/json');

$servername = "localhost";
$username = "kasemra2_dcc";
$password = "123456";
$dbname = "kasemra2_dcc";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$fcode = $conn->real_escape_string($_POST['fcode']);
$seal = $conn->real_escape_string($_POST['seal']);
$pressure = $conn->real_escape_string($_POST['pressure']);
$hose = $conn->real_escape_string($_POST['hose']);
$body = $conn->real_escape_string($_POST['body']);
$dateMake = $conn->real_escape_string($_POST['evaluationDate']); // Get the date from the form

// Handle file upload
$uploadDir = 'evaluation_image/';
$imageFileName = '';

if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    $imageFileName = basename($_FILES['image']['name']);
    $uploadFile = $uploadDir . $imageFileName;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
        // File upload failed
        header('Location: confirmation_page.php?status=error');
        exit();
    }
}

// Save form data to the database
$sql = "INSERT INTO evaluations (seal, pressure, hose, body, image,FCODE, date_make) VALUES ('$seal', '$pressure', '$hose', '$body', '$imageFileName', '$fcode', '$dateMake')";

if ($conn->query($sql) === TRUE) {
    header('Location: confirmation_page.php?status=success');
} else {
    // Show SQL error
    header('Location: confirmation_page.php?status=error');
}

$conn->close();
?>
