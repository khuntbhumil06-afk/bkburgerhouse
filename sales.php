<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: login.php");
    exit();
}
if ($_SESSION["admin"] == 'NO') {
    header("location: index.php");
    exit();
}

require_once "config.php";

date_default_timezone_set("Asia/Kolkata");
$current_time = date("Y-m-d H:i:s");

// Move orders to the next status after 30 seconds
mysqli_query($conn, "
    UPDATE orders
    SET details='Out for Delivery', status_time='$current_time'
    WHERE details='Confirmed'
    AND TIMESTAMPDIFF(SECOND, status_time, '$current_time') >= 30
");

mysqli_query($conn, "
    UPDATE orders
    SET details='Delivered', status_time='$current_time'
    WHERE details='Out for Delivery'
    AND TIMESTAMPDIFF(SECOND, status_time, '$current_time') >= 30
");

// Most recent order delivered in the last 5 seconds (for the success banner)
$check = mysqli_query($conn, "
    SELECT * FROM orders
    WHERE details='Delivered'
    AND TIMESTAMPDIFF(SECOND, status_time, '$current_time') <= 5
    ORDER BY id DESC LIMIT 1
");
$data = $check ? mysqli_fetch_assoc($check) : null;

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function renderOrderTable($conn, $status, $mode)
{
    $safeStatus = mysqli_real_escape_string($conn, $status);
    $results = mysqli_query($conn, "SELECT * FROM orders WHERE details='$safeStatus'");

    if (!$results || mysqli_num_rows($results) == 0) {
        echo "<p class='no-orders'>No Orders!</p>";
        return;
    }

    $showActions = ($mode !== 'delivered');

    echo "<table class='orders-table'>";
    echo "<tr>
            <th>ID</th>
            <th>Order ID</th>
            <th>Username</th>
            <th>Full Name</th>
            <th>Item</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Email</th>
            <th>Address</th>
            <th>Status</th>";
    if ($showActions) {
        echo "<th>Actions</th>";
    }
    echo "</tr>";

    while ($ord = mysqli_fetch_assoc($results)) {
        switch ($mode) {
            case 'confirmed':
                $statusHtml = "<span class='status-confirmed'>Order Confirmed</span>";
                $actionHtml = "<i class='fas fa-truck truck-icon' title='Out for delivery soon'></i>
                    <a href='deleteOrder.php?order_id=" . urlencode($ord['order_id']) . "'
                       class='delete-link'
                       onclick=\"return confirm('Are you sure you want to delete this order?')\">
                        <i class='fas fa-trash'></i>
                    </a>";
                break;

            case 'out':
                $statusHtml = "<span class='status-out'>Out for Delivery 🚚</span>";
                $actionHtml = "<a href='deleteOrder.php?order_id=" . urlencode($ord['order_id']) . "'
                       class='delete-link'
                       onclick=\"return confirm('Are you sure you want to delete this order?')\">
                        <i class='fas fa-trash'></i>
                    </a>";
                break;

            default: // delivered
                $statusHtml = "<span class='status-delivered'>Order Delivered Successfully</span>";
                $actionHtml = "";
                break;
        }

        echo "<tr>
                <td>" . e($ord['id']) . "</td>
                <td>" . e($ord['order_id']) . "</td>
                <td>" . e($ord['username']) . "</td>
                <td>" . e($ord['fullname']) . "</td>
                <td>" . e($ord['item']) . "</td>
                <td>" . e($ord['price']) . "</td>
                <td>" . e($ord['quantity']) . "</td>
                <td>" . e($ord['email']) . "</td>
                <td>" . e($ord['address']) . "</td>
                <td>$statusHtml</td>";
        if ($showActions) {
            echo "<td class='action-cell'>$actionHtml</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Burger Ordering System</title>
    <link rel="icon" href="Assets/logo.png">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: rgba(255, 255, 255, 0.8);
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: black;
            padding: 10px 30px;
            width: 100%;
            box-sizing: border-box;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container ul {
            list-style: none;
            display: flex;
            align-items: center;
            margin: 0;
            padding: 0;
        }

        .brand a {
            color: white;
            text-decoration: none;
            font-size: 22px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .brand img {
            width: 40px;
        }

        .right-ul li {
            margin-left: 20px;
        }

        .right-ul a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }

        .profile {
            position: relative;
        }

        .profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .profile img:hover {
            transform: scale(1.1);
        }

        .profile .dropdown {
            display: none;
            position: absolute;
            top: 50px;
            right: 0;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            min-width: 150px;
            z-index: 100;
            overflow: hidden;
        }

        .profile .dropdown a {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }

        .profile .dropdown a:hover {
            background-color: #f0f0f0;
            color: #007BFF;
        }

        .sales-page {
            padding: 1% 5%;
        }

        .section-heading {
            margin: 4% 0 0 5%;
        }

        .section-heading h3 {
            font-size: 25px;
            margin-bottom: 8px;
        }

        .section-heading hr {
            margin-right: 5%;
        }

        .section-body {
            margin: 1% 0 4% 5%;
        }

        .orders-table {
            border-collapse: collapse;
            width: 95%;
            background: white;
        }

        .orders-table th,
        .orders-table td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
        }

        .orders-table td.action-cell {
            border: none;
        }

        .no-orders {
            color: grey;
            font-size: 20px;
            margin: 0;
        }

        .status-confirmed {
            color: orange;
            font-weight: bold;
        }

        .status-out {
            color: blue;
            font-weight: bold;
        }

        .status-delivered {
            color: green;
            font-weight: bold;
        }

        .truck-icon {
            color: black;
            margin-right: 10px;
        }

        .delete-link {
            color: red;
            text-decoration: none;
        }

        .delete-link:hover {
            color: darkred;
        }

        .success-banner {
            color: green;
            text-align: center;
        }

        @media (max-width: 600px) {
            .profile .dropdown {
                right: -10px;
            }
        }
    </style>
</head>

<body>
    <nav class="nav-container">
        <ul class="nav-left">
            <li class="brand">
                <a href="index.php">
                    <img src="Assets/logo.png" alt="logo">
                    B K's Burger House
                </a>
            </li>
        </ul>
        <ul class="right-ul">
            <li><a href="index.php">Home</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="manage_menu.php">Manage Menu</a></li>
            <li class="profile">
                <img src="uploads/bk.png" id="profileBtn" alt="Profile">
                <div class="dropdown" id="dropMenu">
                    <a href="account.php">Account</a>
                    <a href="logout.php">Logout</a>
                </div>
            </li>
        </ul>
    </nav>

    <?php if ($data): ?>
        <h2 class="success-banner">
            🎉 Order Delivered Successfully (Order ID: <?php echo e($data['order_id']); ?>)
        </h2>
    <?php endif; ?>

    <div class="container">
        <section class="sales-page">

            <div class="section-heading">
                <h3>Confirmed</h3>
                <hr>
            </div>
            <div class="section-body">
                <?php renderOrderTable($conn, 'Confirmed', 'confirmed'); ?>
            </div>

            <div class="section-heading">
                <h3>Out for Delivery</h3>
                <hr>
            </div>
            <div class="section-body">
                <?php renderOrderTable($conn, 'Out for Delivery', 'out'); ?>
            </div>

            <div class="section-heading">
                <h3>Delivered</h3>
                <hr>
            </div>
            <div class="section-body">
                <?php renderOrderTable($conn, 'Delivered', 'delivered'); ?>
            </div>

        </section>
    </div>

    <script src="https://kit.fontawesome.com/6f42fc440c.js" crossorigin="anonymous"></script>
    <script src="script.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const profileBtn = document.getElementById("profileBtn");
            const dropMenu = document.getElementById("dropMenu");

            profileBtn.addEventListener("click", function (e) {
                e.stopPropagation();
                dropMenu.style.display = dropMenu.style.display === "block" ? "none" : "block";
            });

            document.addEventListener("click", function () {
                dropMenu.style.display = "none";
            });
        });
    </script>
</body>

</html>