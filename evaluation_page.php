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
<?php else:
    require 'config.php'; // เชื่อมต่อฐานข้อมูล

    $fcode = isset($_GET['data']) ? $_GET['data'] : '';
    if ($fcode) {
        $sql = "SELECT F_water FROM fire_extinguisher WHERE FCODE = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            die('Error preparing the statement: ' . htmlspecialchars($conn->error));
        }
        
        $stmt->bind_param('s', $fcode);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $showAdditionalQuestions = $result && !empty($result['F_water']);
    } else {
        $showAdditionalQuestions = false;
    }
    
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
    <link rel="icon" href="/hos/fire_ex/favicon.ico" type="image/x-icon">
    <?php include 'navbar.php'; ?>
    <style>
/* General Styles */
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
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease-in-out;
}

.container:hover {
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

h1 {
    font-size: 24px;
    text-align: center;
    color: #ae00ff;
    margin-bottom: 20px;
}

/* Form Group and List Styles */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    font-weight: bold;
    margin-right: 15px;
    font-size: 18px;
}

.radio-list {
    list-style: none;
    padding-left: 0;
    margin: 0;
    display: flex;
    justify-content: center;
}

.radio-list li {
    margin-right: 20px;
    position: relative;
    padding-left: 40px;
    line-height: 25px;
    cursor: pointer;
    user-select: none;
    font-size: 20px;
    color: #c209f0;
    transition: color 0.3s ease-in-out;
}

.radio-list li:hover {
    color: #6b095e;
}

.radio-list input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.radio-list input[type="radio"] + label::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 30px;
    height: 30px;
    border: 2px solid #c300ff;
    border-radius: 50%;
    background-color: #fff;
    transition: background-color 0.3s ease-in-out, border-color 0.3s ease-in-out;
}

.radio-list input[type="radio"]:checked + label::before {
    background-color: #e207ff;
    border-color: #59086d;
}

.radio-list input[type="radio"][value="no"]:checked + label::before {
    content: "❌ ";
    color: red;
    font-size: 1.2em;
}

.radio-list input[type="radio"][value="yes"]:checked + label::before {
    content: " ✔";
    color: green;
    font-size: 1.7em;
}

/* Button Styles */
.btn {
    display: inline-block;
    padding: 15px 30px;
    font-size: 18px;
    color: #fff;
    background-color: #41008b;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    text-align: center;
    cursor: pointer;
    margin-top: 20px;
    width: 100%;
    transition: background-color 0.3s ease-in-out, transform 0.2s;
}

.btn:hover {
    background-color: #7100b3;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

#image {
    opacity: 0;
    position: absolute;
    width: 0;
    height: 0;
}

/* Custom Button for File Input */
.upload-btn {
    display: inline-block;
    padding: 10px 20px;
    font-size: 16px;
    color: #fff;
    background-color: #e014a3;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    cursor: pointer;
    transition: background-color 0.3s ease-in-out, transform 0.2s;
    max-width: 200px;
    text-align: center;
}

.upload-btn:hover {
    background-color: #cf29b4;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Display the Selected File Name */
#file-name {
    display: block;
    margin-top: 10px;
    font-size: 16px;
    color: #555;
}

.date-time-container {
    position: relative;
    display: inline-block;
}

input[type="datetime-local"] {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 10px;
    font-size: 16px;
    color: #333;
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: border-color 0.3s ease-in-out;
    width: 100%;
}

input[type="datetime-local"]:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 2px rgba(38, 143, 255, 0.2);
}

.date-time-label {
    position: absolute;
    top: 10px;
    left: 15px;
    font-size: 16px;
    color: #aaa;
    pointer-events: none;
    transition: all 0.3s ease-in-out;
    transform-origin: left top;
}

input[type="datetime-local"]:focus ~ .date-time-label,
input[type="datetime-local"]:not(:placeholder-shown) ~ .date-time-label {
    top: -10px;
    left: 10px;
    font-size: 12px;
    color: #007bff;
    background-color: #fff;
    padding: 0 5px;
    border-radius: 3px;
}

input[type="text"]#comment {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 10px;
    font-size: 16px;
    color: #333;
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: border-color 0.3s ease-in-out;
    width: calc(100% - 40px); /* Adjust width to account for padding */
    margin-top: 5px;
}

input[type="text"]#comment:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 2px rgba(38, 143, 255, 0.2);
}

input[type="text"]#comment::placeholder {
    color: #aaa;
    font-size: 16px;
}
    </style>
</head>

<body>
    
    <div class="container">
        <h1>ฟอร์มตรวจเช็คถังดับเพลิง</h1>
        <h2>ผู้ประเมิน: <?php echo htmlspecialchars($_SESSION['firstname']); ?></h2> <!-- Display the evaluator's name here -->
        <h3 style="color: red;">**ตรวจสอบชื่อผู้ประเมิน หากบันทึกแล้วชื่อของท่านจะถูกบันทึกลงระบบโดยชื่อนั้นและจะไม่มีการเปลี่ยนแปลงย้อนหลังได้**</h3>
        <p id="fire-code">ชื่อถัง: <span id="fcode-display"></span></p>
        <form id="evaluationForm" method="POST" enctype="multipart/form-data" action="evaluate_data.php">
            <!-- Date Input -->
            <div class="form-group">
                <label for="evaluationDate">วันที่และเวลา:</label>
                <input type="datetime-local" id="evaluationDate" name="evaluationDate" >
                <input type="hidden" id="fcode" name="fcode">
        <input type="hidden" id="evaluator" name="evaluator" value="<?php echo htmlspecialchars($_SESSION['firstname']); ?>">
            </div>
            <h2>ส่วนตรวจสอบถังดับเพลิง</h2>
    <div class="form-group">
        <label for="seal">คันบังคับ/สลัก:</label>
        <ul class="radio-list">
            <li>
                <input type="radio" id="sealYes" name="seal" value="yes">
                <label for="sealYes">ปกติ</label>
            </li>
            <li>
                <input type="radio" id="sealNo" name="seal" value="no">
                <label for="sealNo">ชำรุด</label>
            </li>
        </ul>
    </div>
    <div class="form-group">
        <label for="pressure">แรงดัน:</label>
        <ul class="radio-list">
            <li>
                <input type="radio" id="pressureYes" name="pressure" value="yes">
                <label for="pressureYes">ปกติ</label>
            </li>
            <li>
                <input type="radio" id="pressureNo" name="pressure" value="no">
                <label for="pressureNo">ชำรุด</label>
            </li>
        </ul>
    </div>
    <div class="form-group">
        <label for="hose">สายฉีด:</label>
        <ul class="radio-list">
            <li>
                <input type="radio" id="hoseYes" name="hose" value="yes">
                <label for="hoseYes">ปกติ</label>
            </li>
            <li>
                <input type="radio" id="hoseNo" name="hose" value="no">
                <label for="hoseNo">ชำรุด</label>
            </li>
        </ul>
    </div>
    <div class="form-group">
        <label for="body">ตัวถัง:</label>
        <ul class="radio-list">
            <li>
                <input type="radio" id="bodyYes" name="body" value="yes">
                <label for="bodyYes">ปกติ</label>
            </li>
            <li>
                <input type="radio" id="bodyNo" name="body" value="no">
                <label for="bodyNo">ชำรุด</label>
            </li>
        </ul>
    </div>
    <div class="form-group">
        <label for="construct">สิ่งกีดขวางถัง:</label>
        <ul class="radio-list">
            <li>
                <input type="radio" id="constructYes" name="construct" value="yes">
                <label for="constructYes">ปกติ(ไม่มีสิ่งกีดขวาง)</label>
            </li>
            <li>
                <input type="radio" id="constructNo" name="construct" value="no">
                <label for="constructNo">ไม่ปกติ(มีสิ่งกีดขวาง)</label>
            </li>
        </ul>
    </div>

    <!-- Additional Questions -->
    <?php if ($showAdditionalQuestions): ?>
    <h2>ส่วนตรวจสอบตู้น้ำดับเพลิง</h2>
    <div class="form-group">
        <label for="w_glass">กระจก / ประตู:</label>
        <ul class="radio-list">
            <li>
                <input type="radio" id="w_glassYes" name="w_glass" value="yes">
                <label for="w_glassYes">ปกติ</label>
            </li>
            <li>
                <input type="radio" id="w_glassNo" name="w_glass" value="no">
                <label for="w_glassNo">ชำรุด</label>
            </li>
        </ul>
    </div>
    <div class="form-group">
        <label for="w_val">วาล์ว:</label>
        <ul class="radio-list">
            <li>
                <input type="radio" id="w_valYes" name="w_val" value="yes">
                <label for="w_valYes">ปกติ</label>
            </li>
            <li>
                <input type="radio" id="w_valNo" name="w_val" value="no">
                <label for="w_valNo">ชำรุด</label>
            </li>
        </ul>
    </div>
    <div class="form-group">
        <label for="w_hose">หัวฉีด:</label>
        <ul class="radio-list">
            <li>
                <input type="radio" id="w_hoseYes" name="w_hose" value="yes">
                <label for="w_hoseYes">ปกติ</label>
            </li>
            <li>
                <input type="radio" id="w_hoseNo" name="w_hose" value="no">
                <label for="w_hoseNo">ชำรุด</label>
            </li>
        </ul>
    </div>
    <div class="form-group">
        <label for="w_construct">สิ่งกีดขวางตู้:</label>
        <ul class="radio-list">
            <li>
                <input type="radio" id="w_constructYes" name="w_construct" value="yes">
                <label for="w_constructYes">ปกติ(ไม่มีสิ่งกีดขวาง)</label>
            </li>
            <li>
                <input type="radio" id="w_constructNo" name="w_construct" value="no">
                <label for="w_constructNo">ไม่ปกติ(มีสิ่งกีดขวาง)</label>
            </li>
        </ul>
    </div>
    <?php endif; ?>

    <div class="form-group">
        <label for="comment">หมายเหตุ:</label>
        <input type="text" id="comment" name="comment" placeholder="กรอกหมายเหตุหรือไม่ก็ได้" maxlength="500" oninput="updateCharCount()">
        <span id="charCount">เหลือ 500 ตัวอักษร</span>
    </div>

    <!-- File Upload -->
    <div class="form-group">
        <label for="image">อัพโหลดรูปภาพ:</label>
        <input type="file" id="image" name="image" accept="image/*" capture="camera">
        <label for="image" class="btn upload-btn">ถ่ายรูปหรือเลือกไฟล์</label>
        <span id="file-name">ยังไม่ได้เลือกไฟล์</span>
    </div>
    <button type="submit" class="btn">ส่งข้อมูล</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
    // Extract FCODE from URL
    const urlParams = new URLSearchParams(window.location.search);
    const fcode = urlParams.get('data');

    // Set the value in the form and display it
    document.getElementById('fcode').value = fcode;
    document.getElementById('fcode-display').textContent = fcode;

    // Set the date input to today's date with time
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');

    const dateTimeString = `${year}-${month}-${day}T${hours}:${minutes}`;
    document.getElementById('evaluationDate').value = dateTimeString;

    document.getElementById('evaluationForm').addEventListener('submit', function (event) {
        const radioGroups = ['seal', 'pressure', 'hose', 'body','construct'];
                let allRadiosSelected = true;
                let errorMessage = '';

                radioGroups.forEach(group => {
                    const selected = document.querySelector(`input[name="${group}"]:checked`);
                    if (!selected) {
                        allRadiosSelected = false;
                        errorMessage = `กรุณาเลือกตัวเลือกในส่วนตัวถังดับเพลิงให้ครบและ`;
                    }
                });

                if (<?php echo $showAdditionalQuestions ? 'true' : 'false'; ?>) {
                    const additionalGroups = ['w_glass', 'w_val', 'w_hose', 'w_construct'];
                    additionalGroups.forEach(group => {
                        const selected = document.querySelector(`input[name="${group}"]:checked`);
                        if (!selected) {
                            allRadiosSelected = false;
                            errorMessage = `กรุณาเลือกตัวเลือกในส่วนตู้ดับเพลิงให้ครบและ`;
                        }
                    });
                }

        const imageInput = document.getElementById('image');
        const imageUploaded = imageInput.files.length > 0;

        const dateInput = document.getElementById('evaluationDate');
        const selectedDate = dateInput.value;

        if (allRadiosSelected && selectedDate) {
            if (!imageUploaded) {
                // Ask user if they are sure they want to submit without an image
                Swal.fire({
                    title: 'ไม่มีการแนบรูปภาพ?',
                    text: 'คุณไม่ได้แนบรูปภาพ. คุณต้องการส่งข้อมูลนี้หรือไม่?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'ใช่, ส่ง',
                    cancelButtonText: 'ไม่, ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData(this);

                        if (imageUploaded) {
                            const file = imageInput.files[0];
                            const fileName = file.name;
                            const fileExtension = fileName.substring(fileName.lastIndexOf('.'));
                            const newFileName = `${fileName.substring(0, fileName.lastIndexOf('.'))}_${selectedDate}${fileExtension}`;
                            const newFile = new File([file], newFileName, { type: file.type });
                            formData.set('image', newFile);
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ',
                            text: 'ส่งข้อมูลสำเร็จ',
                        }).then(() => {
                            fetch('evaluate_data.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => {
                                if (response.ok) {
                                    window.location.href = 'index.php';
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'เกิดข้อผิดพลาด',
                                        text: 'ไม่สามารถส่งข้อมูลได้'
                                    });
                                }
                            })
                            .catch(error => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'เกิดข้อผิดพลาด',
                                    text: 'ไม่สามารถส่งข้อมูลได้'
                                });
                            });
                        });
                    }
                });
            } 
            
            
            else {
                const formData = new FormData(this);

                const file = imageInput.files[0];
                const fileName = file.name;
                const fileExtension = fileName.substring(fileName.lastIndexOf('.'));
                const newFileName = `${fileName.substring(0, fileName.lastIndexOf('.'))}_${selectedDate}${fileExtension}`;
                const newFile = new File([file], newFileName, { type: file.type });
                formData.set('image', newFile);

                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ',
                    text: 'ส่งข้อมูลสำเร็จ',
                }).then(() => {
                    fetch('evaluate_data.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (response.ok) {
                            window.location.href = 'index.php';
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: 'ไม่สามารถส่งข้อมูลได้'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถส่งข้อมูลได้'
                        });
                    });
                });
            }
        } else {
            if (!allRadiosSelected) {
                errorMessage += 'กรุณาเลือกตัวเลือกให้ครบทุกข้อ';
            }
            if (!selectedDate) {
                errorMessage += errorMessage ? ' และกรุณาเลือกวันที่' : 'กรุณาเลือกวันที่';
            }
            Swal.fire({
                icon: 'error',
                title: 'ไม่ครบถ้วน',
                text: errorMessage,
            });
        }
        console.log('fcode:', document.getElementById('fcode').value);
        console.log('evaluator:', document.getElementById('evaluator').value);
        event.preventDefault();
    });

    document.getElementById('image').addEventListener('change', function () {
        const fileName = this.files.length > 0 ? this.files[0].name : 'ยังไม่ได้เลือกไฟล์';
        document.getElementById('file-name').textContent = fileName;
    });
});
function updateCharCount() {
            var commentInput = document.getElementById('comment');
            var charCountSpan = document.getElementById('charCount');
            var maxLength = commentInput.maxLength;
            var currentLength = commentInput.value.length;
            var remaining = maxLength - currentLength;

            // Update the character count display
            charCountSpan.textContent = 'เหลือ ' + remaining + ' ตัวอักษร';
        }

        // Initial call to set the character count on page load
        updateCharCount();
    </script>
    
</body>

</html>
<?php endif; ?>