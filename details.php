<?php
include 'config.php';

$date_make = isset($_GET['date_make']) ? $_GET['date_make'] : '';
$fcode = isset($_GET['fcode']) ? $_GET['fcode'] : '';

if ($date_make && $fcode) {
    // Query to get details for the specific date and fcode
    $detailQuery = "SELECT fe.fcode, fe.F_Tank, fe.F_located, e.date_make, e.seal, e.pressure, e.hose, e.body 
                    FROM fire_extinguisher fe
                    JOIN evaluations e ON fe.fcode = e.fcode
                    WHERE e.date_make = ? AND fe.fcode = ?";
    $stmt = $conn->prepare($detailQuery);
    $stmt->bind_param("ss", $date_make, $fcode);
    $stmt->execute();
    $result = $stmt->get_result();
    $details = $result->fetch_assoc();
} else {
    $details = null;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดการประเมิน</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            width: 80%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #6a1b9a;
            margin-bottom: 20px;
        }
        .info {
            margin-bottom: 20px;
            font-size: 1.2em;
            font-weight: bold;
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        a {
            color: #6a1b9a;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>รายละเอียดการประเมิน</h1>

        <?php if ($details): ?>
            <div class="info">
                FCODE: <?= htmlspecialchars($details['fcode']) ?><br>
                F_Tank: <?= htmlspecialchars($details['F_Tank']) ?><br>
                F_located: <?= htmlspecialchars($details['F_located']) ?><br>
                วันที่: <?= formatDateThai($details['date_make']) ?><br>
                ซีล: <?= formatYesNo($details['seal']) ?><br>
                ความดัน: <?= formatYesNo($details['pressure']) ?><br>
                สาย: <?= formatYesNo($details['hose']) ?><br>
                ตัวถัง: <?= formatYesNo($details['body']) ?><br>
            </div>
        <?php else: ?>
            <p>ไม่พบข้อมูลรายละเอียด</p>
        <?php endif; ?>

        <a href="home.php">กลับไปยังหน้าหลัก</a>
    </div>
</body>
</html>
