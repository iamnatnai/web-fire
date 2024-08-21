<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli('localhost', 'kasemra2_dcc', '123456', 'kasemra2_dcc');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get current month and year
$currentMonth = date('m');
$currentYear = date('Y');

// Query to get evaluated count for the current month and year
$queryEvaluated = "SELECT COUNT(*) as evaluated FROM evaluations 
                    JOIN fire_extinguisher ON evaluations.FCODE = fire_extinguisher.FCODE 
                    WHERE MONTH(evaluations.date_make) = ? AND YEAR(evaluations.date_make) = ?";
$stmtEvaluated = $conn->prepare($queryEvaluated);
if ($stmtEvaluated === false) {
    die('Prepare failed: ' . $conn->error);
}
$stmtEvaluated->bind_param('ii', $currentMonth, $currentYear);
$stmtEvaluated->execute();
$resultEvaluated = $stmtEvaluated->get_result();
if ($resultEvaluated === false) {
    die('Query failed: ' . $stmtEvaluated->error);
}
$evaluatedRow = $resultEvaluated->fetch_assoc();
$evaluatedCount = $evaluatedRow['evaluated'];

// Query to get total count of fire extinguishers
$queryTotal = "SELECT COUNT(*) as total FROM fire_extinguisher";
$stmtTotal = $conn->prepare($queryTotal);
if ($stmtTotal === false) {
    die('Prepare failed: ' . $conn->error);
}
$stmtTotal->execute();
$resultTotal = $stmtTotal->get_result();
if ($resultTotal === false) {
    die('Query failed: ' . $stmtTotal->error);
}
$totalRow = $resultTotal->fetch_assoc();
$totalCount = $totalRow['total'];

// Calculate percentage
$percentage = ($totalCount > 0) ? ($evaluatedCount / $totalCount) * 100 : 0;

// Output percentage as JSON
header('Content-Type: application/json');
echo json_encode(['currentMonth' => round($percentage)]);

// Close connections
$stmtEvaluated->close();
$stmtTotal->close();
$conn->close();
?>
