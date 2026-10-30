<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: login.php");
    exit();
}
if ($_SESSION["admin"] == 'NO') {
    header("location: index.php");
    exit();
}

require_once "config.php";

date_default_timezone_set("Asia/Kolkata");
$current_time = date("Y-m-d H:i:s");

// Move orders to the next status after 30 seconds
mysqli_query($conn, "
    UPDATE orders
    SET details='Out for Delivery', status_time='$current_time'
    WHERE details='Confirmed'
    AND TIMESTAMPDIFF(SECOND, status_time, '$current_time') >= 30
");

mysqli_query($conn, "
    UPDATE orders
    SET details='Delivered', status_time='$current_time'
    WHERE details='Out for Delivery'
    AND TIMESTAMPDIFF(SECOND, status_time, '$current_time') >= 30
");

// Most recent order delivered in the last 5 seconds (for the success banner)
$check = mysqli_query($conn, "
    SELECT * FROM orders
    WHERE details='Delivered'
    AND TIMESTAMPDIFF(SECOND, status_time, '$current_time') <= 5
    ORDER BY id DESC LIMIT 1
");
$data = $check ? mysqli_fetch_assoc($check) : null;

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function countOrders($conn, $status)
{
    $safe = mysqli_real_escape_string($conn, $status);
    $r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM orders WHERE details='$safe'");
    $row = $r ? mysqli_fetch_assoc($r) : null;
    return (int) ($row['c'] ?? 0);
}

// "Coke, Veg Wrap, " -> chips  (last comma/space ignore)
function chips($text, $prefix = '')
{
    $parts = array_values(array_filter(array_map('trim', explode(',', (string) $text)), 'strlen'));
    if (!$parts) {
        return e($text);
    }
    $html = '';
    foreach ($parts as $p) {
        $html .= "<span class='chip'>" . $prefix . e($p) . "</span>";
    }
    return $html;
}

function renderOrderTable($conn, $status, $mode)
{
    $safeStatus = mysqli_real_escape_string($conn, $status);
    $results = mysqli_query($conn, "SELECT * FROM orders WHERE details='$safeStatus'");

    if (!$results || mysqli_num_rows($results) == 0) {
        echo "<div class='empty'><span>🍽️</span><p>No Orders!</p></div>";
        return;
    }

    $showActions = ($mode !== 'delivered');

    echo "<div class='table-card'><div class='table-scroll'><table class='orders-table'>";
    echo "<thead><tr>
            <th>ID</th>
            <th>Order ID</th>
            <th>Username</th>
            <th>Full Name</th>
            <th>Item</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Email</th>
            <th>Address</th>
            <th>Status</th>";
    if ($showActions) {
        echo "<th>Actions</th>";
    }
    echo "</tr></thead><tbody>";

    while ($ord = mysqli_fetch_assoc($results)) {
        $delUrl = "deleteOrder.php?order_id=" . urlencode($ord['order_id']);

        switch ($mode) {
            case 'confirmed':
                $statusHtml = "<span class='pill confirmed'>Order Confirmed</span>";
                $actionHtml = "<span class='truck' title='Out for delivery soon'>🚚</span>
                    <a href='" . e($delUrl) . "' class='delete-link' title='Delete order'
                       onclick=\"return confirm('Are you sure you want to delete this order?')\">🗑</a>";
                break;

            case 'out':
                $statusHtml = "<span class='pill out'>Out for Delivery 🚚</span>";
                $actionHtml = "<a href='" . e($delUrl) . "' class='delete-link' title='Delete order'
                       onclick=\"return confirm('Are you sure you want to delete this order?')\">🗑</a>";
                break;

            default: // delivered
                $statusHtml = "<span class='pill delivered'>Order Delivered Successfully</span>";
                $actionHtml = "";
                break;
        }

        echo "<tr>
                <td>" . e($ord['id']) . "</td>
                <td><span class='oid'>#" . e($ord['order_id']) . "</span></td>
                <td>" . e($ord['username']) . "</td>
                <td>" . e($ord['fullname']) . "</td>
                <td class='wide'>" . chips($ord['item']) . "</td>
                <td class='price'>₹" . e($ord['price']) . "</td>
                <td>" . chips($ord['quantity'], '×') . "</td>
                <td>" . e($ord['email']) . "</td>
                <td>" . e($ord['address']) . "</td>
                <td>$statusHtml</td>";
        if ($showActions) {
            echo "<td class='action-cell'>$actionHtml</td>";
        }
        echo "</tr>";
    }

    echo "</tbody></table></div></div>";
}

$cConfirmed = countOrders($conn, 'Confirmed');
$cOut       = countOrders($conn, 'Out for Delivery');
$cDelivered = countOrders($conn, 'Delivered');

// Admin profile photo (na hoy to default)
$adminImg = is_file(__DIR__ . "/uploads/bk.png") ? "uploads/bk.png" : "uploads/default.png";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Burger Ordering System</title>
    <link rel="icon" href="Assets/logo.png">
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
        .wrap { max-width: 1250px; margin: 0 auto; padding: 0 20px; }

        /* ---------- NAV ---------- */
        .nav {
            background: #0b0b0b;
            font-family: 'Poppins', sans-serif;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .nav .bar {
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
        .links > a.active { color: var(--gold); }
        .profile { position: relative; margin-left: 4px; }
        .profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            display: block;
            background: var(--card2);
        }
        .dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 52px;
            min-width: 150px;
            z-index: 2000;
            background: var(--red);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,.5);
        }
        .dropdown a {
            display: block;
            padding: 11px 16px;
            text-decoration: none;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
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
        .hero { padding: 40px 0 24px; }
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
        .live {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 14px;
            font-size: 12px;
            font-weight: 700;
            color: #4ade80;
            background: rgba(74,222,128,.12);
            padding: 5px 14px;
            border-radius: 999px;
        }
        .live i {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
            animation: blink 1.4s infinite;
        }
        @keyframes blink { 50% { opacity: .25; } }

        /* ---------- BANNER ---------- */
        .success-banner {
            margin: 0 0 20px;
            padding: 14px 22px;
            border-radius: 16px;
            text-align: center;
            font-weight: 800;
            font-size: 16px;
            color: #052e16;
            background: linear-gradient(90deg, #4ade80, #bbf7d0);
            box-shadow: 0 10px 30px rgba(74,222,128,.25);
        }

        /* ---------- STATS ---------- */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            margin-bottom: 10px;
        }
        .stat {
            display: flex;
            align-items: center;
            gap: 16px;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 16px 20px;
        }
        .stat .e {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: var(--card2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: inset 0 0 0 2px rgba(255,183,3,.35);
            flex-shrink: 0;
        }
        .stat span {
            font-size: 11px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 700;
        }
        .stat strong {
            display: block;
            font-family: 'Bebas Neue', sans-serif;
            font-weight: 400;
            font-size: 40px;
            letter-spacing: 1px;
            line-height: 1.05;
        }

        /* ---------- SECTIONS ---------- */
        .section { margin-top: 38px; }
        .sec-head {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 14px;
        }
        .sec-head h3 {
            font-family: 'Bebas Neue', sans-serif;
            font-weight: 400;
            font-size: 42px;
            letter-spacing: 2px;
        }
        .sec-head .count {
            background: var(--card2);
            border: 1px solid var(--line);
            color: var(--gold);
            font-weight: 800;
            font-size: 13px;
            padding: 4px 14px;
            border-radius: 999px;
            flex-shrink: 0;
        }
        .sec-head .dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .dot.confirmed { background: #fb923c; box-shadow: 0 0 0 5px rgba(251,146,60,.2); }
        .dot.out { background: #60a5fa; box-shadow: 0 0 0 5px rgba(96,165,250,.2); }
        .dot.delivered { background: #4ade80; box-shadow: 0 0 0 5px rgba(74,222,128,.2); }
        .sec-head::after {
            content: "";
            flex: 1;
            height: 2px;
            background: repeating-linear-gradient(90deg, #3a2f28 0 10px, transparent 10px 18px);
        }

        .table-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 14px 40px rgba(0,0,0,.4);
        }
        .table-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 980px;
        }
        .orders-table th {
            background: linear-gradient(90deg, var(--red), var(--red2));
            color: #fff;
            white-space: nowrap;
            font-size: 12px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 14px 14px;
            text-align: left;
        }
        .orders-table td {
            padding: 14px;
            border-bottom: 1px solid var(--line);
            font-size: 14px;
            font-weight: 600;
            vertical-align: middle;
        }
        .orders-table tbody tr:last-child td { border-bottom: none; }
        .orders-table tbody tr { transition: .15s; }
        .orders-table tbody tr:hover { background: var(--card2); }
        .orders-table td.wide { min-width: 200px; }
        .orders-table td.price {
            color: var(--gold);
            font-weight: 800;
            white-space: nowrap;
        }
        .oid {
            display: inline-block;
            background: var(--card2);
            border-radius: 999px;
            padding: 3px 12px;
            font-size: 12px;
            white-space: nowrap;
        }
        .chip {
            display: inline-block;
            background: var(--card2);
            border-radius: 999px;
            padding: 3px 11px;
            font-size: 12px;
            margin: 2px 4px 2px 0;
        }

        .pill {
            display: inline-block;
            font-size: 12px;
            font-weight: 800;
            padding: 6px 14px;
            border-radius: 999px;
            line-height: 1.25;
        }
        .pill.confirmed { color: #fdba74; background: rgba(251,146,60,.15); }
        .pill.out { color: #93c5fd; background: rgba(96,165,250,.15); }
        .pill.delivered { color: #86efac; background: rgba(74,222,128,.15); }

        .action-cell { white-space: nowrap; }
        .truck { margin-right: 8px; font-size: 18px; }
        .delete-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(230,57,70,.15);
            text-decoration: none;
            font-size: 16px;
            transition: .2s;
        }
        .delete-link:hover {
            background: var(--red);
            transform: scale(1.1);
        }

        .empty {
            text-align: center;
            background: var(--card);
            border: 2px dashed #3a2f28;
            border-radius: 22px;
            padding: 30px 20px;
        }
        .empty span { font-size: 34px; }
        .empty p {
            color: var(--muted);
            font-size: 16px;
            font-weight: 600;
            margin-top: 4px;
        }

        .bottom { height: 80px; }

        /* ==================== RESPONSIVE ==================== */

        /* Tablets */
        @media (max-width: 900px) {
            .nav .bar { padding: 12px 28px; }
            .wrap { padding: 0 16px; }
            .stats {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }
            .sec-head h3 { font-size: 34px; }
        }

        /* Mobile landscape / large phones */
        @media (max-width: 768px) {
            .nav .bar {
                padding: 10px 18px;
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
                padding: 28px 0 20px;
            }

            .hero h1 {
                font-size: clamp(48px, 14vw, 72px);
            }

            .success-banner {
                font-size: 14px;
                padding: 12px 16px;
                border-radius: 12px;
            }

            .stats {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .stat {
                padding: 14px 16px;
                border-radius: 16px;
            }

            .stat .e {
                width: 48px;
                height: 48px;
                font-size: 22px;
            }

            .stat strong {
                font-size: 34px;
            }

            .section { margin-top: 28px; }

            .sec-head {
                gap: 10px;
            }

            .sec-head h3 {
                font-size: 28px;
                letter-spacing: 1px;
            }

            .sec-head .count {
                font-size: 12px;
                padding: 3px 11px;
            }

            .sec-head::after {
                display: none;
            }

            .table-card {
                border-radius: 16px;
            }

            .orders-table {
                min-width: 900px;
            }

            .orders-table th {
                padding: 12px 10px;
                font-size: 11px;
            }

            .orders-table td {
                padding: 12px 10px;
                font-size: 13px;
            }

            .empty {
                padding: 24px 16px;
                border-radius: 16px;
            }
        }

        /* Small phones */
        @media (max-width: 480px) {
            .brand { font-size: 16px; gap: 8px; }
            .brand img { width: 32px; height: 32px; }

            .wrap { padding: 0 14px; }

            .hero {
                padding: 24px 0 16px;
            }

            .hi {
                font-size: 11px;
                letter-spacing: 2.5px;
            }

            .hero h1 {
                font-size: clamp(42px, 16vw, 58px);
                letter-spacing: 1px;
            }

            .live {
                font-size: 11px;
                padding: 4px 12px;
            }

            .success-banner {
                font-size: 13px;
                padding: 11px 14px;
            }

            .stat {
                padding: 12px 14px;
            }

            .stat .e {
                width: 44px;
                height: 44px;
                font-size: 20px;
            }

            .stat span {
                font-size: 10px;
            }

            .stat strong {
                font-size: 30px;
            }

            .section { margin-top: 24px; }

            .sec-head h3 {
                font-size: 24px;
            }

            .orders-table {
                min-width: 820px;
            }

            .orders-table th {
                padding: 10px 8px;
                font-size: 10px;
            }

            .orders-table td {
                padding: 10px 8px;
                font-size: 12px;
            }

            .pill {
                font-size: 11px;
                padding: 5px 10px;
            }

            .chip {
                font-size: 11px;
                padding: 2px 9px;
            }

            .delete-link {
                width: 32px;
                height: 32px;
                font-size: 14px;
            }

            .empty span { font-size: 28px; }
            .empty p { font-size: 14px; }

            .bottom { height: 50px; }
        }

        /* Extra small */
        @media (max-width: 360px) {
            .sec-head h3 { font-size: 20px; }
            .stat strong { font-size: 26px; }
        }
    </style>
</head>

<body>
    <!-- ---------- NAV ---------- -->
    <nav class="nav">
        <div class="bar">
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
                <a href="dashboard.php">Dashboard</a>
                <a href="manage_menu.php">Manage Menu</a>
                <a href="sales.php" class="active">Sales</a>
                <div class="profile">
                    <img src="<?= e($adminImg) ?>" id="profileBtn" alt="Profile" onerror="this.onerror=null;this.src='Assets/logo.png'">
                    <div class="dropdown" id="dropMenu">
                        <a href="account.php">Account</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="wrap">

        <header class="hero">
            <p class="hi">Admin panel</p>
            <h1>Orders <span>&amp; sales</span></h1>
            <span class="live"><i></i> Live &bull; refreshes every 5 seconds</span>
        </header>

        <?php if ($data): ?>
            <div class="success-banner">
                🎉 Order Delivered Successfully (Order ID: <?php echo e($data['order_id']); ?>)
            </div>
        <?php endif; ?>

        <section class="stats">
            <div class="stat"><div class="e">🕒</div><div><span>Confirmed</span><strong><?= $cConfirmed ?></strong></div></div>
            <div class="stat"><div class="e">🚚</div><div><span>Out for delivery</span><strong><?= $cOut ?></strong></div></div>
            <div class="stat"><div class="e">✅</div><div><span>Delivered</span><strong><?= $cDelivered ?></strong></div></div>
        </section>

        <section class="section">
            <div class="sec-head"><i class="dot confirmed"></i><h3>Confirmed</h3><span class="count"><?= $cConfirmed ?></span></div>
            <?php renderOrderTable($conn, 'Confirmed', 'confirmed'); ?>
        </section>

        <section class="section">
            <div class="sec-head"><i class="dot out"></i><h3>Out for Delivery</h3><span class="count"><?= $cOut ?></span></div>
            <?php renderOrderTable($conn, 'Out for Delivery', 'out'); ?>
        </section>

        <section class="section">
            <div class="sec-head"><i class="dot delivered"></i><h3>Delivered</h3><span class="count"><?= $cDelivered ?></span></div>
            <?php renderOrderTable($conn, 'Delivered', 'delivered'); ?>
        </section>

        <div class="bottom"></div>
    </main>

    <script src="script.js"></script>
    <script>
        /* Hamburger menu */
        const hamburger = document.getElementById("hamburger");
        const navLinks = document.getElementById("navLinks");

        hamburger.addEventListener("click", function () {
            hamburger.classList.toggle("active");
            navLinks.classList.toggle("open");
        });

        navLinks.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                hamburger.classList.remove("active");
                navLinks.classList.remove("open");
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
            const profileBtn = document.getElementById("profileBtn");
            const dropMenu = document.getElementById("dropMenu");

            profileBtn.addEventListener("click", function (e) {
                e.stopPropagation();
                if (window.innerWidth <= 768) {
                    dropMenu.classList.toggle("show");
                } else {
                    dropMenu.style.display = dropMenu.style.display === "block" ? "none" : "block";
                }
            });

            document.addEventListener("click", function () {
                dropMenu.style.display = "none";
                dropMenu.classList.remove("show");
            });
        });
    </script>
</body>

</html>