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

// ---------- CART HELPERS ----------
// Item cart ma umero. Same naam hoy to qty ane price vadhare, nahi to navi line
function addToCart($name, $qty, $linePrice) {
    $_SESSION['cartItemN'] = $_SESSION['cartItemN'] ?? [];
    $_SESSION['cartItemQ'] = $_SESSION['cartItemQ'] ?? [];
    $_SESSION['cartItemP'] = $_SESSION['cartItemP'] ?? [];

    $pos = array_search($name, $_SESSION['cartItemN'], true);
    if ($pos !== false) {
        $_SESSION['cartItemQ'][$pos] += $qty;
        $_SESSION['cartItemP'][$pos] += $linePrice;
    } else {
        $_SESSION['cartItemN'][] = $name;
        $_SESSION['cartItemQ'][] = $qty;
        $_SESSION['cartItemP'][] = $linePrice;
    }
}

// Cart na badha totals pharithi gano
function recalcCart() {
    $totPrice      = array_sum($_SESSION['cartItemP'] ?? []);
    $discount      = $totPrice * 0.20;
    $afterDiscount = $totPrice - $discount;
    $tax           = $afterDiscount * 0.20;

    $_SESSION['total']         = $totPrice;
    $_SESSION['discount']      = $discount;
    $_SESSION['offerDiscount'] = 0;
    $_SESSION['tax']           = $tax;
    $_SESSION['netAmount']     = $afterDiscount + $tax;
}

// ---------- COMBO CUSTOMISE OPTIONS ----------
// Format: 'Naam' => [extra price, emoji, (size mate description)]
// Image mate: Assets/Store/options/<Naam>.png mukho (jem ke "Peri Peri Fries.png"). Na hoy to emoji dekhase
$sizeOptions = [
    'Regular' => [0,  '🥡', 'Standard portion'],
    'Medium'  => [30, '🍱', 'Bigger portion'],
    'Large'   => [60, '🛍️', 'Max portion'],
];
$friesOptions = [
    'Regular French Fries' => [0,  '🍟'],
    'Peri Peri Fries'      => [20, '🌶️'],
    'Cheese Fries'         => [40, '🧀'],
    'Potato Wedges'        => [30, '🥔'],
];
$drinkOptions = [
    'Coke'         => [0,  '🥤'],
    'Sprite'       => [0,  '🥤'],
    'Fanta'        => [0,  '🧃'],
    'Cold Coffee'  => [30, '☕'],
    'Water Bottle' => [0,  '💧'],
];
$toppingOptions = [
    'Extra Cheese'     => [20, '🧀'],
    'Extra Patty'      => [40, '🍖'],
    'Jalapeños'        => [15, '🌶️'],
    'Extra Mayonnaise' => [10, '🥄'],
    'Extra Veggies'    => [10, '🥬'],
];

// Option nu image shodho (options / drink / premium folder). Na malse to '' aapse
function optionImage($name, $extensions) {
    $names = [$name, strtr($name, ['ñ' => 'n'])];            // Jalapeños -> Jalapenos.png pan chalse
    // Pehla tamara real photos (png/jpg/jpeg/webp) badhi folder ma, pachhi j svg
    foreach ([$extensions, ['svg']] as $exts) {
        foreach (["Assets/Store/options", "Assets/Store/drink", "Assets/Store/premium"] as $dir) {
            foreach ($exts as $ext) {
                foreach ($names as $nm) {
                    $path = $dir . "/" . $nm . "." . $ext;
                    if (is_file(__DIR__ . "/" . $path)) {
                        return implode("/", array_map('rawurlencode', explode("/", $path)));
                    }
                }
            }
        }
    }
    return '';
}

function priceText($x, $free = 'Free') {
    return $x > 0 ? '+₹' . (int)$x : $free;
}

// Ek option tile (image + naam + price). type = radio / checkbox
function tileHtml($type, $inputName, $value, $label, $extra, $imgUrl, $emoji, $checked, $priceText, $attrs = '') {
    $h  = '<label class="tile"' . $attrs . '>';
    $h .= '<input type="' . $type . '" name="' . e($inputName) . '" value="' . e($value) . '" data-extra="' . (float)$extra . '"'
        . ($checked ? ' checked' : '') . ' onchange="calcTotal()">';
    $h .= '<span class="tile-img">' . ($imgUrl !== '' ? '<img src="' . e($imgUrl) . '" alt="">' : e($emoji)) . '</span>';
    $h .= '<span class="tile-name">' . e($label) . '</span>';
    $h .= '<span class="tile-price">' . e($priceText) . '</span>';
    $h .= '<span class="tile-check">✓</span>';
    $h .= '</label>';
    return $h;
}

// Burger list (Veg + Non-Veg) menu mathi. Upgrade charge = potani category na sauthi sasta burger thi vadhare
$burgers    = [];
$minBurger  = null;
foreach ($menu as $bId => $b) {
    if ($b['category'] === 'Veg Burger' || $b['category'] === 'Non-Veg Burger') {
        $burgers[$bId] = $b;
        $pr = (float)$b['price'];
        if ($minBurger === null || $pr < $minBurger) {
            $minBurger = $pr;
        }
    }
}
$burgerExtra = [];
foreach ($burgers as $bId => $b) {
    $burgerExtra[$bId] = max(0, (float)$b['price'] - $minBurger);
}

// Customised combo cart ma add karo (price server par j gane chhe)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'custom_combo') {
    $cid   = (int)($_POST['combo_id'] ?? 0);
    $bid   = (int)($_POST['burger_id'] ?? 0);
    $size  = $_POST['size'] ?? '';
    $fries = $_POST['fries'] ?? '';
    $drink = $_POST['drink'] ?? '';
    $tops  = $_POST['toppings'] ?? [];
    $qty   = max(1, min(20, (int)($_POST['qty'] ?? 1)));
    if (!is_array($tops)) {
        $tops = [];
    }

    $ok = isset($menu[$cid]) && $menu[$cid]['category'] === 'Combo'
          && isset($sizeOptions[$size])
          && isset($friesOptions[$fries])
          && isset($drinkOptions[$drink]);

    $burgerName = '';
    $burgerAdd  = 0;
    if ($ok && $burgers) {
        if (isset($burgers[$bid])) {
            $burgerName = $burgers[$bid]['name'];
            $burgerAdd  = $burgerExtra[$bid];
        } else {
            $ok = false;
        }
    }

    $topNames = [];
    $topExtra = 0;
    if ($ok) {
        foreach ($tops as $t) {
            if (isset($toppingOptions[$t]) && !in_array($t, $topNames, true)) {
                $topNames[] = $t;
                $topExtra  += $toppingOptions[$t][0];
            }
        }
    }

    if ($ok) {
        $unit = (float)$menu[$cid]['price'] + $burgerAdd
              + $sizeOptions[$size][0] + $friesOptions[$fries][0] + $drinkOptions[$drink][0] + $topExtra;

        $parts = [$size];
        if ($burgerName !== '') {
            $parts[] = $burgerName;
        }
        $parts[] = $fries;
        $parts[] = $drink;
        if ($topNames) {
            $parts[] = '+' . implode(', +', $topNames);
        }
        $label = $menu[$cid]['name'] . ' (' . implode(', ', $parts) . ')';

        addToCart($label, $qty, $unit * $qty);
        recalcCart();

        header("location: cart.php");
        exit();
    }
}

// Normal items cart ma add karo (junu cart rahese)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'custom_combo') {
    $qtys = $_POST['qty'] ?? [];
    if (!is_array($qtys)) {
        $qtys = [];
    }

    $added = false;
    foreach ($qtys as $id => $qty) {
        $id  = (int)$id;
        $qty = (int)$qty;

        if ($qty > 0 && isset($menu[$id])) {
            addToCart($menu[$id]['name'], $qty, $qty * (float)$menu[$id]['price']);
            $added = true;
        }
    }

    if ($added) {
        recalcCart();
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
.customise-btn {
    margin-top: 8px;
    background: #ff9800;
    color: #fff;
    border: none;
    border-radius: 20px;
    padding: 6px 15px;
    cursor: pointer;
    transition: 0.3s;
}
.customise-btn:hover {
    background: gold;
    color: black;
}
.empty-msg {
    text-align: center;
    padding: 40px;
    font-size: 18px;
}
.hidden { display: none; }

/* ---------- CUSTOMISE POPUP : "Combo Ticket" design ---------- */
.modal-bg {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.78);
    backdrop-filter: blur(5px);
    z-index: 2000;
    justify-content: center;
    align-items: center;
    padding: 12px;
    box-sizing: border-box;
}
.modal-bg.show { display: flex; }

.cm-box {
    display: grid;
    grid-template-columns: 290px 1fr;
    width: 100%;
    max-width: 920px;
    height: min(660px, 94vh);
    background: #14110f;
    color: #fff;
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 25px 70px rgba(0,0,0,0.85), 0 0 0 1px rgba(255,255,255,0.08);
}

/* ----- Left: order ticket ----- */
.cm-ticket {
    position: relative;
    display: flex;
    flex-direction: column;
    min-height: 0;
    padding: 22px 22px 18px;
    background: #fff3d6;
    color: #2b2118;
    font-family: "Courier New", monospace;
}
.cm-ticket::after {
    content: "";
    position: absolute;
    top: 0; bottom: 0; right: -1px;
    width: 10px;
    background: radial-gradient(circle at 10px 8px, #14110f 6px, transparent 6.5px) 0 0 / 10px 16px repeat-y;
}
.tk-top {
    text-align: center;
    padding-bottom: 12px;
    border-bottom: 2px dashed #c9b88f;
}
.tk-top img {
    width: 118px;
    height: 118px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #fff;
    box-shadow: 0 0 0 3px #e63946, 0 8px 18px rgba(0,0,0,0.25);
}
.tk-top small {
    display: block;
    margin-top: 12px;
    font-size: 10px;
    letter-spacing: 3px;
    color: #e63946;
    font-weight: bold;
}
.tk-top h2 {
    margin: 4px 0 2px;
    font-family: Arial, sans-serif;
    font-size: 18px;
    text-transform: uppercase;
}
.tk-top .cm-base { font-size: 12px; color: #7a6a4f; }

.tk-receipt {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    margin: 8px 0;
}
.rc-line {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    padding: 6px 0;
    font-size: 12px;
    border-bottom: 1px dashed #d8c9a3;
}
.rc-line small {
    display: block;
    font-size: 9px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #9a8a6c;
}
.rc-line b { color: #c1121f; white-space: nowrap; align-self: center; }

.tk-bottom { display: flex; flex-direction: column; gap: 10px; }
.tk-total {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    border-top: 2px solid #2b2118;
    padding-top: 8px;
}
.tk-total .small { font-size: 12px; letter-spacing: 2px; font-weight: bold; }
.tk-total .total { font-size: 30px; font-weight: bold; color: #c1121f; font-family: Arial, sans-serif; }
.cm-qty {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fff;
    border: 2px solid #2b2118;
    border-radius: 30px;
    padding: 3px;
}
.cm-qty button {
    width: 30px;
    height: 30px;
    border: none;
    border-radius: 50%;
    background: #2b2118;
    color: #ffb703;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
}
.cm-qty span { font-weight: bold; font-family: Arial, sans-serif; }
.cm-add {
    background: #e63946;
    color: #fff;
    border: none;
    border-radius: 14px;
    padding: 14px;
    font-family: Arial, sans-serif;
    font-size: 15px;
    font-weight: bold;
    letter-spacing: 1px;
    text-transform: uppercase;
    cursor: pointer;
    box-shadow: 0 5px 0 #9d0208;
    transition: 0.15s;
}
.cm-add:hover { background: #f1485a; }
.cm-add:active { transform: translateY(4px); box-shadow: 0 1px 0 #9d0208; }

/* ----- Right: options ----- */
.cm-main {
    display: flex;
    flex-direction: column;
    min-height: 0;
    background: radial-gradient(circle at 85% -10%, #3a2217 0%, #14110f 55%);
}
.cm-main-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 24px 4px 28px;
}
.cm-main-head h3 {
    margin: 0;
    font-size: 24px;
    letter-spacing: 0.5px;
}
.cm-main-head h3 span { color: #ffb703; }
.cm-main-head small { display: block; font-size: 12px; color: #a99888; margin-top: 2px; }
.cm-close {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 50%;
    background: #2a221d;
    color: #fff;
    font-size: 22px;
    cursor: pointer;
}
.cm-close:hover { background: #e63946; }

.cm-body {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding: 0 24px 22px 28px;
}
.cm-body::-webkit-scrollbar { width: 6px; }
.cm-body::-webkit-scrollbar-thumb { background: #4a3d34; border-radius: 6px; }

.cm-sec { margin-top: 20px; }
.cm-sec h4 {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0 0 12px;
    font-size: 14px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
}
.cm-sec h4 .num {
    font-size: 34px;
    font-weight: 900;
    line-height: 1;
    color: transparent;
    -webkit-text-stroke: 1.5px #ffb703;
}
.cm-sec h4 .num::before { content: "0"; }
.cm-sec h4 em {
    margin-left: auto;
    font-style: normal;
    font-weight: normal;
    font-size: 11px;
    letter-spacing: 0;
    text-transform: none;
    color: #a99888;
}

.tile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(104px, 1fr));
    gap: 10px 8px;
}
.tile {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 6px;
    padding: 8px 4px 10px;
    border-radius: 16px;
    cursor: pointer;
    transition: 0.2s;
}
.tile:hover { background: rgba(255,255,255,0.05); }
.tile input { position: absolute; opacity: 0; pointer-events: none; }
.tile-img {
    width: 78px;
    height: 78px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    background: #221c18;
    border: 3px solid #3a302a;
    overflow: hidden;
    transition: 0.25s;
}
.tile-img img { width: 100%; height: 100%; object-fit: cover; }
.tile-name { font-size: 12px; font-weight: bold; line-height: 1.2; }
.tile-price {
    font-size: 11px;
    padding: 2px 9px;
    border-radius: 20px;
    background: #2a221d;
    color: #ffb703;
}
.tile-check {
    display: none;
    position: absolute;
    top: 4px;
    right: 12px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #ffb703;
    color: #000;
    font-size: 12px;
    font-weight: bold;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.5);
}
.tile:has(input:checked) .tile-img {
    border-color: #ffb703;
    transform: scale(1.08);
    box-shadow: 0 0 0 4px rgba(255,183,3,0.22), 0 10px 22px rgba(255,183,3,0.28);
}
.tile:has(input:checked) .tile-price { background: #ffb703; color: #000; font-weight: bold; }
.tile:has(input:checked) .tile-check { display: flex; }

/* ----- Mobile: ticket niche bar ban jay ----- */
@media (max-width: 720px) {
    .modal-bg { padding: 0; }
    .cm-box {
        grid-template-columns: 1fr;
        grid-template-rows: 1fr auto;
        height: 100vh;
        max-width: none;
        border-radius: 0;
    }
    .cm-main { order: 1; }
    .cm-ticket { order: 2; padding: 12px 14px; }
    .cm-ticket::after, .tk-top, .tk-receipt { display: none; }
    .tk-bottom { flex-direction: row; align-items: center; gap: 10px; }
    .tk-total { border: none; padding: 0; flex-direction: column; align-items: flex-start; }
    .tk-total .total { font-size: 22px; }
    .cm-qty { gap: 2px; }
    .cm-add { flex: 1; padding: 12px 8px; font-size: 13px; }
    .cm-body { padding: 0 14px 16px; }
    .cm-main-head { padding: 14px 14px 4px; }
    .tile-grid { grid-template-columns: repeat(auto-fill, minmax(92px, 1fr)); }
}

/* ========== RESPONSIVE PATCH (mobile + tablet) ========== */
img { max-width: 100%; }
html, body { overflow-x: hidden; }

.nav-toggle {
    display: none;
    background: none;
    border: 0;
    color: #fff;
    font-size: 28px;
    line-height: 1;
    cursor: pointer;
    padding: 0 4px;
}

@media (max-width: 900px), (hover: none) {
    body { background-attachment: scroll; }
}

@media (max-width: 900px) {
    .nav-container { flex-wrap: wrap; gap: 10px; padding: 10px 15px; }
    .brand { font-size: 18px; }
    .brand img { width: 38px; height: 38px; }
    .nav-toggle { display: block; margin-left: auto; }

    .search-box { order: 3; flex: 1 1 100%; max-width: 100%; }

    .nav-right {
        order: 4;
        display: none;
        flex: 1 1 100%;
        flex-direction: column;
        align-items: stretch;
        gap: 4px;
        border-top: 1px solid #333;
        padding-top: 8px;
    }
    .nav-right.open { display: flex; }
    .nav-right > a { padding: 10px 12px; }

    .profile { margin: 6px 0 0; }
    .dropdown { position: static; margin-top: 8px; width: 100%; }

    .category-bar {
        position: static;
        justify-content: flex-start;
        gap: 10px;
        margin: 12px 10px;
    }
    .category-buttons {
        flex: 1;
        overflow-x: auto;
        padding-bottom: 4px;
        scrollbar-width: none;
    }
    .category-buttons::-webkit-scrollbar { display: none; }
    .category-buttons button { flex: 0 0 auto; white-space: nowrap; }
    .cart-icon { margin-left: 0; flex: 0 0 auto; }
}

@media (max-width: 768px) {
    .food-wrapper { overflow: visible; }
    .arrow-btn { display: none; }

    .food-items {
        flex-wrap: wrap;
        justify-content: center;
        overflow-x: visible;
        white-space: normal;
        gap: 14px;
        padding: 10px 12px;
    }
    .food {
        flex: 0 1 calc(50% - 7px);
        min-width: 0;
        width: auto;
        height: auto;
        padding: 10px 10px 14px;
    }
    .food img { height: 160px; }
    .food h3 { font-size: 15px; margin: 8px 0; }
    .food:hover { transform: none; }
    .food:hover img { transform: none; }

    .qty-box button { width: 34px; height: 34px; }
    .add-btn, .wishlist-btn, .customise-btn { padding: 8px 14px; }
}

@media (max-width: 480px) {
    .brand { font-size: 16px; }
    .food { flex-basis: 100%; max-width: 360px; }
    .food img { height: 220px; }
}

@media (max-width: 720px) {
    .cm-box { height: 100vh; height: 100dvh; }
    .cm-sec h4 { flex-wrap: wrap; }
    .cm-qty { min-width: 100px; }
}
@media (max-width: 400px) {
    .tile-grid { grid-template-columns: repeat(3, 1fr); gap: 8px 4px; }
    .tile-img { width: 64px; height: 64px; font-size: 30px; }
    .tile-name { font-size: 11px; }
    .cm-main-head h3 { font-size: 20px; }
    .cm-sec h4 .num { font-size: 28px; }
}
</style>
</head>

<body>

<header class="nav-container">
    <a href="index.php" class="brand">
        <img src="Assets/logo.png" alt="logo">
        <span>B K's Burger House</span>
    </a>
    <button type="button" class="nav-toggle" id="navToggle" aria-label="Menu">☰</button>

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
            $isCombo = ($p['category'] === 'Combo');
        ?>
        <div class="food <?= e($meta['class']) ?>">
            <img src="<?= e($imgPath) ?>" alt="<?= e($p['name']) ?>">
            <h3><?= e($p['name']) ?></h3>
            <div class="price-badge">₹<?= e(money($p['price'])) ?></div>

            <?php if (!$isDrink): ?>
                <div class="rating"><?= ratingHtml(itemRating($p['name'])) ?></div>
            <?php endif; ?>

            <?php if (!$isCombo): ?>
                <div class="qty-box">
                    <input type="hidden" id="qty_<?= $id ?>" name="qty[<?= $id ?>]" value="0">
                    <button type="button" onclick="minus(<?= $id ?>)">-</button>
                    <span id="qty_<?= $id ?>_txt">0</span>
                    <button type="button" onclick="plus(<?= $id ?>)">+</button>
                </div>
                <button type="submit" class="add-btn">Add to Cart</button>
            <?php else: ?>
                <button type="button" class="add-btn"
                        data-id="<?= (int)$id ?>"
                        data-name="<?= e($p['name']) ?>"
                        data-price="<?= e($p['price']) ?>"
                        data-img="<?= e($imgPath) ?>"
                        data-type="<?= (stripos($p['name'], 'non') !== false || stripos($p['name'], 'chicken') !== false) ? 'nonveg' : ((stripos($p['name'], 'veg') !== false) ? 'veg' : 'all') ?>"
                        onclick="openCustomise(this)">Add+</button>
            <?php endif; ?>

            <button type="button" class="wishlist-btn" data-name="<?= e($p['name']) ?>" onclick="addToWishlist(this)">❤️ Wishlist</button>
        </div>
        <?php endforeach; ?>
    </div>

    <button type="button" class="arrow-btn right-btn" onclick="scrollRightBtn()">&#8594;</button>
</div>

<br><br>
</form>

<!-- ---------- CUSTOMISE POPUP (main form ni bahar) ---------- -->
<div class="modal-bg" id="customModal">
  <form method="post" class="cm-box" id="cmForm">
    <input type="hidden" name="action" value="custom_combo">
    <input type="hidden" name="combo_id" id="cm_id">
    <input type="hidden" name="qty" id="cm_qty" value="1">

    <!-- Left: live order ticket -->
    <aside class="cm-ticket">
      <div class="tk-top">
        <img id="cm_img" src="" alt="">
        <small>COMBO ORDER</small>
        <h2 id="cm_name"></h2>
        <span class="cm-base">Base price ₹<span id="cm_base">0</span></span>
      </div>

      <div class="tk-receipt" id="cm_lines"></div>

      <div class="tk-bottom">
        <div class="tk-total">
          <span class="small">TOTAL</span>
          <span class="total">₹<span id="cm_total">0</span></span>
        </div>
        <div class="cm-qty">
          <button type="button" onclick="cmQty(-1)">-</button>
          <span id="cm_qty_txt">1</span>
          <button type="button" onclick="cmQty(1)">+</button>
        </div>
        <button type="submit" class="cm-add">Add to Cart</button>
      </div>
    </aside>

    <!-- Right: options -->
    <div class="cm-main">
      <div class="cm-main-head">
        <div>
          <h3>Build your <span>combo</span></h3>
          <small id="cm_name2"></small>
        </div>
        <button type="button" class="cm-close" onclick="closeCustomise()">&times;</button>
      </div>

      <div class="cm-body">

        <?php if ($burgers): ?>
        <section class="cm-sec">
          <h4><span class="num">1</span> Choose Your Burger <em>Upgrade ma extra charge</em></h4>
          <div class="tile-grid">
            <?php foreach ($burgers as $bId => $b):
                $bx = $burgerExtra[$bId];
                $bImg = itemImage($b['name'], $categoryMeta[$b['category']]['folder'], $imageExtensions);
                echo tileHtml('radio', 'burger_id', $bId, $b['name'], $bx, $bImg, '🍔', false,
                              priceText($bx, 'Free'),
                              ' data-burger="1" data-type="' . ($b['category'] === 'Veg Burger' ? 'veg' : 'nonveg') . '"');
            endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <section class="cm-sec">
          <h4><span class="num">2</span> Choose Your Sides</h4>
          <div class="tile-grid">
            <?php $first = true; foreach ($friesOptions as $n => $o):
                echo tileHtml('radio', 'fries', $n, $n, $o[0], optionImage($n, $imageExtensions), $o[1], $first, priceText($o[0]));
                $first = false;
            endforeach; ?>
          </div>
        </section>

        <section class="cm-sec">
          <h4><span class="num">3</span> Choose Your Drink</h4>
          <div class="tile-grid">
            <?php $first = true; foreach ($drinkOptions as $n => $o):
                echo tileHtml('radio', 'drink', $n, $n, $o[0], optionImage($n, $imageExtensions), $o[1], $first, priceText($o[0], 'Free'));
                $first = false;
            endforeach; ?>
          </div>
        </section>

        <section class="cm-sec">
          <h4><span class="num">4</span> Extra Toppings <em>Ek thi vadhare pasand kari shako</em></h4>
          <div class="tile-grid">
            <?php foreach ($toppingOptions as $n => $o):
                echo tileHtml('checkbox', 'toppings[]', $n, $n, $o[0], optionImage($n, $imageExtensions), $o[1], false, priceText($o[0]));
            endforeach; ?>
          </div>
        </section>

        <section class="cm-sec">
          <h4><span class="num">5</span> Combo Size</h4>
          <div class="tile-grid">
            <?php $first = true; foreach ($sizeOptions as $n => $o):
                echo tileHtml('radio', 'size', $n, $n, $o[0], optionImage('size-' . $n, $imageExtensions), $o[1], $first, priceText($o[0], 'Regular price'));
                $first = false;
            endforeach; ?>
          </div>
        </section>

      </div>
    </div>
  </form>
</div>

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

/* ---------- Customise popup ---------- */
let cmBase = 0;

function openCustomise(btn) {
    cmBase = parseFloat(btn.dataset.price);

    document.getElementById("cmForm").reset();   // fries/drink/size pacha pehla option par, toppings band
    document.getElementById("cm_id").value = btn.dataset.id;
    document.getElementById("cm_name").textContent = btn.dataset.name;
    document.getElementById("cm_img").src = btn.dataset.img;
    document.getElementById("cm_base").textContent = cmBase;
    document.getElementById("cm_qty").value = 1;
    document.getElementById("cm_qty_txt").textContent = 1;
    document.querySelector("#cmForm .cm-body").scrollTop = 0;

    // Combo na type vala burger pehla, pachhi bija. Maximum 6 burger batavo
    const firstTile = document.querySelector("#cmForm .tile[data-burger]");
    if (firstTile) {
        const grid = firstTile.parentElement;
        const tiles = Array.from(grid.querySelectorAll(".tile[data-burger]"));
        tiles.forEach((t, i) => { if (t.dataset.idx === undefined) t.dataset.idx = i; });

        const type = btn.dataset.type;
        const rank = t => (type === "all" || t.dataset.type === type) ? 0 : 1;
        tiles.sort((x, y) => rank(x) - rank(y) || (x.dataset.idx - y.dataset.idx));

        tiles.forEach((t, i) => {
            grid.appendChild(t);
            t.style.display = i < 6 ? "flex" : "none";
            t.querySelector("input").checked = (i === 0);
        });
    }

    calcTotal();
    document.getElementById("customModal").classList.add("show");
}

function closeCustomise() {
    document.getElementById("customModal").classList.remove("show");
}

function cmQty(d) {
    const q = document.getElementById("cm_qty");
    const n = Math.max(1, Math.min(20, parseInt(q.value) + d));
    q.value = n;
    document.getElementById("cm_qty_txt").textContent = n;
    calcTotal();
}

function calcTotal() {
    let extra = 0;
    document.querySelectorAll("#cmForm input[data-extra]:checked").forEach(i => {
        extra += parseFloat(i.dataset.extra) || 0;
    });
    const qty = parseInt(document.getElementById("cm_qty").value);
    document.getElementById("cm_total").textContent = Math.round((cmBase + extra) * qty);
    updateTicket(qty);
}

// Left ticket ma pasand kareli vastu live batavo
function updateTicket(qty) {
    const box = document.getElementById("cm_lines");
    if (!box) return;
    box.textContent = "";

    const addLine = (label, text, price) => {
        const row = document.createElement("div");
        row.className = "rc-line";
        const left = document.createElement("span");
        const sm = document.createElement("small");
        sm.textContent = label;
        left.appendChild(sm);
        left.appendChild(document.createTextNode(text));
        const right = document.createElement("b");
        right.textContent = price;
        row.appendChild(left);
        row.appendChild(right);
        box.appendChild(row);
    };

    const name = document.getElementById("cm_name").textContent;
    document.getElementById("cm_name2").textContent = name;
    addLine("Combo", name, "₹" + cmBase);

    const groups = [["size", "Size"], ["burger_id", "Burger"], ["fries", "Side"], ["drink", "Drink"], ["toppings[]", "Topping"]];
    groups.forEach(g => {
        document.querySelectorAll('#cmForm input[name="' + g[0] + '"]:checked').forEach(i => {
            const text = i.closest(".tile").querySelector(".tile-name").textContent;
            const x = parseFloat(i.dataset.extra) || 0;
            addLine(g[1], text, x > 0 ? "+₹" + x : "Free");
        });
    });
    addLine("Quantity", "× " + qty, "");
}

document.getElementById("customModal").addEventListener("click", function (e) {
    if (e.target === this) closeCustomise();
});

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

    // Mobile hamburger menu
    const navToggle = document.getElementById("navToggle");
    if (navToggle) {
        navToggle.addEventListener("click", function () {
            document.querySelector(".nav-right").classList.toggle("open");
        });
    }
});
</script>

</body>
</html>