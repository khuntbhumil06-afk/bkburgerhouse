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

<style>
body {
    font-family: Arial, sans-serif;
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                url('Assets/images.png') no-repeat center center fixed;
    background-size: cover;
    color: white;
    margin: 0;
}

.nav-container {
    background: black;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 40px;
    position: sticky;
    top: 0;
    z-index: 1000;
}
.brand {
    color: #fff;
    font-size: 22px;
    font-weight: 600;
    display: flex;
    align-items: center;
    text-decoration: none;
}
.brand img {
    width: 34px;
    margin-right: 10px;
}
.nav-right {
    display: flex;
    align-items: center;
    gap: 1px;
}
.nav-links {
    display: flex;
    gap: 1px;
}
.nav-links a {
    color: white;
    text-decoration: none;
    font-weight: 500;
    padding: 5px 10px;
    transition: background 0.3s;
}
.nav-links a:hover {
    background-color: orange;
    border-radius: 5px;
}
.profile {
    display: inline-block;
    margin-left: 10px;
    position: relative;
}
.profile img {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 2px solid white;
    cursor: pointer;
}
.dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: 55px;
    background: #ff0000;
    min-width: 140px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    z-index: 1000;
    overflow: hidden;
}
.dropdown a {
    color: white;
    text-decoration: none;
    display: block;
    padding: 10px;
    font-size: 14px;
}
.dropdown a:hover {
    background: #cc0000;
}

.container {
    padding: 20px;
    text-align: center;
}

.card {
    background: #222;
    padding: 15px;
    margin: 10px;
    border-radius: 12px;
    display: inline-block;
    width: 220px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.5);
    transition: 0.3s;
    vertical-align: top;
}
.card:hover {
    transform: scale(1.05);
}
.card img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 10px;
}
.card h3 {
    margin: 10px 0 5px;
    font-size: 16px;
    word-break: break-word;
}
.price {
    color: gold;
    font-size: 18px;
    font-weight: bold;
    margin: 5px 0;
}
.remove-btn {
    background: red;
    color: white;
    border: none;
    padding: 6px 10px;
    cursor: pointer;
    border-radius: 5px;
    margin-top: 5px;
}
.cart-btn {
    background: green;
    color: white;
    border: none;
    padding: 6px 10px;
    cursor: pointer;
    border-radius: 5px;
    margin-top: 5px;
}
.back-btn {
    background: #e60000;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 25px;
    cursor: pointer;
    margin: 20px auto;
    font-size: 14px;
    transition: 0.3s;
}
.back-btn:hover {
    background: #cc0000;
}
.empty {
    font-size: 20px;
    color: gray;
    margin-top: 50px;
}
</style>
</head>
<body>

<div class="nav-container">
    <a href="index.php" class="brand">
        <img src="Assets/logo.png" alt="logo">
        <span>B K's Burger House</span>
    </a>

    <div class="nav-right">
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="cart.php">Cart</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
        </div>

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

<div class="container">
    <h2>My Wishlist</h2>
    <div id="wishlistItems"></div>
</div>

<div style="text-align:center;">
    <button class="back-btn" onclick="goBack()">⬅ Back to Menu</button>
</div>

<script>
// Database mathi aavel products (admin ma badlav karo to aa j badlashe)
const products = <?= json_encode($products, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("profileBtn");
    const menu = document.getElementById("dropMenu");

    if (btn) {
        btn.addEventListener("click", function (e) {
            e.stopPropagation();
            menu.style.display = menu.style.display === "block" ? "none" : "block";
        });
    }
    document.addEventListener("click", function () {
        menu.style.display = "none";
    });

    renderWishlist();
});

function renderWishlist() {
    const wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];
    const container = document.getElementById("wishlistItems");
    container.innerHTML = "";

    if (wishlist.length === 0) {
        container.innerHTML = "<div class='empty'>Your Wishlist is Empty 😢</div>";
        return;
    }

    wishlist.forEach(item => {
        // Jo item DB mathi delete thai gayo hoy, to card batavvo nahi
        if (!products[item]) return;

        const div = document.createElement("div");
        div.className = "card";
        div.innerHTML = `
            <img src="${products[item].img}" alt="${item}">
            <h3>${item}</h3>
            <div class="price">₹${products[item].price}</div>

            <button class="cart-btn" onclick="addToCart('${item}')">🛒 Add to Cart</button>
            <br>
            <button class="remove-btn" onclick="removeItem('${item}')">Remove</button>
        `;
        container.appendChild(div);
    });
}

function removeItem(item) {
    let wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];
    wishlist = wishlist.filter(i => i !== item);
    localStorage.setItem("wishlist", JSON.stringify(wishlist));
    renderWishlist();
}

function addToCart(item) {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    cart.push(item);
    localStorage.setItem("cart", JSON.stringify(cart));
    alert(item + " added to Cart 🛒");
}

function goBack() {
    window.location.href = "menu.php";
}
</script>

</body>
</html>