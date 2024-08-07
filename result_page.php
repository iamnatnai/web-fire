<?php
// $servername = "localhost"; // ชื่อเซิร์ฟเวอร์ฐานข้อมูล
// $username = "root"; // ชื่อผู้ใช้ฐานข้อมูล
// $password = ""; // รหัสผ่านฐานข้อมูล
// $dbname = "fired_data"; // ชื่อฐานข้อมูล

$servername = "localhost";
$username = "kasemra2_dcc"; // ชื่อผู้ใช้ฐานข้อมูล
$password = "123456"; // รหัสผ่านฐานข้อมูล
$dbname = "kasemra2_dcc"; // ชื่อฐานข้อมูล


// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตรวจสอบว่ามีการส่งข้อมูล QR Code มาหรือไม่
if (!isset($_GET['data'])) {
    echo "QR Code data missing";
    $conn->close();
    exit();
}

// รับข้อมูล QR Code ที่ส่งมาจาก query string
$qrData = $_GET['data'];

// ป้องกัน SQL Injection
$qrData = $conn->real_escape_string($qrData);

// ค้นหาข้อมูลในฐานข้อมูลตามค่า QR Code ที่ได้
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
            <img src="<?php echo htmlspecialchars("/my-php/" . $response['data']['image_path']); ?>" alt="Fire Extinguisher Image">
            <!-- เพิ่มปุ่มเพื่อไปยังหน้าทำแบบประเมิน -->
            <a href="evaluation_page.html?data=<?php echo urlencode($response['data']['FCODE']); ?>" class="btn">ไปยังแบบประเมิน</a>
        <?php else: ?>
            <p>ไม่พบข้อมูลในฐานข้อมูล</p>
        <?php endif; ?>
    </div>
</body>
</html>
