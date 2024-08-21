<?php
// Include the database configuration file
include 'config.php';

// Initialize response
$response = [];

// Check if GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if 'data' is set
    if (isset($_GET['data'])) {
        $qrData = $_GET['data'];
        
        // Protect against SQL Injection
        $qrData = $conn->real_escape_string($qrData);

        // Query the database for fire extinguisher information
        $sql = "SELECT * FROM fire_extinguisher WHERE FCODE='$qrData'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $layerName = $row['F_layer'];

            // Get current month and year
            $currentMonth = date('m');
            $currentYear = date('Y');

            // Query the evaluations table
            $sqlEvaluation = "SELECT * FROM evaluations WHERE FCODE='$qrData' AND MONTH(date_make)='$currentMonth' AND YEAR(date_make)='$currentYear'";
            $resultEvaluation = $conn->query($sqlEvaluation);

            $response = [
                "status" => "found",
                "data" => [
                    "F_Tank" => $row["F_Tank"],
                    "FCODE" => $row["FCODE"],
                    "F_water" => $row["F_water"],
                    "F_located" => $row["F_located"],
                    "image_path" => $row["image_path"],
                    "evaluation_done" => $resultEvaluation->num_rows > 0 // Check if evaluation is done
                ]
            ];

            // Get total and evaluated counts for the specific layer of the given FCODE
            $sqlTotal = "SELECT COUNT(*) AS total_count FROM fire_extinguisher WHERE F_layer='$layerName'";
            $resultTotal = $conn->query($sqlTotal);
            $total = $resultTotal->fetch_assoc()['total_count'];

            $sqlEvaluated = "SELECT COUNT(*) AS evaluated_count FROM evaluations
                             JOIN fire_extinguisher ON evaluations.FCODE = fire_extinguisher.FCODE
                             WHERE fire_extinguisher.F_layer='$layerName' AND MONTH(evaluations.date_make) = $currentMonth AND YEAR(evaluations.date_make) = $currentYear";
            $resultEvaluated = $conn->query($sqlEvaluated);
            $evaluated = $resultEvaluated->fetch_assoc()['evaluated_count'];

            $response['layerStatus'] = [
                "layer_name" => $layerName,
                "evaluated" => $evaluated,
                "total" => $total
            ];

            // Get list of locations in this layer that haven't been evaluated yet
            $sqlUnEvaluated = "SELECT F_located FROM fire_extinguisher
                               WHERE F_layer='$layerName' AND FCODE NOT IN (SELECT FCODE FROM evaluations WHERE MONTH(date_make) = $currentMonth AND YEAR(date_make) = $currentYear)";
            $resultUnEvaluated = $conn->query($sqlUnEvaluated);

            $unEvaluatedLocations = [];
            while ($location = $resultUnEvaluated->fetch_assoc()) {
                $unEvaluatedLocations[] = $location['F_located'];
            }

            $response['unEvaluatedLocations'] = $unEvaluatedLocations;
        } else {
            $response = [
                "status" => "not_found"
            ];
        }
    } else {
        $response = [
            "status" => "error",
            "message" => "QR Code data missing"
        ];
    }
} else {
    $response = [
        "status" => "error",
        "message" => "Invalid request method"
    ];
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="/mick/my-php/favicon.ico" type="image/x-icon">
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
        .success-message {
            color: #28a745; /* Green color */
            font-size: 16px;
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #28a745;
            border-radius: 4px;
            background-color: #d4edda; /* Light green background */
            display: flex;
            align-items: center;
        }
        .success-message i {
            margin-right: 10px;
            color: #28a745;
        }
        .layer-info {
            margin-top: 20px;
        }
        .layer-info p {
            font-size: 16px;
            margin: 5px 0;
        }
        .details-container {
            margin-top: 20px;
            display: none; /* Initially hidden */
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
        .show-details {
            cursor: pointer;
            color: #007bff;
            text-decoration: underline;
        }
        
    </style>
</head>
<body>
<div class="container">
    <h1>ข้อมูลถังดับเพลิง</h1>
    <?php if ($response['status'] === 'found'): ?>
        <?php if ($response['data']['evaluation_done']): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                การประเมินสำหรับถังนี้ได้ทำเรียบร้อยแล้วในเดือนนี้
            </div>
        <?php else: ?>
            <a href="evaluation_page.php?data=<?php echo urlencode($response['data']['FCODE']); ?>" class="btn">ไปยังแบบประเมิน</a>
        <?php endif; ?>
        <p><strong>ลำดับถังที่:</strong> <?php echo htmlspecialchars($response['data']['F_Tank']); ?></p>
        <p><strong>สายน้ำ:</strong> <?php echo htmlspecialchars($response['data']['F_water']); ?></p>
        <p><strong>ที่อยู่:</strong> <?php echo htmlspecialchars($response['data']['F_located']); ?></p>
        <p><strong>รูปภาพ:</strong></p>
        <img src="<?php echo htmlspecialchars("/mick/my-php" . $response['data']['image_path']); ?>" alt="Fire Extinguisher Image">

        <!-- Layer Status Information -->
        <?php if (!empty($response['layerStatus'])): ?>
            <div class="layer-info">
                <h2>ข้อมูลการประเมินชั้น: <?php echo htmlspecialchars($response['layerStatus']['layer_name']); ?></h2>
                <p>
                    เหลืออีก <?php echo htmlspecialchars($response['layerStatus']['total'] - $response['layerStatus']['evaluated']); ?> ที่ยังไม่ได้ทำการประเมิน 
                    จากทั้งหมด <?php echo htmlspecialchars($response['layerStatus']['total']); ?> ที่ 
                    <span class="show-details" onclick="toggleDetails()">แสดงรายละเอียด</span>
                </p>
            </div>
        <?php endif; ?>

        <!-- Details for un-evaluated locations -->
        <?php if (!empty($response['unEvaluatedLocations'])): ?>
            <div class="details-container" id="detailsContainer">
                <p><strong>สถานที่ที่ยังไม่ได้ทำการประเมิน:</strong></p>
                <ul>
                    <?php foreach ($response['unEvaluatedLocations'] as $location): ?>
                        <li><?php echo htmlspecialchars($location); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <p>ไม่มีสถานที่ที่ยังไม่ได้ทำการประเมินในชั้นนี้</p>
        <?php endif; ?>
    <?php else: ?>
        <p>ไม่พบข้อมูลในฐานข้อมูล</p>
    <?php endif; ?>
</div>

<script>
    function toggleDetails() {
        var detailsContainer = document.getElementById('detailsContainer');
        if (detailsContainer.style.display === 'none' || detailsContainer.style.display === '') {
            detailsContainer.style.display = 'block';
        } else {
            detailsContainer.style.display = 'none';
        }
    }
</script>
</body>
</html>
