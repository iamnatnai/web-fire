<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
<?php else: ?>
<?php
// Database connection
include 'configregis.php'; // Your database connection file

// Get FCODE from URL
$fcode = isset($_GET['data']) ? $_GET['data'] : '';

// Query to get F_water value
$query = $pdo->prepare("SELECT F_water FROM fire_extinguisher WHERE FCODE = ?");
$query->execute([$fcode]);
$result = $query->fetch(PDO::FETCH_ASSOC);

$f_water = $result['F_water'] ?? null; // Get F_water value
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Page</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="icon" href="/mick/my-php/favicon.ico" type="image/x-icon">
    <?php include 'navbar.php'; ?>
    <style>
        /* Your existing CSS here */
    </style>
</head>
<body>
    <div class="container">
        <h1>ฟอร์มตรวจเช็คถังดับเพลิง</h1>
        <h2>ผู้ประเมิน: <?php echo htmlspecialchars($_SESSION['firstname']); ?></h2> <!-- Display the evaluator's name here -->
        <p id="fire-code">ชื่อถัง: <span id="fcode-display"><?php echo htmlspecialchars($fcode); ?></span></p>
        <form id="evaluationForm" method="POST" enctype="multipart/form-data" action="evaluate_data.php">
            <!-- Hidden field to store FCODE -->
            <input type="hidden" id="fcode" name="fcode" value="<?php echo htmlspecialchars($fcode); ?>">
            <!-- Date Input -->
            <div class="form-group">
                <label for="evaluationDate">วันที่และเวลา:</label>
                <input type="datetime-local" id="evaluationDate" name="evaluationDate">
            </div>
            <div class="form-group">
                <label for="seal">ซีล:</label>
                <ul class="radio-list">
                    <li>
                        <input type="radio" id="sealYes" name="seal" value="yes">
                        <label for="sealYes">ผ่าน</label>
                    </li>
                    <li>
                        <input type="radio" id="sealNo" name="seal" value="no">
                        <label for="sealNo">ไม่ผ่าน</label>
                    </li>
                </ul>
            </div>
            <div class="form-group">
                <label for="pressure">แรงดัน:</label>
                <ul class="radio-list">
                    <li>
                        <input type="radio" id="pressureYes" name="pressure" value="yes">
                        <label for="pressureYes">ผ่าน</label>
                    </li>
                    <li>
                        <input type="radio" id="pressureNo" name="pressure" value="no">
                        <label for="pressureNo">ไม่ผ่าน</label>
                    </li>
                </ul>
            </div>
            <div class="form-group">
                <label for="hose">สายวัด:</label>
                <ul class="radio-list">
                    <li>
                        <input type="radio" id="hoseYes" name="hose" value="yes">
                        <label for="hoseYes">ผ่าน</label>
                    </li>
                    <li>
                        <input type="radio" id="hoseNo" name="hose" value="no">
                        <label for="hoseNo">ไม่ผ่าน</label>
                    </li>
                </ul>
            </div>
            <div class="form-group">
                <label for="body">ตัวถัง:</label>
                <ul class="radio-list">
                    <li>
                        <input type="radio" id="bodyYes" name="body" value="yes">
                        <label for="bodyYes">ผ่าน</label>
                    </li>
                    <li>
                        <input type="radio" id="bodyNo" name="body" value="no">
                        <label for="bodyNo">ไม่ผ่าน</label>
                    </li>
                </ul>
            </div>
            <div class="form-group">
                <label for="image">อัพโหลดรูปภาพ:</label>
                <input type="file" id="image" name="image" accept="image/*" capture="camera">
                <label for="image" class="btn upload-btn">ถ่ายรูปหรือเลือกไฟล์</label>
                <span id="file-name">ยังไม่ได้เลือกไฟล์</span>
            </div>
            <!-- Additional Questions if F_water is not null -->
            <?php if ($f_water): ?>
                <div class="form-group">
                    <label for="waterTest1">คำถามเพิ่มเติม 1:</label>
                    <ul class="radio-list">
                        <li>
                            <input type="radio" id="waterTest1Yes" name="waterTest1" value="yes">
                            <label for="waterTest1Yes">ผ่าน</label>
                        </li>
                        <li>
                            <input type="radio" id="waterTest1No" name="waterTest1" value="no">
                            <label for="waterTest1No">ไม่ผ่าน</label>
                        </li>
                    </ul>
                </div>
                <div class="form-group">
                    <label for="waterTest2">คำถามเพิ่มเติม 2:</label>
                    <ul class="radio-list">
                        <li>
                            <input type="radio" id="waterTest2Yes" name="waterTest2" value="yes">
                            <label for="waterTest2Yes">ผ่าน</label>
                        </li>
                        <li>
                            <input type="radio" id="waterTest2No" name="waterTest2" value="no">
                            <label for="waterTest2No">ไม่ผ่าน</label>
                        </li>
                    </ul>
                </div>
                <div class="form-group">
                    <label for="waterTest3">คำถามเพิ่มเติม 3:</label>
                    <ul class="radio-list">
                        <li>
                            <input type="radio" id="waterTest3Yes" name="waterTest3" value="yes">
                            <label for="waterTest3Yes">ผ่าน</label>
                        </li>
                        <li>
                            <input type="radio" id="waterTest3No" name="waterTest3" value="no">
                            <label for="waterTest3No">ไม่ผ่าน</label>
                        </li>
                    </ul>
                </div>
                <div class="form-group">
                    <label for="waterTest4">คำถามเพิ่มเติม 4:</label>
                    <ul class="radio-list">
                        <li>
                            <input type="radio" id="waterTest4Yes" name="waterTest4" value="yes">
                            <label for="waterTest4Yes">ผ่าน</label>
                        </li>
                        <li>
                            <input type="radio" id="waterTest4No" name="waterTest4" value="no">
                            <label for="waterTest4No">ไม่ผ่าน</label>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
            <button type="submit" class="btn">ส่งข้อมูล</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fcode = document.getElementById('fcode').value;

            // Set the date input to today's date with time
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');

            const dateTimeString = `${year}-${month}-${day}T${hours}:${minutes}`;
            document.getElementById('evaluationDate').value = dateTimeString;

            // Display the selected file name
            const fileInput = document.getElementById('image');
            const fileNameSpan = document.getElementById('file-name');
            fileInput.addEventListener('change', function () {
                const fileName = fileInput.files.length ? fileInput.files[0].name : 'ยังไม่ได้เลือกไฟล์';
                fileNameSpan.textContent = fileName;
            });
        });
    </script>
</body>
</html>
<?php endif; ?>
