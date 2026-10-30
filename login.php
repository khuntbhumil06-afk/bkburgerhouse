<?php
session_start();
include 'config.php';

if (isset($_SESSION['username'])) {
    if (isset($_SESSION['admin']) && $_SESSION['admin'] === 'YES') {
        header("Location: sales.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

$username = $password = "";
$uerr = $perr = "";

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    if (empty(trim($_POST['username'])) || empty(trim($_POST['password']))) {
        $uerr = "Please enter both username and password";
    } else {

        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $sql = "SELECT id, fullname, email, username, password, role, created_at, image
                FROM loginform
                WHERE username = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "s", $username);

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) === 1) {

                    mysqli_stmt_bind_result(
                        $stmt,
                        $id,
                        $fullname,
                        $email,
                        $db_username,
                        $hashed_password,
                        $role,
                        $created_at,
                        $image
                    );

                    if (mysqli_stmt_fetch($stmt)) {

                        if (password_verify($password, $hashed_password)) {

                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $db_username;
                            $_SESSION["fullname"] = $fullname;
                            $_SESSION["email"] = $email;
                            $_SESSION["created_at"] = $created_at;
                            $_SESSION["image"] = $image;

                            if (trim($role) === 'admin') {
                                $_SESSION['admin'] = 'YES';
                                header("Location: dashboard.php");
                            } else {
                                $_SESSION['admin'] = 'NO';
                                header("Location: index.php");
                            }
                            exit();

                        } else {
                            $perr = "Incorrect password";
                        }
                    }

                } else {
                    $uerr = "Username does not exist";
                }

            } else {
                $uerr = "Something went wrong!";
            }

            mysqli_stmt_close($stmt);
        }
    }
}

mysqli_close($conn);

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Burger Mania - Login</title>
    <link rel="icon" href="Assets/logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #0f0d0c;
            --card: #1a1613;
            --ink: #fff7ec;
            --red: #e63946;
            --red2: #9d0208;
            --gold: #ffb703;
            --paper: #fff3d6;
            --paper-ink: #2b2118;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            overflow-x: hidden;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            color: var(--ink);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            background:
                linear-gradient(rgba(15,13,12,.88), rgba(15,13,12,.92)),
                url('Assets/bg.png') center / cover fixed,
                var(--bg);
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 8% -5%, rgba(230,57,70,.30), transparent 40%),
                radial-gradient(circle at 100% 100%, rgba(255,183,3,.16), transparent 40%);
        }

        img {
            max-width: 100%;
        }

        /* ---------- TOP BRAND ---------- */
        .top {
            position: relative;
            z-index: 2;
            padding: 18px 45px;
            font-family: 'Poppins', sans-serif;
        }

        .brand {
            display: inline-flex;
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

        /* ---------- LOGIN CARD ---------- */
        .container {
            position: relative;
            z-index: 2;
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 20px 50px;
        }

        .login-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            max-width: 900px;
            min-height: 520px;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0,0,0,.65);
            animation: fadeIn .8s ease;
        }

        .login-box > * {
            min-width: 0;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* left: promo */
        .promo {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 44px 40px;
            background: linear-gradient(160deg, var(--red), var(--red2));
            overflow: hidden;
        }

        .promo::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            right: -1px;
            width: 14px;
            background: radial-gradient(circle at 14px 10px, var(--paper) 7px, transparent 7.5px) 0 0 / 14px 20px repeat-y;
        }

        .promo .hi {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--gold);
        }

        .promo h1 {
            font-family: 'Bebas Neue', sans-serif;
            font-weight: 400;
            font-size: clamp(56px, 9vw, 92px);
            line-height: .92;
            letter-spacing: 2px;
            margin: 8px 0 16px;
        }

        .promo h1 span {
            color: transparent;
            -webkit-text-stroke: 2px #fff;
        }

        .promo p {
            max-width: 300px;
            font-size: 14px;
            line-height: 1.7;
            color: rgba(255,255,255,.88);
        }

        .float {
            position: absolute;
            font-size: 44px;
            animation: floaty 5s ease-in-out infinite;
            filter: drop-shadow(0 8px 10px rgba(0,0,0,.4));
        }

        .float.f1 { right: 26px; top: 26px; }
        .float.f2 { right: 40px; bottom: 90px; animation-delay: 1.3s; font-size: 36px; }
        .float.f3 { left: 30px; bottom: 26px; animation-delay: 2.2s; font-size: 40px; }

        @keyframes floaty {
            0%, 100% { transform: translateY(0) rotate(-6deg); }
            50%      { transform: translateY(-14px) rotate(6deg); }
        }

        /* right: form */
        .formside {
            background: var(--paper);
            color: var(--paper-ink);
            padding: 44px 42px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .tag {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 3px;
            color: var(--red);
        }

        h2 {
            font-family: 'Bebas Neue', sans-serif;
            font-weight: 400;
            font-size: 52px;
            letter-spacing: 2px;
            line-height: 1;
            margin: 4px 0 22px;
            color: var(--paper-ink);
            text-align: left;
        }

        .field {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #7a6a4f;
            margin-bottom: 4px;
        }

        .input-wrap {
            position: relative;
        }

        input {
            width: 100%;
            padding: 10px 2px;
            margin: 0;
            background: transparent;
            border: none;
            border-bottom: 2px solid #c9b88f;
            border-radius: 0;
            font-family: inherit;
            font-size: 15px;
            color: var(--paper-ink);
            outline: none;
            transition: .2s;
        }

        input:focus {
            border-bottom-color: var(--red);
        }

        .input-wrap input[type="password"],
        .input-wrap input.shown {
            padding-right: 44px;
        }

        .eye {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: auto;
            padding: 6px 8px;
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            box-shadow: none;
        }

        .eye:hover {
            transform: translateY(-50%) scale(1.15);
            box-shadow: none;
        }

        .error {
            display: block;
            margin-top: 6px;
            font-size: 13px;
            font-weight: 700;
            color: #b91c1c;
        }

        .error:empty {
            display: none;
        }

        button[type="submit"] {
            width: 100%;
            margin-top: 6px;
            padding: 15px;
            background: var(--red);
            color: #fff;
            border: none;
            border-radius: 14px;
            font-family: inherit;
            font-weight: 800;
            font-size: 16px;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow: 0 5px 0 var(--red2);
            transition: .15s;
        }

        button[type="submit"]:hover {
            background: #f1485a;
        }

        button[type="submit"]:active {
            transform: translateY(4px);
            box-shadow: 0 1px 0 var(--red2);
        }

        .register-link {
            text-align: center;
            margin-top: 22px;
            font-size: 14px;
            font-weight: 600;
            color: #7a6a4f;
        }

        .register-link a {
            color: var(--red);
            font-weight: 800;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* ---------- RESPONSIVE ---------- */

        /* iPhone / touch: "fixed" background kaam nathi karto */
        @media (max-width: 900px), (hover: none) {
            body {
                background:
                    linear-gradient(rgba(15,13,12,.88), rgba(15,13,12,.92)),
                    url('Assets/bg.png') center / cover scroll,
                    var(--bg);
            }
        }

        /* Tablet */
        @media (max-width: 900px) {
            .promo { padding: 36px 30px; }
            .formside { padding: 36px 30px; }
        }

        /* Mobile: promo upar nano banner, form niche */
        @media (max-width: 760px) {
            .top { padding: 14px 18px; }
            .container { align-items: flex-start; padding: 8px 14px 36px; }
            .login-box {
                grid-template-columns: 1fr;
                min-height: 0;
                max-width: 440px;
                border-radius: 22px;
            }
            .promo { padding: 28px 26px 30px; }
            .promo::after { display: none; }
            .promo h1 { font-size: 56px; margin-bottom: 8px; }
            .promo p { display: none; }
            .float.f2, .float.f3 { display: none; }
            .float.f1 { font-size: 36px; right: 20px; top: 20px; }
            .formside { padding: 30px 26px 34px; }
            h2 { font-size: 44px; }

            /* 16px thi nana font par iPhone auto-zoom kare chhe */
            input { font-size: 16px; }
            .eye { padding: 10px; font-size: 20px; }
        }

        /* Nano phone */
        @media (max-width: 380px) {
            .brand { font-size: 17px; }
            .brand img { width: 32px; height: 32px; }
            .container { padding: 6px 10px 30px; }
            .promo { padding: 24px 20px; }
            .promo h1 { font-size: 46px; }
            .formside { padding: 26px 20px 30px; }
            h2 { font-size: 38px; }
            .register-link { font-size: 13px; }
        }

        /* Landscape phone: ochhi height */
        @media (max-height: 500px) and (orientation: landscape) {
            .container { align-items: flex-start; padding-bottom: 24px; }
            .login-box { min-height: 0; }
            .promo, .formside { padding-top: 24px; padding-bottom: 24px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .float, .login-box { animation: none; }
        }
    </style>
</head>

<body>
    <div class="top">
        <a href="index.php" class="brand">
            <img src="Assets/logo.png" alt="logo">
            <span>B K's Burger House</span>
        </a>
    </div>

    <div class="container">
        <div class="login-box">

            <div class="promo">
                <span class="float f1">🍔</span>
                <span class="float f2">🍟</span>
                <span class="float f3">🥤</span>
                <span class="hi">Burger House</span>
                <h1>Welcome <span>back</span></h1>
                <p>Login to order your favourite burgers, track your orders and save your wishlist.</p>
            </div>

            <div class="formside">
                <span class="tag">MEMBER LOGIN</span>
                <h2>Login</h2>

                <form method="post" autocomplete="off">
                    <div class="field">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required value="<?php echo e($username); ?>">
                        <span class="error"><?php echo e($uerr ?? ''); ?></span>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <input type="password" id="password" name="password" required>
                            <button type="button" class="eye" id="eye" aria-label="Show password">👁️</button>
                        </div>
                        <span class="error"><?php echo e($perr ?? ''); ?></span>
                    </div>

                    <button type="submit">Login</button>

                    <div class="register-link">
                        Don't have an account?
                        <a href="register.php">Register First</a>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        // Password show / hide
        (function () {
            const pw = document.getElementById("password");
            const eye = document.getElementById("eye");
            eye.addEventListener("click", function () {
                const show = pw.type === "password";
                pw.type = show ? "text" : "password";
                pw.classList.toggle("shown", show);
                eye.textContent = show ? "🙈" : "👁️";
                eye.setAttribute("aria-label", show ? "Hide password" : "Show password");
            });
        })();
    </script>
</body>
</html>