<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include 'configregis.php';

// Retrieve and decode the POST data
$data = json_decode(file_get_contents('php://input'), true);
$fcode = isset($data['fcode']) ? $data['fcode'] : null;

// Log the received fcode
error_log('Received FCODE: ' . $fcode);

// Ensure fcode is provided
if ($fcode === null) {
    echo json_encode(['F_water' => false]);
    exit;
}

try {
    // Prepare and execute the query to check if the fcode exists
    $query = $pdo->prepare("SELECT F_water FROM fire_extinguisher WHERE FCODE = :fcode");
    $query->bindParam(':fcode', $fcode);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    // Log the query result
    error_log('Query result: ' . print_r($result, true));

    // Check if F_water is present and respond
    if ($result && isset($result['F_water'])) {
        $response = ['F_water' => (bool)$result['F_water']];
    } else {
        $response = ['F_water' => false];
    }
    echo json_encode($response);
} catch (PDOException $e) {
    // Handle SQL errors
    error_log('Database query error: ' . $e->getMessage());
    echo json_encode(['F_water' => false]);
}
?>
