<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลการจองคิว - โรงพยาบาลเกษมราษฎร์ ประชาชื่น</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'TH SarabunPSK', sans-serif;
            background-color: #f4f4f4;
            color: #000000;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 10px;
            padding: 30px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            width: 100px;
            height: auto;
        }
        .header h1 {
            font-size: 24px;
            margin-top: 10px;
            color: #6a1b9a; /* สีม่วง */
        }
        .appointment-details {
            font-size: 18px;
            margin-bottom: 30px;
        }
        .appointment-details label {
            font-weight: bold;
        }
        .status {
            font-size: 20px;
            color: #4caf50; /* สีเขียว */
            margin-bottom: 20px;
            text-align: center;
        }
        .btn-print {
            display: block;
            width: 100%;
            text-align: center;
            padding: 10px;
            background-color: #6a1b9a; /* สีม่วง */
            color: #ffffff;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
        }
        .btn-print:hover {
            background-color: #5e2a91; /* สีม่วงเข้ม */
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <img src="hospital-logo.png" alt="โรงพยาบาลเกษมราษฎร์ ประชาชื่น">
            <h1>โรงพยาบาลเกษมราษฎร์ ประชาชื่น</h1>
        </div>

        <div class="status">
            <p>การจองของคุณได้รับการอนุมัติแล้ว!</p>
        </div>

        <div class="appointment-details">
            <p><label>รหัสการจอง:</label> A123456</p>
            <p><label>ชื่อผู้ป่วย:</label> นายสมชาย ใจดี</p>
            <p><label>แผนก:</label> คลินิกทันตกรรม</p>
            <p><label>วันที่นัดหมาย:</label> วันเสาร์ที่ 14 กันยายน 2024</p>
            <p><label>เวลานัดหมาย:</label> 09:00 - 10:00 น.</p>
            <p><label>แพทย์ผู้รับผิดชอบ:</label> พญ. กานดา รักษาโรค</p>
        </div>

        <button class="btn-print" onclick="window.location.href='print_appointment.php'">พิมพ์ใบนัดหมาย</button>
    </div>

</body>
</html>
