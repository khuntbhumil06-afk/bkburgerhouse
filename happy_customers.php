<?php
session_start();
include "config.php";

$q = mysqli_query($conn, "SELECT * FROM stats WHERE id=1");
$row = $q ? mysqli_fetch_assoc($q) : null;
$happy_customers = $row ? (int)$row['happy_customers'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Happy Customers</title>
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
            display: flex;
            flex-direction: column;
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

        /* ---------- MAIN ---------- */
        .page {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px 70px;
        }

        .ticket {
            position: relative;
            width: 100%;
            max-width: 520px;
            text-align: center;
            background: #fff3d6;
            color: #2b2118;
            border-radius: 28px;
            padding: 40px 34px 34px;
            box-shadow: 0 25px 60px rgba(0,0,0,.6);
        }

        .ticket::before,
        .ticket::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 36px;
            height: 36px;
            margin-top: -18px;
            border-radius: 50%;
            background: var(--bg);
        }

        .ticket::before { left: -18px; }
        .ticket::after  { right: -18px; }

        .emoji {
            width: 92px;
            height: 92px;
            margin: 0 auto 14px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            background: var(--gold);
            border: 5px dashed #2b2118;
            animation: pop 3s ease-in-out infinite;
        }

        @keyframes pop {
            0%, 100% { transform: rotate(-6deg) scale(1); }
            50%      { transform: rotate(6deg) scale(1.08); }
        }

        .tag {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 3px;
            color: var(--red);
        }

        .ticket h1 {
            font-family: 'Bebas Neue', sans-serif;
            font-weight: 400;
            font-size: 54px;
            letter-spacing: 2px;
            line-height: 1;
            margin: 4px 0 6px;
        }

        .cut {
            border-top: 2px dashed #c9b88f;
            margin: 20px -34px;
        }

        .count {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(90px, 22vw, 150px);
            line-height: 1;
            letter-spacing: 2px;
            color: transparent;
            -webkit-text-stroke: 3px var(--red);
        }

        .sub {
            margin-top: 4px;
            font-size: 14px;
            font-weight: 600;
            color: #7a6a4f;
        }

        .home-btn {
            display: inline-block;
            margin-top: 26px;
            padding: 15px 34px;
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

        @media (max-width: 520px) {
            .ticket { padding: 32px 22px 28px; }
            .cut { margin: 20px -22px; }
            .ticket h1 { font-size: 44px; }
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

    <div class="page">
        <div class="ticket">
            <div class="emoji">😊</div>
            <span class="tag">CUSTOMER LOVE</span>
            <h1>Happy Customers</h1>

            <div class="cut"></div>

            <div class="count" id="count" data-value="<?php echo $happy_customers; ?>"><?php echo $happy_customers; ?></div>
            <p class="sub">Happy Customers</p>

            <a href="dashboard.php" class="home-btn">🏠 Home</a>
        </div>
    </div>

    <script>
        // Number 0 thi shuru thai ne dhire dhire vadhe (animation)
        (function () {
            const el = document.getElementById("count");
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
        })();
    </script>

</body>
</html>