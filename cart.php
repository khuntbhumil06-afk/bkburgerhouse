<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: login.php");
    exit();
}

include 'config.php';

function h($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function fmt($v) {
    return number_format((float) $v, 2);
}

/* ---------- Cart item nu naam todo ----------
   "FamilyVegCombo (Large, Aloo Tikki Burger, Peri Peri Fries, Coke, +Extra Cheese)"
   -> base = "FamilyVegCombo", details = [Large, Aloo Tikki Burger, ...]            */
function splitItem($name) {
    $name = (string) $name;
    if (preg_match('/^(.*?)\s*\((.*)\)\s*$/u', $name, $m)) {
        $details = array_values(array_filter(array_map('trim', explode(',', $m[2])), 'strlen'));
        return [trim($m[1]), $details];
    }
    return [trim($name), []];
}

/* ---------- Item no image shodho (base naam parthi) ---------- */
function cartImage($baseName) {
    $dirs = ["premium", "combo", "drink", "non-vegburger", "vegburger", "options"];
    $exts = ["png", "jpg", "jpeg", "webp", "svg"];
    foreach ($dirs as $d) {
        foreach ($exts as $e) {
            $path = "Assets/Store/" . $d . "/" . $baseName . "." . $e;
            if (is_file(__DIR__ . "/" . $path)) {
                return implode("/", array_map('rawurlencode', explode("/", $path)));
            }
        }
    }
    return "Assets/logo.png";
}

/* ---------- Chip mate emoji ---------- */
function chipEmoji($text) {
    $l = strtolower($text);
    if (in_array($l, ['regular', 'medium', 'large'], true)) return '📏';
    foreach ([
        ['fries', '🍟'], ['wedge', '🍟'],
        ['coffee', '☕'], ['water', '💧'], ['coke', '🥤'], ['sprite', '🥤'], ['fanta', '🥤'], ['drink', '🥤'],
        ['chicken', '🍗'], ['burger', '🍔'],
    ] as $p) {
        if (strpos($l, $p[0]) !== false) return $p[1];
    }
    return '🍽️';
}

$cartItemN = $_SESSION["cartItemN"] ?? [];
$cartItemQ = $_SESSION["cartItemQ"] ?? [];
$cartItemP = $_SESSION["cartItemP"] ?? [];

$items = $cartItemN;
$qty   = $cartItemQ;
$price = $cartItemP;

/* ---------- Qty +  /  -  /  remove ---------- */
if (isset($_POST['action']) && isset($_POST['index'])) {
    $i = (int) $_POST['index'];

    if (isset($items[$i], $qty[$i], $price[$i]) && $qty[$i] > 0) {
        $unitPrice = $price[$i] / $qty[$i];
        $act = $_POST['action'];

        if ($act === "add")    { $qty[$i]++; }
        if ($act === "remove") { $qty[$i]--; }
        if ($act === "delete") { $qty[$i] = 0; }

        if ($qty[$i] <= 0) {
            array_splice($items, $i, 1);
            array_splice($qty, $i, 1);
            array_splice($price, $i, 1);
        } else {
            $price[$i] = $unitPrice * $qty[$i];
        }
    }

    $total         = array_sum($price);
    $discount      = $total * 0.20;
    $afterDiscount = $total - $discount;
    $tax           = $afterDiscount * 0.20;
    $netAmount     = $afterDiscount + $tax;

    $_SESSION['cartItemN']     = $items;
    $_SESSION['cartItemQ']     = $qty;
    $_SESSION['cartItemP']     = $price;
    $_SESSION['total']         = $total;
    $_SESSION['discount']      = $discount;
    $_SESSION['offerDiscount'] = 0;
    $_SESSION['tax']           = $tax;
    $_SESSION['netAmount']     = $netAmount;

    header("Location: cart.php");
    exit();
}

/* ---------- Totals ---------- */
$total         = array_sum($price);
$discount      = $total * 0.20;
$afterDiscount = $total - $discount;
$tax           = $afterDiscount * 0.20;
$netAmount     = $afterDiscount + $tax;
$totalQty      = array_sum($qty);

/* ---------- Profile image ---------- */
$img = "uploads/default.png";
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cart - Burger Mania</title>
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
img { max-width: 100%; }
a { color: inherit; }
.wrap { max-width: 1100px; margin: 0 auto; padding: 0 20px; }

/* ---------- NAV ---------- */
.nav {
    background: #0b0b0b;
    font-family: 'Poppins', sans-serif;
    position: sticky; top: 0; z-index: 500;
}
.nav .wrap {
    max-width: none;
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    padding: 12px 45px; min-height: 64px; flex-wrap: wrap;
}
.brand {
    display: flex; align-items: center; gap: 12px; text-decoration: none; color: #fff;
    font-family: 'Poppins', sans-serif; font-size: 20px; font-weight: 700; letter-spacing: 0;
}
.brand img { width: 38px; height: 38px; object-fit: contain; }
.nav-toggle {
    display: none; background: none; border: 0; color: #fff;
    font-size: 30px; line-height: 1; cursor: pointer; padding: 0 4px; margin-left: auto;
}
.links { display: flex; align-items: center; gap: 28px; flex-wrap: wrap; }
.links > a {
    text-decoration: none; color: #fff; font-family: 'Poppins', sans-serif;
    font-weight: 500; font-size: 17px; padding: 0; border-radius: 0; transition: color .2s;
}
.links > a:hover { background: none; color: #ff4d4d; }
.profile { position: relative; margin-left: 4px; }
.profile img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: none; cursor: pointer; display: block; }
.dropdown { display: none; position: absolute; right: 0; top: 52px; min-width: 150px; background: var(--red); border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,.5); z-index: 200; }
.dropdown a { display: block; padding: 11px 16px; text-decoration: none; font-size: 14px; font-weight: 600; font-family: 'Poppins', sans-serif; }
.dropdown a:hover { background: var(--red2); }

@media (max-width: 800px) {
    .nav .wrap { padding: 10px 16px; }
    .brand { font-size: 18px; }
    .nav-toggle { display: block; }
    .links {
        display: none; width: 100%; flex-direction: column; align-items: flex-start;
        gap: 4px; padding: 10px 0 6px; border-top: 1px solid #333;
    }
    .links.open { display: flex; }
    .links > a { font-size: 16px; padding: 10px 0; width: 100%; }
    .profile { margin: 6px 0 0; width: 100%; }
    .dropdown { position: static; margin-top: 10px; width: 100%; box-shadow: none; }
}
@media (max-width: 380px) {
    .brand { font-size: 16px; }
    .brand img { width: 32px; height: 32px; }
}

/* ---------- HERO ---------- */
.hero { padding: 40px 0 26px; }
.hi { color: var(--gold); font-weight: 800; font-size: 13px; letter-spacing: 3px; text-transform: uppercase; }
.hero h1 { font-family: 'Bebas Neue', sans-serif; font-weight: 400; font-size: clamp(54px, 10vw, 96px); line-height: .9; letter-spacing: 2px; margin-top: 6px; }
.hero h1 span { color: transparent; -webkit-text-stroke: 2px var(--red); }

/* ---------- LAYOUT ---------- */
.layout { display: grid; grid-template-columns: 1fr 350px; gap: 26px; align-items: start; padding-bottom: 80px; }
.layout > * { min-width: 0; }
.list { display: grid; gap: 16px; }

/* ---------- CART ITEM ---------- */
.item {
    display: grid; grid-template-columns: 120px 1fr auto; gap: 18px; align-items: center;
    background: var(--card); border: 1px solid var(--line); border-radius: 22px; padding: 16px;
    transition: .25s;
}
.item > div { min-width: 0; }
.item:hover { border-color: var(--gold); }
.item .pic { width: 120px; height: 120px; border-radius: 18px; overflow: hidden; background: var(--card2); box-shadow: 0 8px 20px rgba(0,0,0,.45); }
.item .pic img { width: 100%; height: 100%; object-fit: cover; display: block; }
.item h3 { font-family: 'Bebas Neue', sans-serif; font-weight: 400; font-size: 28px; letter-spacing: 1.2px; line-height: 1.1; word-break: break-word; }
.unit { font-size: 12px; color: var(--muted); margin-top: 2px; }
.chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
.chip { font-size: 12px; font-weight: 600; background: var(--card2); border-radius: 999px; padding: 4px 11px; }
.chip.top { background: rgba(255,183,3,.14); color: var(--gold); }
.chip.size { background: var(--red); color: #fff; }

.side { text-align: right; display: grid; gap: 12px; justify-items: end; }
.amount { font-family: 'Bebas Neue', sans-serif; font-size: 32px; letter-spacing: 1px; color: var(--gold); line-height: 1; }
.stepper { display: inline-flex; align-items: center; gap: 4px; background: var(--card2); border-radius: 999px; padding: 4px; }
.stepper form { display: inline; }
.stepper button { width: 32px; height: 32px; border: none; border-radius: 50%; background: var(--gold); color: #000; font-size: 18px; font-weight: 800; cursor: pointer; transition: .15s; }
.stepper button:hover { background: #fff; }
.stepper span { min-width: 28px; text-align: center; font-weight: 800; }
.del { background: none; border: none; color: var(--muted); font-family: inherit; font-size: 12px; cursor: pointer; text-decoration: underline; }
.del:hover { color: var(--red); }

/* ---------- BILL TICKET ---------- */
.bill {
    position: sticky; top: 90px;
    background: var(--paper); color: var(--paper-ink);
    border-radius: 24px; padding: 28px 26px 26px; box-shadow: 0 20px 50px rgba(0,0,0,.55);
    font-family: "Courier New", monospace;
}
.bill::before, .bill::after { content: ""; position: absolute; top: 76px; width: 28px; height: 28px; border-radius: 50%; background: var(--bg); }
.bill::before { left: -14px; }
.bill::after  { right: -14px; }
.bill .tag { font-size: 11px; letter-spacing: 3px; font-weight: 800; color: var(--red); }
.bill h2 { font-family: 'Bebas Neue', sans-serif; font-weight: 400; font-size: 42px; letter-spacing: 1.5px; line-height: 1; margin: 2px 0 16px; }
.bill .cut { border-top: 2px dashed #c9b88f; margin: 0 -26px 16px; }
.row { display: flex; justify-content: space-between; gap: 10px; padding: 6px 0; font-size: 14px; }
.row b { font-family: Arial, sans-serif; }
.row.minus b { color: #15803d; }
.pay { display: flex; justify-content: space-between; align-items: baseline; border-top: 2px solid var(--paper-ink); margin-top: 8px; padding-top: 12px; }
.pay span { font-size: 12px; font-weight: bold; letter-spacing: 2px; }
.pay strong { font-family: Arial, sans-serif; font-size: 32px; color: #c1121f; }
.btn {
    display: block; width: 100%; text-align: center; text-decoration: none; cursor: pointer; border: none;
    font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 15px; letter-spacing: .5px;
    padding: 14px; border-radius: 14px; transition: .15s;
}
.btn.red { background: var(--red); color: #fff; box-shadow: 0 5px 0 var(--red2); margin-top: 18px; }
.btn.red:hover { background: #f1485a; }
.btn.red:active { transform: translateY(4px); box-shadow: 0 1px 0 var(--red2); }
.btn.ghost { background: transparent; border: 2px solid var(--paper-ink); color: var(--paper-ink); margin-top: 12px; }
.btn.ghost:hover { background: var(--paper-ink); color: var(--paper); }
.note { font-size: 11px; color: #7a6a4f; text-align: center; margin-top: 12px; }

/* ---------- EMPTY ---------- */
.empty { grid-column: 1 / -1; text-align: center; background: var(--card); border: 2px dashed #3a2f28; border-radius: 24px; padding: 64px 20px; }
.empty .big { font-size: 64px; }
.empty h2 { font-family: 'Bebas Neue', sans-serif; font-weight: 400; font-size: 44px; letter-spacing: 1.5px; margin-top: 6px; }
.empty p { color: var(--muted); margin: 6px 0 24px; }
.empty .btn { display: inline-block; width: auto; padding: 13px 30px; margin: 0; border-radius: 999px; background: var(--gold); color: #000; box-shadow: 0 4px 0 #b37f00; }

/* ---------- RESPONSIVE ---------- */
@media (max-width: 900px), (hover: none) {
    body { background-attachment: scroll; }
}
@media (max-width: 900px) {
    .layout { grid-template-columns: 1fr; padding-bottom: 50px; }
    .bill { position: relative; top: 0; }
    .hero { padding: 28px 0 18px; }
}
@media (max-width: 640px) {
    .item { grid-template-columns: 90px 1fr; padding: 14px; gap: 14px; }
    .item .pic { width: 90px; height: 90px; border-radius: 14px; }
    .item h3 { font-size: 22px; }
    .side {
        grid-column: 1 / -1;
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: 10px; text-align: left;
        border-top: 1px dashed var(--line); padding-top: 12px;
    }
    .amount { font-size: 28px; }
    .stepper button { width: 36px; height: 36px; }
    .del { padding: 8px 4px; font-size: 13px; }
}
@media (max-width: 400px) {
    .item { grid-template-columns: 76px 1fr; }
    .item .pic { width: 76px; height: 76px; }
    .bill { padding: 24px 18px 22px; }
    .bill .cut { margin: 0 -18px 16px; }
    .bill h2 { font-size: 36px; }
    .pay strong { font-size: 28px; }
    .empty { padding: 44px 16px; }
    .empty h2 { font-size: 36px; }
}
</style>
</head>
<body>

<nav class="nav">
    <div class="wrap">
        <a class="brand" href="index.php"><img src="Assets/logo.png" alt="logo"> <span>B K's Burger House</span></a>
        <button type="button" class="nav-toggle" id="navToggle" aria-label="Menu">☰</button>
        <div class="links" id="navLinks">
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

<main class="wrap">
    <header class="hero">
        <p class="hi"><?= (int) $totalQty ?> item<?= $totalQty == 1 ? '' : 's' ?> in your bag</p>
        <h1>Your <span>cart</span></h1>
    </header>

    <div class="layout">
    <?php if (count($items) === 0): ?>
        <section class="empty">
            <div class="big">🛒</div>
            <h2>Your cart is empty</h2>
            <p>Add some tasty burgers and come back here to checkout.</p>
            <a class="btn" href="menu.php">Browse the menu</a>
        </section>
    <?php else: ?>

        <section class="list">
            <?php for ($i = 0; $i < count($items); $i++):
                [$base, $details] = splitItem($items[$i]);
                $pic  = cartImage($base);
                $unit = $qty[$i] > 0 ? $price[$i] / $qty[$i] : $price[$i];
            ?>
            <article class="item">
                <div class="pic"><img src="<?= h($pic) ?>" alt="<?= h($base) ?>"></div>

                <div>
                    <h3><?= h($base) ?></h3>
                    <div class="unit">₹<?= fmt($unit) ?> each</div>

                    <?php if ($details): ?>
                    <div class="chips">
                        <?php foreach ($details as $k => $d):
                            $isTop  = (strpos($d, '+') === 0);
                            $isSize = ($k === 0 && in_array(strtolower($d), ['regular', 'medium', 'large'], true));
                            $label  = $isTop ? ltrim($d, '+') : $d;
                        ?>
                            <span class="chip<?= $isTop ? ' top' : '' ?><?= $isSize ? ' size' : '' ?>">
                                <?= $isTop ? '➕' : chipEmoji($d) ?> <?= h($label) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="side">
                    <div class="amount">₹<?= fmt($price[$i]) ?></div>

                    <div class="stepper">
                        <form method="post">
                            <input type="hidden" name="index" value="<?= $i ?>">
                            <input type="hidden" name="action" value="remove">
                            <button type="submit" aria-label="Decrease">−</button>
                        </form>
                        <span><?= (int) $qty[$i] ?></span>
                        <form method="post">
                            <input type="hidden" name="index" value="<?= $i ?>">
                            <input type="hidden" name="action" value="add">
                            <button type="submit" aria-label="Increase">+</button>
                        </form>
                    </div>

                    <form method="post">
                        <input type="hidden" name="index" value="<?= $i ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="del">Remove</button>
                    </form>
                </div>
            </article>
            <?php endfor; ?>
        </section>

        <aside class="bill">
            <span class="tag">YOUR BILL</span>
            <h2>Order summary</h2>
            <div class="cut"></div>

            <div class="row"><span>Items (<?= (int) $totalQty ?>)</span><b>₹<?= fmt($total) ?></b></div>
            <div class="row minus"><span>Discount (20%)</span><b>− ₹<?= fmt($discount) ?></b></div>
            <div class="row"><span>Tax (20%)</span><b>₹<?= fmt($tax) ?></b></div>

            <div class="pay"><span>PAYABLE</span><strong>₹<?= fmt($netAmount) ?></strong></div>

            <form action="payment.php" method="post" onsubmit="return checkCart()">
                <button type="submit" class="btn red">Proceed to Checkout</button>
            </form>
            <a class="btn ghost" href="menu.php">← Back to menu</a>
            <p class="note">Tax is calculated after discount</p>
        </aside>

    <?php endif; ?>
    </div>
</main>

<script>
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

    // Mobile hamburger menu
    const toggle = document.getElementById("navToggle");
    const links = document.getElementById("navLinks");
    if (toggle && links) {
        toggle.addEventListener("click", function () {
            links.classList.toggle("open");
        });
    }
});

function checkCart() {
    var count = <?= (int) count($items) ?>;
    if (count === 0) {
        alert("Your cart is empty!");
        return false;
    }
    return true;
}
</script>
</body>
</html>