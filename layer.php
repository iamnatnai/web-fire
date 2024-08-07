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
            <!-- Data will be displayed here -->
        </div>

        <script>
            let data = [];

            // Fetch data and populate dropdown
            fetch('fetch_layer.php')
                .then(response => response.json())
                .then(result => {
                    if (result.status === "success") {
                        data = result.data; // Store data globally
                        const dropdown = document.getElementById('layer-dropdown');

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
                            container.innerHTML = '';

                            if (selectedCode) {
                                data
                                    .filter(item => item.layer_code === selectedCode)
                                    .forEach(item => {
                                        const itemDiv = document.createElement('div');
                                        itemDiv.className = 'item';
                                        itemDiv.innerHTML = `
                                            <p><strong>Layer Code:</strong> ${item.layer_code}</p>
                                            <p><strong>Description:</strong> ${item.description}</p>
                                            <p><strong>Image:</strong></p>
                                            <img src="uploads/${item.image_path}" alt="${item.layer_code}">
                                        `;
                                        container.appendChild(itemDiv);
                                    });
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
