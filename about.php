<?php
session_start();

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
<html>
<head>
    <title>About Us - Burger House</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body{
    font-family: Arial;
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                url('Assets/images.png') no-repeat center center fixed;
    background-size: cover;
    color:white;
    margin:0;
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
    .profile {
    position: relative;
    list-style: none;
}

.profile img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
}

.dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: 45px;
    background: white;
    width: 140px;
    border-radius: 8px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
    padding: 10px;
}

.dropdown a{
    display: block;
    text-decoration: none;
    color: black;
    padding: 5px;
}

.dropdown a:hover{
    background: #eee;
}

 .about-images{
    position: relative;
}

.main-image{
    width: 100%;
    border-radius: 15px;
    transition:0.5s;
    animation: zoomInOut 4s ease-in-out infinite;
}

@keyframes zoomInOut{
    0%{
        transform: scale(1);
    }
    50%{
        transform: scale(1.08);
    }
    100%{
        transform: scale(1);
    }
}

.experience-badge{
    position: absolute;
    bottom: -20px;
    right: -20px;
    background: #e74c3c;
    color: white;
    padding: 20px;
    border-radius: 15px;
    text-align: center;
    width: 120px;
    animation: zoomBadge 4s ease-in-out infinite;
}

@keyframes zoomBadge{
    0%{
        transform: scale(1);
    }
    50%{
        transform: scale(1.1);
    }
    100%{
        transform: scale(1);
    }
}

.experience-badge .years{
    font-size: 35px;
    font-weight: bold;
    display: block;
}

.experience-badge .text{
    font-size: 14px;
}

.section-subtitle{
    color: #e74c3c;
    font-weight: bold;
    text-transform: uppercase;
}

.section-title{
    font-size: 40px;
    font-weight: bold;
    color: #2c3e50;
}

.text-highlight{
    color: #e74c3c;
}

.about-text{
    color: #555;
    font-size: 16px;
}

.about-feature{
    color: #2c3e50;
    margin-bottom: 8px;
}

.btn-primary{
    background: #0d6efd;
    border: none;
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

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <div class="nav-left">
        <a href="index.php" class="brand">
            <img src="Assets/logo.png">
            <span>B K's Burger House</span>
        </a>
        </div>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="menu.php">Menu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">Contact</a>
                </li>
                <li class="profile">
                    <img src="<?php echo $img; ?>" id="profileBtn">
                    <div class="dropdown" id="dropMenu">
                        <a href="account.php">Account</a>
                        <a href="wishlist.php">Wishlist</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<section id="about" class="about-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="about-images">
                        <img src="Assets/burgerking.png" alt="About Burger House" class="img-fluid main-image rounded-4 shadow">
                        <div class="experience-badge">
                            <span class="years">15+</span>
                            <span class="text">Years of<br>Experience</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-content">
                        <span class="section-subtitle">About Us</span>
                        <h2 class="section-title" style="color: white;">We Make The Best <span class="text-highlight">Burgers In The City</span> </h2>
                        <p class="about-text" style="color: white;">At Burger House, we believe that a great burger starts with great ingredients. That's why we source only the freshest, highest-quality ingredients for our burgers.</p>
                        <p class="about-text" style="color: white;"><span class="text-highlight">Our patties are made from 100% premium beef, our vegetables are locally sourced, and our buns are baked fresh daily. Every burger is handcrafted with love and attention to detail.</p>
                        <div class="about-features">
                            <div class="about-feature" style="color: white;">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>100% Premium Beef</span>
                            </div>
                            <div class="about-feature" style="color: white;">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Fresh Local Vegetables</span>
                            </div>
                            <div class="about-feature" style="color: white;">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Handcrafted Daily</span>
                            </div>
                        </div>
                        <a href="#menu.php" onclick="Menu()" class="btn btn-primary mt-4">Explore Our Menu</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/main.js"></script>

<script>
document.getElementById("profileBtn").onclick = function(){
    let d = document.getElementById("dropMenu");

    if(d.style.display == "block"){
        d.style.display = "none";
    }else{
        d.style.display = "block";
    }
}

function Menu(){
    <?php if(!isset($_SESSION['admin']) || $_SESSION['admin'] == "NIL"): ?>
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

</body>
</html>
