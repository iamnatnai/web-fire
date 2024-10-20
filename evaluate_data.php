<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json'); // ตั้งค่า Content-Type ให้เป็น JSON

$response = [];

include 'config.php';

// Check connection
if ($conn->connect_error) {
    $response['status'] = 'error';
    $response['message'] = 'Connection failed: ' . $conn->connect_error;
    echo json_encode($response);
    exit();
}

// ตรวจสอบข้อมูล POST
$response['post'] = $_POST;
$response['files'] = $_FILES;

// ตรวจสอบข้อมูลไฟล์
if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    $uploadDir = 'evaluation_image/';
    $imageFileName = basename($_FILES['image']['name']);
    $uploadFile = $uploadDir . $imageFileName;

    // Check if file is an image
    $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($imageFileType, $allowedTypes)) {
        $response['status'] = 'error';
        $response['message'] = 'Invalid image type';
        echo json_encode($response);
        exit();
    }

    // Move uploaded file
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
        $response['status'] = 'error';
        $response['message'] = 'File upload failed';
        echo json_encode($response);
        exit();
    }
}

// Save form data to the database
$sql = "INSERT INTO evaluations (seal, pressure, hose, body,construct, image, FCODE, date_make, comment, evaluator, w_glass, w_val, w_hose, w_construct) 
        VALUES ('" . $conn->real_escape_string($_POST['seal']) . "', 
                '" . $conn->real_escape_string($_POST['pressure']) . "', 
                '" . $conn->real_escape_string($_POST['hose']) . "', 
                '" . $conn->real_escape_string($_POST['body']) . "', 
                '" . $conn->real_escape_string($_POST['construct']) . "', 
                '" . $conn->real_escape_string($imageFileName) . "', 
                '" . $conn->real_escape_string($_POST['fcode']) . "', 
                '" . $conn->real_escape_string($_POST['evaluationDate']) . "', 
                '" . $conn->real_escape_string($_POST['comment']) . "', 
                '" . $conn->real_escape_string($_POST['evaluator']) . "', 
                '" . $conn->real_escape_string($_POST['w_glass']) . "', 
                '" . $conn->real_escape_string($_POST['w_val']) . "', 
                '" . $conn->real_escape_string($_POST['w_hose']) . "', 
                '" . $conn->real_escape_string($_POST['w_construct']) . "')";


if ($conn->query($sql) === TRUE) {
    $response['status'] = 'success';
    $response['message'] = 'Data successfully inserted';
} else {
    $response['status'] = 'error';
    $response['message'] = 'SQL Error: ' . $conn->error;
}
$response['sql'] = $sql;
echo json_encode($response);
exit();

echo json_encode($response);

$conn->close();
?>
