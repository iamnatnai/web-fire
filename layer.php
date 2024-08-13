<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fire Extinguishers</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .container {
            padding: 20px;
        }
        .item {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .item img {
            max-width: 100%;
            height: auto;
        }
        #layer-dropdown {
            margin: 20px;
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
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .image-column img {
            max-width: 100px; /* Adjust as needed */
            height: auto;
        }
    </style>
</head>
<body>
    <?php 
    session_start(); // Start the session to access session variables
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
        <?php include 'navbar.php'; ?>
        <div id="layer-dropdown-container">
            <select id="layer-dropdown">
                <option value="">Select a Layer Code</option>
            </select>
        </div>

        <div class="container" id="data-container">
            <!-- Data from layerforfire will be displayed here -->
        </div>

        <div class="container" id="fire-extinguisher-table-container">
            <!-- Data from fire_extinguisher will be displayed here -->
            <table id="fire-extinguisher-table">
                <thead>
                    <tr>
                        <th>FCODE</th>
                        <th>F_water</th>
                        <th>F_layer</th>
                        <th>F_located</th>
                        <th>Image</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be inserted here -->
                </tbody>
            </table>
        </div>

        <script>
            let data = [];

            // Fetch data and populate dropdown and table
            fetch('fetch_data.php')
                .then(response => response.json())
                .then(result => {
                    if (result.status === "success") {
                        data = result.data; // Store data globally
                        const dropdown = document.getElementById('layer-dropdown');
                        const tableBody = document.getElementById('fire-extinguisher-table').querySelector('tbody');

                        // Clear existing options except for the first
                        dropdown.innerHTML = '<option value="">Select a Layer Code</option>';

                        // Populate dropdown
                        const uniqueLayerCodes = [...new Set(data.map(item => item.layer_code))];
                        uniqueLayerCodes.forEach(code => {
                            const option = document.createElement('option');
                            option.value = code;
                            option.textContent = code;
                            dropdown.appendChild(option);
                        });

                        // Handle dropdown change
                        dropdown.addEventListener('change', (event) => {
    const selectedCode = event.target.value;
    const container = document.getElementById('data-container');
    container.innerHTML = ''; // ล้างเนื้อหาที่เคยมี

    // ล้างแถวที่มีอยู่ในตารางก่อนหน้า
    tableBody.innerHTML = '';

    if (selectedCode) {
        // ค้นหารายการแรกที่ตรงกับ layer_code ที่เลือก
        const selectedItems = data.filter(item => item.layer_code === selectedCode);

        if (selectedItems.length > 0) {
            const item = selectedItems[0]; // แสดงเฉพาะรายการแรกในส่วนข้อมูลด้านบน

            // แสดงข้อมูลของ layerforfire
            const itemDiv = document.createElement('div');
            itemDiv.className = 'item';
            itemDiv.innerHTML = `
                <p><strong>Layer Code:</strong> ${item.layer_code}</p>
                <p><strong>Description:</strong> ${item.description}</p>
                <p><strong>Image:</strong></p>
                <img src="uploads/${item.image_path}" alt="${item.layer_code}">
            `;
            container.appendChild(itemDiv);

            // วนลูปเพื่อแสดงข้อมูล fire_extinguisher ในตาราง
            selectedItems.forEach(item => {
    const row = document.createElement('tr');
    
    // Remove "/uploads" from the path if it exists
    const imagePath = item.extinguisher_image_path.replace('/uploads', '');

    row.innerHTML = `
        <td>${item.FCODE || 'N/A'}</td>
        <td>${item.F_water || 'N/A'}</td>
        <td>${item.F_layer || 'N/A'}</td>
        <td>${item.F_located || 'N/A'}</td>
        <td class="image-column">
            <img src="uploads/${imagePath}" alt="Fire Extinguisher Image">
        </td>
    `;
    tableBody.appendChild(row);
});
        }
    }
});



                    } else {
                        console.error('Error fetching data:', result.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        </script>
    <?php endif; ?>
</body>
</html>
