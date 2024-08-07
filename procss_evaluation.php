<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // เช็คและจัดการข้อมูลจากฟอร์ม
    $seal = $_POST['seal'] ?? '';
    $pressure = $_POST['pressure'] ?? '';
    $hose = $_POST['hose'] ?? '';
    $body = $_POST['body'] ?? '';

    // ตรวจสอบการอัพโหลดไฟล์
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        $fileType = $_FILES['image']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // กำหนดที่จัดเก็บไฟล์
        $uploadFileDir = './uploaded_files/';
        $dest_path = $uploadFileDir . $fileName;

        // ตรวจสอบการอัพโหลดไฟล์
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            echo json_encode(['status' => 'success', 'message' => 'File is successfully uploaded.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'There was an error uploading the file.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No file uploaded or there was an upload error.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
