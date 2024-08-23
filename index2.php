<?php
include 'config.php';

$fcode = isset($_GET['fcode']) ? $_GET['fcode'] : '';

// Base query
$query = "SELECT fe.fcode, fe.F_Tank, fe.F_located, e.date_make, e.seal, e.pressure, e.hose, e.body 
          FROM fire_extinguisher fe
          JOIN evaluations e ON fe.fcode = e.fcode";

if ($fcode !== '' && $fcode !== 'All') {
    $query .= " WHERE fe.fcode = ?";
}

$query .= " ORDER BY fe.F_Tank ASC, fe.fcode ASC, e.date_make ASC";

$stmt = $conn->prepare($query);

if ($fcode !== '' && $fcode !== 'All') {
    $stmt->bind_param("s", $fcode);
}

$stmt->execute();
$result = $stmt->get_result();

function formatYesNo($value) {
    return $value == 'yes' ? '✔' : '❌';
}

function formatDateThai($date) {
    $dateObj = new DateTime($date);
    $thaiMonths = [
        'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
        'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
    ];
    $year = $dateObj->format('Y') + 543;
    $month = $thaiMonths[$dateObj->format('n') - 1];
    $day = $dateObj->format('j');
    return "$day $month $year";
}

// Fetch details for selected fcode
$info = null;
if ($fcode !== '' && $fcode !== 'All') {
    $infoQuery = "SELECT fcode, F_Tank, F_located FROM fire_extinguisher WHERE fcode = ?";
    $infoStmt = $conn->prepare($infoQuery);
    $infoStmt->bind_param("s", $fcode);
    $infoStmt->execute();
    $infoResult = $infoStmt->get_result();
    $info = $infoResult->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการประเมิน</title>
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
        .dropdown {
            margin-bottom: 20px;
        }
        .dropdown form {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .dropdown label {
            margin-right: 10px;
            font-size: 1.1em;
            color: #333;
        }
        .dropdown select {
            padding: 8px;
            font-size: 1em;
            border: 1px solid #ddd;
            border-radius: 4px;
            outline: none;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #6a1b9a;
            color: white;
        }
        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tbody tr:hover {
            background-color: #e0e0e0;
        }
        tfoot td {
            background-color: #6a1b9a;
            color: white;
            padding: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ประวัติการประเมิน</h1>

        <div class="dropdown">
            <form action="" method="get">
                <label for="fcode">เลือก FCODE:</label>
                <select name="fcode" id="fcode" onchange="this.form.submit()">
                    <option value="">ทั้งหมด</option>
                    <?php
                    $fcodeQuery = "SELECT DISTINCT fcode FROM fire_extinguisher ORDER BY fcode ASC";
                    $fcodeResult = $conn->query($fcodeQuery);
                    while ($row = $fcodeResult->fetch_assoc()) {
                        $selected = $fcode === $row['fcode'] ? 'selected' : '';
                        echo "<option value='{$row['fcode']}' $selected>{$row['fcode']}</option>";
                    }
                    ?>
                </select>
            </form>
        </div>

        <?php if ($fcode !== '' && $fcode !== 'All' && $info): ?>
            <div class="info">
                FCODE: <?= htmlspecialchars($info['fcode']) ?><br>
                F_Tank: <?= htmlspecialchars($info['F_Tank']) ?><br>
                F_located: <?= htmlspecialchars($info['F_located']) ?><br>
            </div>
        <?php endif; ?>

        <?php if ($fcode !== '' && $fcode !== 'All'): ?>
            <table>
                <thead>
                    <tr>
                        <th>วันที่</th>
                        <th>ซีล</th>
                        <th>ความดัน</th>
                        <th>สาย</th>
                        <th>ตัวถัง</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . formatDateThai($row['date_make']) . "</td>";
                        echo "<td>" . formatYesNo($row['seal']) . "</td>";
                        echo "<td>" . formatYesNo($row['pressure']) . "</td>";
                        echo "<td>" . formatYesNo($row['hose']) . "</td>";
                        echo "<td>" . formatYesNo($row['body']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
