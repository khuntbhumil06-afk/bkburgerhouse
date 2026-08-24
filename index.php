<?php
session_start();
include 'config.php';
$q = mysqli_query($conn,"SELECT * FROM stats WHERE id=1");
$row = mysqli_fetch_assoc($q);

$happy = $row["happy_customers"];
$sold = $row["burgers_sold"];
$today_orders = 0;
$today = date("Y-m-d");
$sql2 = "SELECT COUNT(*) as total 
         FROM orders 
         WHERE DATE(order_time) = '$today'";
$q2 = mysqli_query($conn,$sql2);
if($q2){
    $r2 = mysqli_fetch_assoc($q2);
    $today_orders = $r2["total"];
}

if(!(isset($_SESSION['admin']))){
    $_SESSION['admin'] = "NIL";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
   <style>
body{
    margin:0;
    padding:0;
    background:#000;
}

.container{
    max-width:100%;
    padding-left:0;
    padding-right:0;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}

.nav-container{
    width:100%;
    background:#111;
    padding:10px 10px;
}

.nav-container ul{
    display:flex;
    align-items:center;
    justify-content:space-between;
    list-style:none;
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
    display:flex;
    gap:25px;
}

.right-ul li a{
    text-decoration:none;
    color:#fff;
    font-size:16px;
    transition:0.3s;
}

.right-ul li a:hover{
    color:#ff4d4d;
}

.profile{
    position: relative;
    display: inline-block;
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
    top:45px;
    background:#222;
    min-width:150px;
    border-radius:8px;
    overflow:hidden;
    box-shadow:0 5px 15px rgba(0,0,0,0.4);
    z-index:999;
}

.dropdown a{
    display:block;
    padding:10px;
    color:white;
    text-decoration:none;
}

.dropdown a:hover{
    background:white;
}

.banner img{
    width:100%;
    height:400px;
    object-fit:cover;
}

.hero-section{
    position:relative;
    background:#111;
    color:white;
    padding:100px 0;
    width:100%;
}

.hero-content{
    padding-left:80px;
}

.hero-title{
    font-size:50px;
    font-weight:700;
}

.hero-subtitle{
    font-size:18px;
    color:#ff4d4d;
}

.text-highlight{
    color:#ff4d4d;
}

.hero-text{
    margin-top:15px;
    line-height:1.6;
}

.hero-buttons{
    margin-top:25px;
}

.hero-buttons .btn{
    padding:12px 30px;
    border-radius:30px;
}

.hero-image{
    display:flex;
    justify-content:center;
    padding-right:0;
}

.hero-image img{
    width:400px;
    height:400px;
    object-fit:cover;
    border-radius:0;
    margin-left:100px;
}

.feature-item{
    text-align:center;
}

.feature-item i{
    font-size:25px;
    color:#ff4d4d;
}

.feature-item span{
    display:block;
    font-size:14px;
    margin-top:5px;
}

.specials-section{
    background:#ff4d4d;
    color:white;
}

.section-title{
    font-size:36px;
    font-weight:600;
}

.section-subtitle{
    font-size:18px;
}

.stats-section{
    background:#f8f9fa;
    padding:40px 0;
}

.stat-item{
    text-align:center;
    padding:20px 10px;
}

.stat-item i{
    font-size:40px;
    color:#ff4d4d;
    margin-bottom:10px;
}

.stat-number{
    font-size:28px;
    font-weight:600;
    margin:5px 0;
}

.stat-label{
    font-size:15px;
    color:#555;
}

.stats-section .col-6{
    flex: 0 0 20%;
    max-width: 20%;
}

@media(max-width:768px){
    .stats-section .col-6{
        flex: 0 0 50%;
        max-width:50%;
        margin-bottom:20px;
    }
}

.speciality {
    display: flex;
    justify-content: space-around;
    margin: 40px 0;
}

.speciality-content {
    text-decoration: none;
    width: 30%;
    background: #ff6b6b;
    color: white;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: 0.3s;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

.speciality-content:hover {
    background: #ff4757;
    transform: translateY(-5px);
}

.speciality-pic img {
    width: 80px;
    height: 80px;
}

.speciality-head h2 {
    margin-top: 10px;
    font-size: 20px;
}   


.floating-animation{
    animation:float 4s ease-in-out infinite;
}

@keyframes float{
    0%{transform:translateY(0px);}
    50%{transform:translateY(-15px);}
    100%{transform:translateY(0px);}
}

@media(max-width:768px){

.hero-title{
    font-size:35px;
}

.speciality{
    flex-direction:column;
    gap:30px;
}

.right-ul{
    gap:15px;
}

.hero-content{
    padding-left:20px;
}

.hero-image{
    justify-content:center;
    padding-right:0;
}

}

.footer{
    background:#111;
    color:#fff;
}

.footer h3,
.footer h5{
    color:#fff;
    margin-bottom:15px;
}

.footer p{
    color:#ddd;
    font-size:14px;
}

.footer-links{
    list-style:none;
    padding:0;
}

.footer-links li{
    margin-bottom:8px;
}

.footer-links a{
    text-decoration:none;
    color:#fff;
    transition:0.3s;
}

.footer-links a:hover{
    color:#ff4d4d;
}

.social-links a{
    color:#fff;
    font-size:20px;
    margin-right:10px;
    transition:0.3s;
}

.social-links a:hover{
    color:#ff4d4d;
}

.newsletter-form {
    text-align: center;
}

.newsletter-form input {
    width: 100%;
    padding: 8px 10px;
    border-radius: 6px;
    margin-bottom:12px;
    border: 2px solid transparent;
    outline: none;
    font-size: 14px;
    transition: 0.3s;
}

.newsletter-form input:focus {
    border: 2px solid orange;
}

.newsletter-form input.error-border {
    border: 2px solid red;
}

.newsletter-form button {
    width: 100%;
    padding: 8px;
    border: none;
    border-radius: 6px;
    background: orange;
    color: white;
    font-size: 14px;
    cursor: pointer;
    transition: 0.3s;
}

.newsletter-form button:hover {
    background: darkorange;
    transform: scale(1.05);
}

.newsletter-form button:active {
    transform: scale(0.95);
}

.error {
    color: red;
    font-size: 13px;
    margin-bottom: 5px;
    text-align: left;
}

.footer hr{
    border-color:#444;
}

</style>
</head>
<body>
    <nav class="nav-container">
        <ul>
            <div class="nav-left">
        <a href="index.php" class="brand">
            <img src="Assets/logo.png">
            <span>B K's Burger House</span>
        </a>
    </div>
            <ul class="right-ul">
                <li>
                    <a id="active" href="index.php">Home</a>
                </li>
                <?php if($_SESSION["admin"]=='YES'): ?>
                <li><a href="sales.php">Sales</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li class="profile">
                    <?php if($_SESSION["admin"]=='YES'): ?>
                        <img src="uploads/bk.png" id="profileBtn">
                    <?php endif; ?>
                    <div class="dropdown" id="dropMenu">
                        <a href="account.php">Account</a><br>
                        <a href="logout.php">Logout</a>
                    </div>
                </li>
                <?php elseif ($_SESSION["admin"]=='NIL'):?>
                <li><a href="login.php">Login</a></li>
                <?php else:?>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="cart.php">Cart</a></li>
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
                <?php endif;?>
            </ul>
        </ul>
    </nav>
    <div class="container">
        <section id="home" class="hero-section">
        <div class="hero-overlay"></div>
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-lg-6 hero-content">
                    <span class="hero-subtitle">Welcome to Burger House</span>
                    <h1 class="hero-title">The Best <span class="text-highlight">Burgers</span> You'll Ever Taste!</h1>
                    <p class="hero-text">Fresh ingredients, juicy patties, and unforgettable flavors. Experience the ultimate burger experience with our handcrafted gourmet burgers.</p>
                    <div class="hero-buttons">
                        <a href="#menu.php" onclick="ordernow()" class="btn btn-primary btn-lg me-3">Order Now</a>
                    </div>
                    <div class="hero-features mt-5">
                        <div class="row">
                            <div class="col-4">
                                <div class="feature-item">
                                    <i class="bi bi-clock"></i>
                                    <span>Fast Delivery</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="feature-item">
                                    <i class="bi bi-star-fill"></i>
                                    <span>Best Quality</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="feature-item">
                                    <i class="bi bi-award"></i>
                                    <span>Fresh Food</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 hero-image d-none d-lg-block">
                    <img src="Assets/veg_burger.png"
                        alt="Delicious Burger"
                        class="img-fluid floating-animation"
                        style="width:400px;height:400px;object-fit:cover;">
                </div>
            </div>
        </div>
        <div class="wave-bottom">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#f8f9fa"/>
            </svg>
        </div>
    </section>

    <section id="specials" class="specials-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <span class="section-subtitle">Today's Special</span>
                    <h2 class="section-title text-white">Get 20% Off</span> Your First Order!</h2>
                    <p class="special-text">Use code <strong>BURGER20</strong> at checkout and enjoy a delicious discount on your first order. Don't miss out on this amazing deal!</p>
                    <a href="#" onclick="ordernow()" class="btn btn-primary btn-lg me-3">Order Now</a>
                </div>
            </div>
        </div>
    </section>

    <section class="stats-section py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-6 col-md-2">
                    <div class="stat-item text-center">
                        <i class="bi bi-emoji-smile"></i>
                        <h3 class="stat-number"><?php echo $happy; ?></h3>
                        <p class="stat-label">Happy Customers</p>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="stat-item text-center">
                        <i class="bi bi-basket"></i>
                        <h3 class="stat-number"><?php echo $sold; ?></h3>
                        <p class="stat-label">Burgers Sold</p>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="stat-item text-center">
                        <i class="bi bi-star"></i>
                        <h3 class="stat-number">4.9</h3>
                        <p class="stat-label">Average Rating</p>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="stat-item text-center">
                        <i class="bi bi-award"></i>
                        <h3 class="stat-number">16</h3>
                        <p class="stat-label">Years Experience</p>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="stat-item text-center">
                        <i class="bi bi-bag"></i>
                        <h3 class="stat-number"><?php echo $today_orders; ?></h3>
                        <p class="stat-label">Today Orders</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


        
    <section class="speciality">

    <a href="burgers.php" class="speciality-content">
        <div class="speciality-pic">
            <img src="Assets/veg_burger.png" alt="b">
        </div>
        <div class="speciality-head">
            <h2>Menu List</h2>
        </div>
    </a>

    <a href="total_deliver.php" class="speciality-content">
        <div class="speciality-pic">
            <img src="Assets/delivery.png" alt="d">
        </div>
        <div class="speciality-head">
            <h2>Total Orders Delivered</h2>
        </div>
    </a>

</section>
</div>

    <footer class="footer py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3">
                <h3><i class="bi bi-shop"></i> B K's Burger House</h3>
                <p>Serving the best burgers in town since 2010.</p>
                <div class="social-links">
                    <a href="https://www.facebook.com"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.instagram.com"><i class="bi bi-instagram"></i></a>
                    <a href="https://www.twitter.com"><i class="bi bi-twitter"></i></a>
                    <a href="https://www.youtube.com"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-4">
                <h5>Quick Links</h5>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="menu.php">Menu</a></li>
                    <li><a href="cart.php">Cart</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-4">
                <h5>Location</h5>
                <div class="map-container">
                    <iframe 
                        src = "https://maps.google.com/maps?q=MotaVarachha,Surat&output=embed"
                        width="100%" 
                        height="150" 
                        style="border:0;"
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
                <p>
            <a href="https://www.google.com/maps/place/Ajay's+Sudama+Chowk,+Surat/@21.2394624,72.8443551,5186m/data=!3m1!1e3!4m10!1m2!2m1!1sajay's+burger!3m6!1s0x3be04f838741b843:0x436df813b3079b08!8m2!3d21.239469!4d72.8803962!15sCg1hamF5J3MgYnVyZ2VyWg8iDWFqYXkncyBidXJnZXKSARRmYXN0X2Zvb2RfcmVzdGF1cmFudJoBI0NoWkRTVWhOTUc5blMwVkpRMEZuU1VOUE1TMTZWa3AzRUFF4AEA-gEECAAQSg!16s%2Fg%2F11nc9gy0jb?entry=ttu&g_ep=EgoyMDI2MDMxOC4xIKXMDSoASAFQAw%3D%3D" 
            target="_blank" 
            style="text-decoration:none;color:inherit;">
            <p>Ajay's Burger House,Sudama Chowk,Mota Varachha, Surat ,Gujarat</p>
            </a>
            </p>
                <p>
            <a href="mailto:burgerhousebk28@gmail.com" style="text-decoration:none; color:white;">
                <i class="bi bi-envelope-fill"></i> burgerhousebk28@gmail.com
            </a>
            </p>
            <p>
            <a href="https://wa.me/919904522025" target="_blank" style="text-decoration:none; color:white;">
                💬 +91 99045 22025
            </a>
            </p>
            </div>
            <div class="col-lg-2 col-md-4">
                <h5>Opening Hours</h5>
                <p>Mon - Fri: 10 AM - 10 PM</p>
                <p>Sat - Sun: 11 AM - 12 AM</p>
            </div>
            <div class="col-lg-2">
                <h5>NewSubscribe</h5>
                <form class="newsletter-form" action="newsletter.php" method="POST" onsubmit="return validateEmail()">
                    <input type="name" id="name" name="name" placeholder="Enter Name">
                    <div id="nameError" class="error"></div>
                    <input type="email" id="email" name="email" placeholder="Your Email">
                    <div id="emailError" class="error"></div>
                    <input type="city" id="city" name="city" placeholder="Enter City">
                    <div id="cityError" class="error"></div>
                    <button type="submit" onclick="goToSuccess()">Subscribe</button>
                </form>
            </div>
        </div>
    </div>
</footer>

    <script src="https://kit.fontawesome.com/6f42fc440c.js" crossorigin="anonymous"></script>
    <script src="script.js"></script>
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

function ordernow(){
    <?php if($_SESSION['admin'] == "NIL"): ?>
        window.location.href = "login.php";
    <?php elseif($_SESSION['admin'] == "YES"): ?>
        window.location.href = "menu.php";
    <?php else: ?>
        window.location.href = "menu.php";
    <?php endif; ?>
}


let burgers = [
    { name: "Veg Burger", price: 50 },
    { name: "Cheese Burger", price: 80 },
    { name: "Paneer Burger", price: 100 }
];

let orders = [
    { id: 1, total: 150 },
    { id: 2, total: 200 },
    { id: 3, total: 100 }
];

function showBurgers() {
    let html = "<h3>Burger List</h3><ul>";
    
    burgers.forEach(b => {
        html += `<li>${b.name} - ₹${b.price}</li>`;
    });

    html += "</ul>";
    document.getElementById("output").innerHTML = html;
}

function showTotalPrice() {
    let total = 0;

    orders.forEach(o => {
        total += o.total;
    });

    document.getElementById("output").innerHTML =
        "<h3>Total Earnings: ₹" + total + "</h3>";
}

function showTotalOrders() {
    let count = orders.length;

    document.getElementById("output").innerHTML =
        "<h3>Total Orders Delivered: " + count + "</h3>";
}


function goToSuccess(){
    sessionStorage.setItem("playSound", "true");
}

function validateEmail() {
    let name = document.getElementById("name").value;
    let email = document.getElementById("email").value;
    let city = document.getElementById("city").value;

    let nameError = document.getElementById("nameError");
    let emailError = document.getElementById("emailError");
    let cityError = document.getElementById("cityError");

    let isValid = true;

    nameError.innerText = "";
    emailError.innerText = "";
    cityError.innerText = "";

    if (name.trim() === "") {
        nameError.style.color = "red";
        nameError.innerText = "⚠ Please enter your Name!";
        isValid = false;
    }

    if (email.trim() === "") {
        emailError.style.color = "red";
        emailError.innerText = "⚠ Please enter your Email!";
        isValid = false;
    } else {
        let pattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
        if (!email.match(pattern)) {
            emailError.style.color = "red";
            emailError.innerText = "⚠ Invalid email format!";
            isValid = false;
        }
    }

    if (city.trim() === "") {
        cityError.style.color = "red";
        cityError.innerText = "⚠ Please enter your City!";
        isValid = false;
    }

    return isValid;
}
</script>
</script>
</body>
</html>