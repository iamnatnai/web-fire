<?php
session_start();
if (!isset($_SESSION['user_id'])): ?>
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
    
    <?php else:
// Include the database configuration file
include 'config.php';

// Initialize response
$response = [];

// Function to convert layer names to sortable values
function sortLayer($layerName) {
    if ($layerName === '0') {
        return 'zzz'; // 'zzz' for Ambulance to appear last
    } elseif (preg_match('/B\d+/', $layerName)) {
        return 'AAA' . $layerName; // Prefix for B1, B2, etc. to appear first
    } else {
        return str_pad($layerName, 2, '0', STR_PAD_LEFT); // Pad numeric layers with leading zeros
    }
}

// Get the current month and year
$currentMonth = date('m');
$currentYear = date('Y');

// Query to get all layers and their evaluations
$sqlLayers = "SELECT F_layer, COUNT(*) as total FROM fire_extinguisher GROUP BY F_layer";
$resultLayers = $conn->query($sqlLayers);

if ($resultLayers->num_rows > 0) {
    while ($layer = $resultLayers->fetch_assoc()) {
        $layerName = $layer['F_layer'];
        $total = $layer['total'];

        // Query to get number of evaluated extinguishers for each layer this month
        $sqlEvaluated = "SELECT COUNT(*) as evaluated FROM evaluations 
                         JOIN fire_extinguisher ON evaluations.FCODE = fire_extinguisher.FCODE 
                         WHERE fire_extinguisher.F_layer = '$layerName' 
                         AND MONTH(evaluations.date_make) = $currentMonth 
                         AND YEAR(evaluations.date_make) = $currentYear";
        $resultEvaluated = $conn->query($sqlEvaluated);
        $evaluated = $resultEvaluated->fetch_assoc()['evaluated'];

        // Query to get unevaluated locations for each layer this month
        $sqlLocations = "SELECT F_located FROM fire_extinguisher 
                         WHERE F_layer = '$layerName' 
                         AND FCODE NOT IN (SELECT FCODE FROM evaluations 
                                           WHERE MONTH(date_make) = $currentMonth 
                                           AND YEAR(date_make) = $currentYear)";
        $resultLocations = $conn->query($sqlLocations);
        $locations = [];
        while ($location = $resultLocations->fetch_assoc()) {
            $locations[] = $location['F_located'];
        }

        $response[] = [
            'layer_name' => $layerName,
            'total' => $total,
            'evaluated' => $evaluated,
            'unevaluated' => $total - $evaluated,
            'locations' => $locations
        ];
    }

    // Sort the response array based on custom sort function
    usort($response, function($a, $b) {
        return strcmp(sortLayer($a['layer_name']), sortLayer($b['layer_name']));
    });
} else {
    $response = ['status' => 'no_data'];
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
    <title>Layer Status</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="navbar.css">
    <style>
       body {
    font-family: Arial, sans-serif;
    margin: 20px;
    color: #333;
}

.container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #f9f9f9;
    overflow-x: auto; /* Allow horizontal scrolling if content overflows */
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    table-layout: fixed; /* Ensure table does not overflow container */
}

th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
    word-wrap: break-word; /* Allow content to wrap to the next line */
}

th {
    background-color: #f2f2f2;
}

.details {
    cursor: pointer;
    color: #007bff;
    text-decoration: underline;
}

.details-container {
    display: none;
    margin-top: 10px;
}

.details-container ul {
    list-style-type: none;
    padding: 0;
}

.details-container ul li {
    margin: 5px 0;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #f9f9f9;
}

.complete {
    background-color: #d4edda; /* Light green background */
}

.btn {
    display: inline-block;
    padding: 15px 22px; /* Reduced padding */
    font-size: 16px; /* Slightly smaller font size */
    color: #fff;
    background-color: #41008b;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    text-align: center;
    cursor: pointer;
    margin-top: 20px;
    transition: background-color 0.3s ease-in-out, transform 0.2s;
    
    /* Center the button */
    position: relative;
    left: 50%;
    transform: translateX(-50%);
}

.btn:hover {
    background-color: #7100b3;
    transform: translate(-50%, -2px); /* Adjusted to keep it centered */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

    </style>
</head>
<body>
<div class="container">
    <h1>สถานะการประเมินตามชั้น</h1>
    <a href="download_report.php" class="btn">ดาวน์โหลดรายละเอียด</a><br>
    <a href="home.php" class="btn">หน้ารายงานสำหรับบุคลทั้วไป</a>
    <?php if (!empty($response)): ?>
        <table>
            <thead>
                <tr>
                    <th>ชั้น</th>
                    <th>ทั้งหมด</th>
                    <th>ประเมินแล้ว</th>
                    <th>เหลือ</th>
                    <th>รายละเอียด</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($response as $layer): ?>
                    <tr class="<?php echo ($layer['total'] == $layer['evaluated']) ? 'complete' : ''; ?>">
                        <td>
                            <?php
                            if ($layer['layer_name'] === '0') {
                                echo 'ในรถ Ambulance';
                            } else {
                                echo htmlspecialchars($layer['layer_name']);
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($layer['total']); ?></td>
                        <td><?php echo htmlspecialchars($layer['evaluated']); ?></td>
                        <td><?php echo htmlspecialchars($layer['unevaluated']); ?></td>
                        <td>
                            <?php if ($layer['total'] == $layer['evaluated']): ?>
                                <span>ครบแล้ว🆗</span>
                            <?php else: ?>
                                <span class="details" onclick="toggleDetails('<?php echo htmlspecialchars($layer['layer_name']); ?>')">ดูที่ยังไม่ประเมิน</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($layer['total'] != $layer['evaluated']): ?>
                        <tr class="details-container" id="details-<?php echo htmlspecialchars($layer['layer_name']); ?>">
                            <td colspan="5">
                                <p><strong>สถานที่ที่ยังไม่ได้ทำการประเมิน:</strong></p>
                                <ul>
                                    <?php foreach ($layer['locations'] as $location): ?>
                                        <li><?php echo htmlspecialchars($location); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>ไม่มีข้อมูลสถานะการประเมินหรือประเมินครบแล้ว✅</p>
    <?php endif; ?>
</div>


<script>
    function toggleDetails(layerName) {
        var detailsContainer = document.getElementById('details-' + layerName);
        if (detailsContainer.style.display === 'none' || detailsContainer.style.display === '') {
            detailsContainer.style.display = 'table-row'; // Show details
        } else {
            detailsContainer.style.display = 'none'; // Hide details
        }
    }
</script>
</body>
</html>
<?php endif; ?>