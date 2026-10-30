<?php
session_start();
require_once "config.php";

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Category -> image folder (menu.php ane manage_menu.php jevu j)
$categoryFolders = [
    'Veg Burger'     => 'vegburger',
    'Non-Veg Burger' => 'non-vegburger',
    'Combo'          => 'combo',
    'Drink'          => 'drink',
];

$imageExtensions = ["png", "jpg", "jpeg", "webp"];

// Item image shodho: premium folder, pachhi category folder, nahi to logo
function itemImage($name, $folder, $extensions) {
    $dirs = ["Assets/Store/premium", "Assets/Store/" . $folder];
    foreach ($dirs as $dir) {
        foreach ($extensions as $ext) {
            $path = $dir . "/" . $name . "." . $ext;
            if (is_file(__DIR__ . "/" . $path)) {
                return implode("/", array_map('rawurlencode', explode("/", $path)));
            }
        }
    }
    return "Assets/logo.png";
}

// Database na badha items: naam ni key sathe price ane image
$products = [];
$res = mysqli_query($conn, "SELECT name, price, category FROM products");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $cat    = isset($categoryFolders[$row['category']]) ? $row['category'] : 'Drink';
        $folder = $categoryFolders[$cat];

        $products[$row['name']] = [
            'price' => (float)$row['price'],
            'img'   => itemImage($row['name'], $folder, $imageExtensions),
        ];
    }
}

$img = "uploads/default.png";
if (isset($_SESSION['username'])) {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Wishlist</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    color: var(--ink);
    min-height: 100vh;
    background:
        radial-gradient(circle at 8% -5%, rgba(230,57,70,.28), transparent 38%),
        radial-gradient(circle at 100% 100%, rgba(255,183,3,.14), transparent 40%),
        var(--bg);
    background-attachment: fixed;
    overflow-x: hidden;
}
a { color: inherit; }
.wrap { max-width: 1100px; margin: 0 auto; padding: 0 20px; }

/* ---------- NAV ---------- */
.nav {
    background: #0b0b0b;
    font-family: 'Poppins', sans-serif;
    position: sticky;
    top: 0;
    z-index: 1000;
}
.nav .wrap {
    max-width: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 12px 45px;
    min-height: 64px;
}
.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: #fff;
    font-family: 'Poppins', sans-serif;
    font-size: 20px;
    font-weight: 700;
    z-index: 1001;
}
.brand img { width: 38px; height: 38px; object-fit: contain; }
.links {
    display: flex;
    align-items: center;
    gap: 28px;
}
.links > a {
    text-decoration: none;
    color: #fff;
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
    font-size: 17px;
    transition: color .2s;
}
.links > a:hover { color: #ff4d4d; }
.profile { position: relative; margin-left: 4px; }
.profile img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    cursor: pointer;
    display: block;
}
.dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: 52px;
    min-width: 150px;
    z-index: 200;
    background: var(--red);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,.5);
}
.dropdown a {
    display: block;
    padding: 11px 16px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    color: #fff;
}
.dropdown a:hover { background: var(--red2); }

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

/* ---------- HERO ---------- */
.hero {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    padding: 44px 0 28px;
}
.hi {
    color: var(--gold);
    font-weight: 800;
    font-size: 13px;
    letter-spacing: 3px;
    text-transform: uppercase;
}
.hero h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-weight: 400;
    font-size: clamp(54px, 10vw, 96px);
    line-height: .9;
    letter-spacing: 2px;
    margin-top: 6px;
}
.hero h1 span {
    color: transparent;
    -webkit-text-stroke: 2px var(--red);
}
.pill-link {
    text-decoration: none;
    cursor: pointer;
    font-family: inherit;
    font-weight: 800;
    font-size: 14px;
    color: var(--gold);
    background: transparent;
    border: 2px solid var(--gold);
    padding: 10px 22px;
    border-radius: 999px;
    transition: .2s;
}
.pill-link:hover {
    background: var(--gold);
    color: #000;
}

/* ---------- GRID ---------- */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
    gap: 22px;
    padding-bottom: 90px;
}
.item {
    position: relative;
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: 24px;
    padding: 12px 12px 16px;
    text-align: center;
    box-shadow: 0 12px 30px rgba(0,0,0,.45);
    transition: .3s;
}
.item:hover {
    transform: translateY(-8px);
    border-color: var(--gold);
}
.item.removing {
    opacity: 0;
    transform: scale(.85);
}
.pic {
    position: relative;
    height: 190px;
    border-radius: 18px;
    overflow: hidden;
    background: var(--card2);
}
.pic img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: .4s;
}
.item:hover .pic img {
    transform: scale(1.08);
}
.heart {
    position: absolute;
    top: 10px;
    left: 10px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(0,0,0,.65);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
.price {
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--gold);
    color: #000;
    font-weight: 800;
    font-size: 14px;
    padding: 5px 12px;
    border-radius: 999px;
}
.item h3 {
    font-family: 'Bebas Neue', sans-serif;
    font-weight: 400;
    font-size: 27px;
    letter-spacing: 1.2px;
    line-height: 1.1;
    margin: 14px 4px 14px;
    word-break: break-word;
}
.actions {
    display: grid;
    gap: 8px;
    padding: 0 6px;
}
.btn {
    border: none;
    cursor: pointer;
    font-family: inherit;
    font-weight: 800;
    font-size: 14px;
    padding: 11px 14px;
    border-radius: 999px;
    transition: .15s;
}
.btn.add {
    background: var(--gold);
    color: #000;
    box-shadow: 0 4px 0 #b37f00;
}
.btn.add:hover { background: #ffc933; }
.btn.add:active {
    transform: translateY(3px);
    box-shadow: 0 1px 0 #b37f00;
}
.btn.rm {
    background: transparent;
    color: var(--muted);
    border: 2px solid var(--line);
}
.btn.rm:hover {
    color: #fff;
    background: var(--red);
    border-color: var(--red);
}

/* ---------- EMPTY ---------- */
.empty {
    grid-column: 1 / -1;
    text-align: center;
    background: var(--card);
    border: 2px dashed #3a2f28;
    border-radius: 26px;
    padding: 70px 20px;
}
.empty .big { font-size: 66px; }
.empty h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-weight: 400;
    font-size: 46px;
    letter-spacing: 1.5px;
    margin-top: 6px;
}
.empty p {
    color: var(--muted);
    margin: 6px 0 24px;
    font-size: 15px;
}
.empty .btn {
    display: inline-block;
    text-decoration: none;
    padding: 13px 30px;
    background: var(--red);
    color: #fff;
    box-shadow: 0 4px 0 var(--red2);
}

/* ---------- TOAST ---------- */
.toast {
    position: fixed;
    left: 50%;
    bottom: 28px;
    z-index: 3000;
    transform: translate(-50%, 30px);
    opacity: 0;
    pointer-events: none;
    background: #fff3d6;
    color: #2b2118;
    font-weight: 700;
    font-size: 14px;
    padding: 13px 22px;
    border-radius: 999px;
    box-shadow: 0 12px 30px rgba(0,0,0,.55);
    transition: .3s;
    max-width: 90%;
    text-align: center;
}
.toast.show {
    opacity: 1;
    transform: translate(-50%, 0);
}

/* ==================== RESPONSIVE ==================== */

/* Tablets */
@media (max-width: 900px) {
    .nav .wrap { padding: 12px 28px; }
    .grid {
        grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
        gap: 18px;
    }
    .hero { padding: 36px 0 24px; }
}

/* Mobile landscape / large phones */
@media (max-width: 768px) {
    .nav .wrap {
        padding: 10px 18px;
        position: relative;
    }
    .hamburger { display: flex; }
    .links {
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
    .links.open { right: 0; }
    .links > a {
        font-size: 18px;
        padding: 12px 0;
        width: 100%;
        border-bottom: 1px solid #222;
    }
    .profile {
        margin: 20px 0 0;
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

    .hero {
        flex-direction: column;
        align-items: flex-start;
        gap: 18px;
        padding: 32px 0 22px;
    }
    .hero h1 {
        font-size: clamp(48px, 14vw, 72px);
    }
    .pill-link {
        padding: 10px 20px;
        font-size: 13px;
    }

    .grid {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 16px;
        padding-bottom: 70px;
    }
    .item {
        border-radius: 18px;
        padding: 10px 10px 14px;
    }
    .item:hover { transform: translateY(-4px); }
    .pic { height: 150px; border-radius: 14px; }
    .heart {
        width: 32px;
        height: 32px;
        font-size: 16px;
        top: 8px;
        left: 8px;
    }
    .price {
        font-size: 13px;
        padding: 4px 10px;
        top: 8px;
        right: 8px;
    }
    .item h3 {
        font-size: 22px;
        margin: 12px 2px 12px;
    }
    .btn {
        font-size: 13px;
        padding: 10px 12px;
    }

    .empty {
        padding: 50px 18px;
        border-radius: 20px;
    }
    .empty .big { font-size: 54px; }
    .empty h2 { font-size: 36px; }
    .empty p { font-size: 14px; }
}

/* Small phones */
@media (max-width: 480px) {
    .brand { font-size: 16px; gap: 8px; }
    .brand img { width: 32px; height: 32px; }

    .hero { padding: 28px 0 18px; }
    .hi { font-size: 11px; letter-spacing: 2.5px; }
    .hero h1 {
        font-size: clamp(42px, 16vw, 58px);
        letter-spacing: 1px;
    }
    .pill-link {
        width: 100%;
        text-align: center;
        padding: 11px;
    }

    .grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        padding-bottom: 60px;
    }
    .item {
        border-radius: 16px;
        padding: 8px 8px 12px;
    }
    .item:hover { transform: none; }
    .pic { height: 130px; border-radius: 12px; }
    .heart {
        width: 28px;
        height: 28px;
        font-size: 14px;
        top: 6px;
        left: 6px;
    }
    .price {
        font-size: 12px;
        padding: 3px 8px;
        top: 6px;
        right: 6px;
    }
    .item h3 {
        font-size: 18px;
        margin: 10px 2px 10px;
        letter-spacing: 0.5px;
    }
    .actions { gap: 6px; padding: 0 4px; }
    .btn {
        font-size: 12px;
        padding: 9px 10px;
    }

    .empty {
        padding: 40px 16px;
        border-radius: 18px;
    }
    .empty .big { font-size: 48px; }
    .empty h2 { font-size: 30px; }
    .empty p { font-size: 13px; margin-bottom: 20px; }
    .empty .btn {
        padding: 12px 24px;
        font-size: 14px;
    }

    .toast {
        bottom: 20px;
        font-size: 13px;
        padding: 12px 18px;
        max-width: 92%;
    }
}

/* Extra small phones */
@media (max-width: 360px) {
    .grid {
        grid-template-columns: 1fr;
        max-width: 280px;
        margin: 0 auto;
    }
    .item h3 { font-size: 20px; }
    .pic { height: 160px; }
}
</style>
</head>
<body>

<!-- ---------- NAV ---------- -->
<nav class="nav">
    <div class="wrap">
        <a class="brand" href="index.php">
            <img src="Assets/logo.png" alt="logo">
            <span>B K's Burger House</span>
        </a>

        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <div class="links" id="navLinks">
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="cart.php">Cart</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
            <div class="profile">
                <img src="<?= e($img) ?>" id="profileBtn" alt="Profile">
                <div class="dropdown" id="dropMenu">
                    <a href="account.php">Account</a>
                    <a href="wishlist.php">Wishlist</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>
</nav>

<main class="wrap">
    <header class="hero">
        <div>
            <p class="hi" id="wishCount">Your favourites</p>
            <h1>My <span>wishlist</span></h1>
        </div>
        <button class="pill-link" onclick="goBack()">← Back to Menu</button>
    </header>

    <div class="grid" id="wishlistItems"></div>
</main>

<div class="toast" id="toast"></div>

<script>
// Database mathi aavel products (admin ma badlav karo to aa j badlashe)
const products = <?= json_encode($products, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

/* Hamburger menu */
const hamburger = document.getElementById("hamburger");
const navLinks = document.getElementById("navLinks");

hamburger.addEventListener("click", function () {
    hamburger.classList.toggle("active");
    navLinks.classList.toggle("open");
});

/* Close menu when clicking a link */
navLinks.querySelectorAll("a").forEach(function (link) {
    link.addEventListener("click", function () {
        hamburger.classList.remove("active");
        navLinks.classList.remove("open");
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
        menu.style.display = "none";
        menu.classList.remove("show");
    });

    renderWishlist();
});

function getWishlist() {
    return JSON.parse(localStorage.getItem("wishlist")) || [];
}

function showToast(text) {
    const t = document.getElementById("toast");
    t.textContent = text;
    t.classList.add("show");
    clearTimeout(showToast.timer);
    showToast.timer = setTimeout(function () { t.classList.remove("show"); }, 2200);
}

function el(tag, cls, text) {
    const n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined) n.textContent = text;
    return n;
}

function renderWishlist() {
    // Jo item DB mathi delete thai gayo hoy, to card batavvo nahi
    const wishlist = getWishlist().filter(function (item) { return products[item]; });
    const container = document.getElementById("wishlistItems");
    container.textContent = "";

    document.getElementById("wishCount").textContent =
        wishlist.length + " saved item" + (wishlist.length === 1 ? "" : "s");

    if (wishlist.length === 0) {
        const box = el("div", "empty");
        box.appendChild(el("div", "big", "💔"));
        box.appendChild(el("h2", "", "Your wishlist is empty"));
        box.appendChild(el("p", "", "Tap the ❤️ Wishlist button on any burger in the menu to save it here."));
        const a = el("a", "btn", "Browse the menu");
        a.href = "menu.php";
        box.appendChild(a);
        container.appendChild(box);
        return;
    }

    wishlist.forEach(function (item) {
        const card = el("div", "item");

        const pic = el("div", "pic");
        const im = document.createElement("img");
        im.src = products[item].img;
        im.alt = item;
        pic.appendChild(im);
        pic.appendChild(el("span", "heart", "❤️"));
        pic.appendChild(el("span", "price", "₹" + String(+products[item].price)));
        card.appendChild(pic);

        card.appendChild(el("h3", "", item));

        const actions = el("div", "actions");
        const add = el("button", "btn add", "🛒 Add to Cart");
        add.type = "button";
        add.addEventListener("click", function () { addToCart(item); });
        const rm = el("button", "btn rm", "Remove");
        rm.type = "button";
        rm.addEventListener("click", function () { removeItem(item, card); });
        actions.appendChild(add);
        actions.appendChild(rm);
        card.appendChild(actions);

        container.appendChild(card);
    });
}

function removeItem(item, card) {
    const wishlist = getWishlist().filter(function (i) { return i !== item; });
    localStorage.setItem("wishlist", JSON.stringify(wishlist));
    card.classList.add("removing");
    setTimeout(renderWishlist, 250);
}

function addToCart(item) {
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    cart.push(item);
    localStorage.setItem("cart", JSON.stringify(cart));
    showToast(item + " added to Cart 🛒");
}

function goBack() {
    window.location.href = "menu.php";
}
</script>

</body>
</html>