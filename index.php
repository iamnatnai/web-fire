<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Fire Extinguisher Data</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="icon" href="/mick/my-php/favicon.ico" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        .button {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            background-color: #4CAF50;
            font-size: 16px;
            cursor: pointer;
        }
        .button:hover {
            background-color: #45a049;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            font-size: 18px;
            color: #fff;
            background-color: #41008b;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            margin-top: 20px;
            width: 100%;
            transition: background-color 0.3s ease-in-out, transform 0.2s;
        }

        .btn:hover {
            background-color: #7100b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
<div class="container">
    </div>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You need to login to access this page.',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'login.html';
                }
            });
        </script>
    <?php else: ?>
        <?php include 'navbar.php'; ?>
        <div class="container">
            <h1>Fire Extinguisher Data</h1>
            <a href="download_checkyear.php" class="btn">ดาวน์โหลดรายงานรายปี</a>
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <a href="logout.php" class="button" style="background-color: #f44336;">Logout</a>
            <?php
            // เชื่อมต่อฐานข้อมูล
            // $servername = "localhost";
            // $username = "root";
            // $password = "";
            // $dbname = "fired_data";

            $servername = "localhost";
$username = "kasemra2_dcc"; // ชื่อผู้ใช้ฐานข้อมูล
$password = "123456"; // รหัสผ่านฐานข้อมูล
$dbname = "kasemra2_dcc"; // ชื่อฐานข้อมูล

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
            <?php if (is_array($data)): ?>
                <?php foreach ($data as $record): ?>
                    <div class="record">
                        <p><strong>ชื่อถัง:</strong> <?php echo htmlspecialchars($record['FCODE']); ?></p>
                        <p><strong>สายน้ำ:</strong> <?php echo htmlspecialchars($record['F_water']); ?></p>
                        <p><strong>ที่อยู่:</strong> <?php echo htmlspecialchars($record['F_located']); ?></p>
                        <p><strong>รูปภาพ:</strong></p>
                        <img src="<?php echo htmlspecialchars("/mick/my-php" . $record['image_path']); ?>" alt="Fire Extinguisher Image">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p><?php echo htmlspecialchars($data); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
</body>
</html>
