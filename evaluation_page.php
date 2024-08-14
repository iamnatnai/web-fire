<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "kasemra2_dcc";
$password = "123456";
$dbname = "kasemra2_dcc";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
$seal = $conn->real_escape_string($_POST['seal']);
$pressure = $conn->real_escape_string($_POST['pressure']);
$hose = $conn->real_escape_string($_POST['hose']);
$body = $conn->real_escape_string($_POST['body']);

// Handle file upload
$uploadDir = 'uploads/';
$imageFileName = '';

if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    $imageFileName = basename($_FILES['image']['name']);
    $uploadFile = $uploadDir . $imageFileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
        // File upload success
    } else {
        // File upload failed
        echo "<script>
                alert('Failed to upload file.');
                window.location.href = 'index.php';
              </script>";
        exit();
    }
}

// Save form data to the database
$sql = "INSERT INTO evaluations (seal, pressure, hose, body, image) VALUES ('$seal', '$pressure', '$hose', '$body', '$imageFileName')";

if ($conn->query($sql) === TRUE) {
    echo "<script>
            window.location.href = 'index.php';
          </script>";
} else {
    // Show SQL error
    echo "<script>
            alert('Error: " . $conn->error . "');
            window.location.href = 'index.php';
          </script>";
}

$conn->close();

?>
