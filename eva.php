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
            opacity: 1;
            width: 0;
            height: 0;
        }

        .radio-list input[type="radio"]+label::before {
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

        .radio-list input[type="radio"]:checked+label::before {
            background-color: #e207ff;
            border-color: #59086d;
        }

        .radio-list input[type="radio"]:checked+label::after {
            content: '✔';
            position: absolute;
            left: 7px;
            top: 1px;
            font-size: 20px;
            color: #feffad;
            animation: checkmark 0.3s ease-in-out;
        }

        @keyframes checkmark {
            0% {
                transform: scale(0);
            }

            50% {
                transform: scale(1.3);
            }

            100% {
                transform: scale(1);
            }
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
    </style>
</head>

<body>
    
    <div class="container">
        <h1>ฟอร์มตรวจเช็คถังดับเพลิง</h1>
        <h2>ผู้ประเมิน: <?php echo htmlspecialchars($_SESSION['firstname']); ?></h2>
        <p id="fire-code">ชื่อถัง: <span id="fcode-display"><?php echo htmlspecialchars($fcode); ?></span></p>
        <form id="evaluationForm" enctype="multipart/form-data">
            <input type="hidden" id="fcode" name="fcode" value="<?php echo htmlspecialchars($fcode); ?>">
            <!-- Evaluation Date -->
            <div class="form-group">
                <label for="evaluation-date">วันที่ประเมิน:</label>
                <input type="datetime-local" id="evaluation-date" name="evaluation-date" required>
                <span class="date-time-label" for="evaluation-date">วันที่และเวลา</span>
            </div>
            <!-- Evaluation Questions -->
            <div class="form-group">
                <label>การตรวจสอบ seal:</label>
                <ul class="radio-list">
                    <li><input type="radio" id="seal-yes" name="seal" value="yes"><label for="seal-yes">✔ ใช่</label></li>
                    <li><input type="radio" id="seal-no" name="seal" value="no"><label for="seal-no">❌ ไม่ใช่</label></li>
                </ul>
            </div>
            <div class="form-group">
                <label>การตรวจสอบ pressure:</label>
                <ul class="radio-list">
                    <li><input type="radio" id="pressure-yes" name="pressure" value="yes"><label for="pressure-yes">✔ ใช่</label></li>
                    <li><input type="radio" id="pressure-no" name="pressure" value="no"><label for="pressure-no">❌ ไม่ใช่</label></li>
                </ul>
            </div>
            <div class="form-group">
                <label>การตรวจสอบ hose:</label>
                <ul class="radio-list">
                    <li><input type="radio" id="hose-yes" name="hose" value="yes"><label for="hose-yes">✔ ใช่</label></li>
                    <li><input type="radio" id="hose-no" name="hose" value="no"><label for="hose-no">❌ ไม่ใช่</label></li>
                </ul>
            </div>
            <div class="form-group">
                <label>การตรวจสอบ body:</label>
                <ul class="radio-list">
                    <li><input type="radio" id="body-yes" name="body" value="yes"><label for="body-yes">✔ ใช่</label></li>
                    <li><input type="radio" id="body-no" name="body" value="no"><label for="body-no">❌ ไม่ใช่</label></li>
                </ul>
            </div>
            <!-- Additional Questions -->
            <?php if ($showAdditionalQuestions): ?>
                <div class="form-group">
                    <label for="water">น้ำในถัง:</label>
                    <input type="text" id="water" name="water" placeholder="กรุณากรอกปริมาณน้ำ" required>
                </div>
            <?php endif; ?>
            <!-- File Upload -->
            <div class="form-group">
                <label for="image" class="upload-btn">เลือกไฟล์ภาพ</label>
                <input type="file" id="image" name="image">
                <span id="file-name">ไม่มีไฟล์ที่เลือก</span>
            </div>
            <button type="submit" class="btn">ส่งข้อมูล</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const fcodeInput = document.getElementById('fcode');
            const fcodeDisplay = document.getElementById('fcode-display');
            const evaluationDateInput = document.getElementById('evaluation-date');
            const imageInput = document.getElementById('image');
            const fileNameDisplay = document.getElementById('file-name');
            const form = document.getElementById('evaluationForm');

            fcodeInput.value = new URLSearchParams(window.location.search).get('data');
            fcodeDisplay.textContent = fcodeInput.value;

            imageInput.addEventListener('change', () => {
                const fileName = imageInput.files.length > 0 ? imageInput.files[0].name : 'ไม่มีไฟล์ที่เลือก';
                fileNameDisplay.textContent = fileName;
            });

            form.addEventListener('submit', (event) => {
                event.preventDefault();

                const seal = form.querySelector('input[name="seal"]:checked');
                const pressure = form.querySelector('input[name="pressure"]:checked');
                const hose = form.querySelector('input[name="hose"]:checked');
                const body = form.querySelector('input[name="body"]:checked');
                const evaluationDate = evaluationDateInput.value;

                if (seal && pressure && hose && body && evaluationDate) {
                    const selectedDate = new Date(evaluationDate).toISOString().split('T')[0];
                    let imageUploaded = false;

                    if (imageInput.files.length > 0) {
                        imageUploaded = true;
                    }

                    if (imageUploaded) {
                        Swal.fire({
                            title: 'ยืนยันการส่งข้อมูล',
                            text: 'คุณแน่ใจว่าต้องการส่งข้อมูลพร้อมกับไฟล์ภาพ?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'ใช่, ส่งข้อมูล!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const formData = new FormData(form);
                                const file = imageInput.files[0];
                                const fileName = file.name;
                                const fileExtension = fileName.substring(fileName.lastIndexOf('.'));
                                const newFileName = `${fileName.substring(0, fileName.lastIndexOf('.'))}_${selectedDate}${fileExtension}`;
                                const newFile = new File([file], newFileName, { type: file.type });
                                formData.set('image', newFile);

                                fetch('evaluate_data.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => {
                                    if (response.ok) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'สำเร็จ',
                                            text: 'ส่งข้อมูลสำเร็จ'
                                        }).then(() => {
                                            window.location.href = 'index.php';
                                        });
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
                            }
                        });
                    } else {
                        const formData = new FormData(form);
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
                    Swal.fire({
                        icon: 'warning',
                        title: 'โปรดกรอกข้อมูลให้ครบถ้วน',
                        text: 'กรุณากรอกข้อมูลให้ครบทุกช่อง และเลือกวันที่และเวลา',
                    });
                }
            });
        });
    </script>
</body>
</html>
<?php endif; ?>
