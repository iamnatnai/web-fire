<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Classes/PHPExcel.php'; // Path to PHPExcel.php

// Create a new PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);
$sheet = $objPHPExcel->getActiveSheet();

// Set column headers with purple background and white text
$headers = ['Fire Code', 'Location', 'Evaluation Date', 'Seal Status', 'Pressure Status', 'Hose Status', 'Body Status'];
$columnNames = range('A', 'G');

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

// Database connection
$conn = new mysqli('localhost', 'kasemra2_dcc', '123456', 'kasemra2_dcc');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch data and sort by evaluation date
$sql = "SELECT fe.fcode, fe.F_located, e.date_make, e.seal, e.pressure, e.hose, e.body 
        FROM fire_extinguisher fe
        JOIN evaluations e ON fe.fcode = e.fcode
        ORDER BY e.date_make ASC"; // Sorting by evaluation date in ascending order
$result = $conn->query($sql);

// Check if the query was successful
if ($result === FALSE) {
    die('Error in SQL query: ' . $conn->error);
}

// Thai month names
$thaiMonths = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];

// Populate Excel sheet with data
$row = 2; // Start from the second row
while ($data = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $data['fcode']);
    $sheet->setCellValue('B' . $row, $data['F_located']);
    
    // Format the date to Thai date format and time
    $date = new DateTime($data['date_make']);
    $day = $date->format('d');
    $month = $thaiMonths[intval($date->format('m'))];
    $year = $date->format('Y') + 543; // Convert to Thai Buddhist Year
    $formattedDate = $day . ' ' . $month . ' ' . $year . ' ' . $date->format('H:i:s');
    $sheet->setCellValue('C' . $row, $formattedDate);
    
    $sheet->setCellValue('D' . $row, $data['seal']);
    $sheet->setCellValue('E' . $row, $data['pressure']);
    $sheet->setCellValue('F' . $row, $data['hose']);
    $sheet->setCellValue('G' . $row, $data['body']);
    
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

$sheet->getStyle('A1:G' . ($row - 1))->applyFromArray($styleArray);

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
