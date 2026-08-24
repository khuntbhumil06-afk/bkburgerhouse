<?php
include 'config.php';

$total_sql = "SELECT SUM(price) as total FROM orders";
$total_result = mysqli_query($conn, $total_sql);
if(!$total_result){
    die("Error: " . mysqli_error($conn));
}
$total = mysqli_fetch_assoc($total_result)['total'] ?? 0;

$today_sql = "SELECT SUM(price) as today_total 
              FROM orders 
              WHERE DATE(order_time) = CURDATE()";
$today_result = mysqli_query($conn, $today_sql);
if(!$today_result){
    die("Error: " . mysqli_error($conn));
}
$today = mysqli_fetch_assoc($today_result)['today_total'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Earnings Dashboard</title>
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

        .cards {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        .card {
            background: white;
            padding: 30px;
            width: 250px;
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
            font-size: 28px;
            color: #2ecc71;
        }

        .today h3 {
            color: #3498db;
        }

        .home-btn {
            display: block;         
            width: 120px;            
            margin: 20px auto;
            padding: 10px 25px;
            background: #ff6b6b;
            color: white;
            text-decoration: none;
            text-align: center; 
            font-weight: bold;
            border-radius: 5px;
            transition: 0.3s;
        }

        .home-btn:hover {
            background: #ff4757;
        }

    </style>
</head>
<body>

<div class="container">
    <h1>💰 Earnings Dashboard</h1>

    <div class="cards">
        <div class="card">
            <h2>Total Earnings</h2>
            <h3>₹<?php echo $total; ?></h3>
        </div>

        <div class="card today">
            <h2>Today's Earnings</h2>
            <h3>₹<?php echo $today; ?></h3>
        </div>
    </div>
</div>
<a href="dashboard.php" class="home-btn">🏠 Home</a>
</body>
</html>