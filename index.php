<?php
session_start();

// Function to fetch distinct values for dropdowns
function getDistinctValues($conn, $column, $table) {
    $sql = "SELECT DISTINCT $column FROM $table";
    $result = $conn->query($sql);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    $values = [];
    while ($row = $result->fetch_assoc()) {
        $values[] = $row[$column];
    }
    return $values;
}

// Database connection
include 'config.php';

// Fetch distinct months, years, and layers
$months = getDistinctValues($conn, 'MONTH(date_make)', 'evaluations');
$years = getDistinctValues($conn, 'YEAR(date_make) + 543', 'evaluations'); // Adding 543 to convert to Buddhist year
$layers = getDistinctValues($conn, 'F_layer', 'fire_extinguisher');

// Check if filters are set
$selectedMonth = isset($_POST['month']) ? $_POST['month'] : date('n'); // Use current month by default
$selectedYear = isset($_POST['year']) ? $_POST['year'] : date('Y') + 543; // Use current Buddhist year by default
$selectedLayer = isset($_POST['layer']) ? $_POST['layer'] : 'all';

// Fetch total number of fire extinguishers
$sqlTotal = "SELECT COUNT(*) as total FROM fire_extinguisher";
if ($selectedLayer != 'all') {
    $sqlTotal .= " WHERE F_layer = '$selectedLayer'";
}
$resultTotal = $conn->query($sqlTotal);
if (!$resultTotal) {
    die("Query failed: " . $conn->error);
}
$rowTotal = $resultTotal->fetch_assoc();
$totalCount = $rowTotal['total'];

// Build query based on selected filters
$queryConditions = [];
if ($selectedMonth != 'all') {
    $queryConditions[] = "MONTH(evaluations.date_make) = $selectedMonth";
}
if ($selectedYear != 'all') {
    $queryConditions[] = "YEAR(evaluations.date_make) + 543 = $selectedYear"; // Convert to Buddhist year for SQL query
}
if ($selectedLayer != 'all') {
    $queryConditions[] = "fire_extinguisher.F_layer = '$selectedLayer'";
}

$queryCondition = implode(' AND ', $queryConditions);
if ($queryCondition) {
    $queryCondition = 'AND ' . $queryCondition;
}

// Fetch number of evaluated fire extinguishers based on filters
$sqlEvaluated = "SELECT COUNT(*) as evaluated FROM evaluations 
                  JOIN fire_extinguisher ON evaluations.FCODE = fire_extinguisher.FCODE 
                  WHERE 1=1 $queryCondition";
$resultEvaluated = $conn->query($sqlEvaluated);
if (!$resultEvaluated) {
    die("Query failed: " . $conn->error);
}
$rowEvaluated = $resultEvaluated->fetch_assoc();
$evaluatedCount = $rowEvaluated['evaluated'];

// Convert years to a range
$currentYear = date('Y') + 543; // Current Buddhist Year
$startYear = $currentYear - 10;
$endYear = $currentYear; // Use current year

// Filter valid years for dropdown
$validYears = array_filter($years, function($year) use ($startYear, $endYear) {
    return $year >= $startYear && $year <= $endYear;
});
$chartData = [];
$columns = [
    'seal' => 'คันบังคับ/สลัก',
    'pressure' => 'ความดันน้ำ',
    'hose' => 'สายยาง',
    'body' => 'ตัวถัง',
    'construct' => 'สิ่งกีดขวางถัง',
];

// Initialize data structure
foreach ($columns as $column => $thaiName) {
    $chartData[$thaiName] = ['yes' => 0, 'no' => 0];
}

// Build query conditions
$chartQueryConditions = $queryConditions;
$queryConditionChart = implode(' AND ', $chartQueryConditions);
if ($queryConditionChart) {
    $queryConditionChart = 'WHERE ' . $queryConditionChart;
}

// Fetch chart data
foreach ($columns as $column => $thaiName) {
    $sqlColumn = "SELECT $column, COUNT(*) as count FROM evaluations 
                  JOIN fire_extinguisher ON evaluations.FCODE = fire_extinguisher.FCODE 
                  $queryConditionChart 
                  GROUP BY $column";
    
    $resultChart = $conn->query($sqlColumn);
    if (!$resultChart) {
        die("Query failed: " . $conn->error);
    }
    
    while ($rowChart = $resultChart->fetch_assoc()) {
        $value = $rowChart[$column];
        if ($value === 'yes') {
            $chartData[$thaiName]['yes'] += $rowChart['count'];
        } else if ($value === 'no') {
            $chartData[$thaiName]['no'] += $rowChart['count'];
        }
    }
}
// Define columns for the new chart

$newChartColumns = [
    'w_glass' => 'กระจก / ประตู:',
    'w_val' => 'วาล์ว',
    'w_hose' => 'หัวฉีด',
    'w_construct' => 'สิ่งกีดขวางตู้',
];

// Initialize new chart data structure
$newChartData = [];
foreach ($newChartColumns as $column => $label) {
    $newChartData[$label] = ['yes' => 0, 'no' => 0];
}

// Build query conditions for the new chart
$newChartQueryConditions = $queryConditions;
$queryConditionNewChart = implode(' AND ', $newChartQueryConditions);
if ($queryConditionNewChart) {
    $queryConditionNewChart = 'WHERE ' . $queryConditionNewChart;
}

// Fetch new chart data
foreach ($newChartColumns as $column => $label) {
    $sqlNewColumn = "SELECT $column, COUNT(*) as count FROM evaluations 
                     JOIN fire_extinguisher ON evaluations.FCODE = fire_extinguisher.FCODE 
                     $queryConditionNewChart 
                     GROUP BY $column";
    
    $resultNewChart = $conn->query($sqlNewColumn);
    if (!$resultNewChart) {
        die("Query failed: " . $conn->error);
    }
    
    while ($rowNewChart = $resultNewChart->fetch_assoc()) {
        $value = $rowNewChart[$column];
        if ($value === 'yes') {
            $newChartData[$label]['yes'] += $rowNewChart['count'];
        } else if ($value === 'no') {
            $newChartData[$label]['no'] += $rowNewChart['count'];
        }
    }
}


// Find FCODEs with "no"
$noFcodes = [];
$columns = ['seal', 'pressure', 'hose', 'body', 'construct']; // Columns to check

foreach ($columns as $column) {
    // Construct the SQL query
    $sql = "SELECT fire_extinguisher.F_Tank, evaluations.FCODE, fire_extinguisher.F_layer, fire_extinguisher.F_located
            FROM evaluations 
            JOIN fire_extinguisher ON evaluations.FCODE = fire_extinguisher.FCODE ";
    
    if (!empty($queryConditionChart)) {
        $sql .= "$queryConditionChart AND evaluations.$column = 'no'";
    } else {
        $sql .= "WHERE evaluations.$column = 'no'";
    }

    // Execute the query
    $result = $conn->query($sql);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    // Add all FCODEs with 'no' to the array
    while ($row = $result->fetch_assoc()) {
        $fcodeInfo = [
            'FCODE' => $row['FCODE'],
            'F_Tank' => $row['F_Tank'],
            'F_layer' => $row['F_layer'],
            'F_located' => $row['F_located']
        ];
        $noFcodes[] = $fcodeInfo;
    }
}

// Remove duplicates based on FCODE
$noFcodes = array_unique($noFcodes, SORT_REGULAR);

// Convert to JSON
$noFcodesJson = json_encode($noFcodes);


// Find FCODEs with "no"
$nowaterFcodes = [];
$columns = ['w_glass','w_val','w_hose','w_construct']; // Columns to check

foreach ($columns as $column) {
    // Construct the SQL query
    $sql = "SELECT fire_extinguisher.F_Tank, evaluations.FCODE, fire_extinguisher.F_layer, fire_extinguisher.F_located
            FROM evaluations 
            JOIN fire_extinguisher ON evaluations.FCODE = fire_extinguisher.FCODE ";
    
    if (!empty($queryConditionChart)) {
        $sql .= "$queryConditionChart AND evaluations.$column = 'no'";
    } else {
        $sql .= "WHERE evaluations.$column = 'no'";
    }

    // Execute the query
    $result = $conn->query($sql);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    // Add all FCODEs with 'no' to the array
    while ($row = $result->fetch_assoc()) {
        $waterfcodeInfo = [
            'FCODE' => $row['FCODE'],
            'F_Tank' => $row['F_Tank'],
            'F_layer' => $row['F_layer'],
            'F_located' => $row['F_located']
        ];
        $nowaterFcodes[] = $waterfcodeInfo;
    }
}

// Remove duplicates based on FCODE
$nowaterFcodes = array_unique($nowaterFcodes, SORT_REGULAR);

// Convert to JSON
$nowaterFcodesJson = json_encode($nowaterFcodes);

// Debug or Display data
// foreach ($noFcodes as $fcode) {
//     echo "ถังลำดับที่: {$fcode['F_Tank']} อยู่ชั้นที่: {$fcode['F_layer']}, ตำแหน่งที่: {$fcode['F_located']}<br>";
// }

$yesFcodes = [];
$columns = ['seal', 'pressure', 'hose', 'body', 'construct']; // Columns to check for 'yes'

// Construct the SQL query for "yes"
$sql = "SELECT fire_extinguisher.F_Tank, evaluations.FCODE, fire_extinguisher.F_layer, fire_extinguisher.F_located
        FROM evaluations 
        JOIN fire_extinguisher ON evaluations.FCODE = fire_extinguisher.FCODE ";

if (!empty($queryConditionChart)) {
    $sql .= "$queryConditionChart AND " . implode(" = 'yes' AND ", $columns) . " = 'yes'";
} else {
    $sql .= "WHERE " . implode(" = 'yes' AND ", $columns) . " = 'yes'";
}

// Execute the query
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Add all FCODEs with 'yes' for all columns to the array
while ($row = $result->fetch_assoc()) {
    $fcodeInfo = [
        'FCODE' => $row['FCODE'],
        'F_Tank' => $row['F_Tank'],
        'F_layer' => $row['F_layer'],
        'F_located' => $row['F_located']
    ];
    $yesFcodes[] = $fcodeInfo;
}

// Remove duplicates based on FCODE
$yesFcodes = array_unique($yesFcodes, SORT_REGULAR);

// Convert to JSON
$yesFcodesJson = json_encode($yesFcodes);


$yesWaterFcodes = [];
$columns = ['w_glass', 'w_val', 'w_hose', 'w_construct']; // Columns to check for 'yes'

// Construct the SQL query for "yes"
$sql = "SELECT fire_extinguisher.F_Tank, evaluations.FCODE, fire_extinguisher.F_layer, fire_extinguisher.F_located
        FROM evaluations 
        JOIN fire_extinguisher ON evaluations.FCODE = fire_extinguisher.FCODE ";

if (!empty($queryConditionNewChart)) {
    $sql .= "$queryConditionNewChart AND " . implode(" = 'yes' AND ", $columns) . " = 'yes'";
} else {
    $sql .= "WHERE " . implode(" = 'yes' AND ", $columns) . " = 'yes'";
}

// Execute the query
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Add all FCODEs with 'yes' for all columns to the array
while ($row = $result->fetch_assoc()) {
    $waterfcodeInfo = [
        'FCODE' => $row['FCODE'],
        'F_Tank' => $row['F_Tank'],
        'F_layer' => $row['F_layer'],
        'F_located' => $row['F_located']
    ];
    $yesWaterFcodes[] = $waterfcodeInfo;
}

// Remove duplicates based on FCODE
$yesWaterFcodes = array_unique($yesWaterFcodes, SORT_REGULAR);

// Convert to JSON
$yesWaterFcodesJson = json_encode($yesWaterFcodes);



// Close the connection
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fire Extinguisher Data</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="icon" href="/hos/fire_ex/favicon.ico" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #41008b;
            margin-bottom: 20px;
        }
        h2 {
            color: #333;
        }
        .button, .btn {
            display: inline-block;
            padding: 15px 30px;
            font-size: 18px;
            color: #fff;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s ease-in-out, transform 0.2s;
        }
        .button {
            background-color: #4CAF50;
        }
        .button:hover {
            background-color: #45a049;
        }
        .btn {
            background-color: #41008b;
        }
        .btn:hover {
            background-color: #7100b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-group select {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ddd;
            width: 100%;
        }
        .description {
            font-size: 26px;
            border: 16px;
            margin-top: 20px;
            color: #333;
        }
        canvas {
    width: 100% !important; /* Ensure the canvas takes up the full width */
    height: auto !important; /* Maintain aspect ratio */
    max-height: 400px; /* Optional: Limit max height to prevent it from being too large */
    margin-top: 20px;
}
.large-icon {
            display: block;
            width: 150px; /* Adjust size as needed */
            height: auto; /* Maintain aspect ratio */
            margin: 0 auto 20px; /* Center the image and add margin below */
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            background: #f8f8f8;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .fcode-title {
            font-weight: bold;
            color: #d9534f;
        }
        .fcode-title-yes {
            font-weight: bold;
            color: #56d94f;
        }



    </style>
</head>
<body>
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
        <div style="text-align: center; margin-bottom: 20px;">
        <img src="/hos/fire_ex/home-icon.png" alt="Home Icon" class="large-icon">
            </div>
            <h1>หน้าหลัก ระบบตรวจสอบถังดับเพลิง</h1>
            <h2>ยินดีต้อนรับ! คุณ<?php echo htmlspecialchars($_SESSION['firstname']); ?>!</h2>
            <a href="download_checkyear.php" class="btn">ดาวน์โหลดรายงานประจำปี</a>
            <h2>เลือกการค้นหา</h2>

            <!-- Filter Form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="month">เลือกเดือน:</label>
                    <select name="month" id="month" required>
                        <?php
                        // Month names in Thai
                        $thaiMonths = [
                            1 => 'มกราคม',
                            2 => 'กุมภาพันธ์',
                            3 => 'มีนาคม',
                            4 => 'เมษายน',
                            5 => 'พฤษภาคม',
                            6 => 'มิถุนายน',
                            7 => 'กรกฎาคม',
                            8 => 'สิงหาคม',
                            9 => 'กันยายน',
                            10 => 'ตุลาคม',
                            11 => 'พฤศจิกายน',
                            12 => 'ธันวาคม'
                        ];
                        foreach ($thaiMonths as $num => $name): ?>
                            <option value="<?php echo htmlspecialchars($num); ?>" <?php if ($selectedMonth == $num) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="year">เลือกปี:</label>
                    <select name="year" id="year" required>
                        <?php foreach ($validYears as $year): ?>
                            <option value="<?php echo htmlspecialchars($year); ?>" <?php if ($selectedYear == $year) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($year); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="layer">เลือกชั้น:</label>
                    <select name="layer" id="layer" required>
                        <option value="all">ชั้นทั้งหมด</option>
                        <?php foreach ($layers as $layer): ?>
                            <option value="<?php echo htmlspecialchars($layer); ?>" <?php if ($selectedLayer == $layer) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($layer); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="button">ยืนยันการค้นหา</button>
            </form>

            <div class="description">
                <?php
                // Calculating the non-evaluated count and percentage
                $notEvaluatedCount = $totalCount - $evaluatedCount;
                $percentage = ($totalCount > 0) ? round(($evaluatedCount / $totalCount) * 100, 2) : 'N/A';

                // Description in Thai for the selected filters
                $description = "ข้อมูลการประเมินในเดือน " . $thaiMonths[$selectedMonth] . " ปี " . $selectedYear . " ประเมินทั้งหมด ". $evaluatedCount ." ถัง จากทั้งหมด " . $totalCount. " ถัง " ;

                if ($selectedLayer === 'all') {
                    $desth .= "ของชั้นทั้งหมด";
                    $description .= " ของชั้นทั้งหมด";
                    if ($totalCount > 0) {
                        $description .= " คิดเป็น " . $percentage . "% ของชั้นทั้งหมด";
                    } else {
                        $description .= " ไม่มีข้อมูล";
                    }
                } else {
                    $desth .= "ของชั้น" . $selectedLayer;
                    $description .= " ของชั้น " . $selectedLayer;
                    if ($totalCount > 0) {
                        $description .= " คิดเป็น " . $percentage . "% ของชั้นที่ " . $selectedLayer;
                    } else {
                        $description .= " ไม่มีข้อมูล";
                    }
                }
                ?>
                
            </div>
            <h2>เปอร์เซ็นต์การตรวจสอบ<?php echo $desth ?></h2>
            <canvas id="myPieChart"></canvas>
            <div class="description">
            <?php echo $description;?>
            </div>
            <h2>สถิติการตรวจสอบถังดับเพลิง<?php echo $desth ?></h2>
            <canvas id="myBarChart"></canvas>
            <div id="fcode-list">
        <h2>รายการถังดับเพลิงที่<font color="green">ผ่าน</font>การตรวจสอบ<?php echo $desth ?></h2>
        <p>จำนวนถังดับเพลิง<?php echo $desth ?>ที่<font color="green">ผ่าน</font>: <?php echo count($yesFcodes); ?> ถัง</p>
        <ul>
            <?php foreach ($yesFcodes as $fcode): ?>
                <li>
                    <span class="fcode-title-yes">ถังลำดับที่:</span> <?php echo $fcode['F_Tank']; ?><br>
                    <span class="fcode-title-yes">ชั้นที่:</span> <?php echo $fcode['F_layer']; ?><br>
                    <span class="fcode-title-yes">ตำแหน่ง:</span> <?php echo $fcode['F_located']; ?>
                </li>
            <?php endforeach; ?>
            <?php if (empty($yesFcodes)): ?>
                <li>ในส่วน<?php echo $desth ?>ไม่มีถังดับเพลิงที่ผ่าน</li>
            <?php endif; ?>
        </ul>
    </div>
    
    <div id="fcode-list">
            <h2>รายการถังดับเพลิงที่<font color="red">ไม่ผ่าน</font>การตรวจสอบ<?php echo $desth; ?></h2>
            <p>จำนวนถังดับเพลิง<?php echo $desth ?>ที่<font color="red">ไม่ผ่าน</font>มีทั้งหมด <?php echo count($noFcodes); ?> ถัง</p>
    <ul>
        <?php foreach ($noFcodes as $fcode): ?>
            <li>
                <span class="fcode-title">ถังลำดับที่:</span> <?php echo $fcode['F_Tank']; ?><br>
                <span class="fcode-title">ชั้นที่:</span> <?php echo $fcode['F_layer']; ?><br>
                <span class="fcode-title">ตำแหน่ง:</span> <?php echo $fcode['F_located']; ?>
            </li>
        <?php endforeach; ?>
        <?php if (empty($noFcodes)): ?>
            <li>ในส่วน<?php echo $desth ?>ไม่มีถังดับเพลิงที่มีค่าไม่ผ่าน</li>
        <?php endif; ?>
    </ul>
    </div>
    <h2>สถิติการตรวจสอบตู้สายน้ำดับเพลิง<?php echo $desth ?></h2>
    <canvas id="myNewChart" width="400" height="200"></canvas>
    <div id="fcode-list">
    <h2>รายการตู้สายน้ำดับเพลิงที่ผลการตรวจสอบ<font color="green">ปกติ</font><?php echo $desth ?></h2>
    <p>จำนวนตู้ดับเพลิง<?php echo $desth ?>ที่<font color="green">ปกติ</font>: <?php echo count($yesWaterFcodes); ?> ตู้</p>
    <ul>
        <?php foreach ($yesWaterFcodes as $fcode): ?>
            <li>
                <span class="fcode-title-yes">บริเวณถังลำดับที่:</span> <?php echo $fcode['F_Tank']; ?><br>
                <span class="fcode-title-yes">ชั้นที่:</span> <?php echo $fcode['F_layer']; ?><br>
                <span class="fcode-title-yes">ตำแหน่งตู้:</span> <?php echo $fcode['F_located']; ?>
            </li>
        <?php endforeach; ?>
        <?php if (empty($yesWaterFcodes)): ?>
            <li>ในส่วน<?php echo $desth ?>ไม่มีตู้ดับเพลิงที่ผ่าน</li>
        <?php endif; ?>
    </ul>
    </div>
    
    <div id="fcode-list">
            <h2>รายการตู้สายน้ำดับเพลิงที่ผลการตรวจสอบ<font color="red">ไม่ปกติ</font><?php echo $desth ?></h2>
            <p>จำนวนรายการตู้ดับเพลิง<?php echo $desth ?>ที่<font color="red">ไม่ปกติ</font>มีทั้งหมด <?php echo count($nowaterFcodes); ?> ตู้</p>
    <ul>
        <?php foreach ($nowaterFcodes as $fcode): ?>
            <li>
                <span class="fcode-title">บริเวณถังลำดับที่:</span> <?php echo $fcode['F_Tank']; ?><br>
                <span class="fcode-title">ชั้นที่:</span> <?php echo $fcode['F_layer']; ?><br>
                <span class="fcode-title">ตำแหน่งตู้:</span> <?php echo $fcode['F_located']; ?>
            </li>
        <?php endforeach; ?>
        <?php if (empty($nowaterFcodes)): ?>
            <li>ในส่วน<?php echo $desth ?>ไม่มีตู้ดับเพลิงที่มีค่าไม่ผ่าน</li>
        <?php endif; ?>
    </ul>
    </div>

    

            <a href="logout.php" class="button" id="logoutButton" style="background-color: #f44336;">Logout</a>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
    // Initialize or destroy existing charts
    var pieChartCanvas = document.getElementById('myPieChart');
    var barChartCanvas = document.getElementById('myBarChart');
    var newChartCanvas = document.getElementById('myNewChart');

    if (pieChartCanvas.chart) {
        pieChartCanvas.chart.destroy();
    }
    if (barChartCanvas.chart) {
        barChartCanvas.chart.destroy();
    }
    if (newChartCanvas.chart) { // Destroy the new chart if it exists
        newChartCanvas.chart.destroy();
    }



    // Destroy existing charts if they exist
    if (pieChartCanvas.chart) {
        pieChartCanvas.chart.destroy();
    }
    if (barChartCanvas.chart) {
        barChartCanvas.chart.destroy();
    }

    var totalCount = <?php echo $totalCount; ?>;
    var evaluatedCount = <?php echo $evaluatedCount; ?>;
    var notEvaluatedCount = totalCount - evaluatedCount;
    var percentage = (totalCount > 0) ? ((evaluatedCount / totalCount) * 100).toFixed(2) : 'N/A';

    var ctxPie = pieChartCanvas.getContext('2d');
    var myPieChart = new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: ['Evaluated', 'Not Evaluated'],
            datasets: [{
                data: [evaluatedCount, notEvaluatedCount],
                backgroundColor: ['#4CAF50', '#FF5733'],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var label = context.label || '';
                            var value = context.raw || 0;
                            var percentage = (totalCount > 0) ? ((value / totalCount) * 100).toFixed(2) : 'N/A';
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    document.getElementById('logoutButton').addEventListener('click', function(event) {
        event.preventDefault();
        Swal.fire({
            title: 'คุณต้องการออกจากระบบใช่ไหม?',
            text: "การออกจากระบบจะสิ้นสุดการทำงานของคุณ",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ใช่, ออกจากระบบ!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        });
    });

    // Initialize bar chart
    var chartData = <?php echo json_encode($chartData); ?>;
    var fcodeList = <?php echo $noFcodesJson; ?>; // Ensure this is properly included
    var labels = Object.keys(chartData);
    var yesData = labels.map(label => chartData[label]['yes']);
    var noData = labels.map(label => chartData[label]['no']);

    var ctxBar = barChartCanvas.getContext('2d');
    var myBarChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
    label: 'ปกติ',
    data: yesData,
    backgroundColor: 'linear-gradient(90deg, rgba(129,199,132,0.5) 0%, rgba(76,175,80,0.5) 100%)', // Gradient สีเขียว
    borderColor: 'rgba(76, 175, 80, 1)', 
    borderWidth: 2
},
{
    label: 'ไม่ปกติ',
    data: noData,
    backgroundColor: 'linear-gradient(90deg, rgba(255,138,128,0.5) 0%, rgba(255,87,51,0.5) 100%)', // Gradient สีส้ม
    borderColor: 'rgba(255, 87, 51, 1)',
    borderWidth: 2
}

            ]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'การประเมินถังดับเพลิง'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'จำนวนถังดับเพลิง'
                    },
                    beginAtZero: true
                }
            }
        }
    });

    var newChartData = <?php echo json_encode($newChartData); ?>;
    var newLabels = Object.keys(newChartData);
    var newYesData = newLabels.map(label => newChartData[label]['yes']);
    var newNoData = newLabels.map(label => newChartData[label]['no']);

    var ctxNew = newChartCanvas.getContext('2d');
    newChartCanvas.chart = new Chart(ctxNew, {
        type: 'bar',
        data: {
            labels: newLabels,
            datasets: [
                {
                    label: 'ปกติ',
                    data: newYesData,
                    backgroundColor: 'linear-gradient(90deg, rgba(102,178,255,0.5) 0%, rgba(54,162,235,0.5) 100%)', // Gradient สีฟ้า
                    borderColor: 'rgba(0, 123, 255, 1)', 
                    borderWidth: 2
                },
                {
                    label: 'ไม่ปกติ',
                    data: newNoData,
                    backgroundColor: 'linear-gradient(90deg, rgba(255,153,153,0.5) 0%, rgba(255,99,132,0.5) 100%)', // Gradient สีแดง
                    borderColor: 'rgba(255, 69, 96, 1)', 
                    borderWidth: 2

                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'การประเมินตู้นิรภัยดับเพลิง'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'จำนวนตู้ที่ประเมิน'
                    },
                    beginAtZero: true
                }
            }
        }
    });

    
     // Display FCODE list
});
console.log('New Chart Data:', newChartData);
console.log('New Yes Data:', newYesData);
console.log('New No Data:', newNoData);


var chartData = <?php echo json_encode($chartData); ?>;
    </script>
    <?php endif; ?>
    
</body>
</html>
