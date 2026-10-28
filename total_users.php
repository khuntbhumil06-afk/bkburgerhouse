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

</body>
</html>