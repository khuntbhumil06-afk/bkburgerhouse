<?php
include 'config.php';

$total_sql = "SELECT SUM(price) as total FROM orders";
$total_result = mysqli_query($conn, $total_sql);
if(!$total_result){
    die("Error: " . mysqli_error($conn));
}
$total = mysqli_fetch_assoc($total_result)['total'] ?? 0;

$today_sql = "SELECT SUM(price) as today_total 
              FROM orders 
              WHERE DATE(order_time) = CURDATE()";
$today_result = mysqli_query($conn, $today_sql);
if(!$today_result){
    die("Error: " . mysqli_error($conn));
}
$today = mysqli_fetch_assoc($today_result)['today_total'] ?? 0;

// 1234.5 -> 1,234.50   |   1234 -> 1,234
function money($v)
{
    $s = number_format((float) $v, 2);
    return preg_replace('/\.00$/', '', $s);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earnings Dashboard</title>
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
            --green: #4ade80;
            --blue: #60a5fa;
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
        }

        /* ---------- HEADER (index.php jevo) ---------- */
        .header {
            background: #0b0b0b;
            font-family: 'Poppins', sans-serif;
            padding: 12px 45px;
            min-height: 64px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            text-decoration: none;
            font-size: 20px;
            font-weight: 700;
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
            flex-wrap: wrap;
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

        @media (max-width: 700px) {
            .header { padding: 10px 16px; }
            .menu { gap: 14px; }
            .menu a { font-size: 14px; }
            .brand { font-size: 17px; }
        }

        /* ---------- PAGE ---------- */
        .wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 20px 90px;
        }

        .hero {
            padding: 48px 0 34px;
        }

        .hi {
            color: var(--gold);
            font-weight: 800;
            font-size: 13px;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .hero h1 {
            font-family: 'Bebas Neue', sans-serif;
            font-weight: 400;
            font-size: clamp(54px, 10vw, 96px);
            line-height: 1;
            letter-spacing: 2px;
        }

        .hero h1 span {
            color: transparent;
            -webkit-text-stroke: 2px var(--red);
        }

        /* ---------- CARDS ---------- */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
        }

        .card {
            position: relative;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 28px;
            padding: 28px 28px 30px;
            overflow: hidden;
            box-shadow: 0 16px 44px rgba(0,0,0,.5);
            transition: .3s;
        }

        .card:hover {
            transform: translateY(-8px);
            border-color: var(--gold);
        }

        .card::before {
            content: "";
            position: absolute;
            top: -60px;
            right: -60px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: linear-gradient(160deg, var(--red), var(--red2));
            opacity: .9;
            transition: .3s;
        }

        .card.today::before {
            background: linear-gradient(160deg, #3b82f6, #1e3a8a);
        }

        .card:hover::before {
            transform: scale(1.2);
        }

        .card .ico {
            position: relative;
            z-index: 1;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--card2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            box-shadow: inset 0 0 0 2px rgba(255,183,3,.4);
        }

        .card h2 {
            position: relative;
            z-index: 1;
            margin-top: 22px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--muted);
        }

        .card h3 {
            position: relative;
            z-index: 1;
            margin-top: 4px;
            font-family: 'Bebas Neue', sans-serif;
            font-weight: 400;
            font-size: clamp(56px, 12vw, 84px);
            line-height: 1;
            letter-spacing: 1px;
            color: var(--green);
            word-break: break-all;
        }

        .card.today h3 {
            color: var(--blue);
        }

        .card .note {
            position: relative;
            z-index: 1;
            margin-top: 10px;
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 999px;
            color: var(--green);
            background: rgba(74,222,128,.12);
        }

        .card.today .note {
            color: var(--blue);
            background: rgba(96,165,250,.14);
        }

        /* ---------- HOME BUTTON ---------- */
        .btn-row {
            text-align: center;
            margin-top: 40px;
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
    </style>
</head>
<body>

<div class="header">
    <a href="index.php" class="brand">
        <img src="Assets/logo.png" alt="logo">
        <span>B K's Burger House</span>
    </a>

    <div class="menu">
        <a href="index.php">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="sales.php">Sales</a>
        <a href="manage_menu.php">Manage Menu</a>
    </div>
</div>

<div class="wrap">

    <div class="hero">
        <p class="hi">Admin panel</p>
        <h1>Earnings <span>dashboard</span></h1>
    </div>

    <div class="cards">
        <div class="card">
            <div class="ico">💰</div>
            <h2>Total Earnings</h2>
            <h3>₹<span class="num" data-value="<?php echo (float) $total; ?>"><?php echo money($total); ?></span></h3>
            <span class="note">All time</span>
        </div>

        <div class="card today">
            <div class="ico">📅</div>
            <h2>Today's Earnings</h2>
            <h3>₹<span class="num" data-value="<?php echo (float) $today; ?>"><?php echo money($today); ?></span></h3>
            <span class="note">Today</span>
        </div>
    </div>

    <div class="btn-row">
        <a href="dashboard.php" class="home-btn">🏠 Home</a>
    </div>

</div>

<script>
    // Number 0 thi dhire dhire vadhine asli value par aave (animation)
    document.querySelectorAll(".num").forEach(function (el) {
        const target = parseFloat(el.dataset.value) || 0;
        if (target <= 0) return;

        const finalText = el.textContent;
        const hasDecimal = (target % 1) !== 0;
        const duration = 1300;
        const start = performance.now();

        function fmt(n) {
            return hasDecimal
                ? n.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                : Math.round(n).toLocaleString("en-US");
        }

        function tick(now) {
            const t = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - t, 3);
            el.textContent = t < 1 ? fmt(target * eased) : finalText;
            if (t < 1) requestAnimationFrame(tick);
        }

        el.textContent = "0";
        requestAnimationFrame(tick);
    });
</script>

</body>
</html>