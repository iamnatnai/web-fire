<?php
header('Content-Type: application/json');

include 'config.php';

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Query to fetch data from both tables
$query = "SELECT layerforfire.id, 
       layerforfire.layer_code, 
       layerforfire.description, 
       layerforfire.image_path,
       fire_extinguisher.F_Tank, 
       fire_extinguisher.FCODE, 
       fire_extinguisher.F_water, 
       fire_extinguisher.F_layer, 
       fire_extinguisher.F_located,
       fire_extinguisher.image_path AS extinguisher_image_path
FROM layerforfire
LEFT JOIN fire_extinguisher ON layerforfire.layer_code = fire_extinguisher.F_layer  
ORDER BY layerforfire.id ASC;
";

$result = $conn->query($query);

$data = [];

if ($result->num_rows > 0) {
    // Fetch data from each row
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode(["status" => "success", "data" => $data]);

$conn->close();
?>
