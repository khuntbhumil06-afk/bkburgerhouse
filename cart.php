<?php
session_start();

if(!isset($_SESSION['username'])){
    header("location: login.php");
    exit();
}

if(isset($_POST['checkout'])){
    if(empty($items)){
        echo "<script>alert('Your cart is empty! Please add items first.');</script>";
    } else {
        header("Location: payment.php");
        exit();
    }
}


// if(!isset($_SESSION['cartItemN'])){
//     header("location: menu.php");
//     exit();
// }

$cartItemN = $_SESSION["cartItemN"] ?? [];
$cartItemQ = $_SESSION["cartItemQ"] ?? [];
$cartItemP = $_SESSION["cartItemP"] ?? [];

$items = $cartItemN;
$qty = $cartItemQ;
$price = $cartItemP;

$total = $_SESSION["total"] ?? 0;
$discount = $_SESSION["discount"] ?? 0;
$offerDiscount = $_SESSION["offerDiscount"] ?? 0;
$tax = $_SESSION["tax"] ?? 0;
$netAmount = $_SESSION["netAmount"] ?? 0;

if(isset($_POST['action']) && isset($_POST['index'])){
    $i = (int)$_POST['index'];
    $unitPrice = $price[$i] / $qty[$i];
    if($_POST['action'] == "add"){
        $qty[$i]++;
    }
    if($_POST['action'] == "remove"){
        $qty[$i]--;
    }
    if($qty[$i] <= 0){
        array_splice($items,$i,1);
        array_splice($qty,$i,1);
        array_splice($price,$i,1);
    } else {
        $price[$i] = $unitPrice * $qty[$i];
    }
    $total = array_sum($price);
    $discount = $total * 0.20;   
    $afterDiscount = $total - $discount;
    $tax = $afterDiscount * 0.20;
    $netAmount = $afterDiscount + $tax;

    $_SESSION['cartItemN'] = $items;
    $_SESSION['cartItemQ'] = $qty;
    $_SESSION['cartItemP'] = $price;
    $_SESSION['total'] = $total;
    $_SESSION['discount'] = $discount;
    $_SESSION['tax'] = $tax;
    $_SESSION['netAmount'] = $netAmount;

    header("Location: cart.php");
    exit();
}

$total = 0;

for($i=0; $i<count($price); $i++){
    $total += $price[$i];
}
$discount = $total * 0.20;
$afterDiscount = $total - $discount;
$tax = $afterDiscount * 0.20;
$netAmount = $afterDiscount + $tax;

$img = "uploads/default.png"; 

if(isset($_SESSION['username'])){
    include 'config.php';
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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Cart - Burger Mania</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI', Arial, sans-serif;
}

body{
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                url('Assets/images.png') no-repeat center center fixed;
    background-size:100%;
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
    color:white;
    text-decoration:none;
    font-size:20px;
    font-weight:bold;
}

.brand img{
    width:40px;
}

.right-ul{
    list-style:none;
    display:flex;
    align-items:center;
    margin:0;
}

.right-ul li{
    margin-left:25px;
    position:relative;
}

.right-ul a{
    color:white;
    text-decoration:none;
    font-size:16px;
}

.right-ul a:hover{
    color:#ff9800;
}

.profile img{
    width:40px;
    height:40px;
    border-radius:50%;
    cursor:pointer;
}

.dropdown{
    display:none;
    position:absolute;
    top:50px;
    right:0;
    background:white;
    border-radius:8px;
    padding:10px;
    min-width:120px;
    box-shadow:0 5px 15px rgba(0,0,0,0.2);
}

.dropdown a{
    display:block;
    color:black;
    padding:8px;
    text-decoration:none;
}

.dropdown a:hover{
    background:#ff9800;
    color:white;
}

.profile:hover .dropdown{
    display:block;
}

.container{
    width:75%;
    margin:40px auto;
    background:#fff;
    padding:30px;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
}

.container h2{
    text-align:center;
    font-size:26px;
    margin-bottom:25px;
    color:#333;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

td img{
    border-radius:10px;
    box-shadow:0 5px 10px rgba(0,0,0,0.3);
}

th, td {
    padding: 12px 15px;
    text-align: center; 
    border-bottom: 1px solid #ddd; 
    font-size: 15px;
}

th {
    background-color: #ff9800; 
    color: white;
}

tr:hover {
    background-color: #f9f9f9; 
}

.total-row td {
    font-weight: bold;
    background: #f7f7f7;
}

/* SUMMARY */
.summary{
    margin-top:20px;
    text-align:right;
    font-size:16px;
}

.summary p{
    margin:6px 0;
}

.pay{
    font-size:20px;
    font-weight:700;
    color:#ff9800;
}

/* ADDRESS */
.address{
    margin-top:25px;
    padding:15px;
    background:#f7f7f7;
    border-left:5px solid #ff9800;
    border-radius:6px;
    font-size:16px;
}

.checkout{
    text-align:center;
    margin:30px 0;
}

.checkout-btn{
    background:#ff9800;
    color:#fff;
    border:none;
    padding:14px 35px;
    font-size:16px;
    border-radius:25px;
    cursor:pointer;
}

.checkout-btn:hover{
    background:#e68900;
}

@media(max-width:768px){
    .container{
        width:95%;
        padding:20px;
    }

    th, td{
        font-size:14px;
    }

    .summary{
        text-align:left;
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
    <ul class="right-ul">
        <li><a href="index.php">Home</a></li>
        <li><a href="menu.php">Menu</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li class="profile">
            <img src="<?php echo $img; ?>" id="profileBtn">

            <div class="dropdown" id="dropMenu">
                <a href="account.php">Account</a>
                <a href="wishlist.php">Wishlist</a>
                <a href="logout.php">Logout</a>
            </div>
        </li>
    </ul>
</nav>
<div class="container">
    <h2>Your Cart</h2>

    <table>
        <tr>
            <th>Image</th>
            <th>Item</th>
            <th>Qty</th>
            <th>Amount (₹)</th>
        </tr>

        <?php for($i=0; $i<count($items); $i++): 
        $itemName = $items[$i];
        $paths = [
        "Assets/Store/premium/".$itemName.".png",
        "Assets/Store/combo/".$itemName.".png",
        "Assets/Store/drink/".$itemName.".png",
        "Assets/Store/non-vegburger/".$itemName.".png",
        "Assets/Store/vegburger/".$itemName.".png"
        ];

        $img = "Assets/default.png"; 
        foreach($paths as $path){
        if(file_exists($path)){
            $img = $path;
            break;
        }
        }
        ?>
        <tr>
            <td>
                <img src="<?php echo $img; ?>" width="70">
            </td>
            <td><?php echo $items[$i]; ?></td>
            <td>
        <form method="post" style="display:inline;">
            <input type="hidden" name="index" value="<?php echo $i; ?>">
            <input type="hidden" name="action" value="remove">
            <button type="submit">-</button>
        </form>

        <?php echo $qty[$i]; ?>

        <form method="post" style="display:inline;">
            <input type="hidden" name="index" value="<?php echo $i; ?>">
            <input type="hidden" name="action" value="add">
            <button type="submit">+</button>
        </form>
    </td>
            <td>₹<?php echo $price[$i]; ?></td>
        </tr>
        <?php endfor; ?>

        <tr class="total-row">
            <td colspan="3">Total</td>
            <td>₹<?php echo $total; ?></td>
        </tr>
    </table>

    <div class="summary">
        <p>Discount (20%): - ₹<?php echo $discount; ?></p>
        <p>Tax (20%): ₹<?php echo $tax; ?></p>  
        <p class="pay">Payable Amount: ₹<?php echo $netAmount; ?></p>
    </div>
</div>

<div class="checkout">
    <div class="row justify-content-center">
        <div class="col-md-3 text-center">
            <form action="menu.php" method="post">
                <button type="submit" class="checkout-btn">
                    Back To Menu
                </button>
            </form>
        </div>

        <div class="col-md-3 text-center">
            <form action="payment.php" method="post" onsubmit="return checkCart()">
                <button type="submit" class="checkout-btn">
                    Proceed to Checkout
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("profileBtn");
    const menu = document.getElementById("dropMenu");

    if(btn){
        btn.onclick = function(){
            menu.style.display =
                menu.style.display === "block" ? "none" : "block";
        }
    }
});

function checkCart(){
    var count = <?php echo count($items); ?>;
    if(count == 0){
        alert("Your cart is empty!");
        return false;
    }
    return true;
}
</script>
</body>
</html>