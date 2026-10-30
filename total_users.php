<?php
session_start();
include "config.php";

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$q = mysqli_query($conn,"SELECT * FROM loginform");
$total = $q ? mysqli_num_rows($q) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Total Users</title>
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
    min-height: 100vh;
    background:
        radial-gradient(circle at 8% -5%, rgba(230,57,70,.28), transparent 38%),
        radial-gradient(circle at 100% 100%, rgba(255,183,3,.14), transparent 40%),
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

.menu a {
    color: #fff;
    font-weight: 500;
    font-size: 17px;
    text-decoration: none;
    transition: color .2s;
}

.menu a:hover {
    color: #ff4d4d;
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

/* ---------- PAGE ---------- */
.wrap {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 20px 90px;
}

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

.total-box {
    display: flex;
    align-items: center;
    gap: 14px;
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: 20px;
    padding: 12px 22px 12px 14px;
}

.total-box .e {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: var(--card2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    box-shadow: inset 0 0 0 2px rgba(255,183,3,.35);
    flex-shrink: 0;
}

.total-box span {
    font-size: 11px;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 700;
}

.total-box strong {
    display: block;
    font-family: 'Bebas Neue', sans-serif;
    font-weight: 400;
    font-size: 38px;
    letter-spacing: 1px;
    line-height: 1.05;
    color: var(--gold);
}

/* ---------- TABLE ---------- */
.table-card {
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 14px 40px rgba(0,0,0,.45);
}

.table-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 520px;
}

.users-table th {
    background: linear-gradient(90deg, var(--red), var(--red2));
    color: #fff;
    white-space: nowrap;
    font-size: 12px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    padding: 14px;
    text-align: left;
}

.users-table td {
    padding: 14px;
    border-bottom: 1px solid var(--line);
    font-size: 14px;
    font-weight: 600;
    vertical-align: middle;
}

.users-table tbody tr:last-child td {
    border-bottom: none;
}

.users-table tbody tr {
    transition: .15s;
}

.users-table tbody tr:hover {
    background: var(--card2);
}

.idpill {
    display: inline-block;
    background: var(--card2);
    border-radius: 999px;
    padding: 3px 12px;
    font-size: 12px;
}

.user {
    display: flex;
    align-items: center;
    gap: 12px;
}

.avatar {
    flex: 0 0 38px;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(160deg, var(--red), var(--red2));
    color: #fff;
    font-weight: 800;
    font-size: 15px;
    text-transform: uppercase;
    box-shadow: 0 0 0 2px var(--gold);
}

.mail {
    color: var(--muted);
    word-break: break-all;
}

/* ---------- EMPTY ---------- */
.empty {
    text-align: center;
    background: var(--card);
    border: 2px dashed #3a2f28;
    border-radius: 24px;
    padding: 56px 20px;
}

.empty .big { font-size: 60px; }

.empty h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-weight: 400;
    font-size: 40px;
    letter-spacing: 1.5px;
    margin-top: 4px;
}

.empty p {
    color: var(--muted);
    margin-top: 4px;
}

/* ---------- HOME BUTTON ---------- */
.btn-row {
    text-align: center;
    margin-top: 34px;
}

.home-btn {
    display: inline-block;
    padding: 15px 36px;
    background: var(--red);
    color: #fff;
    text-decoration: none;
    font-weight: 800;
    font-size: 15px;
    letter-spacing: 1px;
    text-transform: uppercase;
    border-radius: 14px;
    box-shadow: 0 5px 0 var(--red2);
    transition: .15s;
}

.home-btn:hover {
    background: #f1485a;
}

.home-btn:active {
    transform: translateY(4px);
    box-shadow: 0 1px 0 var(--red2);
}

/* ==================== RESPONSIVE ==================== */

@media (max-width: 900px) {
    .header { padding: 12px 28px; }
    .wrap { padding: 0 18px 70px; }
    .hero { padding: 36px 0 24px; }
}

@media (max-width: 768px) {
    .header { padding: 10px 18px; }

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

    .menu a {
        font-size: 18px;
        padding: 12px 0;
        width: 100%;
        border-bottom: 1px solid #222;
    }

    .hero {
        flex-direction: column;
        align-items: flex-start;
        gap: 18px;
        padding: 28px 0 22px;
    }

    .hero h1 {
        font-size: clamp(48px, 14vw, 72px);
    }

    .total-box {
        width: 100%;
        max-width: 280px;
    }

    .table-card { border-radius: 16px; }

    .users-table { min-width: 480px; }

    .users-table th {
        padding: 12px 10px;
        font-size: 11px;
    }

    .users-table td {
        padding: 12px 10px;
        font-size: 13px;
    }

    .empty {
        padding: 40px 16px;
        border-radius: 18px;
    }

    .empty .big { font-size: 48px; }
    .empty h2 { font-size: 32px; }
}

@media (max-width: 480px) {
    .brand { font-size: 16px; gap: 8px; }
    .brand img { width: 32px; height: 32px; }

    .wrap { padding: 0 14px 50px; }

    .hero { padding: 24px 0 18px; }

    .hi {
        font-size: 11px;
        letter-spacing: 2.5px;
    }

    .hero h1 {
        font-size: clamp(42px, 16vw, 58px);
        letter-spacing: 1px;
    }

    .total-box {
        padding: 10px 16px 10px 12px;
        border-radius: 16px;
        max-width: 100%;
    }

    .total-box .e {
        width: 44px;
        height: 44px;
        font-size: 20px;
    }

    .total-box span { font-size: 10px; }
    .total-box strong { font-size: 30px; }

    .users-table { min-width: 420px; }

    .users-table th {
        padding: 10px 8px;
        font-size: 10px;
    }

    .users-table td {
        padding: 10px 8px;
        font-size: 12px;
    }

    .avatar {
        flex: 0 0 32px;
        width: 32px;
        height: 32px;
        font-size: 13px;
    }

    .idpill {
        font-size: 11px;
        padding: 2px 10px;
    }

    .empty {
        padding: 36px 14px;
        border-radius: 16px;
    }

    .empty .big { font-size: 42px; }
    .empty h2 { font-size: 28px; }
    .empty p { font-size: 13px; }

    .btn-row { margin-top: 28px; }

    .home-btn {
        padding: 13px 28px;
        font-size: 14px;
        width: 100%;
        max-width: 280px;
    }
}

@media (max-width: 360px) {
    .total-box strong { font-size: 26px; }
    .empty h2 { font-size: 24px; }
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
        <a href="dashboard.php">Dashboard</a>
        <a href="sales.php">Sales</a>
        <a href="manage_menu.php">Manage Menu</a>
    </div>
</div>

<div class="wrap">

    <div class="hero">
        <div>
            <p class="hi">Admin panel</p>
            <h1>Total <span>users</span></h1>
        </div>

        <div class="total-box">
            <div class="e">👥</div>
            <div>
                <span>Registered users</span>
                <strong><?php echo (int) $total; ?></strong>
            </div>
        </div>
    </div>

    <?php if ($total == 0): ?>

        <div class="empty">
            <div class="big">👥</div>
            <h2>No users yet</h2>
            <p>Registered users will show up here.</p>
        </div>

    <?php else: ?>

        <div class="table-card">
            <div class="table-scroll">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    while($row=mysqli_fetch_assoc($q)){
                        $uname = (string) $row['username'];
                        $first = mb_strtoupper(mb_substr($uname, 0, 1, 'UTF-8'), 'UTF-8');
                    ?>
                        <tr>
                            <td><span class="idpill">#<?php echo e($row['id']); ?></span></td>
                            <td>
                                <div class="user">
                                    <span class="avatar"><?php echo e($first); ?></span>
                                    <span><?php echo e($uname); ?></span>
                                </div>
                            </td>
                            <td class="mail"><?php echo e($row['email']); ?></td>
                        </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>

    <div class="btn-row">
        <a href="dashboard.php" class="home-btn">🏠 Home</a>
    </div>

</div>

<script>
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
</script>

</body>
</html>