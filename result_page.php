<?php
// Include the database configuration file
include 'config.php';

// Check if GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if 'data' is set
    if (isset($_GET['data'])) {
        $qrData = $_GET['data'];
        
        // Protect against SQL Injection
        $qrData = $conn->real_escape_string($qrData);

        // Query the database
        $sql = "SELECT * FROM fire_extinguisher WHERE FCODE='$qrData'";
        $result = $conn->query($sql);

        $response = [];

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
<!DOCTYPE html>
<html lang="en">
<?php include 'navbar.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        h1 {
            font-size: 24px;
        }
        img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ข้อมูลถังดับเพลิง</h1>
        <?php if ($response['status'] === 'found'): ?>
            <p><strong>ชื่อถัง:</strong> <?php echo htmlspecialchars($response['data']['FCODE']); ?></p>
            <p><strong>สายน้ำ:</strong> <?php echo htmlspecialchars($response['data']['F_water']); ?></p>
            <p><strong>ที่อยู่:</strong> <?php echo htmlspecialchars($response['data']['F_located']); ?></p>
            <p><strong>รูปภาพ:</strong></p>
            <img src="<?php echo htmlspecialchars("/mick/my-php"  . $response['data']['image_path']); ?>" alt="Fire Extinguisher Image">
            <!-- เพิ่มปุ่มเพื่อไปยังหน้าทำแบบประเมิน -->
            <a href="evaluation_page.html?data=<?php echo urlencode($response['data']['FCODE']); ?>" class="btn">ไปยังแบบประเมิน</a>
        <?php else: ?>
            <p>ไม่พบข้อมูลในฐานข้อมูล</p>
        <?php endif; ?>
    </div>
</body>
</html>
