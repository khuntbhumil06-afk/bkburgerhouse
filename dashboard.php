<?php
session_start();
include "config.php";

$q1 = mysqli_query($conn, "SELECT * FROM orders");
$total_orders = $q1 ? mysqli_num_rows($q1) : 0;

$q2 = mysqli_query($conn, "SELECT * FROM loginform");
$total_users = $q2 ? mysqli_num_rows($q2) : 0;

$q3 = mysqli_query($conn, "SELECT * FROM stats WHERE id=1");
$row = $q3 ? mysqli_fetch_assoc($q3) : null;
$happy_customers = $row ? (int)$row['happy_customers'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0f0d0c;
            --card: #1a1613;
            --card2: #241d18;
            --ink: #fff7ec;
            --muted: #a39384;
            --red: #e63946;
            --red2: #9d0208;
            --gold: #ffb703;
            --line: #2f2620;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            color: var(--ink);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background:
                radial-gradient(circle at 8% -5%, rgba(230,57,70,.28), transparent 38%),
                radial-gradient(circle at 100% 50%, rgba(255,183,3,.12), transparent 40%),
                var(--bg);
            background-attachment: fixed;
            overflow-x: hidden;
        }

        /* ---------- HEADER ---------- */
        .header {
            background: #0b0b0b;
            font-family: 'Poppins', sans-serif;
            padding: 12px 45px;
            min-height: 64px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 700;
            z-index: 1001;
        }

        .brand img {
            width: 38px;
            height: 38px;
            object-fit: contain;
        }

        .menu {
            display: flex;
            align-items: center;
            gap: 28px;
        }

        .menu > a {
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 17px;
            text-decoration: none;
            transition: color .2s;
        }

        .menu > a:hover {
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
            object-fit: cover;
            background: var(--card2);
        }

        .dropdown {
            display: none;
            position: absolute;
            top: 52px;
            right: 0;
            background: var(--red);
            min-width: 150px;
            border-radius: 12px;
            overflow: hidden;
            z-index: 100;
            box-shadow: 0 10px 30px rgba(0,0,0,.5);
        }

        .dropdown a {
            display: block;
            padding: 11px 16px;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
        }

        .dropdown a:hover {
            background: var(--red2);
        }

        /* Hamburger */
        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            z-index: 1001;
            padding: 6px;
        }

        .hamburger span {
            display: block;
            width: 26px;
            height: 3px;
            background: #fff;
            border-radius: 2px;
            transition: .3s;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
        }
        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }
        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(6px, -6px);
        }

        /* ---------- MAIN ---------- */
        .main-container {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 44px 20px 70px;
            flex: 1;
        }

        .hero {
            margin-bottom: 34px;
        }

        .hi {
            color: var(--gold);
            font-weight: 800;
            font-size: 13px;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .main-container h1 {
            font-family: 'Bebas Neue', sans-serif;
            font-weight: 400;
            font-size: clamp(54px, 10vw, 96px);
            line-height: .9;
            letter-spacing: 2px;
            margin-top: 6px;
            color: var(--ink);
        }

        .main-container h1 span {
            color: transparent;
            -webkit-text-stroke: 2px var(--red);
        }

        /* ---------- CARDS ---------- */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(235px, 1fr));
            gap: 22px;
        }

        .card {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 220px;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 26px;
            padding: 24px 24px 22px;
            color: var(--ink);
            text-decoration: none;
            overflow: hidden;
            box-shadow: 0 14px 40px rgba(0,0,0,.45);
            transition: .3s;
        }

        .card::before {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(160deg, var(--red), var(--red2));
            opacity: .9;
            transition: .3s;
        }

        .card:hover {
            transform: translateY(-8px);
            border-color: var(--gold);
        }

        .card:hover::before {
            transform: scale(1.25);
        }

        .card .ico {
            position: relative;
            z-index: 1;
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: var(--card2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            box-shadow: inset 0 0 0 2px rgba(255,183,3,.4);
        }

        .card .ico img {
            width: 34px;
            height: 34px;
            object-fit: contain;
        }

        .card h2 {
            font-family: 'Bebas Neue', sans-serif;
            font-weight: 400;
            font-size: 72px;
            line-height: 1;
            letter-spacing: 1px;
            margin-top: 18px;
            color: var(--gold);
        }

        .card p {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--muted);
            margin-top: 4px;
        }

        .card .go {
            position: absolute;
            right: 22px;
            bottom: 20px;
            font-size: 22px;
            color: var(--gold);
            transition: .3s;
        }

        .card:hover .go {
            transform: translateX(6px);
        }

        .card.wallet h2 {
            font-size: 40px;
            margin-top: 18px;
            color: var(--ink);
            line-height: 1.05;
        }

        /* ---------- FOOTER ---------- */
        .site-footer {
            background: #111;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            padding: 48px 45px 56px;
            margin-top: auto;
        }

        .ft-wrap {
            display: grid;
            grid-template-columns: 1.3fr .9fr 1.45fr 1fr .95fr;
            gap: 34px;
            max-width: 1500px;
            margin: 0 auto;
            align-items: start;
        }

        .site-footer h3 { font-family: 'Poppins', sans-serif; font-size: 26px; font-weight: 500; margin-bottom: 14px; color: #fff; }
        .site-footer h5 { font-family: 'Poppins', sans-serif; font-size: 22px; font-weight: 500; margin-bottom: 14px; color: #fff; }
        .site-footer p { font-size: 15px; font-weight: 500; line-height: 1.65; color: #fff; margin-bottom: 14px; }
        .site-footer a { color: #fff; text-decoration: none; font-weight: 500; transition: color .2s; }
        .site-footer a:hover { color: #ff4d4d; }
        .site-footer ul { list-style: none; }
        .site-footer li { margin-bottom: 8px; font-size: 17px; }

        .ft-social { display: flex; gap: 14px; margin-top: 18px; }
        .ft-social a { font-size: 22px; line-height: 1; }

        .ft-loc iframe { display: block; width: 100%; height: 150px; border: 0; border-radius: 4px; margin-bottom: 18px; }
        .ft-loc p { margin-bottom: 12px; }

        .ft-sub input {
            display: block; width: 100%; height: 40px; padding: 8px 12px; margin-bottom: 14px;
            background: #fff; color: #222; border: 2px solid transparent; border-radius: 10px;
            font-family: 'Poppins', sans-serif; font-size: 14px; outline: none; transition: .2s;
        }
        .ft-sub input:focus { border-color: orange; }
        .ft-sub button {
            width: 100%; padding: 10px; border: none; border-radius: 10px; cursor: pointer;
            background: orange; color: #fff; font-family: 'Poppins', sans-serif; font-size: 15px; font-weight: 500; transition: .2s;
        }
        .ft-sub button:hover { background: darkorange; transform: scale(1.03); }
        .ft-sub button:active { transform: scale(.97); }
        .ft-sub .error { color: red; font-size: 13px; margin: -8px 0 8px; text-align: left; }

        /* ==================== RESPONSIVE ==================== */

        /* Tablets */
        @media (max-width: 1100px) {
            .ft-wrap {
                grid-template-columns: 1fr 1fr 1fr;
                gap: 28px;
            }
        }

        @media (max-width: 900px) {
            .header { padding: 12px 28px; }
            .main-container { padding: 36px 18px 60px; }
            .cards {
                grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
                gap: 18px;
            }
            .ft-wrap {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* Mobile landscape / large phones */
        @media (max-width: 768px) {
            .header {
                padding: 10px 18px;
            }

            .hamburger { display: flex; }

            .menu {
                position: fixed;
                top: 0;
                right: -100%;
                width: 280px;
                height: 100vh;
                background: #111;
                flex-direction: column;
                align-items: flex-start;
                padding: 90px 30px 40px;
                gap: 8px;
                transition: right .35s ease;
                box-shadow: -10px 0 30px rgba(0,0,0,.5);
                z-index: 999;
            }

            .menu.open { right: 0; }

            .menu > a {
                font-size: 18px;
                padding: 12px 0;
                width: 100%;
                border-bottom: 1px solid #222;
            }

            .profile {
                margin-top: 20px;
                width: 100%;
            }

            .profile img {
                width: 48px;
                height: 48px;
            }

            .dropdown {
                position: static;
                display: none;
                margin-top: 10px;
                width: 100%;
                border-radius: 10px;
            }

            .dropdown.show { display: block; }
            .dropdown a { padding: 12px 16px; }

            .main-container {
                padding: 28px 16px 50px;
            }

            .hero { margin-bottom: 26px; }

            .main-container h1 {
                font-size: clamp(48px, 14vw, 72px);
            }

            .cards {
                grid-template-columns: repeat(2, 1fr);
                gap: 14px;
            }

            .card {
                min-height: 180px;
                padding: 18px 16px 16px;
                border-radius: 20px;
            }

            .card:hover {
                transform: translateY(-4px);
            }

            .card::before {
                width: 110px;
                height: 110px;
                top: -40px;
                right: -40px;
            }

            .card .ico {
                width: 48px;
                height: 48px;
                font-size: 22px;
            }

            .card .ico img {
                width: 28px;
                height: 28px;
            }

            .card h2 {
                font-size: 48px;
                margin-top: 14px;
            }

            .card.wallet h2 {
                font-size: 26px;
            }

            .card p {
                font-size: 11px;
                letter-spacing: 1px;
            }

            .card .go {
                right: 16px;
                bottom: 14px;
                font-size: 18px;
            }

            .site-footer {
                padding: 40px 24px 48px;
            }
        }

        /* Small phones */
        @media (max-width: 480px) {
            .brand { font-size: 16px; gap: 8px; }
            .brand img { width: 32px; height: 32px; }

            .main-container {
                padding: 24px 14px 40px;
            }

            .hi {
                font-size: 11px;
                letter-spacing: 2.5px;
            }

            .main-container h1 {
                font-size: clamp(42px, 16vw, 58px);
                letter-spacing: 1px;
            }

            .cards {
                grid-template-columns: 1fr;
                gap: 14px;
            }

            .card {
                min-height: 160px;
                padding: 18px 18px 16px;
                border-radius: 18px;
            }

            .card:hover {
                transform: none;
            }

            .card h2 {
                font-size: 52px;
            }

            .card.wallet h2 {
                font-size: 28px;
            }

            .site-footer {
                padding: 36px 18px 40px;
            }

            .ft-wrap {
                grid-template-columns: 1fr;
                gap: 32px;
            }

            .site-footer h3 { font-size: 22px; }
            .site-footer h5 { font-size: 18px; }
            .site-footer p,
            .site-footer li { font-size: 15px; }
        }

        /* Extra small */
        @media (max-width: 360px) {
            .card h2 { font-size: 44px; }
            .card.wallet h2 { font-size: 24px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="index.php" class="brand">
            <img src="Assets/logo.png" alt="logo">
            <span>B K's Burger House</span>
        </a>

        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <div class="menu" id="navMenu">
            <a href="index.php">Home</a>
            <a href="sales.php">Sales</a>
            <a href="manage_menu.php">Manage Menu</a>
            <div class="profile">
                <img src="uploads/bk.png" id="profileBtn" alt="Profile" onerror="this.onerror=null;this.src='Assets/logo.png'">
                <div class="dropdown" id="dropMenu">
                    <a href="account.php">Account</a>
                    <a href="wishlist.php">Wishlist</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div class="main-container">
        <div class="hero">
            <p class="hi">Welcome back, admin</p>
            <h1>Admin <span>dashboard</span></h1>
        </div>

        <div class="cards">
            <a href="total_orders.php" class="card">
                <div class="ico">🧾</div>
                <div>
                    <h2><?php echo $total_orders; ?></h2>
                    <p>Total Orders</p>
                </div>
                <span class="go">→</span>
            </a>

            <a href="total_users.php" class="card">
                <div class="ico">👥</div>
                <div>
                    <h2><?php echo $total_users; ?></h2>
                    <p>Total Users</p>
                </div>
                <span class="go">→</span>
            </a>

            <a href="happy_customers.php" class="card">
                <div class="ico">😊</div>
                <div>
                    <h2><?php echo $happy_customers; ?></h2>
                    <p>Happy Customers</p>
                </div>
                <span class="go">→</span>
            </a>

            <a href="total_price.php" class="card wallet">
                <div class="ico"><img src="Assets/wallet.png" alt="wallet"></div>
                <div>
                    <h2>Total Earning Price</h2>
                    <p>View earnings</p>
                </div>
                <span class="go">→</span>
            </a>
        </div>
    </div>

    <footer class="site-footer">
        <div class="ft-wrap">
            <div>
                <h3><i class="bi bi-shop"></i> B K's Burger House</h3>
                <p>Serving the best burgers in town since 2010.</p>
                <div class="ft-social">
                    <a href="https://www.facebook.com"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.instagram.com"><i class="bi bi-instagram"></i></a>
                    <a href="https://www.twitter.com"><i class="bi bi-twitter"></i></a>
                    <a href="https://www.youtube.com"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

            <div>
                <h5>Quick Links</h5>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="menu.php">Menu</a></li>
                    <li><a href="cart.php">Cart</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>

            <div class="ft-loc">
                <h5>Location</h5>
                <iframe
                    src="https://maps.google.com/maps?q=MotaVarachha,Surat&output=embed"
                    allowfullscreen=""
                    loading="lazy"
                    title="Map">
                </iframe>
                <p>
                    <a href="https://www.google.com/maps/place/Ajay's+Sudama+Chowk,+Surat/@21.2394624,72.8443551,5186m/data=!3m1!1e3!4m10!1m2!2m1!1sajay's+burger!3m6!1s0x3be04f838741b843:0x436df813b3079b08!8m2!3d21.239469!4d72.8803962!15sCg1hamF5J3MgYnVyZ2VyWg8iDWFqYXkncyBidXJnZXKSARRmYXN0X2Zvb2RfcmVzdGF1cmFudJoBI0NoWkRTVWhOTUc5blMwVkpRMEZuU1VOUE1TMTZWa3AzRUFF4AEA-gEECAAQSg!16s%2Fg%2F11nc9gy0jb?entry=ttu&g_ep=EgoyMDI2MDMxOC4xIKXMDSoASAFQAw%3D%3D"
                       target="_blank">
                        Ajay's Burger House, Sudama Chowk, Mota Varachha, Surat, Gujarat
                    </a>
                </p>
                <p><i class="bi bi-telephone"></i> +91 9904522025</p>
                <p><i class="bi bi-envelope"></i> bk@burgerhouse.com</p>
            </div>

            <div>
                <h5>Opening Hours</h5>
                <p>Mon - Fri: 10 AM - 10 PM</p>
                <p>Sat - Sun: 11 AM - 12 AM</p>
            </div>

            <div class="ft-sub">
                <h5>Newsletter</h5>
                <form action="newsletter.php" method="POST" onsubmit="return validateEmail()">
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
    </footer>

    <script>
        /* Hamburger menu */
        const hamburger = document.getElementById("hamburger");
        const navMenu = document.getElementById("navMenu");

        hamburger.addEventListener("click", function () {
            hamburger.classList.toggle("active");
            navMenu.classList.toggle("open");
        });

        navMenu.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                hamburger.classList.remove("active");
                navMenu.classList.remove("open");
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
            const btn = document.getElementById("profileBtn");
            const menu = document.getElementById("dropMenu");

            if (btn) {
                btn.addEventListener("click", function (e) {
                    e.stopPropagation();
                    if (window.innerWidth <= 768) {
                        menu.classList.toggle("show");
                    } else {
                        menu.style.display = menu.style.display === "block" ? "none" : "block";
                    }
                });
            }

            document.addEventListener("click", function () {
                if (menu) {
                    menu.style.display = "none";
                    menu.classList.remove("show");
                }
            });
        });

        function goToSuccess() {
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