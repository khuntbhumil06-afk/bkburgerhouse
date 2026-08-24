<?php
session_start();

if(!isset($_SESSION['username'])){
    header("location: login.php");
    exit();
}

if($_SESSION["admin"]=='YES'){
    header("location: sales.php");
    exit();
}

$cartCount = 0;
if(isset($_SESSION['cartItemQ'])){
    $cartCount = array_sum($_SESSION['cartItemQ']);
}

include 'config.php';

$products = [
    // Veg
    "AlooTikkiBurger" => 120,
    "CheeseVegBurger" => 100,
    "ClassicVegBurger" => 50,
    "CrispyVegBurger" => 150,
    "DoublePattyVegBurger" => 80,
    "GreenGardenBurger" => 130,
    "JainVegBurger" => 160,
    "MexicanVegBurger" => 180,
    "MushroomVegBurger" => 200,
    "PaneerTikkaBurger" => 210,
    "SpicyVegburger" => 120,
    "SpicyVegDelight" => 220,
    "VegCrunchBurger" => 170,
    "VegMaharajaBurger" => 140,
    "VegSupremeBurger" => 200,

    // Non Veg
    "BBQChickenBurger" => 160,
    "ChickenCheeseBurger" => 140,
    "ChickenCrunchBurger" => 150,
    "ChickenDeluxeBurger" => 130,
    "ChickenMaharajaBurger" => 200,
    "ChickenPattyBurger" => 220,
    "ChickenSupremeBurger" => 230,
    "ChickenTandooriBurger" => 150,
    "ClassicChickenBurger" => 120,
    "CrispyChickenBurger" => 180,
    "DoubleChickenBurger" => 250,
    "EggChickenBurger" => 260,
    "GrilledChickenBurger" => 280,
    "PeriPeriChickenBurger" => 300,
    "SpicyChickenBurger" => 320,

    // Combo
    "ChickenCheeseCombo" => 250,
    "BBQChickenCombo" => 270,
    "ChickenClassicCombo" => 290,
    "ChickenSupremeCombo" => 300,
    "CrispyChickenCombo" => 310,
    "DoubleVegCombo" => 190,
    "FamilyChickenCombo" => 600,
    "FamilyVegCombo" => 500,
    "MaharajaChickenCombo" => 700,
    "MaharajaVegCombo" => 650,
    "PaneerBurgerCombo" => 300,
    "PeriPeriChickenCombo" => 350,
    "VegCheeseCombo" => 250,
    "VegClassicCombo" => 200,
    "VegSupremeCombo" => 150,

    // Drinks
    "ChocolateShake" => 100,
    "CocaCola" => 50,
    "ColdCoffee" => 120,
    "Fanta" => 50,
    "LemonIcedTea" => 70,
    "Limca" => 60,
    "MangoShake" => 140,
    "MineralWater" => 30,
    "OreoShake" => 150,
    "PeachIcedTea" => 100,
    "Pepsi" => 50,
    "Sprite" => 50,
    "StrawberryShake" => 160,
    "ThumsUp" => 50,
    "VanillaShake" => 150
];

$search = "";

if(isset($_POST['search'])){
    $search = strtolower($_POST['search']);
}



$vegItems = [
"AlooTikkiBurger","CheeseVegBurger","ClassicVegBurger","CrispyVegBurger",
"DoublePattyVegBurger","GreenGardenBurger","JainVegBurger","MexicanVegBurger",
"MushroomVegBurger","PaneerTikkaBurger","SpicyVegburger","SpicyVegDelight",
"VegCrunchBurger","VegMaharajaBurger","VegSupremeBurger"
];

$nonVegItems = [
"BBQChickenBurger","ChickenCheeseBurger","ChickenCrunchBurger",
"ChickenDeluxeBurger","ChickenMaharajaBurger","ChickenPattyBurger",
"ChickenSupremeBurger","ChickenTandooriBurger","ClassicChickenBurger",
"CrispyChickenBurger","DoubleChickenBurger","EggChickenBurger",
"GrilledChickenBurger","PeriPeriChickenBurger","SpicyChickenBurger"
];

$comboItems = [
"ChickenCheeseCombo","BBQChickenCombo","ChickenClassicCombo",
"ChickenSupremeCombo","CrispyChickenCombo","DoubleVegCombo",
"FamilyChickenCombo","FamilyVegCombo","MaharajaChickenCombo",
"MaharajaVegCombo","PaneerBurgerCombo","PeriPeriChickenCombo",
"VegCheeseCombo","VegClassicCombo","VegSupremeCombo"
];


$cartItemN = [];
$cartItemQ = [];
$cartItemP = [];
$err = "";

if($_SERVER['REQUEST_METHOD'] == "POST"){

    $vegQty = 0;
    $nonVegQty = 0;
    $comboQty = 0;

    foreach($products as $name => $price){

        $qty = isset($_POST[$name]) ? (int)$_POST[$name] : 0;

        if($qty > 0){
            $cartItemN[] = $name;
            $cartItemQ[] = $qty;
            $cartItemP[] = $qty * $price;
        }
    }

    $totPrice = array_sum($cartItemP);

    if($totPrice > 0){

    $discount = $totPrice * 0.20;
    $offerDiscount = 0;

    $afterDiscount = $totPrice - $discount;
    $tax = $afterDiscount * 0.20;
    $netAmount = $afterDiscount + $tax;

    $_SESSION["cartItemN"] = $cartItemN;
    $_SESSION["cartItemQ"] = $cartItemQ;
    $_SESSION["cartItemP"] = $cartItemP;

    $_SESSION["total"] = $totPrice;
    $_SESSION["discount"] = $discount;
    $_SESSION["offerDiscount"] = $offerDiscount;
    $_SESSION["tax"] = $tax;
    $_SESSION["netAmount"] = $netAmount;

    header("location: cart.php");
    exit();
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
<title>Burger Mania - Store</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body{
    font-family: Arial;
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                url('Assets/images.png') no-repeat center center fixed;
    background-size: cover;
    color:white;
    margin:0;
}
.nav-container{
    background:black;
    justify-content: space-between;
    padding:12px 40px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    position: sticky;
    top: 0;
    z-index: 1000;
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

.nav-container div:first-child{
    color:white;
    font-size:20px;
    font-weight:bold;
}

.search-box{
    display:flex;
    align-items:center;
    gap:8px;
}

.search-box input{
    width:260px;
    padding:10px 18px;
    border-radius:25px;
    border:none;
    outline:none;
    font-size:14px;
}

.search-box button{
    padding:9px 18px;
    border:none;
    border-radius:25px;
    background:white;
    color:#e60000;
    font-weight:bold;
    cursor:pointer;
}

.search-box button:hover{
    background:#ffd6d6;
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

.nav-container a{
    color:white;
    text-decoration:none;
    margin:0 12px;
    font-weight:bold;
}

.profile{
    display:inline-block;
    margin-left:10px;
}

.profile img{
    width:38px;
    height:38px;
    border-radius:50%;
    border:2px solid white;
}

.dropdown{
    display:none;
    position:absolute;
    right:0;
    top:55px;
    background:#ff0000;
    min-width:140px;
    border-radius:8px;
    box-shadow:0 4px 10px rgba(0,0,0,0.2);
    z-index:1000;
    overflow:hidden;
}

.dropdown a{
    color:white;
    text-decoration:none;
    display:block;
    padding:10px;
    font-size:14px;
    transition:0.2s;
}

.dropdown a:hover{
    background:#cc0000;
}

.category-bar{
    display:flex;
    align-items:center;
    justify-content:center;
    position: sticky;
    top:60px;
    z-index:999;
    margin:15px;
}

.category-buttons{
    display:flex;
    gap:10px;
}

.category-buttons button{
    padding:10px 18px;
    border:none;
    border-radius:20px;
    background:#ddd;
    cursor:pointer;
    font-weight:bold;
}

.category-buttons button:hover{
    background:#e60000;
    color:white;
}

.cart-icon{
    position:absolute;
    right:20px;
    font-size:26px;
    cursor:pointer;
}

.cart-count{
    position:absolute;
    top:-8px;
    right:-10px;
    background:red;
    color:white;
    border-radius:50%;
    padding:2px 6px;
    font-size:12px;
}

.food-wrapper{
    position:relative;
    width:100%;
    overflow:hidden;
}

.food-items{
    display:flex;
    gap:20px;
    padding:20px;
    overflow-x:auto;
    scroll-behavior:smooth;
    white-space:nowrap;
}

.food-items::-webkit-scrollbar{
    display:none;
}

.arrow-btn{
    position:absolute;
    top:50%;
    transform:translateY(-50%);
    background:black;
    pointer-events:auto;
    color:white;
    border:none;
    font-size:30px;
    width:45px;
    height:45px;
    cursor:pointer;
    z-index:1000;
}

.left-btn{
    left:0;
}

.right-btn{
    right:0;
}

.price-badge{
    position:absolute;
    top:10px;
    right:10px;
    background:gold;
    color:black;
    padding:4px 8px;
    border-radius:10px;
    font-weight:bold;
}

.title{
    margin:8px 0;
    font-size:18px;
}

.rating{
    color:gold;
    font-size:18px;
}

.qty-box{
    display:flex;
    justify-content:center;
    align-items:center;
    gap:6px;
    margin-top:5px;
}

.qty-box button{
    width:28px;
    height:28px;
    border:none;
    background:black;
    color:white;
    font-size:16px;
    cursor:pointer;
    border-radius:4px;
}

.qty-box span{
    width:30px;
    display:inline-block;
    text-align:center;
    font-weight:bold;
}

.add-btn{
    margin-top:8px;
    background:black;
    color:white;
    border:none;
    border-radius:20px;
    padding:6px 15px;
    cursor:pointer;
    transition:0.3s;
}

.add-btn:hover{
    background:gold;
    color:black;
}

.wishlist-btn{
    margin-top:6px;
    background:white;
    color:red;
    border:1px solid red;
    border-radius:20px;
    padding:6px 12px;
    cursor:pointer;
    transition:0.3s;
}

.wishlist-btn:hover{
    background:red;
    color:white;
}

.food{
    min-width:280px;
    width:280px;
    height:430px;

    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(12px);

    border-radius:20px;
    padding:15px;
    text-align:center;

    border:2px solid rgba(255,255,255,0.3);

    box-shadow:0 10px 25px rgba(0,0,0,0.5);

    transition:0.3s;
    position:relative;
}

.food:hover{
    transform: translateY(-10px) scale(1.05);
    box-shadow:0 15px 40px rgba(0,0,0,0.8);
}

.food img{
    width:100%;
    height:300px;
    object-fit:cover;
    border-radius:15px;
    transition:0.3s;
}

.food:hover img{
    transform:scale(1.1);
}

.food h3{
    margin:10px 0;
}

.food input{
    width:60px;
    padding:6px;
    border-radius:6px;
    border:1px solid #080808;
    text-align:center;
}


.submit-btn{
    background:#e60000;
    color:white;
    padding:12px 30px;
    border:none;
    border-radius:6px;
    font-size:16px;
    cursor:pointer;
}

.submit-btn:hover{
    background:#cc0000;
}

.error{
    color:red;
    font-weight:bold;
}

.hidden{
    display:none;
}
</style>
</head>

<body>

<div class="nav-container">
    <li class="brand">
            <img src="Assets/logo.png"> <a href="index.php">
            B K's Burger House
            </a>
    </li>
    <div class="search-box">
        <input type="text" id="search" placeholder="Search Burger" onkeyup="searchBurger()">
        <button type="submit">Search</button>
    </div>
    <div class="nav-right">
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="cart.php">Cart</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
        </div>
        <li class="profile">
            <img src="<?php echo $img; ?>" id="profileBtn">
            <div class="dropdown" id="dropMenu">
                <a href="account.php">Account</a>
                <a href="wishlist.php">Wishlist</a>
                <a href="logout.php">Logout</a>
            </div>
        </li>
    </div>
</div>

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
        <span class="cart-count"><?php echo $cartCount; ?></span>
    </div>
</div>

<div class="food-wrapper">
<button type="button" class="arrow-btn left-btn" onclick="scrollLeftBtn()">&#8592;</button>
<div class="food-items" id="foodScroll">

    <div class="food veg">
        <img src="Assets/Store/premium/AlooTikkiBurger.png" id="">
        <h3>Aloo Tikki Burger</h3>
        <div class="price-badge">₹120</div>
        <div class="rating">
        ⭐⭐⭐⭐☆
        </div>
        <div class="qty-box">
            <input type="hidden" id="AlooTikkiBurger" name="AlooTikkiBurger" value="0">
            <button type="button" onclick="minus('AlooTikkiBurger')">-</button>
            <span id="AlooTikkiBurger_txt">0</span>
            <button type="button" onclick="plus('AlooTikkiBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('AlooTikkiBurger')">❤️ Wishlist</button>
    </div>

    <div class="food veg">
        <img src="Assets/Store/premium/VegMaharajaBurger.png" id="">
        <h3>VegMaharajaBurger</h3>
        <div class="price-badge">₹180</div>
        <div class="rating">
        ⭐⭐⭐⭐☆
        </div>
        <div class="qty-box">
            <input type="hidden" id="VegMaharajaBurger" name="VegMaharajaBurger" value="0">
            <button type="button" onclick="minus('VegMaharajaBurger')">-</button>
            <span id="VegMaharajaBurger_txt">0</span>
            <button type="button" onclick="plus('VegMaharajaBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('VegMaharajaBurger')">❤️ Wishlist</button>
    </div>

    <div class="food veg">
        <img src="Assets/Store/premium/JainVegBurger.png" id="">
        <h3>JainVegBurger</h3>
        <div class="price-badge">₹160</div>
        <div class="rating">
        ⭐⭐⭐⭐☆
        </div>
        <div class="qty-box">
            <input type="hidden" id="JainVegBurger" name="JainVegBurger" value="0">
            <button type="button" onclick="minus('JainVegBurger')">-</button>
            <span id="JainVegBurger_txt">0</span>
            <button type="button" onclick="plus('JainVegBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('JainVegBurger')">❤️ Wishlist</button>
    </div>

    <div class="food veg">
        <img src="Assets/Store/premium/MushroomVegBurger.png" id="">
        <h3>MushroomVegBurger</h3>
        <div class="price-badge">₹200</div>
        <div class="rating">
        ⭐⭐⭐⭐☆
        </div>
        <div class="qty-box">
            <input type="hidden" id="MushroomVegBurger" name="MushroomVegBurger" value="0">
            <button type="button" onclick="minus('MushroomVegBurger')">-</button>
            <span id="MushroomVegBurger_txt">0</span>
            <button type="button" onclick="plus('MushroomVegBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('MushroomVegBurger')">❤️ Wishlist</button>
    </div>

    <div class="food veg">
        <img src="Assets/Store/vegburger/CheeseVegBurger.png" id="">
        <h3>Cheese Veg Burger - Rs.100</h3>
        <div class="qty-box">
            <input type="hidden" id="CheeseVegBurger" name="CheeseVegBurger" value="0">
            <button type="button" onclick="minus('CheeseVegBurger')">-</button>
            <span id="CheeseVegBurger_txt">0</span>
            <button type="button" onclick="plus('CheeseVegBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('CheeseVegBurger')">❤️ Wishlist</button>
    </div>

    <div class="food veg">
        <img src="Assets/Store/vegburger/ClassicVegBurger.png" id="">
        <h3>Classic Veg Burger - Rs.50</h3>
        <div class="qty-box">
            <input type="hidden" id="ClassicVegBurger" name="ClassicVegBurger" value="0">
            <button type="button" onclick="minus('ClassicVegBurger')">-</button>
            <span id="ClassicVegBurger_txt">0</span>
            <button type="button" onclick="plus('ClassicVegBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('ClassicVegBurger')">❤️ Wishlist</button>
    </div>

    <div class="food veg">
        <img src="Assets/Store/vegburger/CrispyVegBurger.png" id="">
        <h3>Crispy Veg Burger - Rs.150</h3>
        <div class="qty-box">
            <input type="hidden" id="CrispyVegBurger" name="CrispyVegBurger" value="0">
            <button type="button" onclick="minus('CrispyVegBurger')">-</button>
            <span id="CrispyVegBurger_txt">0</span>
            <button type="button" onclick="plus('CrispyVegBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('CrispyVegBurger')">❤️ Wishlist</button>
    </div>

    <div class="food veg">
        <img src="Assets/Store/vegburger/DoublePattyVegBurger.png" id="">
        <h3>Double Patty Veg Burger- Rs.80</h3>
        <div class="qty-box">
            <input type="hidden" id="DoublePattyVegBurger" name="DoublePattyVegBurger" value="0">
            <button type="button" onclick="minus('DoublePattyVegBurger')">-</button>
            <span id="DoublePattyVegBurger_txt">0</span>
            <button type="button" onclick="plus('DoublePattyVegBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('DoublePattyVegBurger')">❤️ Wishlist</button>
    </div>

    <div class="food veg">
        <img src="Assets/Store/vegburger/GreenGardenBurger.png" id="">
        <h3>GreenGardenBurger - Rs.130</h3>
        <div class="qty-box">
            <input type="hidden" id="GreenGardenBurger" name="GreenGardenBurger" value="0">
            <button type="button" onclick="minus('GreenGardenBurger')">-</button>
            <span id="GreenGardenBurger_txt">0</span>
            <button type="button" onclick="plus('GreenGardenBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('GreenGardenBurger')">❤️ Wishlist</button>
    </div>

    <div class="food veg">
        <img src="Assets/Store/vegburger/MexicanVegBurger.png" id="">
        <h3>MexicanVegBurger- Rs.180</h3>
        <div class="qty-box">
            <input type="hidden" id="MexicanVegBurger" name="MexicanVegBurger" value="0">
            <button type="button" onclick="minus('MexicanVegBurger')">-</button>
            <span id="MexicanVegBurger_txt">0</span>
            <button type="button" onclick="plus('MexicanVegBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('MexicanVegBurger')">❤️ Wishlist</button>
    </div>

    <div class="food veg">
        <img src="Assets/Store/vegburger/PaneerTikkaBurger.png" id="">
        <h3>PaneerTikkaBurger - Rs.210</h3>
        <div class="qty-box">
            <input type="hidden" id="PaneerTikkaBurger" name="PaneerTikkaBurger" value="0">
            <button type="button" onclick="minus('PaneerTikkaBurger')">-</button>
            <span id="PaneerTikkaBurger_txt">0</span>
            <button type="button" onclick="plus('PaneerTikkaBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('PaneerTikkaBurger')">❤️ Wishlist</button>
    </div>

    <div class="food veg">
        <img src="Assets/Store/vegburger/SpicyVegburger.png" id="">
        <h3>SpicyVegburger - Rs.120</h3>
        <div class="qty-box">
            <input type="hidden" id="SpicyVegburger" name="SpicyVegburger" value="0">
            <button type="button" onclick="minus('SpicyVegburger')">-</button>
            <span id="SpicyVegburger_txt">0</span>
            <button type="button" onclick="plus('SpicyVegburger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('SpicyVegburger')">❤️ Wishlist</button>
    </div>

    <div class="food veg">
        <img src="Assets/Store/vegburger/SpicyVegDelight.png" id="">
        <h3>SpicyVegDelight - Rs.220</h3>
        <div class="qty-box">
            <input type="hidden" id="SpicyVegDelight" name="SpicyVegDelight" value="0">
            <button type="button" onclick="minus('SpicyVegDelight')">-</button>
            <span id="SpicyVegDelight_txt">0</span>
            <button type="button" onclick="plus('SpicyVegDelight')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('SpicyVegDelight')">❤️ Wishlist</button>
    </div>

    <div class="food veg">
        <img src="Assets/Store/vegburger/VegCrunchBurger.png" id="">
        <h3>VegCrunchBurger - Rs.170</h3>
        <div class="qty-box">
            <input type="hidden" id="VegCrunchBurger" name="VegCrunchBurger" value="0">
            <button type="button" onclick="minus('VegCrunchBurger')">-</button>
            <span id="VegCrunchBurger_txt">0</span>
            <button type="button" onclick="plus('VegCrunchBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('VegCrunchBurger')">❤️ Wishlist</button>
    </div>

    <div class="food veg">
        <img src="Assets/Store/vegburger/VegSupremeBurger.png" id="">
        <h3>VegSupremeBurger - Rs.200</h3>
        <div class="qty-box">
            <input type="hidden" id="VegSupremeBurger" name="VegSupremeBurger" value="0">
            <button type="button" onclick="minus('VegSupremeBurger')">-</button>
            <span id="VegSupremeBurger_txt">0</span>
            <button type="button" onclick="plus('VegSupremeBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('VegSupremeBurger')">❤️ Wishlist</button>
    </div>

    <div class="food nonveg">
        <img src="Assets/Store/premium/PeriPeriChickenBurger.png" id="">
        <h3>PeriPeriChickenBurger</h3>
        <div class="price-badge">₹300</div>
        <div class="rating">
        ⭐⭐⭐⭐☆
        </div>
        <div class="qty-box">
            <input type="hidden" id="PeriPeriChickenBurger" name="PeriPeriChickenBurger" value="0">
            <button type="button" onclick="minus('PeriPeriChickenBurger')">-</button>
            <span id="PeriPeriChickenBurger_txt">0</span>
            <button type="button" onclick="plus('PeriPeriChickenBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('PeriPeriChickenBurger')">❤️ Wishlist</button>
    </div>

    <div class="food nonveg">
        <img src="Assets/Store/premium/ChickenMaharajaBurger.png" id="">
        <h3>ChickenMaharajaBurger</h3>
        <div class="price-badge">₹250</div>
        <div class="rating">
        ⭐⭐⭐⭐☆
        </div>
        <div class="qty-box">
            <input type="hidden" id="ChickenMaharajaBurger" name="ChickenMaharajaBurger" value="0">
            <button type="button" onclick="minus('ChickenMaharajaBurger')">-</button>
            <span id="ChickenMaharajaBurger_txt">0</span>
            <button type="button" onclick="plus('ChickenMaharajaBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('ChickenMaharajaBurger')">❤️ Wishlist</button>
    </div>

    <div class="food nonveg">
        <img src="Assets/Store/premium/ChickenTandooriBurger.png" id="">
        <h3>ChickenTandooriBurger</h3>
        <div class="price-badge">₹200</div>
        <div class="rating">
        ⭐⭐⭐⭐☆
        </div>
        <div class="qty-box">
            <input type="hidden" id="ChickenTandooriBurger" name="ChickenTandooriBurger" value="0">
            <button type="button" onclick="minus('ChickenTandooriBurger')">-</button>
            <span id="ChickenTandooriBurger_txt">0</span>
            <button type="button" onclick="plus('ChickenTandooriBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('ChickenTandooriBurger')">❤️ Wishlist</button>
    </div>

    <div class="food nonveg">
        <img src="Assets/Store/premium/EggChickenBurger.png" id="">
        <h3>EggChickenBurger</h3>
        <div class="price-badge">₹260</div>
        <div class="rating">
        ⭐⭐⭐⭐☆
        </div>
        <div class="qty-box">
            <input type="hidden" id="EggChickenBurger" name="EggChickenBurger" value="0">
            <button type="button" onclick="minus('EggChickenBurger')">-</button>
            <span id="EggChickenBurger_txt">0</span>
            <button type="button" onclick="plus('EggChickenBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('EggChickenBurger')">❤️ Wishlist</button>
    </div>

    <div class="food nonveg">
        <img src="Assets/Store/non-vegburger/BBQChickenBurger.png" id="">
        <h3>BBQChickenBurger - Rs.160</h3>
        <div class="qty-box">
            <input type="hidden" id="BBQChickenBurger" name="BBQChickenBurger" value="0">
            <button type="button" onclick="minus('BBQChickenBurger')">-</button>
            <span id="BBQChickenBurger_txt">0</span>
            <button type="button" onclick="plus('BBQChickenBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('BBQChickenBurger')">❤️ Wishlist</button>
    </div>

    <div class="food nonveg">
        <img src="Assets/Store/non-vegburger/ChickenCheeseBurger.png" id="">
        <h3>ChickenCheeseBurger - Rs.140</h3>
        <div class="qty-box">
            <input type="hidden" id="ChickenCheeseBurger" name="ChickenCheeseBurger" value="0">
            <button type="button" onclick="minus('ChickenCheeseBurger')">-</button>
            <span id="ChickenCheeseBurger_txt">0</span>
            <button type="button" onclick="plus('ChickenCheeseBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('ChickenCheeseBurger')">❤️ Wishlist</button>
    </div>

    <div class="food nonveg">
        <img src="Assets/Store/non-vegburger/ChickenCrunchBurger.png" id="">
        <h3>ChickenCrunchBurger - Rs.150</h3>
        <div class="qty-box">
            <input type="hidden" id="ChickenCrunchBurger" name="ChickenCrunchBurger" value="0">
            <button type="button" onclick="minus('ChickenCrunchBurger')">-</button>
            <span id="ChickenCrunchBurger_txt">0</span>
            <button type="button" onclick="plus('ChickenCrunchBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('ChickenCrunchBurger')">❤️ Wishlist</button>
    </div>

    <div class="food nonveg">
        <img src="Assets/Store/non-vegburger/ChickenDeluxeBurger.png" id="">
        <h3>ChickenDeluxeBurger - Rs.130</h3>
        <div class="qty-box">
            <input type="hidden" id="ChickenDeluxeBurger" name="ChickenDeluxeBurger" value="0">
            <button type="button" onclick="minus('ChickenDeluxeBurger')">-</button>
            <span id="ChickenDeluxeBurger_txt">0</span>
            <button type="button" onclick="plus('ChickenDeluxeBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('ChickenDeluxeBurger')">❤️ Wishlist</button>
    </div>

    <div class="food nonveg">
        <img src="Assets/Store/non-vegburger/ChickenPattyBurger.png" id="">
        <h3>ChickenPattyBurger - Rs.220</h3>
        <div class="qty-box">
            <input type="hidden" id="ChickenPattyBurger" name="ChickenPattyBurger" value="0">
            <button type="button" onclick="minus('ChickenPattyBurger')">-</button>
            <span id="ChickenPattyBurger_txt">0</span>
            <button type="button" onclick="plus('ChickenPattyBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('ChickenPattyBurger')">❤️ Wishlist</button>
    </div>

    <div class="food nonveg">
        <img src="Assets/Store/non-vegburger/ChickenSupremeBurger.png" id="">
        <h3>ChickenSupremeBurger - Rs.230</h3>
        <div class="qty-box">
            <input type="hidden" id="ChickenSupremeBurger" name="ChickenSupremeBurger" value="0">
            <button type="button" onclick="minus('ChickenSupremeBurger')">-</button>
            <span id="ChickenSupremeBurger_txt">0</span>
            <button type="button" onclick="plus('ChickenSupremeBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('ChickenSupremeBurger')">❤️ Wishlist</button>
    </div>

    <div class="food nonveg">
        <img src="Assets/Store/non-vegburger/ClassicChickenBurger.png" id="">
        <h3>ClassicChickenBurger- Rs.120</h3>
        <div class="qty-box">
            <input type="hidden" id="ClassicChickenBurger" name="ClassicChickenBurger" value="0">
            <button type="button" onclick="minus('ClassicChickenBurger')">-</button>
            <span id="ClassicChickenBurger_txt">0</span>
            <button type="button" onclick="plus('ClassicChickenBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('ClassicChickenBurger')">❤️ Wishlist</button>
    </div>

    <div class="food nonveg">
        <img src="Assets/Store/non-vegburger/CrispyChickenBurger.png" id="">
        <h3>CrispyChickenBurger - Rs.180</h3>
        <div class="qty-box">
            <input type="hidden" id="CrispyChickenBurger" name="CrispyChickenBurger" value="0">
            <button type="button" onclick="minus('CrispyChickenBurger')">-</button>
            <span id="CrispyChickenBurger_txt">0</span>
            <button type="button" onclick="plus('CrispyChickenBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('CrispyChickenBurger')">❤️ Wishlist</button>
    </div>

    <div class="food nonveg">
        <img src="Assets/Store/non-vegburger/DoubleChickenBurger.png" id="">
        <h3>DoubleChickenBurger - Rs.250</h3>
        <div class="qty-box">
            <input type="hidden" id="DoubleChickenBurger" name="DoubleChickenBurger" value="0">
            <button type="button" onclick="minus('DoubleChickenBurger')">-</button>
            <span id="DoubleChickenBurger_txt">0</span>
            <button type="button" onclick="plus('DoubleChickenBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('DoubleChickenBurger')">❤️ Wishlist</button>
    </div>

    <div class="food nonveg">
        <img src="Assets/Store/non-vegburger/GrilledChickenBurger.png" id="">
        <h3>GrilledChickenBurger - Rs.280</h3>
       <div class="qty-box">
            <input type="hidden" id="GrilledChickenBurger" name="GrilledChickenBurger" value="0">
            <button type="button" onclick="minus('GrilledChickenBurger')">-</button>
            <span id="GrilledChickenBurger_txt">0</span>
            <button type="button" onclick="plus('GrilledChickenBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('GrilledChickenBurger')">❤️ Wishlist</button>
    </div>

    <div class="food nonveg">
        <img src="Assets/Store/non-vegburger/SpicyChickenBurger.png" id="">
        <h3>SpicyChickenBurger - Rs.320</h3>
        <div class="qty-box">
            <input type="hidden" id="SpicyChickenBurger" name="SpicyChickenBurger" value="0">
            <button type="button" onclick="minus('SpicyChickenBurger')">-</button>
            <span id="SpicyChickenBurger_txt">0</span>
            <button type="button" onclick="plus('SpicyChickenBurger')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('SpicyChickenBurger')">❤️ Wishlist</button>
    </div>    

    <div class="food combo">
        <img src="Assets/Store/premium/FamilyVegCombo.png" id="">
        <h3>FamilyVegCombo</h3>
        <div class="price-badge">₹500</div>
        <div class="rating">
        ⭐⭐⭐⭐☆
        </div>
        <div class="qty-box">
            <input type="hidden" id="FamilyVegCombo" name="FamilyVegCombo" value="0">
            <button type="button" onclick="minus('FamilyVegCombo')">-</button>
            <span id="FamilyVegCombo_txt">0</span>
            <button type="button" onclick="plus('FamilyVegCombo')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('FamilyVegCombo')">❤️ Wishlist</button>
    </div>

    <div class="food combo">
        <img src="Assets/Store/premium/MaharajaChickenCombo.png" id="">
        <h3>MaharajaChickenCombo</h3>
        <div class="price-badge">₹700</div>
        <div class="rating">
        ⭐⭐⭐⭐☆
        </div>
        <div class="qty-box">
            <input type="hidden" id="MaharajaChickenCombo" name="MaharajaChickenCombo" value="0">
            <button type="button" onclick="minus('MaharajaChickenCombo')">-</button>
            <span id="MaharajaChickenCombo_txt">0</span>
            <button type="button" onclick="plus('MaharajaChickenCombo')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('MaharajaChickenCombo')">❤️ Wishlist</button>
    </div>

    <div class="food combo">
        <img src="Assets/Store/premium/MaharajaVegCombo.png" id="">
        <h3>MaharajaVegCombo</h3>
        <div class="price-badge">₹650</div>
        <div class="rating">
        ⭐⭐⭐⭐☆
        </div>
        <div class="qty-box">
            <input type="hidden" id="MaharajaVegCombo" name="MaharajaVegCombo" value="0">
            <button type="button" onclick="minus('MaharajaVegCombo')">-</button>
            <span id="MaharajaVegCombo_txt">0</span>
            <button type="button" onclick="plus('MaharajaVegCombo')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('MaharajaVegCombo')">❤️ Wishlist</button>
    </div>

    <div class="food combo">
        <img src="Assets/Store/premium/FamilyChickenCombo.png" id="">
        <h3>FamilyChickenCombo</h3>
        <div class="price-badge">₹600</div>
        <div class="rating">
        ⭐⭐⭐⭐☆
        </div>
        <div class="qty-box">
            <input type="hidden" id="FamilyChickenCombo" name="FamilyChickenCombo" value="0">
            <button type="button" onclick="minus('FamilyChickenCombo')">-</button>
            <span id="FamilyChickenCombo_txt">0</span>
            <button type="button" onclick="plus('FamilyChickenCombo')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('FamilyChickenCombo')">❤️ Wishlist</button>
    </div>

    <div class="food combo">
        <img src="Assets/Store/combo/PeriPeriChickenCombo.png" id="">
        <h3>PeriPeriChickenCombo - Rs.350</h3>
        <div class="qty-box">
            <input type="hidden" id="PeriPeriChickenCombo" name="PeriPeriChickenCombo" value="0">
            <button type="button" onclick="minus('PeriPeriChickenCombo')">-</button>
            <span id="PeriPeriChickenCombo_txt">0</span>
            <button type="button" onclick="plus('PeriPeriChickenCombo')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('PeriPeriChickenCombo')">❤️ Wishlist</button>
    </div>

    <div class="food combo">
        <img src="Assets/Store/combo/ChickenCheeseCombo.png" id="">
        <h3>ChickenCheeseCombo - Rs.250</h3>
        <div class="qty-box">
            <input type="hidden" id="ChickenCheeseCombo" name="ChickenCheeseCombo" value="0">
            <button type="button" onclick="minus('ChickenCheeseCombo')">-</button>
            <span id="ChickenCheeseCombo_txt">0</span>
            <button type="button" onclick="plus('ChickenCheeseCombo')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('ChickenCheeseCombo')">❤️ Wishlist</button>
    </div>

    <div class="food combo">
        <img src="Assets/Store/combo/VegSupremeCombo.png" id="">
        <h3>VegSupremeCombo - Rs.250</h3>
        <div class="qty-box">
            <input type="hidden" id="VegSupremeCombo" name="VegSupremeCombo" value="0">
            <button type="button" onclick="minus('VegSupremeCombo')">-</button>
            <span id="VegSupremeCombo_txt">0</span>
            <button type="button" onclick="plus('VegSupremeCombo')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('VegSupremeCombo')">❤️ Wishlist</button>
    </div>

    <div class="food combo">
        <img src="Assets/Store/combo/BBQChickenCombo.png" id="">
        <h3>BBQChickenCombo - Rs.270</h3>
        <div class="qty-box">
            <input type="hidden" id="BBQChickenCombo" name="BBQChickenCombo" value="0">
            <button type="button" onclick="minus('BBQChickenCombo')">-</button>
            <span id="BBQChickenCombo_txt">0</span>
            <button type="button" onclick="plus('BBQChickenCombo')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('BBQChickenCombo')">❤️ Wishlist</button>
    </div>

    <div class="food combo">
        <img src="Assets/Store/combo/ChickenClassicCombo.png" id="">
        <h3>ChickenClassicCombo - Rs.290</h3>
        <div class="qty-box">
            <input type="hidden" id="ChickenClassicCombo" name="ChickenClassicCombo" value="0">
            <button type="button" onclick="minus('ChickenClassicCombo')">-</button>
            <span id="ChickenClassicCombo_txt">0</span>
            <button type="button" onclick="plus('ChickenClassicCombo')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('ChickenClassicCombo')">❤️ Wishlist</button>
    </div>

    <div class="food combo">
        <img src="Assets/Store/combo/ChickenSupremeCombo.png" id="">
        <h3>ChickenSupremeCombo - Rs.300</h3>
        <div class="qty-box">
            <input type="hidden" id="ChickenSupremeCombo" name="ChickenSupremeCombo" value="0">
            <button type="button" onclick="minus('ChickenSupremeCombo')">-</button>
            <span id="ChickenSupremeCombo_txt">0</span>
            <button type="button" onclick="plus('ChickenSupremeCombo')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('ChickenSupremeCombo')">❤️ Wishlist</button>
    </div>

    <div class="food combo">
        <img src="Assets/Store/combo/CrispyChickenCombo.png" id="">
        <h3>CrispyChickenCombo - Rs.310</h3>
        <div class="qty-box">
            <input type="hidden" id="CrispyChickenCombo" name="CrispyChickenCombo" value="0">
            <button type="button" onclick="minus('CrispyChickenCombo')">-</button>
            <span id="CrispyChickenCombo_txt">0</span>
            <button type="button" onclick="plus('CrispyChickenCombo')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('CrispyChickenCombo')">❤️ Wishlist</button>
    </div>

    <div class="food combo">
        <img src="Assets/Store/combo/DoubleVegCombo.png" id="">
        <h3>DoubleVegCombo - Rs.190</h3>
        <div class="qty-box">
            <input type="hidden" id="DoubleVegCombo" name="DoubleVegCombo" value="0">
            <button type="button" onclick="minus('DoubleVegCombo')">-</button>
            <span id="DoubleVegCombo_txt">0</span>
            <button type="button" onclick="plus('DoubleVegCombo')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('DoubleVegCombo')">❤️ Wishlist</button>
    </div>

    <div class="food combo">
        <img src="Assets/Store/combo/PaneerBurgerCombo.png" id="">
        <h3>PaneerBurgerCombo - Rs.300</h3>
        <div class="qty-box">
            <input type="hidden" id="PaneerBurgerCombo" name="PaneerBurgerCombo" value="0">
            <button type="button" onclick="minus('PaneerBurgerCombo')">-</button>
            <span id="PaneerBurgerCombo_txt">0</span>
            <button type="button" onclick="plus('PaneerBurgerCombo')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('PaneerBurgerCombo')">❤️ Wishlist</button>
    </div>

    <div class="food combo">
        <img src="Assets/Store/combo/VegCheeseCombo.png" id="">
        <h3>VegCheeseCombo - Rs.250</h3>
        <div class="qty-box">
            <input type="hidden" id="VegCheeseCombo" name="VegCheeseCombo" value="0">
            <button type="button" onclick="minus('VegCheeseCombo')">-</button>
            <span id="VegCheeseCombo_txt">0</span>
            <button type="button" onclick="plus('VegCheeseCombo')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('VegCheeseCombo')">❤️ Wishlist</button>
    </div>

    <div class="food combo">
        <img src="Assets/Store/combo/VegClassicCombo.png" id="">
        <h3>VegClassicCombo - Rs.200</h3>
        <div class="qty-box">
            <input type="hidden" id="VegClassicCombo" name="VegClassicCombo" value="0">
            <button type="button" onclick="minus('VegClassicCombo')">-</button>
            <span id="VegClassicCombo_txt">0</span>
            <button type="button" onclick="plus('VegClassicCombo')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('VegClassicCombo')">❤️ Wishlist</button>
    </div>

    <div class="food drinks">
        <img src="Assets/Store/drink/ChocolateShake.png" id="">
        <h3>ChocolateShake - Rs.100</h3>
        <div class="qty-box">
            <input type="hidden" id="ChocolateShake" name="ChocolateShake" value="0">
            <button type="button" onclick="minus('ChocolateShake')">-</button>
            <span id="ChocolateShake_txt">0</span>
            <button type="button" onclick="plus('ChocolateShake')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('ChocolateShake')">❤️ Wishlist</button>
    </div>

    <div class="food drinks">
        <img src="Assets/Store/drink/CocaCola.png" id="">
        <h3>CocaCola - Rs.50</h3>
        <div class="qty-box">
            <input type="hidden" id="CocaCola" name="CocaCola" value="0">
            <button type="button" onclick="minus('CocaCola')">-</button>
            <span id="CocaCola_txt">0</span>
            <button type="button" onclick="plus('CocaCola')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('CocaCola')">❤️ Wishlist</button>
    </div>

    <div class="food drinks">
        <img src="Assets/Store/drink/ColdCoffee.png" id="">
        <h3>ColdCoffee - Rs.120</h3>
        <div class="qty-box">
            <input type="hidden" id="ColdCoffee" name="ColdCoffee" value="0">
            <button type="button" onclick="minus('ColdCoffee')">-</button>
            <span id="ColdCoffee_txt">0</span>
            <button type="button" onclick="plus('ColdCoffee')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('ColdCoffee')">❤️ Wishlist</button>
    </div>

    <div class="food drinks">
        <img src="Assets/Store/drink/Fanta.png" id="">
        <h3>Fanta - Rs.50</h3>
        <div class="qty-box">
            <input type="hidden" id="Fanta" name="Fanta" value="0">
            <button type="button" onclick="minus('Fanta')">-</button>
            <span id="Fanta_txt">0</span>
            <button type="button" onclick="plus('Fanta')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('Fanta')">❤️ Wishlist</button>
    </div>

    <div class="food drinks">
        <img src="Assets/Store/drink/LemonIcedTea.png" id="">
        <h3>LemonIcedTea - Rs.70</h3>
        <div class="qty-box">
            <input type="hidden" id="LemonIcedTea" name="LemonIcedTea" value="0">
            <button type="button" onclick="minus('LemonIcedTea')">-</button>
            <span id="LemonIcedTea_txt">0</span>
            <button type="button" onclick="plus('LemonIcedTea')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('LemonIcedTea')">❤️ Wishlist</button>
    </div>

    <div class="food drinks">
        <img src="Assets/Store/drink/Limca.png" id="">
        <h3>Limca - Rs.60</h3>
        <div class="qty-box">
            <input type="hidden" id="Limca" name="Limca" value="0">
            <button type="button" onclick="minus('Limca')">-</button>
            <span id="Limca_txt">0</span>
            <button type="button" onclick="plus('Limca')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('Limca')">❤️ Wishlist</button>
    </div>

    <div class="food drinks">
        <img src="Assets/Store/drink/MangoShake.png" id="">
        <h3>MangoShake - Rs.140</h3>
        <div class="qty-box">
            <input type="hidden" id="MangoShake" name="MangoShake" value="0">
            <button type="button" onclick="minus('MangoShake')">-</button>
            <span id="MangoShake_txt">0</span>
            <button type="button" onclick="plus('MangoShake')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('MangoShake')">❤️ Wishlist</button>
    </div>

    <div class="food drinks">
        <img src="Assets/Store/drink/MineralWater.png" id="">
        <h3>MineralWater - Rs.30</h3>
        <div class="qty-box">
            <input type="hidden" id="MineralWater" name="MineralWater" value="0">
            <button type="button" onclick="minus('MineralWater')">-</button>
            <span id="MineralWater_txt">0</span>
            <button type="button" onclick="plus('MineralWater')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('MineralWater')">❤️ Wishlist</button>
    </div>

    <div class="food drinks">
        <img src="Assets/Store/drink/OreoShake.png" id="">
        <h3>OreoShake - Rs.150</h3>
        <div class="qty-box">
            <input type="hidden" id="OreoShake" name="OreoShake" value="0">
            <button type="button" onclick="minus('OreoShake')">-</button>
            <span id="OreoShake_txt">0</span>
            <button type="button" onclick="plus('OreoShake')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('OreoShake')">❤️ Wishlist</button>
    </div>

    <div class="food drinks">
        <img src="Assets/Store/drink/PeachIcedTea.png" id="">
        <h3>PeachIcedTea - Rs.100</h3>
        <div class="qty-box">
            <input type="hidden" id="PeachIcedTea" name="PeachIcedTea" value="0">
            <button type="button" onclick="minus('PeachIcedTea')">-</button>
            <span id="PeachIcedTea_txt">0</span>
            <button type="button" onclick="plus('PeachIcedTea')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('PeachIcedTea')">❤️ Wishlist</button>
    </div>

    <div class="food drinks">
        <img src="Assets/Store/drink/Pepsi.png" id="">
        <h3>Pepsi - Rs.50</h3>
        <div class="qty-box">
            <input type="hidden" id="Pepsi" name="Pepsi" value="0">
            <button type="button" onclick="minus('Pepsi')">-</button>
            <span id="Pepsi_txt">0</span>
            <button type="button" onclick="plus('Pepsi')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('Pepsi')">❤️ Wishlist</button>
    </div>

    <div class="food drinks">
        <img src="Assets/Store/drink/Sprite.png" id="">
        <h3>Sprite - Rs.50</h3>
        <div class="qty-box">
            <input type="hidden" id="Sprite" name="Sprite" value="0">
            <button type="button" onclick="minus('Sprite')">-</button>
            <span id="Sprite_txt">0</span>
            <button type="button" onclick="plus('Sprite')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('Sprite')">❤️ Wishlist</button>
    </div>

    <div class="food drinks">
        <img src="Assets/Store/drink/StrawberryShake.png" id="">
        <h3>StrawberryShake - Rs.160</h3>
        <div class="qty-box">
            <input type="hidden" id="StrawberryShake" name="StrawberryShake" value="0">
            <button type="button" onclick="minus('StrawberryShake')">-</button>
            <span id="StrawberryShake_txt">0</span>
            <button type="button" onclick="plus('StrawberryShake')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('StrawberryShake')">❤️ Wishlist</button>
    </div>

    <div class="food drinks">
        <img src="Assets/Store/drink/ThumsUp.png" id="">
        <h3>ThumsUp - Rs.50</h3>
        <div class="qty-box">
            <input type="hidden" id="ThumsUp" name="ThumsUp" value="0">
            <button type="button" onclick="minus('ThumsUp')">-</button>
            <span id="ThumsUp_txt">0</span>
            <button type="button" onclick="plus('ThumsUp')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('ThumsUp')">❤️ Wishlist</button>
    </div>

    <div class="food drinks">
        <img src="Assets/Store/drink/VanillaShake.png" id="">
        <h3>VanillaShake - Rs.150</h3>
        <div class="qty-box">
            <input type="hidden" id="VanillaShake" name="VanillaShake" value="0">
            <button type="button" onclick="minus('VanillaShake')">-</button>
            <span id="VanillaShake_txt">0</span>
            <button type="button" onclick="plus('VanillaShake')">+</button>
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
        <button type="button" class="wishlist-btn" onclick="addToWishlist('VanillaShake')">❤️ Wishlist</button>
    </div>
</div>
<button type="button" class="arrow-btn right-btn" onclick="scrollRightBtn()">&#8594;</button>
</div>

<br>
<br>
</form>

<script>
function filterSelection(category) {
    var items = document.getElementsByClassName("food");
    for (var i = 0; i < items.length; i++) {
        items[i].classList.add("hidden"); // hide all
        if (category === "all" || items[i].classList.contains(category)) {
            items[i].classList.remove("hidden"); // show matching
        }
    }
}

function searchBurger() {
    let input = document.getElementById("search").value.toLowerCase();
    let items = document.getElementsByClassName("food");
    for (let i = 0; i < items.length; i++) {
        let text = items[i].innerText.toLowerCase();
        if (text.includes(input)) {
            items[i].classList.remove("hidden");
        } else {
            items[i].classList.add("hidden");
        }
    }
}

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

function plus(id){
    var q = document.getElementById(id);
    var t = document.getElementById(id+"_txt");

    q.value = parseInt(q.value) + 1;
    t.innerHTML = q.value;
}

function minus(id){
    var q = document.getElementById(id);
    var t = document.getElementById(id+"_txt");

    if(parseInt(q.value) > 0){
        q.value = parseInt(q.value) - 1;
        t.innerHTML = q.value;
    }
}

function scrollLeftBtn(){
    document.getElementById("foodScroll").scrollLeft -= 400;
}

function scrollRightBtn(){
    document.getElementById("foodScroll").scrollLeft += 400;
}

function addToWishlist(item){
    let wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];

    if(!wishlist.includes(item)){
        wishlist.push(item);
        localStorage.setItem("wishlist", JSON.stringify(wishlist));
        alert(item + " added to Wishlist ❤️");
    } else {
        alert("Already in Wishlist!");
    }
}
</script>

</body>
</html>