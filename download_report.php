<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Classes/PHPExcel.php'; // Path to PHPExcel.php

// Convert Gregorian date to Thai Buddhist calendar format
function toThaiDate($date) {
    $thaiMonths = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];
    $dateObj = new DateTime($date);
    $year = $dateObj->format('Y') + 543;
    $month = $dateObj->format('n');
    $day = $dateObj->format('j');

    return $day . ' ' . $thaiMonths[$month] . ' ' . $year;
}

// Create a new PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);
$mainSheet = $objPHPExcel->getActiveSheet();
$mainSheet->setTitle('Overview');

// Set column headers for the main sheet
$mainHeaders = ['F_Tank', 'F_Located', 'รายละเอียดการประเมินถัง'];
$mainSheet->fromArray($mainHeaders, null, 'A1');

// Apply header style
$headerStyle = [
    'fill' => [
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => ['argb' => 'FF8A2BE2'] // Purple color
    ],
    'font' => [
        'bold' => true,
        'color' => ['argb' => 'FFFFFFFF'], // White text
        'size' => 12
    ],
    'alignment' => [
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
    ]
];
$mainSheet->getStyle('A1:C1')->applyFromArray($headerStyle);

// Database connection
$conn = new mysqli('localhost', 'kasemra2_dcc', '123456', 'kasemra2_dcc');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch data for fire extinguishers, sorted by F_Tank
$sql = "SELECT fe.fcode, fe.F_Tank, fe.F_located, e.date_make, e.seal, e.pressure, e.hose, e.body 
        FROM fire_extinguisher fe
        JOIN evaluations e ON fe.fcode = e.fcode
        ORDER BY fe.F_Tank ASC, fe.fcode ASC, e.date_make ASC"; // Sorting by F_Tank first
$result = $conn->query($sql);

// Check if the query was successful
if ($result === FALSE) {
    die('Error in SQL query: ' . $conn->error);
}

// Store data by F_Tank
$dataByFTank = [];
while ($row = $result->fetch_assoc()) {
    $fTank = $row['F_Tank'];
    if (!isset($dataByFTank[$fTank])) {
        $dataByFTank[$fTank] = [];
    }
    $dataByFTank[$fTank][] = $row;
}

// Populate the main sheet with overview data
$rowIndex = 2; // Start from the second row
foreach ($dataByFTank as $fTank => $entries) {
    $fLocated = $entries[0]['F_located']; // Get the F_located value
    $sheetName = "FT_$fTank";

    // Create hyperlink to the corresponding sheet
    $hyperlink = "sheet://'$sheetName'!A1";
    $mainSheet->setCellValue("A$rowIndex", $fTank);
    $mainSheet->setCellValue("B$rowIndex", $fLocated);
    $mainSheet->setCellValue("C$rowIndex", "ดูรายละเอียดการประเมินถัง");
    $mainSheet->getCell("C$rowIndex")->getHyperlink()->setUrl($hyperlink);

    // Apply button-like styling
    $buttonStyle = [
        'font' => [
            'color' => ['argb' => 'FF0000FF'], // Blue text
            'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE,
            'bold' => true
        ],
        'alignment' => [
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allborders' => [
                'style' => PHPExcel_Style_Border::BORDER_THIN
            ]
        ]
    ];
    $mainSheet->getStyle("C$rowIndex")->applyFromArray($buttonStyle);

    // Create a new sheet for each F_Tank
    $newSheet = $objPHPExcel->createSheet();
    $newSheet->setTitle($sheetName);

    // Set headers for the new sheet
    $headers = ['ลำดับ', 'FCODE', 'Evaluation Date', 'Seal Status', 'Pressure Status', 'Hose Status', 'Body Status'];
    $newSheet->fromArray($headers, null, 'A1');

    // Apply header style
    $newSheet->getStyle('A1:G1')->applyFromArray($headerStyle);

    // Populate the new sheet with data
    $subRowIndex = 2;
    foreach ($entries as $index => $entry) {
        $formattedDate = toThaiDate($entry['date_make']);

        $newSheet->fromArray([
            $index + 1, // ลำดับ
            $entry['fcode'],
            $formattedDate,
            $entry['seal'],
            $entry['pressure'],
            $entry['hose'],
            $entry['body']
        ], null, "A$subRowIndex");

        // Apply cell style
        $newSheet->getStyle("A$subRowIndex:G$subRowIndex")->applyFromArray([
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ]
            ]
        ]);

        $subRowIndex++;
    }

    // Set column width for better readability
    foreach (range('A', 'G') as $columnID) {
        $newSheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    // Add a button to navigate back to the main sheet
    $newSheet->setCellValue('I1', 'Back to Overview');
    $newSheet->getCell('I1')->getHyperlink()->setUrl('sheet://Overview!A1');
    $newSheet->getStyle('I1')->applyFromArray($buttonStyle);

    // Add a table style (optional)
    $tableStyle = [
        'fill' => [
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => ['argb' => 'FFF0F0F0'] // Light gray background for table
        ]
    ];
    $newSheet->getStyle('A2:G' . ($subRowIndex - 1))->applyFromArray($tableStyle);

    $rowIndex++;
}

// Set column width for main sheet
foreach (range('A', 'C') as $columnID) {
    $mainSheet->getColumnDimension($columnID)->setAutoSize(true);
}

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
