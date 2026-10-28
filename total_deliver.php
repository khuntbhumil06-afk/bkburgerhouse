<?php
include 'config.php';

$sql = "SELECT COUNT(*) as total_orders FROM orders";
$result = mysqli_query($conn, $sql);

if(!$result){
    die("Error: " . mysqli_error($conn));
}

$total_orders = mysqli_fetch_assoc($result)['total_orders'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Dashboard</title>
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

        /* ---------- CARD ---------- */
        .cards {
            display: grid;
            grid-template-columns: minmax(280px, 520px);
        }

        .card {
            position: relative;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 28px;
            padding: 30px 30px 32px;
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
            width: 190px;
            height: 190px;
            border-radius: 50%;
            background: linear-gradient(160deg, var(--red), var(--red2));
            opacity: .9;
            transition: .3s;
        }

        .card:hover::before {
            transform: scale(1.2);
        }

        .card .ico {
            position: relative;
            z-index: 1;
            width: 62px;
            height: 62px;
            border-radius: 50%;
            background: var(--card2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
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
            font-size: clamp(70px, 16vw, 110px);
            line-height: 1;
            letter-spacing: 1px;
            color: var(--gold);
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
            color: var(--gold);
            background: rgba(255,183,3,.12);
        }

        /* ---------- HOME BUTTON ---------- */
        .btn-row {
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
        <h1>Orders <span>dashboard</span></h1>
    </div>

    <div class="cards">
        <div class="card">
            <div class="ico">🚚</div>
            <h2>Total Orders Delivered</h2>
            <h3><span class="num" data-value="<?php echo (int) $total_orders; ?>"><?php echo (int) $total_orders; ?></span></h3>
            <span class="note">All orders</span>
        </div>
    </div>

    <div class="btn-row">
        <a href="index.php" class="home-btn">🏠 Home</a>
    </div>

</div>

<script>
    // Number 0 thi dhire dhire vadhine asli value par aave (animation)
    document.querySelectorAll(".num").forEach(function (el) {
        const target = parseInt(el.dataset.value, 10) || 0;
        if (target <= 0) return;

        const duration = 1200;
        const start = performance.now();

        function tick(now) {
            const t = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - t, 3);
            el.textContent = Math.round(target * eased);
            if (t < 1) requestAnimationFrame(tick);
        }

        el.textContent = "0";
        requestAnimationFrame(tick);
    });
</script>

</body>
</html>