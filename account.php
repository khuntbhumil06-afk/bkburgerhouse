<?php
// session_start();
// if(!isset($_SESSION['username'])){
//     header("location: login.php");
//     exit();
// }
// include 'config.php';
?>

<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Burger Ordering System</title>
    <link rel="icon" href="Assets/logo.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="nav-container">
        <ul>
            <ul>
                <li class="brand"><img src="Assets/logo.png" alt="Music">Burger House</li>
            </ul>
            <ul class="right-ul">
                <li><a href="index.php">Home</a></li>
                <li><a id="active" href="account.php">Account</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </ul>
    </nav>
    <div class="container">
        <form action="" method="post">
        <section class="account-page">
            <div class="account-details">
                <div class="account" style="margin-left: 0.7%;">
                    <h3 style="font-size: 25px;">Account</h3 style="color: grey;">
                    <hr style="margin-right: 5%">
                </div>
                <div class="account-info">
                    <table>
                        <tr>
                            <th>Name</th>
                            <td><?php echo $_SESSION["fullname"];?></td>
                        </tr>
                        <tr>
                            <th>Username</th>
                            <td><?php echo $_SESSION["username"];?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo $_SESSION["email"];?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <br>
            <?php if($_SESSION["admin"] == "NO"){?>
            <div class="account-details" style="margin-left: 0.7%; margin-top 4%;">
                <div class="orders">
                    <h3 style="font-size: 25px;">Orders</h3>
                    <hr style="margin-right: 5%">
                </div>
                <div class="sales-table" style="margin-top: 1%;">
                    <table style="justify-content: left;">
                        
                        <?php 
                            $sql = "SELECT * FROM orders WHERE username='".$_SESSION["username"]."'";
                            $results = mysqli_query($conn, $sql);
                            if(mysqli_num_rows($results)==0): ?>
                                <h4 style='color: grey; font-size: 20px;'>No Orders!</h4>
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
                            <?php 
                                endwhile; 
                            endif; ?>
                            </tr>
                    </table>
                </div>
            </div>
            <?php }?>
        </section>
        </form>
    </div>
    <script src="https://kit.fontawesome.com/6f42fc440c.js" crossorigin="anonymous"></script>
    <script src="script.js"></script>
</body>
</html> -->

<?php
session_start();

if(!isset($_SESSION['username'])){
    header("location: login.php");
    exit();
}


include 'config.php';

$img = "uploads/default.png"; 

if($_SESSION['admin'] == "YES"){
    $img = "uploads/bk.png"; 
}
else{
    $username = $_SESSION['username'];

    $sql = "SELECT image FROM loginform WHERE username=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $user_img);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if(!empty($user_img)){
        $img = "uploads/".$user_img;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Account - Burger House</title>
<link rel="icon" href="Assets/logo.png">

<style>
body{
    margin:0;
    font-family:'Segoe UI', sans-serif;
    background:#f3f3f3;
}

.nav-container{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:#111;
    padding:12px 40px;
}

.brand{
    display:flex;
    align-items:center;
    gap:10px;
    text-decoration:none;
    color:white;
    font-size:20px;
    font-weight:bold;
}

.brand img{
    width:40px;
}

.nav-right a{
    color:white;
    text-decoration:none;
    margin-left:25px;
    font-size:16px;
    transition:0.3s;
}

.nav-right a:hover,
#active{
    color:#ff9800;
}

.container{
    width:85%;
    margin:40px auto;
}

.account-details{
    background:white;
    padding:30px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

.account-profile{
    text-align:left;
    margin:20px 0;
}

.account-profile img{
    width:130px;
    height:130px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #ff9800;
}

.account-info table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

.account-info th,
.account-info td{
    text-align:left;
    padding:12px;
    border-bottom:1px solid #eee;
}

.account-info th{
    width:200px;
    color:#555;
}

.orders-section{
    margin-top:40px;
    background:white;
    padding:30px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

.orders-section h3{
    margin-bottom:15px;
}

.orders-table{
    width:100%;
    border-collapse:collapse;
}

.orders-table th,
.orders-table td{
    border:1px solid #ddd;
    padding:10px;
    text-align:center;
}

.orders-table th{
    background:#ff9800;
    color:white;
}

.no-orders{
    color:grey;
    font-size:18px;
}

@media(max-width:768px){
    .container{
        width:95%;
    }

    .account-info th{
        width:auto;
    }
}
</style>
</head>

<body>

<nav class="nav-container">
    <div class="nav-left">
        <a href="index.php" class="brand">
            <img src="Assets/logo.png">
            <span>B K's Burger House</span>
        </a>
    </div>

    <div class="nav-right">
        <a href="index.php">Home</a>
        <a id="active" href="account.php">Account</a>
        <a href="wishlist.php">Wishlist</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">

    <div class="account-details">
        <h2>My Account</h2>

        <div class="account-profile">
            <img src="<?php echo $img; ?>" alt="Profile Image">
        </div>

        <div class="account-info">
            <table>
                <tr>
                    <th>Full Name</th>
                    <td><?php echo $_SESSION['fullname']; ?></td>
                </tr>
                <tr>
                    <th>Username</th>
                    <td><?php echo $_SESSION['username']; ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo $_SESSION['email']; ?></td>
                </tr>
            </table>
        </div>
    </div>

    <?php if($_SESSION["admin"] == "NO"): ?>
    <div class="orders-section">
        <h3>My Orders</h3>

        <?php
        $sql = "SELECT * FROM orders WHERE username='".$_SESSION['username']."'";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) == 0):
        ?>
            <p class="no-orders">No Orders Found!</p>
        <?php else: ?>
            <table class="orders-table">
                <tr>
                    <th>ID</th>
                    <th>Order ID</th>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Address</th>
                </tr>

                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['order_id']; ?></td>
                    <td><?php echo $row['item']; ?></td>
                    <td>₹<?php echo $row['price']; ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td><?php echo $row['address']; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

</body>
</html>