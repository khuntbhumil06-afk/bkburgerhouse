<?php
session_start();
if(!isset($_SESSION['username'])){
    header("location: login.php");
    exit();
}
if($_SESSION["admin"]=='NO'){
    header("location: index.php");
    exit();
}
require_once "config.php";

date_default_timezone_set("Asia/Kolkata");
$current_time = date("Y-m-d H:i:s");

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

$check = mysqli_query($conn, "
SELECT * FROM orders 
WHERE details='Delivered' 
AND TIMESTAMPDIFF(SECOND, status_time, '$current_time') <= 5
ORDER BY id DESC LIMIT 1
");

$data = mysqli_fetch_assoc($check);

$img = "default.png";
if(isset($_SESSION['image']) && $_SESSION['image'] != ""){
    $img = $_SESSION['image'];
}
?>

<?php if($data){ ?>
    <h2 style="color: green; text-align:center;">
        🎉 Order Delivered Successfully (Order ID: <?php echo $data['order_id']; ?>)
    </h2>
<?php } ?>
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
    body{
    background: rgba(255, 255, 255, 0.8);
    margin: 0;
    font-family: Arial, sans-serif;
}

.nav-container{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:black;
    padding:10px 30px;
    width:100%;
    box-sizing: border-box;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.nav-container ul{
    list-style:none;
    display:flex;
    align-items:center;
    margin:0;
    padding:0;
}

.brand a{
    color:white;
    text-decoration:none;
    font-size:22px;
    font-weight:bold;
    display:flex;
    align-items:center;
    gap:8px;
}

.brand img{
    width:40px;
}

.right-ul li{
    margin-left:20px;
}

.right-ul a{
    color:white;
    text-decoration:none;
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
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
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

@media (max-width: 600px){
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
        <li class="profile">
            <img src="uploads/bk.png" id="profileBtn" alt="Profile">
            <div class="dropdown" id="dropMenu">
                <a href="account.php">Account</a>
                <a href="logout.php">Logout</a>
            </div>
        </li>
    </ul>
</nav>
    <div class="container">
        <form action="" method="post">
        <section class="sales-page"><div class="sales-table" style="margin-top: 1%;">
            <div class="container">
                <div class="orders" style="margin-left: 5%; margin-top 4%;">
                    <h3 style="font-size: 25px;">Confirmed</h3>
                    <hr style="margin-right: 5%">
                </div>
                <div class="sales-table" style="margin-top: 1%;">
                    <table style="margin-bottom: 4%;">
                        <?php 
                            $sql = "SELECT * FROM orders WHERE details='Confirmed'";
                            $results = mysqli_query($conn, $sql);
                            if(mysqli_num_rows($results)==0): ?>
                                <h4 style='margin-left: 5%; color: grey; font-size: 20px;'>No Orders!</h4>
                            <?php 
                            else: ?>
                                <tr>
                                <th style="border: 1px solid black; padding: 10px;">ID</th>
                                <th style="border: 1px solid black; padding: 10px;">Order ID</th>
                                <th style="border: 1px solid black; padding: 10px;">Username</th>
                                <th style="border: 1px solid black; padding: 10px;">Full Name</th>
                                <th style="border: 1px solid black; padding: 10px;">Item</th>
                                <th style="border: 1px solid black; padding: 10px;">Price</th>
                                <th style="border: 1px solid black; padding: 10px;">Quantity</th>
                                <th style="border: 1px solid black; padding: 10px;">Email</th>
                                <th style="border: 1px solid black; padding: 10px;">Address</th>
                                <th style="border: 1px solid black; padding: 10px;">Status</th>
                            <?php
                                $oid = ''; $f=0;
                                while($ord = mysqli_fetch_assoc($results)): 
                                    if($oid!=$ord['order_id']): 
                                        if($f==1): $f=0;?>
                                            </tr>
                                        <?php 
                                        endif;
                                        $oid=$ord['order_id']; $f=1;
                                    endif;
                                    ?>
                                    <tr>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["id"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["order_id"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["username"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["fullname"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["item"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["price"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["quantity"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["email"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["address"];?></td>
                                        <td style="color: orange; font-weight: bold;">
                                            Order Confirmed
                                        </td>                                 
                                        <td style="border: none;">
                                            <a style="color: black; text-decoration: none;" onmouseover="style='color: blue; text-decoration: none;'"return; onmouseout="style='color: black; text-decoration: none;'"return;><i class="fas fa-truck"></i></a>
                                            <a style="color: black; text-decoration: none;" onmouseover="this.style.color='red';" onmouseout="this.style.color='black';"onclick="return confirm('Are you sure you want to delete this order?');"></i></a>
                                        </td>
                            <?php 
                                endwhile; 
                            endif; ?>
                            </tr>
                    </table>
                </div>
                
                <div class="orders" style="margin-left: 5%; margin-top 4%;">
                    <h3 style="font-size: 25px;">Out for Delivery</h3>
                    <hr style="margin-right: 5%">
                </div>
                <div class="sales-table" style="margin-top: 1%;">
                    <table style="margin-bottom: 4%;">
                        <?php 
                            $sql = "SELECT * FROM orders WHERE details='Out for Delivery'";
                            $results = mysqli_query($conn, $sql);
                            if(mysqli_num_rows($results)==0): ?>
                                <h4 style='margin-left: 5%; color: grey; font-size: 20px;'>No Orders!</h4>
                            <?php 
                            else: ?>
                                <tr>
                                <th style="border: 1px solid black; padding: 10px;">ID</th>
                                <th style="border: 1px solid black; padding: 10px;">Order ID</th>
                                <th style="border: 1px solid black; padding: 10px;">Username</th>
                                <th style="border: 1px solid black; padding: 10px;">Full Name</th>
                                <th style="border: 1px solid black; padding: 10px;">Item</th>
                                <th style="border: 1px solid black; padding: 10px;">Price</th>
                                <th style="border: 1px solid black; padding: 10px;">Quantity</th>
                                <th style="border: 1px solid black; padding: 10px;">Email</th>
                                <th style="border: 1px solid black; padding: 10px;">Address</th>
                                <th style="border: 1px solid black; padding: 10px;">Status</th>
                            <?php
                                $oid = ''; $f=0;
                                while($ord = mysqli_fetch_assoc($results)): 
                                    if($oid!=$ord['order_id']): 
                                        if($f==1): $f=0;?>
                                            </tr>
                                        <?php 
                                        endif;
                                        $oid=$ord['order_id']; $f=1;
                                    endif;
                                    ?>
                                    <tr>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["id"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["order_id"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["username"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["fullname"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["item"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["price"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["quantity"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["email"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["address"];?></td>
                                        <td style="color: blue; font-weight: bold;">
                                            Out for Delivery 🚚
                                        </td>
                                        <td style="border: none;">
                                            <a href="deleteOrder.php?order_id=<?=$ord['order_id']?>"
                                                style="color: red;"
                                                onclick="return confirm('Are you sure you want to delete this order?')"
                                                onmouseover="this.style.color='darkred'"
                                                onmouseout="this.style.color='red'">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                            <?php 
                                endwhile; 
                            endif; ?>
                            </tr>
                    </table>
                </div>
                
                <div class="orders" style="margin-left: 5%; margin-top 4%;">
                    <h3 style="font-size: 25px;">Delivered</h3>
                    <hr style="margin-right: 5%">
                </div>
                <div class="sales-table" style="margin-top: 1%;">
                    <table style="margin-bottom: 4%;">
                        <?php 
                            $sql = "SELECT * FROM orders WHERE details='Delivered'";
                            $results = mysqli_query($conn, $sql);
                            if(mysqli_num_rows($results)==0): ?>
                                <h4 style='margin-left: 5%; color: grey; font-size: 20px;'>No Orders!</h4>
                            <?php 
                            else: ?>
                                <tr>
                                <th style="border: 1px solid black; padding: 10px;">ID</th>
                                <th style="border: 1px solid black; padding: 10px;">Order ID</th>
                                <th style="border: 1px solid black; padding: 10px;">Username</th>
                                <th style="border: 1px solid black; padding: 10px;">Full Name</th>
                                <th style="border: 1px solid black; padding: 10px;">Item</th>
                                <th style="border: 1px solid black; padding: 10px;">Price</th>
                                <th style="border: 1px solid black; padding: 10px;">Quantity</th>
                                <th style="border: 1px solid black; padding: 10px;">Email</th>
                                <th style="border: 1px solid black; padding: 10px;">Address</th>
                                <th style="border: 1px solid black; padding: 10px;">Status</th>
                            <?php
                                $oid = ''; $f=0;
                                while($ord = mysqli_fetch_assoc($results)): 
                                    if($oid!=$ord['order_id']): 
                                        if($f==1): $f=0;?>
                                            </tr>
                                        <?php 
                                        endif;
                                        $oid=$ord['order_id']; $f=1;
                                    endif;
                                    ?>
                                    <tr>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["id"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["order_id"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["username"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["fullname"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["item"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["price"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["quantity"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["email"];?></td>
                                        <td style="border: 1px solid black; padding: 10px;"><?php echo $ord["address"];?></td>
                                        <td style="color: green; font-weight: bold;">
                                            Order Delivered Successfully
                                        </td>
                            <?php 
                                endwhile; 
                            endif; ?>
                            </tr>
                    </table>
                </div>
            </div>
        </section>
        </form>
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