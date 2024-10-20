<?php
include 'config.php';

$fcode = isset($_GET['fcode']) ? $_GET['fcode'] : '';

// Base query with F_water column
$query = "SELECT fe.fcode, fe.F_Tank, fe.F_located, fe.F_water, e.date_make, e.seal, e.pressure, e.hose, e.body,e.construct";
$query .= ", e.w_glass, e.w_val, e.w_hose, e.w_construct";
$query .= ", e.comment, e.evaluator";
$query .= " FROM fire_extinguisher fe
          JOIN evaluations e ON fe.fcode = e.fcode";

if ($fcode !== '' && $fcode !== 'All') {
    $query .= " WHERE fe.fcode = ?";
}

$query .= " ORDER BY fe.F_Tank ASC, fe.fcode ASC, e.date_make ASC";

$stmt = $conn->prepare($query);

if ($fcode !== '' && $fcode !== 'All') {
    $stmt->bind_param("s", $fcode);
}

$stmt->execute();
$result = $stmt->get_result();

// Check if any rows have F_water with a valid value
$showWaterColumns = false;
$infoMessage = "รหัสตู้สายน้ำ: ไม่ได้อยู่ในตู้";
if ($fcode !== '' && $fcode !== 'All') {
    $waterQuery = "SELECT DISTINCT fe.F_water FROM fire_extinguisher fe WHERE fe.fcode = ?";
    $waterStmt = $conn->prepare($waterQuery);
    $waterStmt->bind_param("s", $fcode);
    $waterStmt->execute();
    $waterResult = $waterStmt->get_result();
    if ($waterResult->num_rows > 0) {
        $waterRow = $waterResult->fetch_assoc();
        $showWaterColumns = !empty($waterRow['F_water']);
        if ($showWaterColumns) {
            $infoMessage = "รหัสตู้สายน้ำ: " . htmlspecialchars($waterRow['F_water']);
        }
    }
}

function formatYesNo($value) {
    return $value == 'yes' ? '✔' : '❌';
}

function formatDateThai($date) {
    $dateObj = new DateTime($date);
    $thaiMonths = [
        'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
        'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
    ];
    $year = $dateObj->format('Y') + 543;
    $month = $thaiMonths[$dateObj->format('n') - 1];
    $day = $dateObj->format('j');
    return "$day $month $year";
}

// Fetch details for selected fcode
$info = null;
if ($fcode !== '' && $fcode !== 'All') {
    $infoQuery = "SELECT fcode, F_id, F_Tank, F_located, F_water FROM fire_extinguisher WHERE fcode = ?";
    $infoStmt = $conn->prepare($infoQuery);
    $infoStmt->bind_param("s", $fcode);
    $infoStmt->execute();
    $infoResult = $infoStmt->get_result();
    $info = $infoResult->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการประเมิน</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
    width: 100%; /* ใช้ความกว้างเต็มพื้นที่ */
    max-width: 1200px; /* ความกว้างสูงสุดตามที่ต้องการ */
    margin: 0 auto; /* จัดให้อยู่กลางหน้า */
    padding: 20px; /* กำหนดระยะห่างภายใน */
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    box-sizing: border-box; /* รวม padding และ border ในความกว้าง */
}
        h1 {
            text-align: center;
            color: #6a1b9a;
            margin-bottom: 20px;
        }
        h2 {
            text-align: center;
            color: #6a1b9a;
            margin-bottom: 17px;
        }
        .dropdown {
            margin-bottom: 20px;
        }
        .dropdown form {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .dropdown label {
            margin-right: 10px;
            font-size: 1.1em;
            color: #333;
        }
        .dropdown select {
            padding: 8px;
            font-size: 1em;
            border: 1px solid #ddd;
            border-radius: 4px;
            outline: none;
        }
        .info {
            margin-bottom: 20px;
            font-size: 1.2em;
            font-weight: bold;
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #6a1b9a;
            color: white;
        }
        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tbody tr:hover {
            background-color: #e0e0e0;
        }
        tfoot td {
            background-color: #6a1b9a;
            color: white;
            padding: 12px;
            font-weight: bold;
        }
        .search-bar {
    text-align: center;
    margin-bottom: 20px;
}
.search-bar {
    position: relative;
    margin-bottom: 20px;
}

.search-bar input {
    padding: 10px;
    width: 100%;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1em;
}

.search-result {
    position: absolute;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ccc;
    background-color: white;
    z-index: 100;
    display: none;
}

.search-result div {
    padding: 10px;
    cursor: pointer;
}

.search-result div:hover {
    background-color: #f0f0f0;
}

.search-bar input {
    padding: 8px;
    font-size: 1em;
    width: 50%;
    border: 1px solid #ddd;
    border-radius: 4px;
    outline: none;
}
.large-icon {
                    display: block;
                    width: 150px; /* Adjust size as needed */
                    height: auto; /* Maintain aspect ratio */
                    margin: 0 auto 20px; /* Center the image and add margin below */
        }
        /* Hide columns on mobile */
        @media (max-width: 768px) {
            .container {
            padding: 10px; /* ปรับ padding ให้เหมาะกับหน้าจอเล็ก */
            }
            .details-column {
                display: none;
            }
            .details-button, .show-water-button {
                display: block;
                background-color: #6a1b9a;
                color: white;
                border: none;
                padding: 10px;
                cursor: pointer;
                text-align: center;
                margin-top: 10px;
            }
            .details-content {
                display: none;
                margin-top: 10px;
            }
            .show-details {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="container">
    <img src="/hos/fire_ex/home-icon.png" alt="Home Icon" class="large-icon">
        <h1>ประวัติการประเมินถังดับเพลิง</h1>
        <h2>โรงพยาบาลเกษมราษฎร์ ประชาชื่น</h2>
        <div class="search-bar">
        <label for="search">ค้นหาตามที่ตำแหน่งของถัง/ตู้ หรือ เลขถัง:</label>
        <input type="text" id="search" placeholder="ค้นหาข้อมูล..." onkeyup="searchTanks()" autocomplete="off">
        <div id="searchResult" class="search-result"></div>
        </div>

        <div class="dropdown">
            <form action="" method="get">
                <label for="fcode">เลือก CODE:</label>
                <select name="fcode" id="fcode" onchange="this.form.submit()">
                    <option value=""></option>
                    <?php
                    $fcodeQuery = "SELECT DISTINCT fcode FROM fire_extinguisher ORDER BY F_Tank ASC";
                    $fcodeResult = $conn->query($fcodeQuery);
                    while ($row = $fcodeResult->fetch_assoc()) {
                        $selected = $fcode === $row['fcode'] ? 'selected' : '';
                        echo "<option value='{$row['fcode']}' $selected>{$row['fcode']}</option>";
                    }
                    ?>
                </select>
            </form>
        </div>
        
        <?php if ($fcode !== '' && $fcode !== 'All' && $info): ?>
            <div class="info">
                CODE: <?= htmlspecialchars($info['fcode']) ?><br>
                ลำดับที่: <?= htmlspecialchars($info['F_Tank']) ?><br>
                ตำแหน่งของถังดับเพลิง/ตู้สายน้ำ: <?= htmlspecialchars($info['F_located']) ?><br>
                รหัสถังดับเพลิง: <?= htmlspecialchars($info['F_id']) ?><br>
                <?= $infoMessage ?><br>
            </div>
        <?php endif; ?>
        <?php if ($fcode !== '' && $fcode !== 'All'): ?>
            <button class="details-button" onclick="toggleDetails()">แสดง/ซ่อน รายละเอียด</button>
            <div class="search-bar">
            <label for="searchdetail">ค้นหาข้อมูลในตาราง:</label>
            <input type="text" id="searchdetail" placeholder="ค้นหาข้อมูล..." onkeyup="filterTable()">
            </div>


            <table>
                <thead>
                    <tr>
                        <th>วันที่</th>
                        <th class="details-column">คันบังคับ/สลัก</th>
                        <th class="details-column">ความดัน</th>
                        <th class="details-column">สาย</th>
                        <th class="details-column">ตัวถัง</th>
                        <th class="details-column">สิ่งกีดขวางถังดับเพลิง</th>
                        <?php if ($showWaterColumns): ?>
                            <th class="details-column">กระจก/ประตู</th>
                            <th class="details-column">วาล์ว</th>
                            <th class="details-column">หัวฉีด</th>
                            <th class="details-column">สิ่งกีดขวางตู้น้ำดับเพลิง</th>
                        <?php endif; ?>
                        <th>ความคิดเห็น</th>
                        <th>ผู้ประเมิน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . formatDateThai($row['date_make']) . "</td>";
                        echo "<td class='details-column'>" . formatYesNo($row['seal']) . "</td>";
                        echo "<td class='details-column'>" . formatYesNo($row['pressure']) . "</td>";
                        echo "<td class='details-column'>" . formatYesNo($row['hose']) . "</td>";
                        echo "<td class='details-column'>" . formatYesNo($row['body']) . "</td>";
                        echo "<td class='details-column'>" . formatYesNo($row['construct']) . "</td>";
                        if ($showWaterColumns) {
                            echo "<td class='details-column'>" . formatYesNo($row['w_glass']) . "</td>";
                            echo "<td class='details-column'>" . formatYesNo($row['w_val']) . "</td>";
                            echo "<td class='details-column'>" . formatYesNo($row['w_hose']) . "</td>";
                            echo "<td class='details-column'>" . formatYesNo($row['w_construct']) . "</td>";
                        }
                        echo "<td>" . htmlspecialchars($row['comment']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['evaluator']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="12">ระบบการประเมินถังดับเพลิงโรงพยาบาลเกษมราษฎร์ ประชาชื่น</td>
                    </tr>
                </tfoot>
            </table>
        <?php else: ?>
            <p>กรุณาเลือก FCODE เพื่อตรวจสอบประวัติการประเมิน</p>
        <?php endif; ?>
    </div>

    <!-- Script to handle details display -->
    <script>
        function toggleDetails() {
            const detailsColumns = document.querySelectorAll('.details-column');
            detailsColumns.forEach(column => {
                column.style.display = column.style.display === 'none' ? 'table-cell' : 'none';
            });
        }
    // ฟังก์ชันเลือกผลลัพธ์จากที่ค้นหา
    function selectTank(fcode) {
    // สร้าง URL โดยใช้ fcode แต่ส่งกลับมาที่หน้าเดิม
    var url = window.location.origin + window.location.pathname + "?fcode=" + fcode;
    
    // เปลี่ยนหน้าไปยัง URL เดิมพร้อมส่งค่า fcode
    window.location.href = url;
}

    function searchTanks() {
        const query = document.getElementById("search").value;
        const resultDiv = document.getElementById("searchResult");

        if (query.length > 0) { // เริ่มค้นหาหลังจากพิมพ์ 3 ตัวอักษรขึ้นไป
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "search_tanks.php?q=" + query, true);
            xhr.onload = function() {
                if (this.status === 200) {
                    resultDiv.innerHTML = this.responseText;
                    resultDiv.style.display = "block"; // แสดงผลลัพธ์การค้นหา
                }
            };
            xhr.send();
        } else {
            resultDiv.style.display = "none"; // ซ่อนผลลัพธ์หากค้นหาน้อยกว่า 3 ตัวอักษร
        }
    }
    function filterTable() {
    // Get the value from the search input
    let input = document.getElementById("searchdetail").value.toLowerCase();
    let table = document.querySelector("table");
    let rows = table.getElementsByTagName("tr");

    // Loop through all rows and hide the ones that don't match the search query
    for (let i = 1; i < rows.length; i++) { // Start from 1 to skip the header
        let row = rows[i];
        let cells = row.getElementsByTagName("td");
        let match = false;
        
        // Check each cell in the row for the search query
        for (let j = 0; j < cells.length; j++) {
            let cell = cells[j];
            if (cell && cell.textContent.toLowerCase().indexOf(input) > -1) {
                match = true;
                break;
            }
        }

        // Toggle visibility of the row based on the search query
        if (match) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    }
}
    </script>
</body>
</html>
