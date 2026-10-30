<?php
session_start();
include 'config.php';

if(isset($_SESSION['payment']) && $_SESSION['payment']=="yes"){
    $receipt_pdf = $_SESSION['receipt_pdf'] ?? '';
    $order_id = $_SESSION['order_id'] ?? '';
} else {
    header("location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Success - B K's Burger House</title>
<link rel="icon" href="Assets/logo.png">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --bg: #0f0d0c;
    --card: #1a1613;
    --ink: #fff7ec;
    --muted: #a39384;
    --green: #22c55e;
    --green2: #16a34a;
    --gold: #ffb703;
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
    align-items: center;
    justify-content: center;
    background:
        radial-gradient(circle at 20% 20%, rgba(34,197,94,.18), transparent 45%),
        radial-gradient(circle at 80% 80%, rgba(255,183,3,.12), transparent 40%),
        var(--bg);
    background-attachment: fixed;
    padding: 20px;
    overflow-x: hidden;
}

.container {
    width: 100%;
    max-width: 480px;
    text-align: center;
}

.card {
    background: var(--card);
    border: 1px solid #2f2620;
    border-radius: 28px;
    padding: 48px 40px 42px;
    box-shadow: 0 25px 60px rgba(0,0,0,.55);
    animation: fadeIn .8s ease;
}

.success-circle {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background: linear-gradient(145deg, var(--green), var(--green2));
    margin: 0 auto 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 12px 30px rgba(34,197,94,.35);
    animation: pop .5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.checkmark {
    color: #fff;
    font-size: 70px;
    font-weight: 700;
    line-height: 1;
    animation: tick .6s ease .15s both;
}

h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-weight: 400;
    font-size: 52px;
    letter-spacing: 2px;
    color: var(--green);
    margin-bottom: 12px;
    line-height: 1;
}

p {
    color: var(--muted);
    font-size: 15px;
    line-height: 1.6;
    margin-bottom: 8px;
}

.order-id {
    display: inline-block;
    background: rgba(34,197,94,.12);
    color: var(--green);
    font-weight: 700;
    font-size: 13px;
    padding: 6px 16px;
    border-radius: 999px;
    margin: 12px 0 20px;
}

.download-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--green);
    color: #fff;
    text-decoration: none;
    font-weight: 800;
    font-size: 15px;
    padding: 14px 28px;
    border-radius: 999px;
    margin-top: 10px;
    box-shadow: 0 5px 0 var(--green2);
    transition: .15s;
}

.download-link:hover {
    background: #2dd46a;
    transform: translateY(-2px);
}

.download-link:active {
    transform: translateY(3px);
    box-shadow: 0 1px 0 var(--green2);
}

.redirect {
    margin-top: 28px;
    font-size: 13px;
    color: var(--muted);
}

.redirect span {
    color: var(--gold);
    font-weight: 700;
}

/* Animations */
@keyframes pop {
    0%   { transform: scale(0); }
    100% { transform: scale(1); }
}

@keyframes tick {
    0%   { transform: scale(0) rotate(-45deg); opacity: 0; }
    100% { transform: scale(1) rotate(0); opacity: 1; }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ==================== RESPONSIVE ==================== */

@media (max-width: 520px) {
    body {
        padding: 16px;
    }

    .card {
        padding: 36px 24px 32px;
        border-radius: 22px;
    }

    .success-circle {
        width: 120px;
        height: 120px;
        margin-bottom: 20px;
    }

    .checkmark {
        font-size: 58px;
    }

    h1 {
        font-size: 42px;
        letter-spacing: 1px;
    }

    p {
        font-size: 14px;
    }

    .download-link {
        width: 100%;
        justify-content: center;
        padding: 13px 20px;
        font-size: 14px;
    }
}

@media (max-width: 380px) {
    .card {
        padding: 30px 18px 28px;
        border-radius: 18px;
    }

    .success-circle {
        width: 100px;
        height: 100px;
    }

    .checkmark {
        font-size: 48px;
    }

    h1 {
        font-size: 36px;
    }

    p {
        font-size: 13px;
    }

    .order-id {
        font-size: 12px;
        padding: 5px 12px;
    }
}
</style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="success-circle">
            <span class="checkmark">✓</span>
        </div>

        <h1>Success!</h1>
        <p>We've received your order and payment!<br>Please don't close or move away from this page.</p>

        <?php if (!empty($order_id)): ?>
            <div class="order-id">Order ID: <?= htmlspecialchars($order_id) ?></div>
        <?php endif; ?>

        <?php if ($receipt_pdf && file_exists($receipt_pdf)): ?>
            <a href="<?= htmlspecialchars($receipt_pdf) ?>" download class="download-link">
                📄 Download Your Receipt
            </a>
        <?php endif; ?>

        <p class="redirect">Redirecting to home in <span id="countdown">10</span> seconds...</p>
    </div>
</div>

<audio id="successSound">
    <source src="Assets/sound/success.mp3" type="audio/mpeg">
</audio>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
window.onload = function () {

    // Play success sound
    const sound = document.getElementById("successSound");
    sound.play().catch(function (error) {
        console.log("Autoplay blocked:", error);
    });

    // Confetti burst
    confetti({
        particleCount: 160,
        spread: 90,
        origin: { y: 0.6 }
    });

    // Extra confetti after short delay
    setTimeout(function () {
        confetti({
            particleCount: 80,
            angle: 60,
            spread: 55,
            origin: { x: 0 }
        });
        confetti({
            particleCount: 80,
            angle: 120,
            spread: 55,
            origin: { x: 1 }
        });
    }, 400);

    // Countdown + redirect
    let seconds = 10;
    const countdownEl = document.getElementById("countdown");

    const timer = setInterval(function () {
        seconds--;
        countdownEl.textContent = seconds;

        if (seconds <= 0) {
            clearInterval(timer);
            window.location.href = "index.php";
        }
    }, 1000);
};
</script>
</body>
</html>