<?php
session_start();
include 'config.php';

$success = "";

if(isset($_POST['send']))
{
    $name    = htmlspecialchars($_POST['name']);
    $email   = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    $sql = "INSERT INTO contact_messages (name, email, subject, message)
            VALUES ('$name', '$email', '$subject', '$message')";

    if($conn->query($sql) === TRUE){
        header("Location: index.php?msg=sent");
        exit();
    } else {
        echo "Error: " . $conn->error;
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
<html>
<head>
    <title>Contact Us - Burger House</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
         body{
    font-family: Arial;
    background:url('Assets/images.png') no-repeat center center fixed;
    background-size: cover;
    color:white;
    margin:0;
}

        .brand{
    color:#fff;
    font-size:22px;
    font-weight:600;
    display:flex;
    align-items:center;
}

.brand img{
    width:34px;
    margin-right:10px;
}
        .profile {
    position: relative;
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
    top: 50px;
    background: red;
    width: 140px;
    border-radius: 8px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
}
.contact-title-box{
    margin:50px auto;
    width: fit-content;
    padding:25px 50px;
    border-radius:20px;
    text-align:center;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #ff4d4d, #ff944d);
    color:#fff;
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
    opacity: 0;
    transform: translateY(-100px);
    transition: all 0.7s ease;
    animation: zoomInOut 4s ease-in-out infinite;
}
@keyframes zoomInOut {
    0% { transform: scale(1); }
    50% { transform: scale(1.08); }
    100% { transform: scale(1); }
}

.contact-title-box.show{
    opacity: 1;
    transform: translateY(0);
}

.contact-title-box h1{
    margin:0;
    font-size:55px;
    font-weight:bold;
    color:#fff;
}

.contact-title-box::before{
    content:'';
    position:absolute;
    width:200px;
    height:200px;
    background: rgba(255,255,255,0.2);
    border-radius:50%;
    top:-60px;
    left:-60px;
}

.contact-title-box::after{
    content:'';
    position:absolute;
    width:150px;
    height:150px;
    background: rgba(255,255,255,0.15);
    border-radius:50%;
    bottom:-50px;
    right:-50px;
}

.contact-title-box:hover{
    transform: scale(1.05);
    box-shadow:0 15px 40px rgba(0,0,0,0.4);
}

@keyframes zoomInOut {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

@media(max-width:768px){
    h1 {
        font-size: 40px;
    }
}

.info-box{
    background:#f5f5f5;
    padding:30px;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,0.2);
    transition:0.3s;
}

.info-box{
    opacity: 0;
    transform: translateY(100px);
    transition: all 0.6s ease;
}

.info-box.show{
    opacity: 1;
    transform: translateY(0);
}

.info-box:hover{
    transform:translateY(-5px);
}

.icon-circle{
    width:60px;
    height:60px;
    background:#f4c7b5;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:0 auto 10px;
    font-size:25px;
}
        .nav-container{
            background:#000;
            padding:10px 20px;
        }
        .nav-container ul{
            list-style:none;
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin:0;
        }
        .nav-container li{
            display:inline;
            margin-right:15px;
        }
        .nav-container a{
            color:white;
            text-decoration:none;
            font-weight:bold;
        }
        .brand{
            color:#ffc107;
            font-size:20px;
            font-weight:bold;
        }
        .contact-form-wrapper{
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
    opacity: 0;
    transform: translateX(200px) scale(0.9);
    transition: all 0.7s ease;
}
.contact-form-wrapper.show{
    opacity: 1;
    transform: translateX(0) scale(1);
}
        @keyframes slideRight{
        from{
        transform: translateX(200px);
        opacity:0;
        }
        to{
        transform: translateX(0);
        opacity:1;
        }
        }
        .contact-form-wrapper:hover{
    transform: scale(1.05);
    box-shadow:0 10px 25px rgba(0,0,0,0.3);
}

        .contact-info{
            background:#fff;
            padding:25px;
            border-radius:10px;
            box-shadow:0 0 10px rgba(0,0,0,0.1);
        }
        .contact-item{
            margin-bottom:20px;
        }

        .map-container{
    opacity: 0;
    transform: translateX(-200px);
    transition: all 0.7s ease;
}

.map-container.show{
    opacity: 1;
    transform: translateX(0);
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
        <li class="brand">
            <img src="Assets/logo.png"> <a href="index.php">
            B K's Burger House
            </a>
        </li>
        <div>
            <li><a href="index.php">Home</a></li>
            <li><a href="menu.php">Menu</a></li>
            <li><a href="cart.php">Cart</a></li>
            <li><a href="about.php">About</a></li>
            <li class="profile">
                    <img src="<?php echo $img; ?>" id="profileBtn">
                    <div class="dropdown" id="dropMenu">
                        <a href="account.php">Account</a><br>
                        <a href="wishlist.php">Wishlist</a><br>
                        <a href="logout.php">Logout</a>
                    </div>
            </li>
        </div>
    </ul>
</nav>

<div class="contact-title-box">
    <h1>Contact Us</h1>
</div>

<section class="py-5">
<div class="container">
    <div class="row text-center mb-5">

        <div class="col-md-4">
        <div class="info-box" style="color: black;">
            <div class="icon-circle">
                <i class="bi bi-geo-alt"></i>
            </div>
            <h5>Store Location</h5>
            <p>
            <a href="https://www.google.com/maps/place/Ajay's+Sudama+Chowk,+Surat/@21.2394624,72.8443551,5186m/data=!3m1!1e3!4m10!1m2!2m1!1sajay's+burger!3m6!1s0x3be04f838741b843:0x436df813b3079b08!8m2!3d21.239469!4d72.8803962!15sCg1hamF5J3MgYnVyZ2VyWg8iDWFqYXkncyBidXJnZXKSARRmYXN0X2Zvb2RfcmVzdGF1cmFudJoBI0NoWkRTVWhOTUc5blMwVkpRMEZuU1VOUE1TMTZWa3AzRUFF4AEA-gEECAAQSg!16s%2Fg%2F11nc9gy0jb?entry=ttu&g_ep=EgoyMDI2MDMxOC4xIKXMDSoASAFQAw%3D%3D" 
            target="_blank" 
            style="text-decoration:none;color:inherit;">
            <p>Ajay's Burger House,Sudama Chowk,Mota Varachha, Surat ,Gujarat</p>
            </a>
            </p>
        </div>
        </div>

        <div class="col-md-4">
        <div class="info-box" style="color: black;">
            <div class="icon-circle">
                <i class="bi bi-clock"></i>
            </div>
            <h5>Opening Hours</h5>
            <p>Mon - Fri : 10 AM - 10 PM</p>
            <p>Sat - Sun : 11 AM - 12 AM</p>
        </div>
        </div>

        <div class="col-md-4">
        <div class="info-box" style="color: black;">
            <div class="icon-circle">
                <i class="bi bi-telephone"></i>
            </div>
            <h5>Contact</h5>
            <p>
            <a href="mailto:burgerhousebk28@gmail.com" style="text-decoration:none; color:black;">
                burgerhousebk28@gmail.com
            </a>
            </p>
            <p>
            <a href="https://wa.me/919904522025" target="_blank" style="text-decoration:none; color:black;">
                💬 +91 99045 22025
            </a>
            </p>
        </div>
        </div>
    </div>
    <div class="col-lg-7 mx-auto">
            <div class="contact-form-wrapper" style="color: black;">
                <h3 class="mb-4">Send Message</h3>

                <?php if($success != "") { ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                    </div>
                <?php } ?>

                <form action="" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="name" placeholder="Your Name" required>
                        </div>

                        <div class="col-md-6">
                            <input type="email" class="form-control" name="email" placeholder="Your Email" required>
                        </div>

                        <div class="col-12">
                            <input type="text" class="form-control" name="subject" placeholder="Subject" required>
                        </div>

                        <div class="col-12">
                            <textarea class="form-control" name="message" rows="5" placeholder="Your Message" required></textarea>
                        </div>

                        <div class="col-12" style="color: black;">
                            <button type="submit" name="send" class="btn btn-warning w-100">
                                Send Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>
    </div>
</div>
</section>

<div class="col-lg-3 col-md-4">
    <div class="map-container">
        <iframe 
            src="https://www.google.com/maps?q=Mota+Varachha,Surat&z=13&output=embed"
            width="400%" 
            height="350" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy">
        </iframe>
    </div>
</div>
<br>
<br>
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

window.addEventListener("load", function() {
    let title = document.querySelector(".contact-title-box");

    if(title){
        setTimeout(() => {
            title.classList.add("show");
        }, 200); 
    }
});

window.addEventListener("scroll", function() {
    let form = document.querySelector(".contact-form-wrapper");
    let map = document.querySelector(".map-container");
    let screenHeight = window.innerHeight;
    if(form){
        let formPos = form.getBoundingClientRect().top;
        if(formPos < screenHeight - 100){
            form.classList.add("show");
        }
    }
    if(map){
        let mapPos = map.getBoundingClientRect().top;
        if(mapPos < screenHeight - 100){
            map.classList.add("show");
        }
    }
    let boxes = document.querySelectorAll(".info-box");
    boxes.forEach((box, index) => {
        let pos = box.getBoundingClientRect().top;

        if(pos < screenHeight - 100){
            setTimeout(() => {
                box.classList.add("show");
            }, index * 200); 
        }
    });
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