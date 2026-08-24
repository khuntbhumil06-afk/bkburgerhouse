<?php
include 'config.php';

$sql = "SELECT COUNT(*) as total_orders FROM orders";
$result = mysqli_query($conn, $sql);

if(!$result){
    die("Error: " . mysqli_error($conn));
}

$total_orders = mysqli_fetch_assoc($result)['total_orders'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Orders Dashboard</title>
    <style>
        body{
    font-family: Arial;
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                url('Assets/images.png') no-repeat center center fixed;
    background-size: cover;
    color:white;
    margin:0;
}

        .container {
            width: 90%;
            margin: 50px auto;
            text-align: center;
        }

        h1 {
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 40px;
            width: 300px;
            margin: auto;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-10px);
        }

        .card h2 {
            margin-bottom: 15px;
            color: #555;
        }

        .card h3 {
            font-size: 35px;
            color: #e67e22;
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

<div class="container">
    <h1>🚚 Orders Dashboard</h1>

    <div class="card">
        <h2>Total Orders Delivered</h2>
        <h3><?php echo $total_orders; ?></h3>
    </div>
</div>
<a href="index.php" class="home-btn">🏠 Home</a>
</body>
</html>