<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: login.php");
    exit();
}
if (($_SESSION['admin'] ?? 'NO') === 'YES') {
    header("location: sales.php");
    exit();
}

require_once "config.php";

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money($value) {
    return rtrim(rtrim(number_format((float)$value, 2, '.', ''), '0'), '.');
}

// Item nu rating (3.5 thi 5.0 vacche), naam parthi nakki thay, etle refresh par badlay nahi
function itemRating($name) {
    $steps = [3.5, 4.0, 4.5, 5.0];
    return $steps[abs(crc32($name)) % count($steps)];
}

// 5 stars: gray base par gold overlay. Width rating pramane hoy, etle 3.5 = 3 aakha + 1 aadho star
function ratingHtml($rating) {
    $percent = ($rating / 5) * 100;
    return '<span class="stars-wrap">'
         .   '<span class="stars-bg">★★★★★</span>'
         .   '<span class="stars-fill" style="width:' . $percent . '%">★★★★★</span>'
         . '</span> <span class="rating-num">' . number_format($rating, 1) . '</span>';
}

// Category -> filter class + image folder
$categoryMeta = [
    'Veg Burger'     => ['class' => 'veg',    'folder' => 'vegburger'],
    'Non-Veg Burger' => ['class' => 'nonveg', 'folder' => 'non-vegburger'],
    'Combo'          => ['class' => 'combo',  'folder' => 'combo'],
    'Drink'          => ['class' => 'drinks', 'folder' => 'drink'],
];

// manage_menu.php pan aa j extensions vaapare chhe
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

// Menu database mathi load karo
$menu = [];
$res = mysqli_query($conn, "SELECT id, name, price, category FROM products
                            ORDER BY FIELD(category, 'Veg Burger', 'Non-Veg Burger', 'Combo', 'Drink'), name");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        if (!isset($categoryMeta[$row['category']])) {
            $row['category'] = 'Drink';
        }
        $menu[(int)$row['id']] = $row;
    }
}

// Cart submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qtys = $_POST['qty'] ?? [];
    if (!is_array($qtys)) {
        $qtys = [];
    }

    $cartItemN = [];
    $cartItemQ = [];
    $cartItemP = [];

    foreach ($qtys as $id => $qty) {
        $id  = (int)$id;
        $qty = (int)$qty;

        if ($qty > 0 && isset($menu[$id])) {
            $cartItemN[] = $menu[$id]['name'];
            $cartItemQ[] = $qty;
            $cartItemP[] = $qty * (float)$menu[$id]['price'];
        }
    }

    $totPrice = array_sum($cartItemP);

    if ($totPrice > 0) {
        $discount      = $totPrice * 0.20;
        $offerDiscount = 0;
        $afterDiscount = $totPrice - $discount;
        $tax           = $afterDiscount * 0.20;
        $netAmount     = $afterDiscount + $tax;

        $_SESSION["cartItemN"] = $cartItemN;
        $_SESSION["cartItemQ"] = $cartItemQ;
        $_SESSION["cartItemP"] = $cartItemP;

        $_SESSION["total"]         = $totPrice;
        $_SESSION["discount"]      = $discount;
        $_SESSION["offerDiscount"] = $offerDiscount;
        $_SESSION["tax"]           = $tax;
        $_SESSION["netAmount"]     = $netAmount;

        header("location: cart.php");
        exit();
    }
}

$cartCount = 0;
if (isset($_SESSION['cartItemQ']) && is_array($_SESSION['cartItemQ'])) {
    $cartCount = array_sum($_SESSION['cartItemQ']);
}

$img = "uploads/default.png";
$stmt = mysqli_prepare($conn, "SELECT image FROM loginform WHERE username=?");
if ($stmt) {
    $username = $_SESSION['username'];
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $user_img);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!empty($user_img)) {
        $img = "uploads/" . $user_img;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Burger Mania - Store</title>
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

/* ---------- HEADER ---------- */
.nav-container {
    position: sticky;
    top: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    min-height: 70px;
    padding: 10px 40px;
    background: black;
    box-sizing: border-box;
}
.brand {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #fff;
    text-decoration: none;
    font-size: 22px;
    font-weight: 600;
    white-space: nowrap;
}
.brand img {
    width: 45px;
    height: 45px;
    object-fit: contain;
}
.search-box {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    max-width: 360px;
}
.search-box input {
    width: 100%;
    padding: 10px 18px;
    border-radius: 25px;
    border: none;
    outline: none;
    font-size: 14px;
}
.search-box button {
    padding: 9px 18px;
    border: none;
    border-radius: 25px;
    background: white;
    color: #e60000;
    font-weight: bold;
    cursor: pointer;
    white-space: nowrap;
}
.search-box button:hover { background: #ffd6d6; }
.nav-right {
    display: flex;
    align-items: center;
    gap: 6px;
}
.nav-right > a {
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 5px;
    transition: background 0.3s;
}
.nav-right > a:hover { background: orange; }
.profile {
    position: relative;
    margin-left: 10px;
}
.profile img {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 2px solid #fff;
    object-fit: cover;
    cursor: pointer;
}
.dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: 50px;
    background: #ff0000;
    min-width: 140px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    z-index: 1001;
    overflow: hidden;
}
.dropdown a {
    display: block;
    padding: 10px;
    color: #fff;
    text-decoration: none;
    font-size: 14px;
}
.dropdown a:hover { background: #cc0000; }

@media (max-width: 900px) {
    .nav-container { flex-wrap: wrap; padding: 10px 15px; }
    .search-box { order: 3; max-width: 100%; width: 100%; }
}

/* ---------- CATEGORY BAR ---------- */
.category-bar {
    display: flex;
    align-items: center;
    justify-content: center;
    position: sticky;
    top: 70px;
    z-index: 999;
    margin: 15px;
}
.category-buttons {
    display: flex;
    gap: 10px;
}
.category-buttons button {
    padding: 10px 18px;
    border: none;
    border-radius: 20px;
    background: #ddd;
    cursor: pointer;
    font-weight: bold;
}
.category-buttons button:hover {
    background: #e60000;
    color: white;
}
.cart-icon {
    position: relative;
    margin-left: 20px;
    font-size: 26px;
    cursor: pointer;
}
.cart-count {
    position: absolute;
    top: -8px;
    right: -10px;
    background: red;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
}

/* ---------- FOOD CARDS ---------- */
.food-wrapper {
    position: relative;
    width: 100%;
    overflow: hidden;
}
.food-items {
    display: flex;
    gap: 20px;
    padding: 20px;
    overflow-x: auto;
    scroll-behavior: smooth;
    white-space: nowrap;
}
.food-items::-webkit-scrollbar { display: none; }
.arrow-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: black;
    color: white;
    border: none;
    font-size: 30px;
    width: 45px;
    height: 45px;
    cursor: pointer;
    z-index: 1000;
}
.left-btn  { left: 0; }
.right-btn { right: 0; }

.food {
    min-width: 280px;
    width: 280px;
    height: 430px;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(12px);
    border-radius: 20px;
    padding: 15px;
    text-align: center;
    border: 2px solid rgba(255,255,255,0.3);
    box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    transition: 0.3s;
    position: relative;
    white-space: normal;
}
.food:hover {
    transform: translateY(-10px) scale(1.05);
    box-shadow: 0 15px 40px rgba(0,0,0,0.8);
}
.food img {
    width: 100%;
    height: 300px;
    object-fit: cover;
    border-radius: 15px;
    transition: 0.3s;
}
.food:hover img { transform: scale(1.1); }
.food h3 { margin: 10px 0; }

.price-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: gold;
    color: black;
    padding: 4px 8px;
    border-radius: 10px;
    font-weight: bold;
}

/* ---------- RATING (half star support) ---------- */
.rating {
    margin-bottom: 4px;
}
.stars-wrap {
    position: relative;
    display: inline-block;
    font-size: 18px;
    line-height: 1;
    letter-spacing: 2px;
}
.stars-bg {
    color: #666;
}
.stars-fill {
    position: absolute;
    left: 0;
    top: 0;
    overflow: hidden;
    white-space: nowrap;
    color: gold;
}
.rating-num {
    color: #fff;
    font-size: 14px;
    font-weight: bold;
}

.qty-box {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
    margin-top: 5px;
}
.qty-box button {
    width: 28px;
    height: 28px;
    border: none;
    background: black;
    color: white;
    font-size: 16px;
    cursor: pointer;
    border-radius: 4px;
}
.qty-box span {
    width: 30px;
    display: inline-block;
    text-align: center;
    font-weight: bold;
}
.add-btn {
    margin-top: 8px;
    background: black;
    color: white;
    border: none;
    border-radius: 20px;
    padding: 6px 15px;
    cursor: pointer;
    transition: 0.3s;
}
.add-btn:hover {
    background: gold;
    color: black;
}
.wishlist-btn {
    margin-top: 6px;
    background: white;
    color: red;
    border: 1px solid red;
    border-radius: 20px;
    padding: 6px 12px;
    cursor: pointer;
    transition: 0.3s;
}
.wishlist-btn:hover {
    background: red;
    color: white;
}
.empty-msg {
    text-align: center;
    padding: 40px;
    font-size: 18px;
}
.hidden { display: none; }
</style>
</head>

<body>

<header class="nav-container">
    <a href="index.php" class="brand">
        <img src="Assets/logo.png" alt="logo">
        <span>B K's Burger House</span>
    </a>

    <div class="search-box">
        <input type="text" id="search" placeholder="Search Burger" onkeyup="searchBurger()">
        <button type="button" onclick="searchBurger()">Search</button>
    </div>

    <nav class="nav-right">
        <a href="index.php">Home</a>
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
    </nav>
</header>

<form method="post">

<div class="category-bar">
    <div class="category-buttons">
        <button type="button" onclick="filterSelection('all')">All</button>
        <button type="button" onclick="filterSelection('veg')">Veg Burger</button>
        <button type="button" onclick="filterSelection('nonveg')">Non-Veg Burger</button>
        <button type="button" onclick="filterSelection('combo')">Combo Burger</button>
        <button type="button" onclick="filterSelection('drinks')">Drinks</button>
    </div>

    <div class="cart-icon" onclick="location.href='cart.php'">
        🛒
        <span class="cart-count"><?= (int)$cartCount ?></span>
    </div>
</div>

<div class="food-wrapper">
    <button type="button" class="arrow-btn left-btn" onclick="scrollLeftBtn()">&#8592;</button>

    <div class="food-items" id="foodScroll">
        <?php if (!$menu): ?>
            <p class="empty-msg">Haju koi item nathi. Admin panel mathi item add karo.</p>
        <?php endif; ?>

        <?php foreach ($menu as $id => $p):
            $meta    = $categoryMeta[$p['category']];
            $imgPath = itemImage($p['name'], $meta['folder'], $imageExtensions);
            $isDrink = ($p['category'] === 'Drink');
        ?>
        <div class="food <?= e($meta['class']) ?>">
            <img src="<?= e($imgPath) ?>" alt="<?= e($p['name']) ?>">
            <h3><?= e($p['name']) ?></h3>
            <div class="price-badge">₹<?= e(money($p['price'])) ?></div>

            <?php if (!$isDrink): ?>
                <div class="rating"><?= ratingHtml(itemRating($p['name'])) ?></div>
            <?php endif; ?>

            <div class="qty-box">
                <input type="hidden" id="qty_<?= $id ?>" name="qty[<?= $id ?>]" value="0">
                <button type="button" onclick="minus(<?= $id ?>)">-</button>
                <span id="qty_<?= $id ?>_txt">0</span>
                <button type="button" onclick="plus(<?= $id ?>)">+</button>
            </div>

            <button type="submit" class="add-btn">Add to Cart</button>
            <button type="button" class="wishlist-btn" data-name="<?= e($p['name']) ?>" onclick="addToWishlist(this)">❤️ Wishlist</button>
        </div>
        <?php endforeach; ?>
    </div>

    <button type="button" class="arrow-btn right-btn" onclick="scrollRightBtn()">&#8594;</button>
</div>

<br><br>
</form>

<script>
function filterSelection(category) {
    const items = document.getElementsByClassName("food");
    for (let i = 0; i < items.length; i++) {
        items[i].classList.add("hidden");
        if (category === "all" || items[i].classList.contains(category)) {
            items[i].classList.remove("hidden");
        }
    }
}

function searchBurger() {
    const input = document.getElementById("search").value.toLowerCase();
    const items = document.getElementsByClassName("food");
    for (let i = 0; i < items.length; i++) {
        const text = items[i].innerText.toLowerCase();
        items[i].classList.toggle("hidden", !text.includes(input));
    }
}

function plus(id) {
    const q = document.getElementById("qty_" + id);
    const t = document.getElementById("qty_" + id + "_txt");
    q.value = parseInt(q.value) + 1;
    t.textContent = q.value;
}

function minus(id) {
    const q = document.getElementById("qty_" + id);
    const t = document.getElementById("qty_" + id + "_txt");
    if (parseInt(q.value) > 0) {
        q.value = parseInt(q.value) - 1;
        t.textContent = q.value;
    }
}

function scrollLeftBtn() {
    document.getElementById("foodScroll").scrollLeft -= 400;
}

function scrollRightBtn() {
    document.getElementById("foodScroll").scrollLeft += 400;
}

function addToWishlist(btn) {
    const item = btn.dataset.name;
    const wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];

    if (!wishlist.includes(item)) {
        wishlist.push(item);
        localStorage.setItem("wishlist", JSON.stringify(wishlist));
        alert(item + " added to Wishlist ❤️");
    } else {
        alert("Already in Wishlist!");
    }
}

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
        if (menu) menu.style.display = "none";
    });
});
</script>

</body>
</html>