<?php
// เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fired_data";

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ดึงข้อมูลจากฐานข้อมูล
$sql = "SELECT * FROM fire_extinguisher"; // เปลี่ยนชื่อ table ตามฐานข้อมูลของคุณ
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    // เก็บข้อมูลในอาร์เรย์
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    $data = "No records found";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Fire Extinguisher Data</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .record {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .record img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }
        .navbar {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px 0;
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.2);
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 10px;
            font-size: 16px;
        }
        .navbar i {
            font-size: 20px;
            margin-right: 8px;
        }
        .navbar a:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <a href="evaluation_page.html"><i class="fas fa-clipboard-list"></i> Evaluation</a>
        <a href="layer.php"><i class="fas fa-map-marker-alt"></i> Location</a>
        <a href="scan.html"><i class="fas fa-qrcode"></i> Scan</a>
    </div>
    <div class="container">
        <h1>Fire Extinguisher Data</h1>
        <?php if (is_array($data)): ?>
            <?php foreach ($data as $record): ?>
                <div class="record">
                    <p><strong>ชื่อถัง:</strong> <?php echo htmlspecialchars($record['FCODE']); ?></p>
                    <p><strong>สายน้ำ:</strong> <?php echo htmlspecialchars($record['F_water']); ?></p>
                    <p><strong>ที่อยู่:</strong> <?php echo htmlspecialchars($record['F_located']); ?></p>
                    <p><strong>รูปภาพ:</strong></p>
                    <img src="<?php echo htmlspecialchars("/my-php/" . $record['image_path']); ?>" alt="Fire Extinguisher Image">
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p><?php echo htmlspecialchars($data); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
