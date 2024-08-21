<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Classes/PHPExcel.php'; // Path to PHPExcel.php

// Create a new PHPExcel object
$objPHPExcel = new PHPExcel();

// Database connection
$conn = new mysqli('localhost', 'kasemra2_dcc', '123456', 'kasemra2_dcc');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch distinct years from evaluations table
$sqlYears = "SELECT DISTINCT YEAR(date_make) AS year FROM evaluations ORDER BY year";
$resultYears = $conn->query($sqlYears);

if ($resultYears === FALSE) {
    die('Error in SQL query: ' . $conn->error);
}

// Thai month names
$thaiMonths = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];

// Custom sorting order for F_layer
$sortOrder = [
    'B2' => 1,
    'B1' => 2,
    '0' => 3,
    '1' => 4,
    '2' => 5,
    '3' => 6,
    '4' => 7,
    '5' => 8,
    '6' => 9,
    '7' => 10,
    '8' => 11,
    '9' => 12,
    '10' => 13,
    '11' => 14,
    '12' => 15
];

// Create worksheets for each year
while ($yearData = $resultYears->fetch_assoc()) {
    $year = $yearData['year'];
    
    // Add a new worksheet for each year
    $sheet = $objPHPExcel->createSheet();
    $sheet->setTitle($year);
    
    // Set column headers with purple background and white text
    $headers = array_merge(['Fire Code', 'F_id', 'F_water', 'F_layer', 'Location'], array_values($thaiMonths));
    $columnNames = range('A', 'Q');

    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
        ],
        'fill' => [
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => ['rgb' => '9B59B6'], // Purple color
        ],
        'alignment' => [
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ],
    ];

    foreach ($columnNames as $index => $column) {
        $cell = $column . '1';
        $sheet->setCellValue($cell, $headers[$index]);
        $sheet->getStyle($cell)->applyFromArray($headerStyle);
    }

    // Set column width for better readability
    foreach ($columnNames as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Fetch and sort data from fire_extinguisher table by F_Tank
    $sql = "SELECT * FROM fire_extinguisher 
            ORDER BY F_Tank ASC, 
                FIELD(F_layer, 'B2', 'B1', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'), 
                FCODE ASC";
    $result = $conn->query($sql);

    // Check if the query was successful
    if ($result === FALSE) {
        die('Error in SQL query: ' . $conn->error);
    }

    // Populate Excel sheet with data
    $row = 2; // Start from the second row

    while ($data = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $row, $data['FCODE']);
        $sheet->setCellValue('B' . $row, $data['F_id']);
        $sheet->setCellValue('C' . $row, $data['F_water']);
        $sheet->setCellValue('D' . $row, $data['F_layer']);
        $sheet->setCellValue('E' . $row, $data['F_located']);
        
        // Initialize all month cells to empty
        for ($col = 6; $col <= 17; $col++) {
            $sheet->setCellValueByColumnAndRow($col, $row, '');
        }
        
        // Fetch evaluation data for the current fire extinguisher
        $FCODE = $data['FCODE'];
        $sqlEval = "SELECT date_make FROM evaluations WHERE FCODE = '$FCODE' AND YEAR(date_make) = '$year'";
        $resultEval = $conn->query($sqlEval);
        
        if ($resultEval !== FALSE) {
            if ($resultEval->num_rows > 0) {
                while ($evalData = $resultEval->fetch_assoc()) {
                    $date = new DateTime($evalData['date_make']);
                    $month = intval($date->format('m'));
                    
                    // Place checkmark in the corresponding month column (adjust index by subtracting 1)
                    $columnIndex = 5 + ($month - 1); // Adjust column index for months
                    $sheet->setCellValueByColumnAndRow($columnIndex, $row, '✔');
                }
            } else {
                // Handle case where there are no evaluations for this fire extinguisher
                // You can leave the cells empty or provide some default value
            }
        } else {
            die('Error in SQL query: ' . $conn->error);
        }
        
        $row++;
    }

    // Apply border style to all cells
    $styleArray = [
        'borders' => [
            'allborders' => [
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    $sheet->getStyle('A1:Q' . ($row - 1))->applyFromArray($styleArray);
}

// Remove the default sheet created by PHPExcel
$objPHPExcel->removeSheetByIndex(0);

// Set filename and output
$filename = 'fire_extinguisher_report_' . date('YmdHis') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

try {
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
} catch (Exception $e) {
    die('Error creating Excel file: ' . $e->getMessage());
}
exit();
?>
