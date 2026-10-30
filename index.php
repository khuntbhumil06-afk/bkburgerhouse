<?php
session_start();
include 'config.php';
$q = mysqli_query($conn,"SELECT * FROM stats WHERE id=1");
$row = mysqli_fetch_assoc($q) ?: ['happy_customers' => 0, 'burgers_sold' => 0];

$happy = $row["happy_customers"];
$sold = $row["burgers_sold"];
$today_orders = 0;
$today = date("Y-m-d");
$sql2 = "SELECT COUNT(*) as total FROM orders WHERE DATE(order_time) = '$today'";
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
*{box-sizing:border-box;font-family:'Poppins',sans-serif}
html,body{margin:0;padding:0;background:#000;overflow-x:hidden}
img{max-width:100%}

/* ---------- NAVBAR ---------- */
.nav-container{width:100%;background:#111;padding:10px 16px;position:sticky;top:0;z-index:1000}
.nav-inner{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;max-width:1400px;margin:0 auto}
.brand{display:flex;align-items:center;gap:10px;color:#fff;text-decoration:none;font-size:clamp(16px,4.5vw,20px);font-weight:bold}
.brand img{width:40px;height:auto}
.nav-toggle{display:none;background:none;border:0;color:#fff;font-size:30px;line-height:1;cursor:pointer;padding:0 4px}
.right-ul{display:flex;align-items:center;gap:25px;list-style:none;margin:0;padding:0}
.right-ul li a{text-decoration:none;color:#fff;font-size:16px;transition:.3s}
.right-ul li a:hover{color:#ff4d4d}
.profile{position:relative;display:inline-block}
.profile img{width:40px;height:40px;border-radius:50%;cursor:pointer;object-fit:cover}
.dropdown{display:none;position:absolute;right:0;top:48px;background:#222;min-width:160px;border-radius:8px;overflow:hidden;box-shadow:0 5px 15px rgba(0,0,0,.4);z-index:999}
.dropdown a{display:block;padding:10px 14px;color:#fff;text-decoration:none}
.dropdown a:hover{background:#ff4d4d;color:#fff}

@media(max-width:991px){
    .nav-toggle{display:block}
    .right-ul{display:none;width:100%;flex-direction:column;align-items:flex-start;gap:14px;padding:12px 0 6px;border-top:1px solid #333}
    .right-ul.open{display:flex}
    .right-ul li{width:100%}
    .profile{display:block}
    .dropdown{position:static;margin-top:10px;width:100%;box-shadow:none}
}

/* ---------- HERO ---------- */
.page-wrap{width:100%}
.hero-section{position:relative;background:#111;color:#fff;padding:clamp(40px,8vw,100px) 0 clamp(60px,9vw,120px);width:100%;overflow:hidden}
.hero-content{padding-left:clamp(16px,5vw,80px);padding-right:16px}
.hero-subtitle{font-size:18px;color:#ff4d4d}
.hero-title{font-size:clamp(30px,6vw,50px);font-weight:700}
.text-highlight{color:#ff4d4d}
.hero-text{margin-top:15px;line-height:1.6}
.hero-buttons{margin-top:25px}
.hero-buttons .btn{padding:12px 30px;border-radius:30px}
.hero-image{display:flex;justify-content:center;align-items:center}
.hero-image img{width:100%;max-width:400px;height:auto;aspect-ratio:1/1;object-fit:cover}
.feature-item{text-align:center}
.feature-item i{font-size:25px;color:#ff4d4d}
.feature-item span{display:block;font-size:14px;margin-top:5px}
.wave-bottom{position:absolute;left:0;bottom:-1px;width:100%;line-height:0}
.wave-bottom svg{width:100%;height:auto;display:block}
.floating-animation{animation:float 4s ease-in-out infinite}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-15px)}}

@media(max-width:991px){
    .hero-content{text-align:center;padding-left:20px;padding-right:20px}
    .hero-image{margin-top:35px}
    .hero-image img{max-width:280px}
}

/* ---------- SPECIALS ---------- */
.specials-section{background:#ff4d4d;color:#fff;padding-left:16px;padding-right:16px}
.section-title{font-size:clamp(24px,5vw,36px);font-weight:600}
.section-subtitle{font-size:18px}

/* ---------- STATS ---------- */
.stats-section{background:#f8f9fa;padding:40px 16px}
.stat-item{text-align:center;padding:20px 10px}
.stat-item i{font-size:40px;color:#ff4d4d;margin-bottom:10px}
.stat-number{font-size:28px;font-weight:600;margin:5px 0}
.stat-label{font-size:15px;color:#555;margin:0}

/* ---------- SPECIALITY ---------- */
.speciality{display:flex;flex-wrap:wrap;justify-content:center;gap:24px;margin:40px 16px}
.speciality-content{text-decoration:none;flex:1 1 260px;max-width:380px;background:#ff6b6b;color:#fff;border-radius:12px;padding:20px;text-align:center;transition:.3s;box-shadow:0 4px 10px rgba(0,0,0,.2)}
.speciality-content:hover{background:#ff4757;transform:translateY(-5px);color:#fff}
.speciality-pic img{width:80px;height:80px;object-fit:contain}
.speciality-head h2{margin-top:10px;font-size:20px}

/* ---------- FOOTER ---------- */
.footer{background:#111;color:#fff;padding-left:16px;padding-right:16px}
.footer h3,.footer h5{color:#fff;margin-bottom:15px}
.footer h3{font-size:clamp(18px,4vw,24px)}
.footer p{color:#ddd;font-size:14px;word-break:break-word}
.footer-links{list-style:none;padding:0}
.footer-links li{margin-bottom:8px}
.footer-links a{text-decoration:none;color:#fff;transition:.3s}
.footer-links a:hover{color:#ff4d4d}
.social-links a{color:#fff;font-size:20px;margin-right:10px;transition:.3s}
.social-links a:hover{color:#ff4d4d}
.map-container iframe{width:100%;height:150px;border:0}

.newsletter-form{text-align:center}
.newsletter-form input{width:100%;padding:8px 10px;border-radius:6px;margin-bottom:12px;border:2px solid transparent;outline:none;font-size:14px;transition:.3s}
.newsletter-form input:focus{border:2px solid orange}
.newsletter-form input.error-border{border:2px solid red}
.newsletter-form button{width:100%;padding:8px;border:none;border-radius:6px;background:orange;color:#fff;font-size:14px;cursor:pointer;transition:.3s}
.newsletter-form button:hover{background:darkorange}
.newsletter-form button:active{transform:scale(.97)}
.error{color:red;font-size:13px;margin-bottom:5px;text-align:left}

@media(max-width:767px){
    .footer{text-align:center}
    .newsletter-form{max-width:320px;margin:0 auto}
}
</style>
</head>
<body>
    <nav class="nav-container">
        <div class="nav-inner">
            <a href="index.php" class="brand">
                <img src="Assets/logo.png" alt="Logo">
                <span>B K's Burger House</span>
            </a>
            <button class="nav-toggle" id="navToggle" aria-label="Toggle menu"><i class="bi bi-list"></i></button>

            <ul class="right-ul" id="navMenu">
                <li><a id="active" href="index.php">Home</a></li>
                <?php if($_SESSION["admin"]=='YES'): ?>
                <li><a href="sales.php">Sales</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li class="profile">
                    <img src="uploads/bk.png" id="profileBtn" alt="Profile">
                    <div class="dropdown" id="dropMenu">
                        <a href="account.php">Account</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </li>
                <?php elseif ($_SESSION["admin"]=='NIL'): ?>
                <li><a href="login.php">Login</a></li>
                <?php else: ?>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li class="profile">
                    <img src="<?php echo $img; ?>" id="profileBtn" alt="Profile">
                    <div class="dropdown" id="dropMenu">
                        <a href="account.php">Account</a>
                        <a href="my_orders.php">My Order</a>
                        <a href="wishlist.php">Wishlist</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="page-wrap">
        <section id="home" class="hero-section">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6 hero-content">
                        <span class="hero-subtitle">Welcome to Burger House</span>
                        <h1 class="hero-title">The Best <span class="text-highlight">Burgers</span> You'll Ever Taste!</h1>
                        <p class="hero-text">Fresh ingredients, juicy patties, and unforgettable flavors. Experience the ultimate burger experience with our handcrafted gourmet burgers.</p>
                        <div class="hero-buttons">
                            <a href="menu.php" onclick="ordernow()" class="btn btn-primary btn-lg">Order Now</a>
                        </div>
                        <div class="hero-features mt-5">
                            <div class="row">
                                <div class="col-4">
                                    <div class="feature-item"><i class="bi bi-clock"></i><span>Fast Delivery</span></div>
                                </div>
                                <div class="col-4">
                                    <div class="feature-item"><i class="bi bi-star-fill"></i><span>Best Quality</span></div>
                                </div>
                                <div class="col-4">
                                    <div class="feature-item"><i class="bi bi-award"></i><span>Fresh Food</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6 hero-image">
                        <img src="Assets/veg_burger.png" alt="Delicious Burger" class="img-fluid floating-animation">
                    </div>
                </div>
            </div>
            <div class="wave-bottom">
                <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                    <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#f8f9fa"/>
                </svg>
            </div>
        </section>

        <section id="specials" class="specials-section py-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto text-center">
                        <span class="section-subtitle">Today's Special</span>
                        <h2 class="section-title text-white">Get 20% Off Your First Order!</h2>
                        <p class="special-text">Use code <strong>BURGER20</strong> at checkout and enjoy a delicious discount on your first order. Don't miss out on this amazing deal!</p>
                        <a href="#" onclick="ordernow(); return false;" class="btn btn-primary btn-lg">Order Now</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="stats-section">
            <div class="container">
                <div class="row g-3 justify-content-center">
                    <div class="col-6 col-md-4 col-lg">
                        <div class="stat-item"><i class="bi bi-emoji-smile"></i><h3 class="stat-number"><?php echo $happy; ?></h3><p class="stat-label">Happy Customers</p></div>
                    </div>
                    <div class="col-6 col-md-4 col-lg">
                        <div class="stat-item"><i class="bi bi-basket"></i><h3 class="stat-number"><?php echo $sold; ?></h3><p class="stat-label">Burgers Sold</p></div>
                    </div>
                    <div class="col-6 col-md-4 col-lg">
                        <div class="stat-item"><i class="bi bi-star"></i><h3 class="stat-number">4.9</h3><p class="stat-label">Average Rating</p></div>
                    </div>
                    <div class="col-6 col-md-4 col-lg">
                        <div class="stat-item"><i class="bi bi-award"></i><h3 class="stat-number">16</h3><p class="stat-label">Years Experience</p></div>
                    </div>
                    <div class="col-12 col-md-4 col-lg">
                        <div class="stat-item"><i class="bi bi-bag"></i><h3 class="stat-number"><?php echo $today_orders; ?></h3><p class="stat-label">Today Orders</p></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="speciality">
            <a href="burgers.php" class="speciality-content">
                <div class="speciality-pic"><img src="Assets/veg_burger.png" alt="Menu"></div>
                <div class="speciality-head"><h2>Menu List</h2></div>
            </a>
            <a href="total_deliver.php" class="speciality-content">
                <div class="speciality-pic"><img src="Assets/delivery.png" alt="Delivery"></div>
                <div class="speciality-head"><h2>Total Orders Delivered</h2></div>
            </a>
        </section>
    </div>

    <footer class="footer py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-12 col-md-6 col-lg-3">
                    <h3><i class="bi bi-shop"></i> B K's Burger House</h3>
                    <p>Serving the best burgers in town since 2010.</p>
                    <div class="social-links">
                        <a href="https://www.facebook.com"><i class="bi bi-facebook"></i></a>
                        <a href="https://www.instagram.com"><i class="bi bi-instagram"></i></a>
                        <a href="https://www.twitter.com"><i class="bi bi-twitter"></i></a>
                        <a href="https://www.youtube.com"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="menu.php">Menu</a></li>
                        <li><a href="cart.php">Cart</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-3 col-lg-2 order-lg-4">
                    <h5>Opening Hours</h5>
                    <p>Mon - Fri: 10 AM - 10 PM</p>
                    <p>Sat - Sun: 11 AM - 12 AM</p>
                </div>
                <div class="col-12 col-md-6 col-lg-3 order-lg-3">
                    <h5>Location</h5>
                    <div class="map-container">
                        <iframe src="https://maps.google.com/maps?q=MotaVarachha,Surat&output=embed" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                    <p class="mt-2">
                        <a href="https://www.google.com/maps/place/Ajay's+Sudama+Chowk,+Surat/@21.2394624,72.8443551,5186m/data=!3m1!1e3!4m10!1m2!2m1!1sajay's+burger!3m6!1s0x3be04f838741b843:0x436df813b3079b08!8m2!3d21.239469!4d72.8803962!15sCg1hamF5J3MgYnVyZ2VyWg8iDWFqYXkncyBidXJnZXKSARRmYXN0X2Zvb2RfcmVzdGF1cmFudJoBI0NoWkRTVWhOTUc5blMwVkpRMEZuU1VOUE1TMTZWa3AzRUFF4AEA-gEECAAQSg!16s%2Fg%2F11nc9gy0jb?entry=ttu&g_ep=EgoyMDI2MDMxOC4xIKXMDSoASAFQAw%3D%3D" target="_blank" style="text-decoration:none;color:inherit;">Ajay's Burger House, Sudama Chowk, Mota Varachha, Surat, Gujarat</a>
                    </p>
                    <p><a href="mailto:burgerhousebk28@gmail.com" style="text-decoration:none;color:#fff;"><i class="bi bi-envelope-fill"></i> burgerhousebk28@gmail.com</a></p>
                    <p><a href="https://wa.me/919904522025" target="_blank" style="text-decoration:none;color:#fff;">💬 +91 99045 22025</a></p>
                </div>
                <div class="col-12 col-md-6 col-lg-2 order-lg-5">
                    <h5>Subscribe</h5>
                    <form class="newsletter-form" action="newsletter.php" method="POST" onsubmit="return validateEmail()">
                        <input type="text" id="name" name="name" placeholder="Enter Name">
                        <div id="nameError" class="error"></div>
                        <input type="email" id="email" name="email" placeholder="Your Email">
                        <div id="emailError" class="error"></div>
                        <input type="text" id="city" name="city" placeholder="Enter City">
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
    const toggle = document.getElementById("navToggle");
    const navMenu = document.getElementById("navMenu");

    // Mobile hamburger
    toggle.addEventListener("click", function () {
        navMenu.classList.toggle("open");
    });

    // Profile dropdown
    if (btn && menu) {
        btn.addEventListener("click", function (e) {
            e.stopPropagation();
            menu.style.display = menu.style.display === "block" ? "none" : "block";
        });
        document.addEventListener("click", function () {
            menu.style.display = "none";
        });
    }
});

function ordernow(){
    <?php if($_SESSION['admin'] == "NIL"): ?>
        window.location.href = "login.php";
    <?php else: ?>
        window.location.href = "menu.php";
    <?php endif; ?>
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
        nameError.innerText = "⚠ Please enter your Name!";
        isValid = false;
    }

    if (email.trim() === "") {
        emailError.innerText = "⚠ Please enter your Email!";
        isValid = false;
    } else {
        let pattern = /^[^ ]+@[^ ]+\.[a-z]{2,}$/;
        if (!email.match(pattern)) {
            emailError.innerText = "⚠ Invalid email format!";
            isValid = false;
        }
    }

    if (city.trim() === "") {
        cityError.innerText = "⚠ Please enter your City!";
        isValid = false;
    }

    return isValid;
}
</script>
</body>
</html>