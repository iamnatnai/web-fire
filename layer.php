<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fire Extinguishers</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="/mick/my-php/favicon.ico" type="image/x-icon">
    <style>
         body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f4;
    }

    #layer-dropdown-container {
        text-align: center;
        margin: 20px 0;
    }

    #layer-dropdown {
        width: 300px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        font-size: 16px;
        transition: border-color 0.3s ease;
        cursor: pointer;
    }

    #layer-dropdown:focus {
        border-color: #7100b3;
        outline: none;
    }

    #layer-dropdown option {
        padding: 10px;
        font-size: 16px;
        background-color: #fff;
        color: #333;
    }

    #layer-dropdown option:hover {
        background-color: #f0f0f0;
    }

    .container {
        padding: 20px;
        max-width: 900px;
        margin: 0 auto;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .item {
        margin-bottom: 20px;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #fafafa;
    }

    .item img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
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
        max-width: 100px;
        height: auto;
        border-radius: 8px;
    }
    </style>
</head>

<body>
<?php include 'navbar.php'; ?>
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
                        <th>เลขถังที่</th>
                        <th>รหัสตู้สายน้ำดับเพลิง</th>
                        <th>ชั้นที่ติดตั้ง</th>
                        <th>สถานที่ติดตั้ง</th>
                        <th>ลักษณะถัง</th>
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
    container.innerHTML = ''; // Clear previous content

    // Clear existing rows in the table
    tableBody.innerHTML = '';

    if (selectedCode) {
        // Find the items that match the selected layer_code
        const selectedItems = data.filter(item => item.layer_code === selectedCode);

        if (selectedItems.length > 0) {
            const item = selectedItems[0]; // Display only the first item in the top section

            // Display layerforfire data
            const itemDiv = document.createElement('div');
            itemDiv.className = 'item';
            itemDiv.innerHTML = `
                <p><strong>Layer Code:</strong> ${item.layer_code}</p>
                <p><strong>Description:</strong> ${item.description}</p>
                <p><strong>Image:</strong></p>
                <img src="uploads/${item.image_path}" alt="${item.layer_code}">
            `;
            container.appendChild(itemDiv);

            // Loop through the fire_extinguisher data and display in the table
            selectedItems.forEach(item => {
    const row = document.createElement('tr');
    
    // Remove "/uploads" from the path if it exists
    const imagePath = item.extinguisher_image_path.replace('/uploads', '');

    row.innerHTML = `
        <td>${item.F_Tank || 'N/A'}</td>
        <td>${item.F_water || 'ไม่ได้อยู่ในตู้'}</td>
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
