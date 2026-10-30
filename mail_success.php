<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subscribed Successfully - B K's Burger House</title>
<link rel="icon" href="Assets/logo.png">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --bg: #0f0d0c;
    --card: #1a1613;
    --ink: #fff7ec;
    --muted: #a39384;
    --red: #e63946;
    --red2: #9d0208;
    --gold: #ffb703;
    --green: #22c55e;
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
        radial-gradient(circle at 20% 20%, rgba(230,57,70,.25), transparent 45%),
        radial-gradient(circle at 80% 80%, rgba(255,183,3,.15), transparent 40%),
        var(--bg);
    background-attachment: fixed;
    padding: 20px;
    overflow: hidden;
}

.container {
    width: 100%;
    max-width: 440px;
    text-align: center;
    position: relative;
    z-index: 2;
}

.card {
    background: var(--card);
    border: 1px solid #2f2620;
    border-radius: 28px;
    padding: 48px 36px 40px;
    box-shadow: 0 25px 60px rgba(0,0,0,.55);
    animation: fadeIn .8s ease;
}

.emoji {
    font-size: 64px;
    margin-bottom: 16px;
    animation: pop .5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-weight: 400;
    font-size: 48px;
    letter-spacing: 2px;
    color: var(--gold);
    margin-bottom: 10px;
    line-height: 1;
}

p {
    color: var(--muted);
    font-size: 15px;
    line-height: 1.6;
    margin-bottom: 6px;
}

.redirect {
    margin-top: 24px;
    font-size: 13px;
    color: var(--muted);
}

.redirect span {
    color: var(--gold);
    font-weight: 700;
}

/* Confetti */
#confetti-container {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 1;
    overflow: hidden;
}

.confetti {
    position: absolute;
    width: 10px;
    height: 10px;
    top: -20px;
    border-radius: 2px;
    animation: fall linear infinite;
}

@keyframes fall {
    to {
        transform: translateY(110vh) rotate(720deg);
    }
}

@keyframes pop {
    0%   { transform: scale(0); }
    100% { transform: scale(1); }
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

    .emoji {
        font-size: 54px;
        margin-bottom: 12px;
    }

    h1 {
        font-size: 40px;
        letter-spacing: 1px;
    }

    p {
        font-size: 14px;
    }
}

@media (max-width: 380px) {
    .card {
        padding: 30px 18px 28px;
        border-radius: 18px;
    }

    .emoji {
        font-size: 46px;
    }

    h1 {
        font-size: 34px;
    }

    p {
        font-size: 13px;
    }

    .redirect {
        font-size: 12px;
    }
}
</style>
</head>
<body>

<div id="confetti-container"></div>

<div class="container">
    <div class="card">
        <div class="emoji">🎉</div>
        <h1>Success!</h1>
        <p>You have subscribed successfully.</p>
        <p>Thank you for joining B K's Burger House family!</p>

        <p class="redirect">Redirecting to home in <span id="countdown">10</span> seconds...</p>
    </div>
</div>

<audio id="successSound" preload="auto">
    <source src="Assets/sound/success.mp3" type="audio/mpeg">
</audio>

<script>
function createConfetti() {
    const container = document.getElementById("confetti-container");
    const colors = ["#ffb703", "#e63946", "#22c55e", "#00f0ff", "#ff6b6b", "#f9c74f"];

    for (let i = 0; i < 60; i++) {
        const c = document.createElement("div");
        c.classList.add("confetti");

        c.style.left = Math.random() * 100 + "vw";
        c.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        c.style.width = (Math.random() * 8 + 6) + "px";
        c.style.height = (Math.random() * 8 + 6) + "px";
        c.style.animationDuration = (Math.random() * 3 + 2.5) + "s";
        c.style.animationDelay = (Math.random() * 1.5) + "s";
        c.style.opacity = Math.random() * 0.5 + 0.5;
        c.style.borderRadius = Math.random() > 0.5 ? "50%" : "2px";

        container.appendChild(c);
    }
}

window.onload = function () {
    createConfetti();

    const sound = document.getElementById("successSound");

    if (sessionStorage.getItem("playSound") === "true") {
        sound.currentTime = 0;
        sound.play().catch(function () {});
        sessionStorage.removeItem("playSound");
    }

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