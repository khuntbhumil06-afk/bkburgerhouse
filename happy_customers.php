<?php
session_start();
include "config.php";

$q = mysqli_query($conn, "SELECT * FROM stats WHERE id=1");
$row = $q ? mysqli_fetch_assoc($q) : null;
$happy_customers = $row ? (int)$row['happy_customers'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Happy Customers</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                        url('Assets/images.png') no-repeat center center fixed;
            background-size: cover;
            color: white;
            text-align: center;
            font-size: 40px;
            padding-top: 100px;
            margin: 0;
        }

        .home-btn {
            display: block;
            width: 120px;
            margin: 20px auto;
            padding: 10px 25px;
            background: #ff6b6b;
            color: white;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            text-align: center;
            transition: 0.3s;
        }

        .home-btn:hover {
            background: #ff4757;
        }
    </style>
</head>
<body>

    Happy Customers :
    <?php echo $happy_customers; ?>

    <a href="dashboard.php" class="home-btn">🏠 Home</a>

</body>
</html>