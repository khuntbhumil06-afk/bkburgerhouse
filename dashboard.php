<?php
session_start();
include "config.php";

$q1 = mysqli_query($conn,"SELECT * FROM orders");
$total_orders = $q1 ? mysqli_num_rows($q1) : 0;

$q2 = mysqli_query($conn,"SELECT * FROM loginform");
$total_users = $q2 ? mysqli_num_rows($q2) : 0;

$q3 = mysqli_query($conn,"SELECT * FROM stats");
$row = mysqli_fetch_assoc($q3);
$happy_customers = $row['happy_customers'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Arial', sans-serif;
    background: #111;
    color: #fff;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.header {
    background: #000;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
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

.menu {
    display: flex;
    align-items: center;
    gap: 20px;
}

.menu a {
    color: #fff;
    font-weight: bold;
    text-decoration: none;
    transition: 0.3s;
}

.menu a:hover {
    color: #ff4d4d;
}

.profile {
    position: relative;
    display: flex;
    align-items: center;
    cursor: pointer;
}

.profile img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.dropdown {
    display: none;
    position: absolute;
    top: 50px;
    right: 0;
    background: #ff4d4d;
    min-width: 150px;
    border-radius: 8px;
    overflow: hidden;
    z-index: 100;
}

.dropdown a {
    display: block;
    padding: 10px 15px;
    color: #fff;
    text-decoration: none;
    transition: 0.2s;
}

.dropdown a:hover {
    background: #cc0000;
}

.main-container {
    padding: 40px 20px;
    text-align: center;
    flex: 1;
}

.main-container h1 {
    font-size: 32px;
    margin-bottom: 30px;
    color: #ff6b6b;
}

.cards {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 30px;
}

.card {
    background: #ff4d4d;
    padding: 25px 20px;
    border-radius: 15px;
    background-color: #ff4d4d; 
    color: black;              
    text-decoration: none; 
    width: 220px;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.5);
    transition: transform 0.3s;
}

.card img {
    width: 80px;
    margin-bottom: 10px;
}

.card:hover {
    background-color: #e04343;
    transform: scale(1.05); 
}

.card h2 {
    font-size: 36px;
    margin-bottom: 10px;
}

.card p {
    font-size: 18px;
}

.footer {
    background: #111;
    color: #fff;
    margin-top: auto;
    padding: 40px 20px;
}

.footer h3, .footer h5 {
    color: #fff;
    margin-bottom: 15px;
}

.footer p {
    color: #ddd;
    font-size: 14px;
    margin-bottom: 8px;
}

.footer-links {
    list-style: none;
    padding: 0;
}

.footer-links li {
    margin-bottom: 8px;
}

.footer-links a {
    color: #fff;
    text-decoration: none;
    transition: 0.3s;
}

.footer-links a:hover {
    color: #ff4d4d;
}

.social-links a {
    color: #fff;
    font-size: 20px;
    margin-right: 10px;
    transition: 0.3s;
}

.social-links a:hover {
    color: #ff4d4d;
}

.newsletter-form {
    text-align: center;
    margin-top: 10px;
}

.newsletter-form input {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 10px;
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
    padding: 10px;
    border: none;
    border-radius: 6px;
    background: orange;
    color: #fff;
    font-size: 14px;
    cursor: pointer;
    transition: 0.3s;
}

.newsletter-form button:hover {
    background: darkorange;
    transform: scale(1.05);
}

@media(max-width: 768px){
    .cards {
        flex-direction: column;
        align-items: center;
    }
    .speciality {
        flex-direction: column;
        align-items: center;
    }
    .speciality-content {
        width: 90%;
    }
}
</style>

</head>
<body>
<div class="header">

<div class="nav-left">
        <a href="index.php" class="brand">
            <img src="Assets/logo.png">
            <span>B K's Burger House</span>
        </a>
    </div>

<div class="menu">
<a href="index.php">Home</a>
<a href="sales.php">sales</a>
<div class="profile">
            <img src="uploads/bk.png" id="profileBtn">
            <div class="dropdown" id="dropMenu">
                <a href="account.php">Account</a>
                <a href="wishlist.php">Wishlist</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
</div>

</div>

<div class="main-container">
<h1>Admin Dashboard</h1>

<div class="cards">

<a href="total_orders.php" class="card">
<h2><?php echo $total_orders; ?></h2>
<p>Total Orders</p>
</a>

<a href="total_users.php" class="card">
<h2><?php echo $total_users; ?></h2>
<p>Total Users</p>
</a>

<a href="happy_customers.php" class="card">
<h2><?php echo $happy_customers; ?></h2>
<p>Happy Customers</p>
</a>
  
<a href="total_price.php" class="card">
        <img src="Assets/wallet.png" alt="wallet" style="width:40px;margin-bottom:10px;">
        <p>Total Earning Price</p>
</a>
</div>

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
                <p><i class="bi bi-telephone"></i> +91 9904522025</p>
                <p><i class="bi bi-envelope"></i> bk@burgerhouse.com</p>
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