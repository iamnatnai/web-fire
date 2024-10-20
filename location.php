<?php
include 'config.php';

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ดึงค่าจากฐานข้อมูล (หากมีการเลือกค่า)
$imagePath = '';
if (isset($_GET['layer'])) {
    $selectedLayer = $_GET['layer'];
    $sql = "SELECT image_path FROM your_table WHERE layer_code = ?"; // เปลี่ยนชื่อ table และคอลัมน์ตามฐานข้อมูลของคุณ
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selectedLayer);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $imagePath = htmlspecialchars($row['image_path']);
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location - Fire Extinguisher Data</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        select {
            padding: 10px;
            font-size: 16px;
            margin-bottom: 20px;
        }
        img {
            max-width: 100%;
            max-height: 400px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <a href="evaluation_page.html"><i class="fas fa-clipboard-list"></i> Evaluation</a>
        <a href="contact.php"><i class="fas fa-map-marker-alt"></i> Location</a>
        <a href="scan.html"><i class="fas fa-qrcode"></i> Scan</a>
    </div>
    <div class="container">
        <h1>Select Layer</h1>
        <form method="get" action="location.php">
            <select name="layer" onchange="this.form.submit()">
                <option value="">Select Layer</option>
                <?php
                // แสดงตัวเลือกทั้งหมดจากฐานข้อมูล
                $conn = new mysqli($servername, $username, $password, $dbname);
                $sql = "SELECT DISTINCT layer_code FROM your_table"; // เปลี่ยนชื่อ table และคอลัมน์ตามฐานข้อมูลของคุณ
                $result = $conn->query($sql);

                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($row['layer_code']) . '"' . ($imagePath ? ' selected' : '') . '>' . htmlspecialchars($row['layer_code']) . '</option>';
                }

                $conn->close();
                ?>
            </select>
        </form>

        <?php if ($imagePath): ?>
            <img src="<?php echo htmlspecialchars("/uploads/" . $imagePath); ?>" alt="Selected Layer Image">
        <?php endif; ?>
    </div>
</body>
</html>
