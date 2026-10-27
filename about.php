<?php
session_start();

function h($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

$img = "uploads/default.png";

if (isset($_SESSION['username'])) {
    include 'config.php';
    $username = $_SESSION['username'];

    $stmt = mysqli_prepare($conn, "SELECT image FROM loginform WHERE username=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $user_img);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if (!empty($user_img)) {
            $img = "uploads/" . $user_img;
        }
    }
}

$mapLink = "https://www.google.com/maps/place/Ajay's+Sudama+Chowk,+Surat/@21.2394624,72.8443551,5186m/data=!3m1!1e3!4m10!1m2!2m1!1sajay's+burger!3m6!1s0x3be04f838741b843:0x436df813b3079b08!8m2!3d21.239469!4d72.8803962!15sCg1hamF5J3MgYnVyZ2VyWg8iDWFqYXkncyBidXJnZXKSARRmYXN0X2Zvb2RfcmVzdGF1cmFudJoBI0NoWkRTVWhOTUc5blMwVkpRMEZuU1VOUE1TMTZWa3AzRUFF4AEA-gEECAAQSg!16s%2Fg%2F11nc9gy0jb?entry=ttu&g_ep=EgoyMDI2MDMxOC4xIKXMDSoASAFQAw%3D%3D";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About Us - Burger House</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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
    --paper: #fff3d6;
    --paper-ink: #2b2118;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body {
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    color: var(--ink);
    background:
        radial-gradient(circle at 8% -5%, rgba(230,57,70,.28), transparent 38%),
        radial-gradient(circle at 100% 45%, rgba(255,183,3,.12), transparent 40%),
        var(--bg);
    background-attachment: fixed;
    overflow-x: hidden;
}
a { color: inherit; }
.wrap { max-width: 1100px; margin: 0 auto; padding: 0 20px; }

/* ---------- NAV (index.php jevo header) ---------- */
.nav { background: #0b0b0b; font-family: 'Poppins', sans-serif; }
.nav .wrap {
    max-width: none;
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    padding: 12px 45px; min-height: 64px; flex-wrap: wrap;
}
.brand {
    display: flex; align-items: center; gap: 12px; text-decoration: none; color: #fff;
    font-family: 'Poppins', sans-serif; font-size: 20px; font-weight: 700;
}
.brand img { width: 38px; height: 38px; object-fit: contain; }
.links { display: flex; align-items: center; gap: 28px; flex-wrap: wrap; }
.links > a {
    text-decoration: none; color: #fff; font-family: 'Poppins', sans-serif;
    font-weight: 500; font-size: 17px; transition: color .2s;
}
.links > a:hover { color: #ff4d4d; }
.profile { position: relative; margin-left: 4px; }
.profile img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; cursor: pointer; display: block; }
.dropdown {
    display: none; position: absolute; right: 0; top: 52px; min-width: 150px; z-index: 200;
    background: var(--red); border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,.5);
}
.dropdown a { display: block; padding: 11px 16px; text-decoration: none; font-size: 14px; font-weight: 600; font-family: 'Poppins', sans-serif; }
.dropdown a:hover { background: var(--red2); }
@media (max-width: 700px) {
    .nav .wrap { padding: 10px 16px; }
    .links { gap: 16px; }
    .links > a { font-size: 15px; }
    .brand { font-size: 18px; }
}

/* ---------- BUTTONS ---------- */
.btn {
    display: inline-flex; align-items: center; gap: 8px; text-decoration: none; cursor: pointer;
    font-family: inherit; font-weight: 800; font-size: 14px; border: none;
    padding: 13px 28px; border-radius: 999px; transition: .15s;
}
.btn.gold { background: var(--gold); color: #000; box-shadow: 0 4px 0 #b37f00; }
.btn.gold:active { transform: translateY(3px); box-shadow: 0 1px 0 #b37f00; }
.btn.gold:hover { background: #ffc933; }
.btn.ghost { border: 2px solid var(--gold); color: var(--gold); background: transparent; }
.btn.ghost:hover { background: var(--gold); color: #000; }

/* ---------- HERO / STORY ---------- */
.story { display: grid; grid-template-columns: 1.05fr 1fr; gap: 56px; align-items: center; padding: 70px 0 70px; }
.hi { color: var(--gold); font-weight: 800; font-size: 13px; letter-spacing: 4px; text-transform: uppercase; }
.story h1 {
    font-family: 'Bebas Neue', sans-serif; font-weight: 400;
    font-size: clamp(50px, 8vw, 92px); line-height: .92; letter-spacing: 2px; margin: 8px 0 20px;
}
.story h1 span { color: transparent; -webkit-text-stroke: 2px var(--red); }
.story p { color: #d8ccbf; line-height: 1.75; margin-bottom: 14px; font-size: 15px; }
.ticks { list-style: none; display: grid; gap: 10px; margin: 20px 0 28px; }
.ticks li {
    display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 15px;
    background: var(--card); border: 1px solid var(--line); border-radius: 14px; padding: 11px 16px;
}
.ticks li i { color: var(--gold); font-size: 18px; }
.cta-row { display: flex; gap: 12px; flex-wrap: wrap; }

.photo { position: relative; padding: 0 22px 26px 0; }
.photo .frame {
    position: relative; border-radius: 30px; overflow: hidden;
    border: 4px solid var(--gold); box-shadow: 18px 18px 0 var(--red), 0 25px 60px rgba(0,0,0,.6);
    transform: rotate(-2deg); transition: .4s;
}
.photo .frame:hover { transform: rotate(0) scale(1.02); }
.photo .frame img { display: block; width: 100%; height: 100%; object-fit: cover; }
.badge {
    position: absolute; right: -6px; bottom: -4px; z-index: 2;
    width: 132px; height: 132px; border-radius: 50%;
    background: var(--gold); color: #000; text-align: center;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    border: 5px dashed #000; box-shadow: 0 10px 25px rgba(0,0,0,.55);
    animation: spin-pop 5s ease-in-out infinite;
}
.badge b { font-family: 'Bebas Neue', sans-serif; font-weight: 400; font-size: 52px; line-height: 1; }
.badge span { font-size: 12px; font-weight: 800; line-height: 1.15; text-transform: uppercase; letter-spacing: 1px; }
@keyframes spin-pop {
    0%, 100% { transform: rotate(-8deg) scale(1); }
    50%      { transform: rotate(8deg) scale(1.08); }
}

/* ---------- MARQUEE ---------- */
.marquee { background: var(--red); transform: rotate(-1.2deg); margin: 0 -20px; overflow: hidden; border-block: 3px solid #000; }
.marquee div {
    display: inline-block; white-space: nowrap; padding: 10px 0;
    font-family: 'Bebas Neue', sans-serif; font-size: 24px; letter-spacing: 3px;
    animation: slide 24s linear infinite;
}
@keyframes slide { to { transform: translateX(-50%); } }

/* ---------- HOW WE MAKE IT ---------- */
.sec-head { text-align: center; padding: 80px 0 34px; }
.sec-head h2 { font-family: 'Bebas Neue', sans-serif; font-weight: 400; font-size: clamp(40px, 7vw, 70px); letter-spacing: 2px; line-height: 1; margin-top: 6px; }
.sec-head h2 span { color: transparent; -webkit-text-stroke: 2px var(--gold); }
.steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
.step {
    position: relative; background: var(--card); border: 1px solid var(--line);
    border-radius: 24px; padding: 28px 22px 24px; overflow: hidden; transition: .3s;
}
.step:hover { transform: translateY(-8px); border-color: var(--gold); }
.step .no {
    position: absolute; top: 6px; right: 14px;
    font-family: 'Bebas Neue', sans-serif; font-size: 80px; line-height: 1;
    color: transparent; -webkit-text-stroke: 1.5px rgba(255,183,3,.35);
}
.step .emo {
    width: 62px; height: 62px; border-radius: 50%; background: var(--card2);
    display: flex; align-items: center; justify-content: center; font-size: 30px; margin-bottom: 16px;
    box-shadow: inset 0 0 0 2px rgba(255,183,3,.4);
}
.step h3 { font-family: 'Bebas Neue', sans-serif; font-weight: 400; font-size: 26px; letter-spacing: 1.2px; margin-bottom: 6px; }
.step p { font-size: 14px; color: var(--muted); line-height: 1.6; }

/* ---------- CTA TICKET ---------- */
.ticket-sec { padding: 80px 0; }
.ticket {
    position: relative; display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap;
    background: var(--paper); color: var(--paper-ink); border-radius: 26px; padding: 38px 44px;
    box-shadow: 0 20px 50px rgba(0,0,0,.55);
}
.ticket::before, .ticket::after {
    content: ""; position: absolute; top: 50%; width: 36px; height: 36px; margin-top: -18px; border-radius: 50%; background: var(--bg);
}
.ticket::before { left: -18px; }
.ticket::after  { right: -18px; }
.ticket .tag { font-size: 11px; letter-spacing: 3px; font-weight: 800; color: var(--red); }
.ticket h2 { font-family: 'Bebas Neue', sans-serif; font-weight: 400; font-size: clamp(36px, 6vw, 58px); letter-spacing: 1.5px; line-height: 1; margin-top: 4px; }
.ticket p { color: #7a6a4f; margin-top: 6px; font-size: 14px; }
.ticket .btn { background: var(--red); color: #fff; box-shadow: 0 5px 0 var(--red2); font-size: 16px; padding: 16px 34px; text-transform: uppercase; letter-spacing: 1px; }
.ticket .btn:active { transform: translateY(4px); box-shadow: 0 1px 0 var(--red2); }

/* ---------- FOOTER (index.php jevo) ---------- */
.site-footer {
    background: #111; color: #fff; font-family: 'Poppins', sans-serif;
    padding: 48px 45px 56px; margin-top: 0;
}
.ft-wrap {
    display: grid; grid-template-columns: 1.3fr .9fr 1.45fr 1fr .95fr; gap: 34px;
    max-width: 1500px; margin: 0 auto; align-items: start;
}
.site-footer h3 { font-family: 'Poppins', sans-serif; font-size: 26px; font-weight: 500; letter-spacing: 0; margin-bottom: 14px; color: #fff; }
.site-footer h5 { font-family: 'Poppins', sans-serif; font-size: 22px; font-weight: 500; letter-spacing: 0; margin-bottom: 14px; color: #fff; }
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
@media (max-width: 1000px) {
    .ft-wrap { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 560px) {
    .site-footer { padding: 36px 20px 40px; }
    .ft-wrap { grid-template-columns: 1fr; }
}

/* ---------- REVEAL ---------- */
.reveal { opacity: 0; transform: translateY(40px); transition: .7s ease; }
.reveal.show { opacity: 1; transform: none; }

@media (max-width: 900px) {
    .story { grid-template-columns: 1fr; gap: 40px; padding-top: 50px; }
    .steps { grid-template-columns: repeat(2, 1fr); }
    .fgrid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 560px) {
    .steps { grid-template-columns: 1fr; }
    .fgrid { grid-template-columns: 1fr; }
    .ticket { padding: 30px 24px; }
    .photo .frame { box-shadow: 10px 10px 0 var(--red), 0 25px 60px rgba(0,0,0,.6); }
    .badge { width: 104px; height: 104px; }
    .badge b { font-size: 40px; }
}
</style>
</head>
<body>

<!-- ---------- NAV ---------- -->
<nav class="nav">
    <div class="wrap">
        <a class="brand" href="index.php"><img src="Assets/logo.png" alt="logo"> <span>B K's Burger House</span></a>
        <div class="links">
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="cart.php">Cart</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
            <div class="profile">
                <img src="<?= h($img) ?>" id="profileBtn" alt="Profile">
                <div class="dropdown" id="dropMenu">
                    <a href="account.php">Account</a>
                    <a href="wishlist.php">Wishlist</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- ---------- STORY ---------- -->
<main class="wrap story">
    <section class="reveal">
        <p class="hi">About us</p>
        <h1>We make the best <span>burgers</span> in the city</h1>
        <p>At Burger House, we believe that a great burger starts with great ingredients. That's why we source only the freshest, highest-quality ingredients for our burgers.</p>
        <p>Our patties are made from 100% premium beef, our vegetables are locally sourced, and our buns are baked fresh daily. Every burger is handcrafted with love and attention to detail.</p>

        <ul class="ticks">
            <li><i class="bi bi-check-circle-fill"></i> 100% Premium Beef</li>
            <li><i class="bi bi-check-circle-fill"></i> Fresh Local Vegetables</li>
            <li><i class="bi bi-check-circle-fill"></i> Handcrafted Daily</li>
        </ul>

        <div class="cta-row">
            <a href="menu.php" onclick="Menu(); return false;" class="btn gold"><i class="bi bi-egg-fried"></i> Explore Our Menu</a>
            <a href="contact.php" class="btn ghost">Contact us</a>
        </div>
    </section>

    <section class="photo reveal">
        <div class="frame">
            <img src="Assets/burgerking.png" alt="About Burger House">
        </div>
        <div class="badge">
            <b>15+</b>
            <span>Years of<br>Experience</span>
        </div>
    </section>
</main>

<div class="marquee"><div>
    <?php for ($i = 0; $i < 12; $i++): ?>🍔 PREMIUM BEEF &nbsp; ✦ &nbsp; FRESH LOCAL VEGGIES &nbsp; ✦ &nbsp; HANDCRAFTED DAILY &nbsp; ✦ &nbsp;<?php endfor; ?>
</div></div>

<!-- ---------- HOW WE MAKE IT ---------- -->
<section class="wrap">
    <div class="sec-head reveal">
        <p class="hi">Our recipe</p>
        <h2>How we make <span>every burger</span></h2>
    </div>

    <div class="steps">
        <div class="step reveal">
            <span class="no">01</span>
            <div class="emo">🥖</div>
            <h3>Fresh Buns</h3>
            <p>Our buns are baked fresh every single day, soft and golden.</p>
        </div>
        <div class="step reveal">
            <span class="no">02</span>
            <div class="emo">🥩</div>
            <h3>Premium Patties</h3>
            <p>Made from 100% premium beef and grilled to juicy perfection.</p>
        </div>
        <div class="step reveal">
            <span class="no">03</span>
            <div class="emo">🥬</div>
            <h3>Local Veggies</h3>
            <p>Crisp vegetables sourced locally, so every bite tastes fresh.</p>
        </div>
        <div class="step reveal">
            <span class="no">04</span>
            <div class="emo">❤️</div>
            <h3>Made With Love</h3>
            <p>Every burger is handcrafted with love and attention to detail.</p>
        </div>
    </div>
</section>

<!-- ---------- CTA TICKET ---------- -->
<section class="wrap ticket-sec">
    <div class="ticket reveal">
        <div>
            <span class="tag">YOUR TABLE IS READY</span>
            <h2>Hungry already?</h2>
            <p>Pick your burger, customise your combo and we will do the rest.</p>
        </div>
        <a href="menu.php" onclick="Menu(); return false;" class="btn">Order now 🍔</a>
    </div>
</section>

<!-- ---------- FOOTER ---------- -->
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
            <iframe src="https://maps.google.com/maps?q=MotaVarachha,Surat&output=embed" allowfullscreen loading="lazy" title="Map"></iframe>
            <p><a href="<?= h($mapLink) ?>" target="_blank">Ajay's Burger House,Sudama Chowk,Mota Varachha, Surat ,Gujarat</a></p>
            <p><a href="mailto:burgerhousebk28@gmail.com"><i class="bi bi-envelope-fill"></i> burgerhousebk28@gmail.com</a></p>
            <p><a href="https://wa.me/919904522025" target="_blank">💬 +91 99045 22025</a></p>
        </div>

        <div>
            <h5>Opening Hours</h5>
            <p>Mon - Fri: 10 AM - 10 PM</p>
            <p>Sat - Sun: 11 AM - 12 AM</p>
        </div>

        <div class="ft-sub">
            <h5>NewSubscribe</h5>
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

<script src="js/main.js"></script>
<script>
/* Profile dropdown */
document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("profileBtn");
    const menu = document.getElementById("dropMenu");
    if (btn) {
        btn.addEventListener("click", function (e) {
            e.stopPropagation();
            menu.style.display = menu.style.display === "block" ? "none" : "block";
        });
        document.addEventListener("click", function () { menu.style.display = "none"; });
    }
});

/* Scroll reveal */
const io = new IntersectionObserver(function (entries) {
    entries.forEach(function (en, i) {
        if (en.isIntersecting) {
            setTimeout(function () { en.target.classList.add("show"); }, i * 120);
            io.unobserve(en.target);
        }
    });
}, { threshold: 0.12 });
document.querySelectorAll(".reveal").forEach(function (el) { io.observe(el); });

/* Menu button: login nathi to login page par */
function Menu() {
    <?php if (!isset($_SESSION['admin']) || $_SESSION['admin'] == "NIL"): ?>
        window.location.href = "login.php";
    <?php else: ?>
        window.location.href = "menu.php";
    <?php endif; ?>
}

/* Newsletter */
function goToSuccess() {
    sessionStorage.setItem("playSound", "true");
}

function validateEmail() {
    const name = document.getElementById("name").value;
    const email = document.getElementById("email").value;
    const city = document.getElementById("city").value;

    const nameError = document.getElementById("nameError");
    const emailError = document.getElementById("emailError");
    const cityError = document.getElementById("cityError");

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
    } else if (!/^[^ ]+@[^ ]+\.[a-z]{2,3}$/.test(email)) {
        emailError.innerText = "⚠ Invalid email format!";
        isValid = false;
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