<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Database credentials
$servername = "localhost";
$username = "kasemra2_dcc";
$password = "123456";
$dbname = "kasemra2_dcc";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Connection failed: " . $conn->connect_error
    ]);
    exit();
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if 'qrData' is set
    if (isset($_POST['qrData'])) {
        $qrData = $conn->real_escape_string($_POST['qrData']);

        // Query the database
        $sql = "SELECT * FROM fire_extinguisher WHERE FCODE='$qrData'";
        $result = $conn->query($sql);

        // Prepare response
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $response = [
                "status" => "found",
                "data" => [
                    "FCODE" => $row["FCODE"],
                    "F_water" => $row["F_water"],
                    "F_located" => $row["F_located"],
                    "image_path" => $row["image_path"]
                ]
            ];
        } else {
            $response = [
                "status" => "not_found"
            ];
        }

        echo json_encode($response);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "QR Code data missing"
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
}

// Close connection
$conn->close();
?>
