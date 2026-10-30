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

$img = "uploads/default.png";
$isAdmin = (($_SESSION['admin'] ?? 'NO') === "YES");

if ($isAdmin) {
    $img = "uploads/bk.png";
} else {
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

/* ---------- Orders (sirf customer mate) ---------- */
$orders = [];
if (!$isAdmin) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE username=? ORDER BY id DESC");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $_SESSION['username']);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            $orders[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

$orderIds = [];
$totalQty = 0;
foreach ($orders as $o) {
    $orderIds[$o['order_id']] = true;
    $totalQty += (int) ($o['quantity'] ?? 0);
}
$orderCount = count($orderIds);

$fullname = $_SESSION['fullname'] ?? '';
$email    = $_SESSION['email'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Account - Burger House</title>
<link rel="icon" href="Assets/logo.png">
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
body {
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    color: var(--ink);
    min-height: 100vh;
    background:
        radial-gradient(circle at 8% -5%, rgba(230,57,70,.28), transparent 38%),
        radial-gradient(circle at 100% 60%, rgba(255,183,3,.12), transparent 40%),
        var(--bg);
    background-attachment: fixed;
    overflow-x: hidden;
}
img { max-width: 100%; }
a { color: inherit; }
.wrap { max-width: 1100px; margin: 0 auto; padding: 0 20px; }

/* ---------- NAV ---------- */
.nav { background: #0b0b0b; font-family: 'Poppins', sans-serif; position: sticky; top: 0; z-index: 500; }
.nav .wrap {
    max-width: none; display: flex; align-items: center; justify-content: space-between; gap: 16px;
    padding: 12px 45px; min-height: 64px; flex-wrap: wrap;
}
.brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: #fff; font-family: 'Poppins', sans-serif; font-size: 20px; font-weight: 700; }
.brand img { width: 38px; height: 38px; object-fit: contain; }
.nav-toggle {
    display: none; background: none; border: 0; color: #fff;
    font-size: 30px; line-height: 1; cursor: pointer; padding: 0 4px; margin-left: auto;
}
.links { display: flex; align-items: center; gap: 28px; flex-wrap: wrap; }
.links > a { text-decoration: none; color: #fff; font-family: 'Poppins', sans-serif; font-weight: 500; font-size: 17px; transition: color .2s; }
.links > a:hover { color: #ff4d4d; }
.profile { position: relative; margin-left: 4px; }
.profile img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; cursor: pointer; display: block; }
.dropdown { display: none; position: absolute; right: 0; top: 52px; min-width: 150px; z-index: 200; background: var(--red); border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,.5); }
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
.hero { padding: 44px 0 26px; }
.hi { color: var(--gold); font-weight: 800; font-size: 13px; letter-spacing: 3px; text-transform: uppercase; word-break: break-word; }
.hero h1 { font-family: 'Bebas Neue', sans-serif; font-weight: 400; font-size: clamp(50px, 10vw, 96px); line-height: .9; letter-spacing: 2px; margin-top: 6px; }
.hero h1 span { color: transparent; -webkit-text-stroke: 2px var(--red); }

/* ---------- PROFILE ID CARD ---------- */
.idcard {
    display: grid; grid-template-columns: 280px 1fr;
    background: var(--card); border: 1px solid var(--line); border-radius: 26px; overflow: hidden;
    box-shadow: 0 18px 50px rgba(0,0,0,.5);
}
.idcard > * { min-width: 0; }
.id-left {
    position: relative; text-align: center; padding: 34px 22px;
    background: linear-gradient(160deg, var(--red), var(--red2));
}
.id-left::after {
    content: ""; position: absolute; top: 0; bottom: 0; right: -1px; width: 14px;
    background: radial-gradient(circle at 14px 10px, var(--card) 7px, transparent 7.5px) 0 0 / 14px 20px repeat-y;
}
.avatar {
    width: 150px; height: 150px; margin: 0 auto 16px; border-radius: 50%; overflow: hidden;
    border: 5px solid #fff; box-shadow: 0 0 0 5px var(--gold), 0 14px 30px rgba(0,0,0,.45);
}
.avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.id-left h2 { font-family: 'Bebas Neue', sans-serif; font-weight: 400; font-size: 34px; letter-spacing: 1.5px; line-height: 1.05; word-break: break-word; }
.id-left .un { font-size: 14px; color: rgba(255,255,255,.85); margin-top: 2px; word-break: break-word; }
.role { display: inline-block; margin-top: 14px; font-size: 12px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; background: #fff; color: var(--red2); padding: 6px 16px; border-radius: 999px; }

.id-right { padding: 30px 34px; display: flex; flex-direction: column; justify-content: center; gap: 14px; }
.row { display: flex; align-items: center; gap: 16px; background: var(--card2); border-radius: 16px; padding: 14px 18px; }
.row > div:last-child { min-width: 0; }
.row .ico {
    flex: 0 0 46px; height: 46px; border-radius: 50%; background: var(--card);
    display: flex; align-items: center; justify-content: center; font-size: 19px; color: var(--gold);
    box-shadow: inset 0 0 0 2px rgba(255,183,3,.35);
}
.row small { display: block; font-size: 11px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted); font-weight: 700; }
.row b { display: block; font-size: 16px; font-weight: 700; word-break: break-word; }

/* ---------- STATS ---------- */
.stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(200px, 100%), 1fr)); gap: 14px; margin-top: 22px; }
.stat { display: flex; align-items: center; gap: 16px; background: var(--card); border: 1px solid var(--line); border-radius: 20px; padding: 16px 20px; }
.stat .e { flex: 0 0 54px; height: 54px; border-radius: 50%; background: var(--card2); display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: inset 0 0 0 2px rgba(255,183,3,.35); }
.stat span { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; }
.stat strong { display: block; font-family: 'Bebas Neue', sans-serif; font-weight: 400; font-size: 40px; letter-spacing: 1px; line-height: 1.05; }

/* ---------- ORDERS ---------- */
.orders { margin: 38px 0 80px; }
.sec-title { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
.sec-title h3 { font-family: 'Bebas Neue', sans-serif; font-weight: 400; font-size: 42px; letter-spacing: 2px; }
.sec-title h3 span { color: var(--gold); }
.pill-link { text-decoration: none; font-weight: 800; font-size: 13px; color: var(--gold); border: 2px solid var(--gold); padding: 8px 18px; border-radius: 999px; transition: .2s; }
.pill-link:hover { background: var(--gold); color: #000; }

.table-card { background: var(--card); border: 1px solid var(--line); border-radius: 22px; overflow: hidden; box-shadow: 0 14px 40px rgba(0,0,0,.4); }
.orders-table { width: 100%; border-collapse: collapse; }
.orders-table th {
    background: linear-gradient(90deg, var(--red), var(--red2)); color: #fff;
    font-size: 12px; letter-spacing: 1.5px; text-transform: uppercase; padding: 14px 16px; text-align: left;
}
.orders-table td { padding: 14px 16px; border-bottom: 1px solid var(--line); font-size: 14px; font-weight: 600; word-break: break-word; }
.orders-table tr:last-child td { border-bottom: none; }
.orders-table tbody tr { transition: .15s; }
.orders-table tbody tr:hover { background: var(--card2); }
.orders-table .price { color: var(--gold); font-weight: 800; }
.orders-table .oid { display: inline-block; background: var(--card2); border-radius: 999px; padding: 3px 12px; font-size: 12px; }

.empty { text-align: center; background: var(--card); border: 2px dashed #3a2f28; border-radius: 24px; padding: 54px 20px; }
.empty .big { font-size: 60px; }
.empty h4 { font-family: 'Bebas Neue', sans-serif; font-weight: 400; font-size: 38px; letter-spacing: 1.5px; margin-top: 4px; }
.empty p { color: var(--muted); margin: 6px 0 22px; }
.btn { display: inline-block; text-decoration: none; cursor: pointer; border: none; font-family: inherit; font-weight: 800; font-size: 14px; padding: 13px 28px; border-radius: 999px; background: var(--gold); color: #000; box-shadow: 0 4px 0 #b37f00; }
.btn:active { transform: translateY(3px); box-shadow: 0 1px 0 #b37f00; }

/* ---------- RESPONSIVE ---------- */
@media (max-width: 900px), (hover: none) {
    body { background-attachment: scroll; }
}
@media (max-width: 900px) {
    .hero { padding: 30px 0 20px; }
    .orders { margin: 30px 0 50px; }

    /* Table -> cards */
    .orders-table thead { display: none; }
    .orders-table, .orders-table tbody, .orders-table tr, .orders-table td { display: block; width: 100%; }
    .orders-table tr { padding: 12px 16px; border-bottom: 1px solid var(--line); }
    .orders-table td { display: flex; justify-content: space-between; gap: 12px; border: none; padding: 5px 0; text-align: right; }
    .orders-table td::before { content: attr(data-label); flex: 0 0 auto; color: var(--muted); font-size: 11px; letter-spacing: 1.2px; text-transform: uppercase; font-weight: 800; text-align: left; }
}
@media (max-width: 800px) {
    .idcard { grid-template-columns: 1fr; }
    .id-left::after { display: none; }
    .id-left { padding: 28px 20px; }
    .id-right { padding: 22px 18px; }
}
@media (max-width: 480px) {
    .avatar { width: 120px; height: 120px; }
    .id-left h2 { font-size: 28px; }
    .row { padding: 12px 14px; gap: 12px; }
    .row .ico { flex-basis: 40px; height: 40px; font-size: 17px; }
    .row b { font-size: 15px; }
    .stat { padding: 14px 16px; gap: 12px; }
    .stat strong { font-size: 34px; }
    .sec-title h3 { font-size: 34px; }
    .empty { padding: 40px 16px; }
    .empty h4 { font-size: 32px; }
}
</style>
</head>
<body>

<!-- ---------- NAV ---------- -->
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
        <p class="hi">Hi, <?= h($fullname !== '' ? $fullname : $_SESSION['username']) ?> 👋</p>
        <h1>My <span>account</span></h1>
    </header>

    <!-- ---------- PROFILE ID CARD ---------- -->
    <section class="idcard">
        <div class="id-left">
            <div class="avatar"><img src="<?= h($img) ?>" alt="Profile Image"></div>
            <h2><?= h($fullname !== '' ? $fullname : $_SESSION['username']) ?></h2>
            <p class="un">@<?= h($_SESSION['username']) ?></p>
            <span class="role"><?= $isAdmin ? '⭐ Admin' : '🍔 Customer' ?></span>
        </div>

        <div class="id-right">
            <div class="row">
                <div class="ico"><i class="bi bi-person-fill"></i></div>
                <div><small>Full Name</small><b><?= h($fullname) ?></b></div>
            </div>
            <div class="row">
                <div class="ico"><i class="bi bi-at"></i></div>
                <div><small>Username</small><b><?= h($_SESSION['username']) ?></b></div>
            </div>
            <div class="row">
                <div class="ico"><i class="bi bi-envelope-fill"></i></div>
                <div><small>Email</small><b><?= h($email) ?></b></div>
            </div>
        </div>
    </section>

    <?php if (!$isAdmin): ?>

        <section class="stats">
            <div class="stat"><div class="e">🧾</div><div><span>Total orders</span><strong><?= (int) $orderCount ?></strong></div></div>
            <div class="stat"><div class="e">🍔</div><div><span>Items ordered</span><strong><?= (int) $totalQty ?></strong></div></div>
        </section>

        <!-- ---------- ORDERS ---------- -->
        <section class="orders">
            <div class="sec-title">
                <h3>My <span>orders</span></h3>
                <?php if ($orders): ?><a class="pill-link" href="menu.php">+ Order more</a><?php endif; ?>
            </div>

            <?php if (!$orders): ?>
                <div class="empty">
                    <div class="big">🛵</div>
                    <h4>No orders found</h4>
                    <p>You have not placed any order yet. Your orders will show up here.</p>
                    <a class="btn" href="menu.php">Browse the menu</a>
                </div>
            <?php else: ?>
                <div class="table-card">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Order ID</th>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Address</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orders as $row): ?>
                            <tr>
                                <td data-label="ID"><?= h($row['id']) ?></td>
                                <td data-label="Order ID"><span class="oid">#<?= h($row['order_id']) ?></span></td>
                                <td data-label="Item"><?= h($row['item']) ?></td>
                                <td data-label="Price" class="price">₹<?= h($row['price']) ?></td>
                                <td data-label="Qty"><?= h($row['quantity']) ?></td>
                                <td data-label="Address"><?= h($row['address']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

    <?php else: ?>
        <section class="orders">
            <div class="empty">
                <div class="big">⭐</div>
                <h4>Admin account</h4>
                <p>Orders and sales are managed from the admin panel.</p>
                <a class="btn" href="sales.php">Go to sales</a>
            </div>
        </section>
    <?php endif; ?>

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
</script>
</body>
</html>