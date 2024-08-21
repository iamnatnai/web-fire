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
$servername = "localhost";
$username = "kasemra2_dcc";
$password = "123456";
$dbname = "kasemra2_dcc";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
    <link rel="icon" href="/mick/my-php/favicon.ico" type="image/x-icon">
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
            font-size: 16px;
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
        <img src="/mick/my-php/home-icon.png" alt="Home Icon" class="large-icon">
            </div>
            <h1>หน้าหลัก ระบบตรวจสอบถังดับเพลิง</h1>
            <h2>ยินดีต้อนรับ! คุณ<?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
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
                $description = "ข้อมูลการประเมินในเดือน " . $thaiMonths[$selectedMonth] . " ปี " . $selectedYear;

                if ($selectedLayer === 'all') {
                    $description .= " ของชั้นทั้งหมด";
                    if ($totalCount > 0) {
                        $description .= " คิดเป็น " . $percentage . "% ของชั้นทั้งหมด";
                    } else {
                        $description .= " ไม่มีข้อมูล";
                    }
                } else {
                    $description .= " ของชั้นที่ " . $selectedLayer;
                    if ($totalCount > 0) {
                        $description .= " คิดเป็น " . $percentage . "% ของชั้นที่ " . $selectedLayer;
                    } else {
                        $description .= " ไม่มีข้อมูล";
                    }
                }

            echo $description;
                ?>
                
            </div>

            <canvas id="myPieChart"></canvas>
            <a href="logout.php" class="button" id="logoutButton" style="background-color: #f44336;">Logout</a>
        </div>

        <script>
           
    document.addEventListener('DOMContentLoaded', function() {
        var totalCount = <?php echo $totalCount; ?>;
        var evaluatedCount = <?php echo $evaluatedCount; ?>;
        var notEvaluatedCount = totalCount - evaluatedCount;
        var percentage = (totalCount > 0) ? ((evaluatedCount / totalCount) * 100).toFixed(2) : 'N/A';

        var ctx = document.getElementById('myPieChart').getContext('2d');
        var myPieChart = new Chart(ctx, {
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
                maintainAspectRatio: false, // Allows the chart to resize based on its container
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
    });
    document.getElementById('logoutButton').addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default action
                Swal.fire({
                    title: 'คุณต้องการออกจากระบบใช่ไหม?',
                    text: "การออกจากระบบจะสิ้นสุดการทำงานของคุณ",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'ใช่, ออกจากระบบ!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'logout.php'; // Redirect to logout.php if confirmed
                    }
                });
            });
</script>
    <?php endif; ?>
    
</body>
</html>
