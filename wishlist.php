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
<title>My Wishlist</title>

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

.container{
    padding:20px;
    text-align:center;
}

.card{
    background:#222;
    padding:15px;
    margin:10px;
    border-radius:12px;
    display:inline-block;
    width:220px;
    text-align:center;
    box-shadow:0 5px 15px rgba(0,0,0,0.5);
    transition:0.3s;
}

.card:hover{
    transform:scale(1.05);
}

.card img{
    width:100%;
    height:150px;
    object-fit:cover;
    border-radius:10px;
}

.price{
    color:gold;
    font-size:18px;
    font-weight:bold;
    margin:5px 0;
}

.remove-btn{
    background:red;
    color:white;
    border:none;
    padding:6px 10px;
    cursor:pointer;
    border-radius:5px;
    margin-top:5px;
}

.cart-btn{
    background:green;
    color:white;
    border:none;
    padding:6px 10px;
    cursor:pointer;
    border-radius:5px;
    margin-top:5px;
}

.back-btn{
    background:#e60000;
    color:white;
    border:none;
    padding:10px 20px;
    border-radius:25px;
    cursor:pointer;
    margin:20px auto;
    font-size:14px;
    transition:0.3s;
}

.back-btn:hover{
    background:#cc0000;
}

.empty{
    font-size:20px;
    color:gray;
    margin-top:50px;
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

<div class="container">
    <h2>My Wishlist</h2>
    <div id="wishlistItems"></div>
</div>
<div style="text-align:center;">
    <button class="back-btn" onclick="goBack()">⬅ Back to Menu</button>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const btn = document.getElementById("profileBtn");
    const menu = document.getElementById("dropMenu");

    if(btn){
        btn.onclick = function(e){
            e.stopPropagation();
            menu.style.display =
                menu.style.display === "block" ? "none" : "block";
        }
    }

    document.addEventListener("click", function(e){
        if(menu.style.display === "block"){
            menu.style.display = "none";
        }
    });

});


let products = {
    // 🔴 VEG BURGER
    "AlooTikkiBurger": {price:120, img:"Assets/Store/premium/AlooTikkiBurger.png"},
    "CheeseVegBurger": {price:100, img:"Assets/Store/vegburger/CheeseVegBurger.png"},
    "ClassicVegBurger": {price:50, img:"Assets/Store/vegburger/ClassicVegBurger.png"},
    "CrispyVegBurger": {price:150, img:"Assets/Store/vegburger/CrispyVegBurger.png"},
    "DoublePattyVegBurger": {price:80, img:"Assets/Store/vegburger/DoublePattyVegBurger.png"},
    "GreenGardenBurger": {price:130, img:"Assets/Store/vegburger/GreenGardenBurger.png"},
    "JainVegBurger": {price:160, img:"Assets/Store/premium/JainVegBurger.png"},
    "MexicanVegBurger": {price:180, img:"Assets/Store/vegburger/MexicanVegBurger.png"},
    "MushroomVegBurger": {price:200, img:"Assets/Store/premium/MushroomVegBurger.png"},
    "PaneerTikkaBurger": {price:210, img:"Assets/Store/vegburger/PaneerTikkaBurger.png"},
    "SpicyVegburger": {price:120, img:"Assets/Store/vegburger/SpicyVegburger.png"},
    "SpicyVegDelight": {price:220, img:"Assets/Store/vegburger/SpicyVegDelight.png"},
    "VegCrunchBurger": {price:170, img:"Assets/Store/vegburger/VegCrunchBurger.png"},
    "VegMaharajaBurger": {price:140, img:"Assets/Store/premium/VegMaharajaBurger.png"},
    "VegSupremeBurger": {price:200, img:"Assets/Store/vegburger/VegSupremeBurger.png"},

    // 🔴 NON VEG BURGER
    "BBQChickenBurger": {price:160, img:"Assets/Store/non-vegburger/BBQChickenBurger.png"},
    "ChickenCheeseBurger": {price:140, img:"Assets/Store/non-vegburger/ChickenCheeseBurger.png"},
    "ChickenCrunchBurger": {price:150, img:"Assets/Store/non-vegburger/ChickenCrunchBurger.png"},
    "ChickenDeluxeBurger": {price:130, img:"Assets/Store/non-vegburger/ChickenDeluxeBurger.png"},
    "ChickenMaharajaBurger": {price:200, img:"Assets/Store/premium/ChickenMaharajaBurger.png"},
    "ChickenPattyBurger": {price:220, img:"Assets/Store/non-vegburger/ChickenPattyBurger.png"},
    "ChickenSupremeBurger": {price:230, img:"Assets/Store/non-vegburger/ChickenSupremeBurger.png"},
    "ChickenTandooriBurger": {price:150, img:"Assets/Store/premium/ChickenTandooriBurger.png"},
    "ClassicChickenBurger": {price:120, img:"Assets/Store/non-vegburger/ClassicChickenBurger.png"},
    "CrispyChickenBurger": {price:180, img:"Assets/Store/non-vegburger/CrispyChickenBurger.png"},
    "DoubleChickenBurger": {price:250, img:"Assets/Store/non-vegburger/DoubleChickenBurger.png"},
    "EggChickenBurger": {price:260, img:"Assets/Store/premium/EggChickenBurger.png"},
    "GrilledChickenBurger": {price:280, img:"Assets/Store/non-vegburger/GrilledChickenBurger.png"},
    "PeriPeriChickenBurger": {price:300, img:"Assets/Store/premium/PeriPeriChickenBurger.png"},
    "SpicyChickenBurger": {price:320, img:"Assets/Store/non-vegburger/SpicyChickenBurger.png"},

    // 🔴 COMBO
    "ChickenCheeseCombo": {price:250, img:"Assets/Store/combo/ChickenCheeseCombo.png"},
    "BBQChickenCombo": {price:270, img:"Assets/Store/combo/BBQChickenCombo.png"},
    "ChickenClassicCombo": {price:290, img:"Assets/Store/combo/ChickenClassicCombo.png"},
    "ChickenSupremeCombo": {price:300, img:"Assets/Store/combo/ChickenSupremeCombo.png"},
    "CrispyChickenCombo": {price:310, img:"Assets/Store/combo/CrispyChickenCombo.png"},
    "DoubleVegCombo": {price:190, img:"Assets/Store/combo/DoubleVegCombo.png"},
    "FamilyChickenCombo": {price:600, img:"Assets/Store/premium/FamilyChickenCombo.png"},
    "FamilyVegCombo": {price:500, img:"Assets/Store/premium/FamilyVegCombo.png"},
    "MaharajaChickenCombo": {price:700, img:"Assets/Store/premium/MaharajaChickenCombo.png"},
    "MaharajaVegCombo": {price:650, img:"Assets/Store/premium/MaharajaVegCombo.png"},
    "PaneerBurgerCombo": {price:300, img:"Assets/Store/combo/PaneerBurgerCombo.png"},
    "PeriPeriChickenCombo": {price:350, img:"Assets/Store/combo/PeriPeriChickenCombo.png"},
    "VegCheeseCombo": {price:250, img:"Assets/Store/combo/VegCheeseCombo.png"},
    "VegClassicCombo": {price:200, img:"Assets/Store/combo/VegClassicCombo.png"},
    "VegSupremeCombo": {price:150, img:"Assets/Store/combo/VegSupremeCombo.png"},

    // 🔴 DRINKS
    "ChocolateShake": {price:100, img:"Assets/Store/drink/ChocolateShake.png"},
    "CocaCola": {price:50, img:"Assets/Store/drink/CocaCola.png"},
    "ColdCoffee": {price:120, img:"Assets/Store/drink/ColdCoffee.png"},
    "Fanta": {price:50, img:"Assets/Store/drink/Fanta.png"},
    "LemonIcedTea": {price:70, img:"Assets/Store/drink/LemonIcedTea.png"},
    "Limca": {price:60, img:"Assets/Store/drink/Limca.png"},
    "MangoShake": {price:140, img:"Assets/Store/drink/MangoShake.png"},
    "MineralWater": {price:30, img:"Assets/Store/drink/MineralWater.png"},
    "OreoShake": {price:150, img:"Assets/Store/drink/OreoShake.png"},
    "PeachIcedTea": {price:100, img:"Assets/Store/drink/PeachIcedTea.png"},
    "Pepsi": {price:50, img:"Assets/Store/drink/Pepsi.png"},
    "Sprite": {price:50, img:"Assets/Store/drink/Sprite.png"},
    "StrawberryShake": {price:160, img:"Assets/Store/drink/StrawberryShake.png"},
    "ThumsUp": {price:50, img:"Assets/Store/drink/ThumsUp.png"},
    "VanillaShake": {price:150, img:"Assets/Store/drink/VanillaShake.png"}

};

let wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];

let container = document.getElementById("wishlistItems");

if(wishlist.length === 0){
    container.innerHTML = "<div class='empty'>Your Wishlist is Empty 😢</div>";
}

wishlist.forEach(item => {

    if(products[item]){

        let div = document.createElement("div");
        div.className = "card";

        div.innerHTML = `
            <img src="${products[item].img}" alt="">
            <h3>${item}</h3>
            <div class="price">₹${products[item].price}</div>

            <button class="cart-btn" onclick="addToCart('${item}')">
                🛒 Add to Cart
            </button>

            <br>

            <button class="remove-btn" onclick="removeItem('${item}')">
                Remove
            </button>
        `;

        container.appendChild(div);
    }
});

function removeItem(item){
    let wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];
    wishlist = wishlist.filter(i => i !== item);
    localStorage.setItem("wishlist", JSON.stringify(wishlist));
    location.reload();
}

function addToCart(item){

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