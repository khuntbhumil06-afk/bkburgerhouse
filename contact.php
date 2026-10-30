<?php
session_start();
include 'config.php';

$success = "";
$error   = "";

function h($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

// ---------- Contact form (prepared statement, SQL injection safe) ----------
if (isset($_POST['send'])) {
    $name    = htmlspecialchars(trim($_POST['name']    ?? ''));
    $email   = htmlspecialchars(trim($_POST['email']   ?? ''));
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    if ($name === '' || $email === '' || $subject === '' || $message === '') {
        $error = "Please fill all the fields.";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: index.php?msg=sent");
                exit();
            }
            $error = "Sorry, message could not be sent. Please try again.";
            $stmt->close();
        } else {
            $error = "Sorry, something went wrong. Please try again.";
        }
    }
}

// ---------- Profile image ----------
$img = "uploads/default.png";

if (isset($_SESSION['username'])) {
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

$mapLink = "https://www.google.com/maps/place/Ajay's+Sudama+Chowk,+Surat/@21.2394624,72.8443551,5186m/data=!3m1!1e3!4m10!1m2!2m1!1sajay's+burger!3m6!1s0x3be04f838741b843:0x436df813b3079b08!8m2!3d21.239469!4d72.8803962!15sCg1hamF5J3MgYnVyZ2VyWg8iDWFqYXkncyBidXJnZXKSARRmYXN0X2Zvb2RfcmVzdGF1cmFudJoBI0NoWkRTVWhOTUc5blMwVkpRMEZuU1VOUE1TMTZWa3AzRUFF4AEA-gEECAAQSg!16s%2Fg%2F11nc9gy0jb?entry=ttu&g_ep=EgoyMDI2MDMxOC4xIKXMDSoASAFQAw%3D%3D";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Us - Burger House</title>

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
html { scroll-behavior: smooth; }
body {
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    color: var(--ink);
    background:
        radial-gradient(circle at 8% -5%, rgba(230,57,70,.28), transparent 38%),
        radial-gradient(circle at 100% 40%, rgba(255,183,3,.12), transparent 40%),
        var(--bg);
    background-attachment: fixed;
    overflow-x: hidden;
}
a { color: inherit; }
.wrap { max-width: 1100px; margin: 0 auto; padding: 0 20px; }

/* ---------- NAV ---------- */
.nav {
    background: #0b0b0b;
    font-family: 'Poppins', sans-serif;
    position: sticky;
    top: 0;
    z-index: 1000;
}
.nav .wrap {
    max-width: none;
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
    letter-spacing: 0;
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
.profile { position: relative; margin-left: 4px; }
.profile img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: none;
    cursor: pointer;
    display: block;
}
.dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: 52px;
    min-width: 150px;
    background: var(--red);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,.5);
    z-index: 200;
}
.dropdown a {
    display: block;
    padding: 11px 16px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    color: #fff;
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
.hero {
    position: relative;
    padding: 70px 0 54px;
    text-align: center;
}
.hi {
    color: var(--gold);
    font-weight: 800;
    font-size: 13px;
    letter-spacing: 4px;
    text-transform: uppercase;
}
.hero h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-weight: 400;
    font-size: clamp(64px, 14vw, 150px);
    line-height: .88;
    letter-spacing: 3px;
    margin: 8px 0 14px;
}
.hero h1 span {
    color: transparent;
    -webkit-text-stroke: 2px var(--red);
}
.hero p.sub {
    max-width: 520px;
    margin: 0 auto 26px;
    color: var(--muted);
    line-height: 1.6;
    font-size: 16px;
}
.quick {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    cursor: pointer;
    font-family: inherit;
    font-weight: 800;
    font-size: 14px;
    border: none;
    padding: 13px 26px;
    border-radius: 999px;
    transition: .15s;
}
.btn.gold {
    background: var(--gold);
    color: #000;
    box-shadow: 0 4px 0 #b37f00;
}
.btn.ghost {
    border: 2px solid var(--gold);
    color: var(--gold);
    background: transparent;
}
.btn.ghost:hover {
    background: var(--gold);
    color: #000;
}
.btn.gold:active {
    transform: translateY(3px);
    box-shadow: 0 1px 0 #b37f00;
}
.float {
    position: absolute;
    font-size: 54px;
    animation: floaty 5s ease-in-out infinite;
    filter: drop-shadow(0 8px 10px rgba(0,0,0,.5));
    pointer-events: none;
}
.float.f1 { top: 60px; left: 6%; }
.float.f2 { top: 150px; right: 7%; animation-delay: 1.2s; }
.float.f3 { bottom: 30px; left: 14%; animation-delay: 2.1s; font-size: 40px; }
.float.f4 { bottom: 50px; right: 15%; animation-delay: .6s; font-size: 44px; }
@keyframes floaty {
    0%, 100% { transform: translateY(0) rotate(-6deg); }
    50%      { transform: translateY(-16px) rotate(6deg); }
}

/* ---------- MARQUEE ---------- */
.marquee {
    background: var(--red);
    transform: rotate(-1.2deg);
    margin: 10px -20px 0;
    overflow: hidden;
    border-block: 3px solid #000;
}
.marquee div {
    display: inline-block;
    white-space: nowrap;
    padding: 10px 0;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 24px;
    letter-spacing: 3px;
    animation: slide 22s linear infinite;
}
@keyframes slide { to { transform: translateX(-50%); } }

/* ---------- MAIN GRID ---------- */
.grid {
    display: grid;
    grid-template-columns: 1fr 1.15fr;
    gap: 28px;
    padding: 64px 0 40px;
    align-items: start;
}
.stack { display: grid; gap: 16px; }
.info {
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: 22px;
    padding: 20px 22px;
    display: flex;
    gap: 16px;
    transition: .25s;
}
.info:hover {
    transform: translateX(6px);
    border-color: var(--gold);
}
.info .ico {
    flex: 0 0 52px;
    height: 52px;
    border-radius: 50%;
    background: var(--card2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: var(--gold);
    box-shadow: inset 0 0 0 2px rgba(255,183,3,.35);
}
.info h3 {
    font-family: 'Bebas Neue', sans-serif;
    font-weight: 400;
    font-size: 26px;
    letter-spacing: 1.5px;
    margin-bottom: 4px;
}
.info p, .info a {
    color: var(--muted);
    font-size: 14px;
    line-height: 1.6;
    text-decoration: none;
}
.info a:hover { color: var(--gold); }

.status {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 12px;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: 999px;
    margin-bottom: 10px;
}
.status i {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
    animation: blink 1.4s infinite;
}
.status.open { color: #4ade80; background: rgba(74,222,128,.12); }
.status.closed { color: #f87171; background: rgba(248,113,113,.12); }
@keyframes blink { 50% { opacity: .25; } }
.hours { width: 100%; font-size: 14px; border-collapse: collapse; }
.hours td { padding: 6px 0; color: var(--muted); border-bottom: 1px dashed var(--line); }
.hours td:last-child { text-align: right; }
.hours tr.today td { color: var(--gold); font-weight: 800; }

/* ---------- MESSAGE SLIP (form) ---------- */
.slip {
    position: relative;
    background: var(--paper);
    color: var(--paper-ink);
    border-radius: 24px;
    padding: 34px 32px 30px;
    box-shadow: 0 20px 50px rgba(0,0,0,.55);
}
.slip::before, .slip::after {
    content: "";
    position: absolute;
    top: 74px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--bg);
}
.slip::before { left: -14px; }
.slip::after  { right: -14px; }
.slip .tag {
    font-size: 11px;
    letter-spacing: 3px;
    font-weight: 800;
    color: var(--red);
}
.slip h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-weight: 400;
    font-size: 46px;
    letter-spacing: 1.5px;
    line-height: 1;
    margin: 4px 0 18px;
}
.slip .cut {
    border-top: 2px dashed #c9b88f;
    margin: 0 -32px 20px;
}
.field { margin-bottom: 16px; }
.field label {
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #7a6a4f;
    margin-bottom: 4px;
}
.field input, .field textarea {
    width: 100%;
    background: transparent;
    border: none;
    border-bottom: 2px solid #c9b88f;
    padding: 8px 2px;
    font-family: inherit;
    font-size: 15px;
    color: var(--paper-ink);
    outline: none;
    transition: .2s;
    resize: vertical;
}
.field input:focus, .field textarea:focus { border-bottom-color: var(--red); }
.two {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}
.chips {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin: 10px 0 0;
}
.chips button {
    font-family: inherit;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    background: #fff;
    border: 2px solid #e0d1ab;
    color: var(--paper-ink);
    padding: 5px 13px;
    border-radius: 999px;
    transition: .15s;
}
.chips button:hover,
.chips button.on {
    background: var(--red);
    border-color: var(--red);
    color: #fff;
}
.send {
    width: 100%;
    margin-top: 8px;
    border: none;
    cursor: pointer;
    background: var(--red);
    color: #fff;
    font-family: inherit;
    font-weight: 800;
    font-size: 16px;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: 15px;
    border-radius: 14px;
    box-shadow: 0 5px 0 var(--red2);
    transition: .15s;
}
.send:hover { background: #f1485a; }
.send:active {
    transform: translateY(4px);
    box-shadow: 0 1px 0 var(--red2);
}
.alert {
    padding: 11px 14px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 14px;
}
.alert.err { background: #fee2e2; color: #7f1d1d; }
.alert.ok  { background: #dcfce7; color: #14532d; }

/* ---------- MAP ---------- */
.mapsec { padding: 30px 0 70px; }
.mapcard {
    position: relative;
    border-radius: 26px;
    overflow: hidden;
    border: 1px solid var(--line);
    box-shadow: 0 20px 50px rgba(0,0,0,.5);
}
.mapcard iframe {
    display: block;
    width: 100%;
    height: 400px;
    border: 0;
    filter: grayscale(.25) contrast(1.05);
}
.maplabel {
    position: absolute;
    left: 20px;
    bottom: 20px;
    max-width: 290px;
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: 18px;
    padding: 16px 18px;
    box-shadow: 0 10px 30px rgba(0,0,0,.6);
}
.maplabel h4 {
    font-family: 'Bebas Neue', sans-serif;
    font-weight: 400;
    font-size: 24px;
    letter-spacing: 1.5px;
}
.maplabel p {
    font-size: 13px;
    color: var(--muted);
    margin: 4px 0 12px;
    line-height: 1.5;
}
.maplabel .btn { padding: 9px 18px; font-size: 13px; }

/* ---------- FOOTER ---------- */
.site-footer {
    background: #111;
    color: #fff;
    font-family: 'Poppins', sans-serif;
    padding: 48px 45px 56px;
    margin-top: 0;
}
.ft-wrap {
    display: grid;
    grid-template-columns: 1.3fr .9fr 1.45fr 1fr .95fr;
    gap: 34px;
    max-width: 1500px;
    margin: 0 auto;
    align-items: start;
}
.site-footer h3 {
    font-family: 'Poppins', sans-serif;
    font-size: 26px;
    font-weight: 500;
    margin-bottom: 14px;
    color: #fff;
}
.site-footer h5 {
    font-family: 'Poppins', sans-serif;
    font-size: 22px;
    font-weight: 500;
    margin-bottom: 14px;
    color: #fff;
}
.site-footer p {
    font-size: 15px;
    font-weight: 500;
    line-height: 1.65;
    color: #fff;
    margin-bottom: 14px;
}
.site-footer a {
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    transition: color .2s;
}
.site-footer a:hover { color: #ff4d4d; }
.site-footer ul { list-style: none; }
.site-footer li { margin-bottom: 8px; font-size: 17px; }
.ft-social { display: flex; gap: 14px; margin-top: 18px; }
.ft-social a { font-size: 22px; line-height: 1; }
.ft-loc iframe {
    display: block;
    width: 100%;
    height: 150px;
    border: 0;
    border-radius: 4px;
    margin-bottom: 18px;
}
.ft-loc p { margin-bottom: 12px; }
.ft-sub input {
    display: block;
    width: 100%;
    height: 40px;
    padding: 8px 12px;
    margin-bottom: 14px;
    background: #fff;
    color: #222;
    border: 2px solid transparent;
    border-radius: 10px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    outline: none;
    transition: .2s;
}
.ft-sub input:focus { border-color: orange; }
.ft-sub button {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    background: orange;
    color: #fff;
    font-family: 'Poppins', sans-serif;
    font-size: 15px;
    font-weight: 500;
    transition: .2s;
}
.ft-sub button:hover {
    background: darkorange;
    transform: scale(1.03);
}
.ft-sub button:active { transform: scale(.97); }
.ft-sub .error {
    color: red;
    font-size: 13px;
    margin: -8px 0 8px;
    text-align: left;
}

/* ---------- REVEAL ---------- */
.reveal {
    opacity: 0;
    transform: translateY(40px);
    transition: .7s ease;
}
.reveal.show {
    opacity: 1;
    transform: none;
}

/* ==================== RESPONSIVE ==================== */

/* Large tablets / small desktops */
@media (max-width: 1100px) {
    .nav .wrap { padding: 12px 28px; }
    .ft-wrap {
        grid-template-columns: 1fr 1fr 1fr;
        gap: 30px;
    }
}

/* Tablets */
@media (max-width: 900px) {
    .grid {
        grid-template-columns: 1fr;
        padding-top: 50px;
        gap: 24px;
    }
    .float { display: none; }
    .hero { padding: 50px 0 40px; }
    .slip h2 { font-size: 40px; }
}

/* Mobile landscape / large phones */
@media (max-width: 768px) {
    .nav .wrap {
        padding: 10px 18px;
        position: relative;
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

    .hero h1 { font-size: clamp(52px, 16vw, 90px); }
    .hero p.sub { font-size: 15px; padding: 0 10px; }
    .btn { padding: 12px 22px; font-size: 13px; }

    .marquee div { font-size: 20px; }

    .info {
        padding: 18px;
        gap: 14px;
    }
    .info .ico {
        flex: 0 0 46px;
        height: 46px;
        font-size: 20px;
    }
    .info h3 { font-size: 22px; }

    .slip {
        padding: 28px 22px 24px;
    }
    .slip::before, .slip::after { display: none; }
    .slip h2 { font-size: 36px; }
    .slip .cut { margin: 0 -22px 18px; }

    .two {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .mapcard iframe { height: 320px; }
    .maplabel {
        position: static;
        max-width: none;
        border-radius: 0;
        border: none;
        border-top: 1px solid var(--line);
    }

    .site-footer { padding: 40px 24px 48px; }
    .ft-wrap {
        grid-template-columns: 1fr 1fr;
        gap: 28px;
    }
}

/* Small phones */
@media (max-width: 560px) {
    .brand { font-size: 16px; gap: 8px; }
    .brand img { width: 32px; height: 32px; }

    .hero { padding: 40px 0 32px; }
    .hi { font-size: 11px; letter-spacing: 3px; }
    .hero h1 {
        font-size: clamp(48px, 18vw, 72px);
        letter-spacing: 1px;
    }
    .hero p.sub {
        font-size: 14px;
        margin-bottom: 20px;
    }
    .quick { gap: 10px; }
    .btn {
        padding: 11px 18px;
        font-size: 13px;
        width: 100%;
        max-width: 260px;
        justify-content: center;
    }

    .marquee {
        margin: 8px -10px 0;
        transform: rotate(-0.8deg);
    }
    .marquee div { font-size: 18px; padding: 8px 0; }

    .grid { padding: 40px 0 30px; gap: 20px; }

    .info {
        padding: 16px;
        border-radius: 16px;
    }
    .info:hover { transform: none; }
    .info h3 { font-size: 20px; }
    .info p, .info a { font-size: 13px; }

    .slip {
        padding: 24px 18px 22px;
        border-radius: 18px;
    }
    .slip h2 { font-size: 32px; }
    .slip .cut { margin: 0 -18px 16px; }
    .field label { font-size: 10px; }
    .field input, .field textarea { font-size: 14px; }
    .chips button {
        font-size: 11px;
        padding: 5px 11px;
    }
    .send {
        font-size: 15px;
        padding: 14px;
    }

    .mapsec { padding: 20px 0 50px; }
    .mapcard { border-radius: 18px; }
    .mapcard iframe { height: 280px; }
    .maplabel {
        padding: 14px 16px;
    }
    .maplabel h4 { font-size: 20px; }

    .site-footer { padding: 36px 18px 40px; }
    .ft-wrap {
        grid-template-columns: 1fr;
        gap: 32px;
    }
    .site-footer h3 { font-size: 22px; }
    .site-footer h5 { font-size: 18px; }
    .site-footer p,
    .site-footer li { font-size: 15px; }
}

/* Extra small phones */
@media (max-width: 380px) {
    .nav .wrap { padding: 10px 14px; }
    .brand span { font-size: 15px; }
    .hero h1 { font-size: 44px; }
    .btn { max-width: 100%; }
    .slip h2 { font-size: 28px; }
}
</style>
</head>
<body>

<!-- ---------- NAV ---------- -->
<nav class="nav">
    <div class="wrap">
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

<!-- ---------- HERO ---------- -->
<header class="hero">
    <span class="float f1">🍔</span>
    <span class="float f2">🍟</span>
    <span class="float f3">🥤</span>
    <span class="float f4">🌶️</span>
    <div class="wrap">
        <p class="hi">We love to hear from you</p>
        <h1>Say <span>hello</span></h1>
        <p class="sub">Feedback, party orders or just a burger craving? Drop us a message and we will reply as fast as a fresh patty hits the grill.</p>
        <div class="quick">
            <a class="btn gold" href="https://wa.me/919904522025" target="_blank"><i class="bi bi-whatsapp"></i> WhatsApp us</a>
            <a class="btn ghost" href="mailto:burgerhousebk28@gmail.com"><i class="bi bi-envelope-fill"></i> Email us</a>
        </div>
    </div>
</header>

<div class="marquee"><div>
    <?php for ($i = 0; $i < 12; $i++): ?>🍔 FRESH &amp; HOT &nbsp; ✦ &nbsp; WE REPLY WITHIN 24 HOURS &nbsp; ✦ &nbsp;<?php endfor; ?>
</div></div>

<!-- ---------- INFO + FORM ---------- -->
<main class="wrap grid">

    <section class="stack">
        <div class="info reveal">
            <div class="ico"><i class="bi bi-geo-alt-fill"></i></div>
            <div>
                <h3>Store Location</h3>
                <a href="<?= h($mapLink) ?>" target="_blank">Ajay's Burger House, Sudama Chowk, Mota Varachha, Surat, Gujarat</a>
            </div>
        </div>

        <div class="info reveal">
            <div class="ico"><i class="bi bi-clock-fill"></i></div>
            <div style="flex:1">
                <h3>Opening Hours</h3>
                <span class="status closed" id="openStatus"><i></i><b>Checking...</b></span>
                <table class="hours">
                    <tr data-days="1,2,3,4,5"><td>Mon - Fri</td><td>10 AM - 10 PM</td></tr>
                    <tr data-days="0,6"><td>Sat - Sun</td><td>11 AM - 12 AM</td></tr>
                </table>
            </div>
        </div>

        <div class="info reveal">
            <div class="ico"><i class="bi bi-telephone-fill"></i></div>
            <div>
                <h3>Contact</h3>
                <p><a href="mailto:burgerhousebk28@gmail.com">burgerhousebk28@gmail.com</a></p>
                <p><a href="https://wa.me/919904522025" target="_blank">💬 +91 99045 22025</a></p>
            </div>
        </div>
    </section>

    <section class="slip reveal">
        <span class="tag">MESSAGE SLIP</span>
        <h2>Send us a message</h2>
        <div class="cut"></div>

        <?php if ($success !== ""): ?><div class="alert ok"><?= h($success) ?></div><?php endif; ?>
        <?php if ($error !== ""): ?><div class="alert err"><?= h($error) ?></div><?php endif; ?>

        <form action="" method="POST">
            <div class="two">
                <div class="field">
                    <label for="c_name">Your name</label>
                    <input type="text" id="c_name" name="name" required value="<?= h($_POST['name'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="c_email">Your email</label>
                    <input type="email" id="c_email" name="email" required value="<?= h($_POST['email'] ?? '') ?>">
                </div>
            </div>

            <div class="field">
                <label for="c_subject">Subject</label>
                <input type="text" id="c_subject" name="subject" required value="<?= h($_POST['subject'] ?? '') ?>">
                <div class="chips">
                    <button type="button" onclick="setSubject(this)">Feedback</button>
                    <button type="button" onclick="setSubject(this)">Order issue</button>
                    <button type="button" onclick="setSubject(this)">Party / Bulk order</button>
                    <button type="button" onclick="setSubject(this)">Other</button>
                </div>
            </div>

            <div class="field">
                <label for="c_message">Message</label>
                <textarea id="c_message" name="message" rows="4" required><?= h($_POST['message'] ?? '') ?></textarea>
            </div>

            <button type="submit" name="send" class="send">Send Message ✈</button>
        </form>
    </section>
</main>

<!-- ---------- MAP ---------- -->
<section class="mapsec">
    <div class="wrap">
        <div class="mapcard reveal">
            <iframe
                src="https://www.google.com/maps?q=Mota+Varachha,Surat&z=13&output=embed"
                allowfullscreen loading="lazy" title="Store map"></iframe>
            <div class="maplabel">
                <h4>Come hungry 🍔</h4>
                <p>Sudama Chowk, Mota Varachha, Surat, Gujarat</p>
                <a class="btn gold" href="<?= h($mapLink) ?>" target="_blank"><i class="bi bi-cursor-fill"></i> Get directions</a>
            </div>
        </div>
    </div>
</section>

<!-- ---------- FOOTER ---------- -->
<footer class="site-footer">
    <div class="ft-wrap">
        <div>
            <h3><i class="bi bi-shop"></i> B K's Burger House</h3>
            <p>Serving the best burgers in town since 2010.</p>
            <div class="ft-social">
                <a href="https://www.facebook.com"><i class="bi bi-facebook"></i></a>
                <a href="https://www.instagram.com"><i class="bi bi-instagram"></i></a>
                <a href="https://www.twitter.com"><i class="bi bi-twitter"></i></a>
                <a href="https://www.youtube.com"><i class="bi bi-youtube"></i></a>
            </div>
        </div>

        <div>
            <h5>Quick Links</h5>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>

        <div class="ft-loc">
            <h5>Location</h5>
            <iframe src="https://maps.google.com/maps?q=MotaVarachha,Surat&output=embed" allowfullscreen loading="lazy" title="Map"></iframe>
            <p><a href="<?= h($mapLink) ?>" target="_blank">Ajay's Burger House, Sudama Chowk, Mota Varachha, Surat, Gujarat</a></p>
            <p><a href="mailto:burgerhousebk28@gmail.com"><i class="bi bi-envelope-fill"></i> burgerhousebk28@gmail.com</a></p>
            <p><a href="https://wa.me/919904522025" target="_blank">💬 +91 99045 22025</a></p>
        </div>

        <div>
            <h5>Opening Hours</h5>
            <p>Mon - Fri: 10 AM - 10 PM</p>
            <p>Sat - Sun: 11 AM - 12 AM</p>
        </div>

        <div class="ft-sub">
            <h5>Newsletter</h5>
            <form action="newsletter.php" method="POST" onsubmit="return validateEmail()">
                <input type="text" id="name" name="name" placeholder="Enter Name">
                <div id="nameError" class="error"></div>
                <input type="email" id="email" name="email" placeholder="Your Email">
                <div id="emailError" class="error"></div>
                <input type="text" id="city" name="city" placeholder="Enter City">
                <div id="cityError" class="error"></div>
                <button type="submit" onclick="goToSuccess()">Subscribe</button>
            </form>
        </div>
    </div>
</footer>

<script>
/* Hamburger menu */
const hamburger = document.getElementById("hamburger");
const navLinks = document.getElementById("navLinks");

hamburger.addEventListener("click", function () {
    hamburger.classList.toggle("active");
    navLinks.classList.toggle("open");
});

/* Close menu when clicking a link */
navLinks.querySelectorAll("a").forEach(function (link) {
    link.addEventListener("click", function () {
        hamburger.classList.remove("active");
        navLinks.classList.remove("open");
    });
});

/* Profile dropdown */
document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("profileBtn");
    const menu = document.getElementById("dropMenu");
    if (btn) {
        btn.addEventListener("click", function (e) {
            e.stopPropagation();
            // Mobile: toggle class, Desktop: toggle display
            if (window.innerWidth <= 768) {
                menu.classList.toggle("show");
            } else {
                menu.style.display = menu.style.display === "block" ? "none" : "block";
            }
        });
        document.addEventListener("click", function () {
            menu.style.display = "none";
            menu.classList.remove("show");
        });
    }
});

/* Scroll reveal */
const io = new IntersectionObserver(function (entries) {
    entries.forEach(function (en, i) {
        if (en.isIntersecting) {
            setTimeout(function () { en.target.classList.add("show"); }, i * 120);
            io.unobserve(en.target);
        }
    });
}, { threshold: 0.12 });
document.querySelectorAll(".reveal").forEach(function (el) { io.observe(el); });

/* Subject quick chips */
function setSubject(btn) {
    document.getElementById("c_subject").value = btn.textContent;
    document.querySelectorAll(".chips button").forEach(function (b) { b.classList.remove("on"); });
    btn.classList.add("on");
}

/* Open / Closed live status + today's row highlight */
(function () {
    const now = new Date();
    const day = now.getDay();
    const mins = now.getHours() * 60 + now.getMinutes();
    const weekend = (day === 0 || day === 6);
    const openAt = (weekend ? 11 : 10) * 60;
    const closeAt = weekend ? 24 * 60 : 22 * 60;
    const isOpen = mins >= openAt && mins < closeAt;

    const st = document.getElementById("openStatus");
    st.className = "status " + (isOpen ? "open" : "closed");
    st.querySelector("b").textContent = isOpen ? "Open now" : "Closed now";

    document.querySelectorAll(".hours tr").forEach(function (tr) {
        if (tr.dataset.days.split(",").indexOf(String(day)) !== -1) tr.classList.add("today");
    });
})();

/* Newsletter */
function goToSuccess() {
    sessionStorage.setItem("playSound", "true");
}

function validateEmail() {
    const name = document.getElementById("name").value;
    const email = document.getElementById("email").value;
    const city = document.getElementById("city").value;

    const nameError = document.getElementById("nameError");
    const emailError = document.getElementById("emailError");
    const cityError = document.getElementById("cityError");

    let isValid = true;
    nameError.innerText = "";
    emailError.innerText = "";
    cityError.innerText = "";

    if (name.trim() === "") {
        nameError.innerText = "⚠ Please enter your Name!";
        isValid = false;
    }

    if (email.trim() === "") {
        emailError.innerText = "⚠ Please enter your Email!";
        isValid = false;
    } else if (!/^[^ ]+@[^ ]+\.[a-z]{2,3}$/.test(email)) {
        emailError.innerText = "⚠ Invalid email format!";
        isValid = false;
    }

    if (city.trim() === "") {
        cityError.innerText = "⚠ Please enter your City!";
        isValid = false;
    }
    return isValid;
}
</script>
</body>
</html>