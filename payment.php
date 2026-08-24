<?php
session_start();
include 'config.php';

if(!isset($_SESSION['username'])){
    header("location: login.php");
    exit();
}

if($_SESSION["admin"]=='YES'){
    header("location: sales.php");
    exit();
}

$username   = $_SESSION['username'];
$cartItemN  = $_SESSION['cartItemN'] ?? [];
$cartItemQ  = $_SESSION['cartItemQ'] ?? [];
$cartItemP  = $_SESSION['cartItemP'] ?? [];
$netAmount  = $_SESSION['netAmount'] ?? 0;

$fullname = $email = $cno = $cvv = $address = $payment_method = "";
$err = "";

$fullname_err = $email_err = $cno_err = $cvv_err = $address_err = $paymentmethod_err = "";

if($_SERVER['REQUEST_METHOD']=="POST"){

    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $cno      = trim($_POST['cno'] ?? '');
    $cvv      = trim($_POST['cvv'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';

    if(empty($fullname) || !preg_match("/^[a-zA-Z ]+$/", $fullname))
        $fullname_err = "Enter valid full name";

    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $email_err = "Enter valid email";

    if(empty($cno) || !preg_match("/^[0-9]{16}$/", $cno))
        $cno_err = "Enter valid 16-digit card number";

    if(empty($cvv) || !preg_match("/^[0-9]{4}$/", $cvv))
        $cvv_err = "Enter valid 4-digit CVV";

    if(empty($address))
        $address_err = "Enter address";

    if(empty($payment_method))
        $paymentmethod_err = "Select payment method";

    if(empty($fullname_err) && empty($email_err) && empty($cno_err) &&
       empty($emy_err) && empty($eyear_err) && empty($cvv_err) &&
       empty($address_err) && empty($paymentmethod_err)){

        $allCN = implode(", ", $cartItemN);
        $allCQ = implode(", ", $cartItemQ);
        $_SESSION['addr'] = $address;
        $order_id = date('dmYhis');
        $current_time = date("Y-m-d H:i:s");
        $details = "Confirmed";

        $sql = "INSERT INTO orders (fullname, username, order_id, item, quantity, email, address, price, details, status_time, payment_method) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Confirmed', ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        if($stmt){
            mysqli_stmt_bind_param($stmt, "sssssssdss", $fullname, $username, $order_id, $allCN, $allCQ, $email, $address, $netAmount, $current_time, $payment_method);
            if(mysqli_stmt_execute($stmt)){

                $totalQty = array_sum($cartItemQ);
                $sql2 = "UPDATE stats SET happy_customers = happy_customers + 1, burgers_sold = burgers_sold + $totalQty WHERE id = 1";
                mysqli_query($conn, $sql2);

                require('libs/fpdf.php');

                $pdf = new FPDF();
                $pdf->AddPage();
                $pdf->SetFont('helvetica','B',16);
                $pdf->Cell(0,10,"Ajay's Burger House Bill",0,1,'C');
                $pdf->Ln(10);
                $pdf->SetFont('Arial','',12);

                $pdf->Cell(50,10,"Order ID:",0,0);
                $pdf->Cell(0,10,$order_id,0,1);
                $pdf->Cell(50,10,"Name:",0,0);
                $pdf->Cell(0,10,$fullname,0,1);
                $pdf->Cell(50,10,"Email:",0,0);
                $pdf->Cell(0,10,$email,0,1);

                $pdf->Cell(50,10,"Items:",0,1);
                for($i=0;$i<count($cartItemN);$i++){
                    $pdf->Cell(50,10,$cartItemN[$i],0,0);
                    $pdf->Cell(0,10,"Qty: ".$cartItemQ[$i],0,1);
                }

                $pdf->Ln(5);
                $pdf->Cell(50,10,"Total Amount:",0,0);
                $pdf->Cell(0,10,"₹".$netAmount,0,1);

                $receiptFile = 'uploads/receipts/Receipt_'.$order_id.'.pdf';
                $pdf->Output('F', $receiptFile);

                $_SESSION['receipt_pdf'] = $receiptFile;
                $_SESSION['order_id'] = $order_id;
                $_SESSION['payment'] = "yes";

                unset($_SESSION['cartItemN'], $_SESSION['cartItemQ'], $_SESSION['cartItemP'], $_SESSION['netAmount']);

                header("location: success.php");
                exit();
            } else {
                $err = "Database error!";
            }

            mysqli_stmt_close($stmt);
        } else {
            $err = "SQL error!";
        }
    }
}

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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Burger Ordering System</title>
    <link rel="icon" href="Assets/logo.png">
    <link rel="stylesheet" href="style.css">
    <style>
    *{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI', Arial, sans-serif;
    }

    body{
    font-family: Arial;
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                url('Assets/images.png') no-repeat center center fixed;
    background-size: cover;
    color:black;
    margin:0;
}

    .profile{
    position:relative;
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
    right:0;
    top:50px;
    background:#111;
    min-width:160px;
    border-radius:10px;
    overflow:hidden;
    box-shadow:0 10px 25px rgba(255, 250, 250, 0.5);
    z-index:999;
}

.dropdown a{
    display:block;
    padding:12px;
    color:#fff;
    text-decoration:none;
    transition:0.3s;
}

.dropdown a:hover{
    background:#ff4d4d;
}

.cart-box {
    width: 90%;
    max-width: 700px;
    margin: 1px auto;
    background: #fff;
    border-radius: 12px;
    padding: 15px;
    font-size: 15px;
    background: rgba(255,255,255,0.95);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    text-align: left;
}

.cart-item {
    display: flex;
    flex-direction: column;  
    padding: 10px 0;
    border-bottom: 1px solid #ddd;
}

.item-name {
    font-weight: 600;
    color: #111;
}

.item-qty {
    font-size: 14px;
    color: #666;
    margin-top: 3px;
}

.item-price {
    font-weight: bold;
    color: #ff4d4d;
}

.cart-total {
    text-align: right;
    margin-top: 10px;
    font-size: 18px;
    font-weight: bold;
    color: #ff4d4d;
}

.cart-box h3 {
    margin: 0;
    cursor: pointer;
}


.payment-container {
    display: flex;
    justify-content: center;
    margin-top: 30px;
}

.payment-page {
    background: #0c0c0c;
    padding: 25px;
    margin-top: 40vh;
    border-radius: 15px;
    color: #f8f2f2;
    mix_width: 500px;
}

.form-row {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}

.pay-details {
    flex: 1;
}

.pay-details input,
.pay-details textarea {
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    border: none;
    margin-top: 5px;
}

.payment-method {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.payment-method select {
    width: 50%;
    padding: 10px;
    border-radius: 8px;
}
.error-text {
    color: #ff4d4d;
    font-size: 12px;
    margin-top: 5px;
    display: block;
}

button {
    width: 20%;
    padding: 12px;
    background: #ff4d4d;
    border: none;
    color: #fff;
    font-size: 16px;
    border-radius: 8px;
    margin-top: 20px;
    cursor: pointer;
}

button:hover {
    background: #e60000;
}

@media(max-width:700px){
    .form-row {
        flex-direction: column;
    }
}
    
    </style>
</head>
<body>
    <nav class="nav-container">
        <ul>
            <ul>
                <li class="brand"><img src="Assets/logo.png" alt="Music">
            B K's Burger House
            </li>
            </ul>
            <ul class="right-ul">
                <li><a id="active" href="index.php">Home</a></li>
                <li><a id="active" href="menu.php">Menu</a></li>
                <li><a id="active" href="cart.php">Cart</a></li>
                <li><a id="active" href="about.php">About</a></li>
                <li><a id="active" href="contact.php">Contact</a></li>
                <li class="profile">
                    <img src="<?php echo $img; ?>" id="profileBtn">
                    <div class="dropdown" id="dropMenu">
                        <a href="account.php">Account</a>
                        <a href="wishlist.php">Wishlist</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </li>
            </ul>
        </ul>
    </nav>
<div class="cart-container">
    <div class="cart-box">
        <h3 id="cartBtn">🛒 Your Cart</h3>
        <div class="cart-items">
            <?php
            for($i=0;$i<count($cartItemN);$i++){
    echo "
    <div class='cart-item'>
        <div class='item-name'>".$cartItemN[$i]."</div>
        <div class='item-qty'>Qty: ".$cartItemQ[$i]."</div>
        <div class='item-price'>₹".$cartItemP[$i]."</div>
    </div>
    ";
}
            ?>
        </div>
        <div class="cart-total">
            Total: ₹<?php echo $netAmount; ?>
        </div>
    </div>
</div> 
<div class="payment-container">
    <form action="" method="post">
            <section class="payment-page">
                <h3 style="text-align:center; margin-bottom:20px;">💳 Payment Details</h3>
                <span style="color:red;"><?php echo $err;?></span>
                <div class="pay-details">
                    <label>Accepted Cards</label>
                    <div>
                        <i class="fa fa-cc-mastercard fa-2x" style="color:red;margin-right:100px;"></i>
                        <i class="fa fa-cc-visa fa-2x" style="color:navy;margin-left:-100px;"></i>
                    </div>
                </div>

                <div class="form-row">
                    <div class="pay-details">
                        <label>Full Name</label>
                        <input type="text" name="fullname" placeholder="Tony Stark">
                        <span style="color:red; font-size:12px;"><?php echo $fullname_err; ?></span>
                    </div>

                    <div class="pay-details">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="email@gmail.com">
                        <span style="color:red; font-size:12px;"><?php echo $email_err; ?></span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="pay-details">
                        <label>Card Number</label>
                        <input type="text" name="cno">
                        <span style="color:red; font-size:12px;"><?php echo $cno_err; ?></span>
                    </div>

                    <div class="pay-details" style="width:110px;">
                        <label>CVV</label>
                        <input type="text" name="cvv">
                        <span style="color:red; font-size:12px;"><?php echo $cvv_err; ?></span>
                    </div>
                </div>

                <div class="pay-details">
                    <label>Address</label>
                        <textarea name="address" placeholder="Enter your delivery address"></textarea>
                        <span class="error-text"><?php echo $address_err; ?></span>
                </div>

                <div class="pay-details payment-method">
                    <label>Payment Method</label>
                    <select name="payment_method" required>
                        <option value="">-- Select Payment Method --</option>
                        <option value="card">Card Payment</option>
                        <option value="upi">UPI</option>
                        <option value="cod">Cash on Delivery</option>
                    </select>
                    <span class="error-text"><?php echo $paymentmethod_err; ?></span>
                </div>

                <div class="checkout-buttons" style="display:flex; justify-content:center; gap:20px; margin-top:30px;">
                    <button type="button" onclick="window.location.href='cart.php';"
                        style="padding:12px 25px; font-size:16px; border:none; border-radius:6px; background:#ccc; color:#000; cursor:pointer;">
                        Back To Cart
                    </button>

                    <form action="payment.php" method="post">
                        <button type="submit" class="btn-pay" style="padding:12px 25px; font-size:16px; border:none; border-radius:6px; background:#ff9800; color:#fff; cursor:pointer;">
                        Pay ₹<?php echo $netAmount; ?>
                        </button>
                    </form>

                </div>
            </section>
        </form>
</div>

    <script src="https://kit.fontawesome.com/6f42fc440c.js" crossorigin="anonymous"></script>
    <script>
document.addEventListener("DOMContentLoaded", function () {

    const btn = document.getElementById("profileBtn");
    const menu = document.getElementById("dropMenu");

    btn.onclick = function(e){
        e.stopPropagation();
        menu.style.display =
            menu.style.display === "block" ? "none" : "block";
    }
    document.addEventListener("click", function () {
        menu.style.display = "none";
    });

});
</script>
</body>
</html>