<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Navbar with Hamburger Menu</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="navbar">
        <div class="navbar-toggle" id="navbar-toggle">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>
        <div class="navbar-menu" id="navbar-menu">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="evaluation_page.php"><i class="fas fa-clipboard-list"></i> Evaluation</a>
            <a href="layer.php"><i class="fas fa-map-marker-alt"></i> Location</a>
            <a href="scan.html"><i class="fas fa-qrcode"></i> Scan</a>
            <a href="layers_status.php"><i class="fas fa-layer-group"></i> Layers Status</a>
        </div>
    </div>
    <script src="navbar.js"></script>
</body>
</html>
