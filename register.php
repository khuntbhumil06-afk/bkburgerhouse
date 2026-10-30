<?php
session_start();
include 'config.php';

if(isset($_SESSION['username'])){
    if($_SESSION["admin"]=='YES'){
        header("location: sales.php");
    } else {
        header("location: index.php");
    }
    exit();
}

$fullname = $email = $username = $password = "";
$fullname_err = $email_err = $username_err = $password_err = $image_err = $err = "";
$uploaded_image = "";

if($_SERVER['REQUEST_METHOD']=="POST"){

    if(empty(trim($_POST['fullname']))){
        $fullname_err = "Full Name cannot be blank";
    } else {
        $fullname = trim($_POST['fullname']);
    }

    if(empty(trim($_POST['email']))){
        $email_err = "Email cannot be blank";
    } else {
        $email = trim($_POST['email']);
    }

    if(empty(trim($_POST['username']))){
        $username_err = "Username cannot be blank";
    } else {
        $username = trim($_POST['username']);
    }

    if(empty(trim($_POST['password']))){
        $password_err = "Password cannot be blank";
    } elseif(strlen($_POST['password']) < 8){
        $password_err = "Password must be at least 8 characters";
    } else {
        $password = $_POST['password'];
    }

    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $allowed = ['jpg','jpeg','png'];
        $fileName = $_FILES['image']['name'];
        $fileTmp  = $_FILES['image']['tmp_name'];
        $fileSize = $_FILES['image']['size'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if(!in_array($ext, $allowed)){
            $image_err = "Only JPG, JPEG, PNG allowed";
        } elseif($fileSize > 2*1024*1024){
            $image_err = "Image size must be less than 2MB";
        } else {
            $uploaded_image = time() . '_' . rand(1000,9999) . "." . $ext;

            if(!is_dir('uploads')){
                mkdir('uploads', 0777, true);
            }

            if(!move_uploaded_file($fileTmp, "uploads/".$uploaded_image)){
                $image_err = "Failed to upload image. Check folder permissions.";
            }
        }
    } else {
        $image_err = "Please upload image";
    }

    if(empty($fullname_err) && empty($email_err) && empty($username_err) && empty($password_err) && empty($image_err)){

        $check_sql = "SELECT * FROM loginform WHERE username=?";
        $stmt_check = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($stmt_check, "s", $username);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if(mysqli_stmt_num_rows($stmt_check) > 0){
            $username_err = "Username already exists";
        } else {
            $sql = "INSERT INTO loginform (fullname, email, username, password, image) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);

            if($stmt){
                $hash = password_hash($password, PASSWORD_DEFAULT);
                mysqli_stmt_bind_param($stmt, "sssss", $fullname, $email, $username, $hash, $uploaded_image);

                if(mysqli_stmt_execute($stmt)){
                    header("location: login.php");
                    exit();
                } else {
                    $err = "Something went wrong!";
                }
            } else {
                $err = "Database error!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Burger Mania - Register</title>
<link rel="icon" href="Assets/logo.png">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --bg: #0f0d0c;
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

body {
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    color: var(--ink);
    min-height: 100vh;
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

/* ---------- CARD ---------- */
.wrap {
    position: relative;
    z-index: 2;
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 10px 20px 50px;
}

.box {
    display: grid;
    grid-template-columns: .9fr 1.1fr;
    width: 100%;
    max-width: 920px;
    border-radius: 28px;
    overflow: hidden;
    box-shadow: 0 30px 80px rgba(0,0,0,.65);
    animation: fadeSlide .8s ease;
}

@keyframes fadeSlide {
    from { opacity: 0; transform: translateY(40px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* left: promo */
.promo {
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 44px 38px;
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
    font-size: clamp(56px, 8vw, 86px);
    line-height: .92;
    letter-spacing: 2px;
    margin: 8px 0 16px;
}

.promo h1 span {
    color: transparent;
    -webkit-text-stroke: 2px #fff;
}

.promo p {
    max-width: 280px;
    font-size: 14px;
    line-height: 1.7;
    color: rgba(255,255,255,.88);
    margin-bottom: 20px;
}

.perks {
    list-style: none;
    display: grid;
    gap: 10px;
}

.perks li {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    font-size: 14px;
}

.perks li span {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: rgba(0,0,0,.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
}

.float {
    position: absolute;
    font-size: 44px;
    animation: floaty 5s ease-in-out infinite;
    filter: drop-shadow(0 8px 10px rgba(0,0,0,.4));
    pointer-events: none;
}

.float.f1 { right: 26px; top: 24px; }
.float.f2 { right: 34px; bottom: 28px; animation-delay: 1.3s; font-size: 38px; }

@keyframes floaty {
    0%, 100% { transform: translateY(0) rotate(-6deg); }
    50%      { transform: translateY(-14px) rotate(6deg); }
}

/* right: form */
.formside {
    background: var(--paper);
    color: var(--paper-ink);
    padding: 38px 40px 34px;
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
    font-size: 50px;
    letter-spacing: 2px;
    line-height: 1;
    margin: 4px 0 18px;
    color: var(--paper-ink);
    text-align: left;
}

.field {
    margin-bottom: 15px;
}

label {
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #7a6a4f;
    margin-bottom: 2px;
}

.form-control {
    width: 100%;
    padding: 9px 2px;
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

.form-control:focus {
    border-bottom-color: var(--red);
}

.input-wrap {
    position: relative;
}

.input-wrap .form-control {
    padding-right: 44px;
}

.eye {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    padding: 6px 8px;
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
}

.eye:hover {
    transform: translateY(-50%) scale(1.15);
}

.error {
    display: block;
    margin-top: 5px;
    font-size: 13px;
    font-weight: 700;
    color: #b91c1c;
}

.error:empty {
    display: none;
}

/* photo upload */
.photo-row {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 6px;
}

.avatar {
    position: relative;
    width: 84px;
    height: 84px;
    flex-shrink: 0;
    border-radius: 50%;
    background: #fff;
    border: 3px dashed #c9b88f;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    overflow: hidden;
}

img#preview {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

input[type="file"] {
    font-family: inherit;
    font-size: 13px;
    color: var(--paper-ink);
    max-width: 100%;
}

input[type="file"]::file-selector-button {
    font-family: inherit;
    font-weight: 800;
    font-size: 13px;
    cursor: pointer;
    border: none;
    border-radius: 999px;
    padding: 9px 18px;
    margin-right: 12px;
    background: var(--paper-ink);
    color: var(--gold);
    transition: .2s;
}

input[type="file"]::file-selector-button:hover {
    background: var(--red);
    color: #fff;
}

.btn-register {
    width: 100%;
    margin-top: 18px;
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

.btn-register:hover {
    background: #f1485a;
}

.btn-register:active {
    transform: translateY(4px);
    box-shadow: 0 1px 0 var(--red2);
}

.login-link {
    text-align: center;
    margin-top: 18px;
    font-size: 14px;
    font-weight: 600;
    color: #7a6a4f;
}

.login-link p {
    margin: 0;
}

.login-link a {
    color: var(--red);
    font-weight: 800;
    text-decoration: none;
}

.login-link a:hover {
    text-decoration: underline;
}

.general-error {
    text-align: center;
    margin-top: 10px;
}

/* ==================== RESPONSIVE ==================== */

/* Tablets */
@media (max-width: 900px) {
    .top { padding: 16px 28px; }
    .wrap { padding: 10px 16px 40px; }
    .box {
        grid-template-columns: .95fr 1.05fr;
        max-width: 820px;
    }
    .promo { padding: 36px 30px; }
    .formside { padding: 32px 30px 30px; }
    h2 { font-size: 44px; }
}

/* Mobile landscape / large phones */
@media (max-width: 800px) {
    .top { padding: 14px 18px; }
    .brand { font-size: 18px; gap: 10px; }
    .brand img { width: 34px; height: 34px; }

    .wrap { padding: 8px 16px 36px; }

    .box {
        grid-template-columns: 1fr;
        max-width: 460px;
        border-radius: 22px;
    }

    .promo {
        padding: 28px 26px 26px;
        text-align: center;
        align-items: center;
    }
    .promo::after { display: none; }
    .promo .hi { letter-spacing: 3px; }
    .promo h1 {
        font-size: 52px;
        margin: 6px 0 10px;
    }
    .promo p {
        max-width: 100%;
        margin-bottom: 14px;
        font-size: 13px;
    }
    .perks {
        display: none; /* hide detailed perks on mobile to save space */
    }
    .float.f1 { right: 18px; top: 16px; font-size: 36px; }
    .float.f2 { display: none; }

    .formside {
        padding: 28px 24px 30px;
    }
    h2 {
        font-size: 40px;
        margin-bottom: 16px;
    }
    .field { margin-bottom: 14px; }
    .form-control { font-size: 15px; padding: 10px 2px; }

    .photo-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .avatar {
        width: 76px;
        height: 76px;
        font-size: 28px;
    }
    input[type="file"] {
        width: 100%;
    }
    input[type="file"]::file-selector-button {
        padding: 10px 16px;
        margin-right: 10px;
    }

    .btn-register {
        padding: 14px;
        font-size: 15px;
        margin-top: 16px;
    }
}

/* Small phones */
@media (max-width: 480px) {
    .top { padding: 12px 16px; }
    .brand { font-size: 16px; gap: 8px; }
    .brand img { width: 30px; height: 30px; }

    .wrap { padding: 6px 14px 30px; }

    .box {
        border-radius: 18px;
        max-width: 100%;
    }

    .promo {
        padding: 22px 20px 20px;
    }
    .promo h1 {
        font-size: 44px;
        letter-spacing: 1px;
    }
    .promo p { font-size: 13px; }
    .float.f1 { font-size: 30px; right: 14px; top: 12px; }

    .formside {
        padding: 24px 18px 26px;
    }
    .tag { font-size: 10px; letter-spacing: 2.5px; }
    h2 {
        font-size: 36px;
        letter-spacing: 1px;
        margin-bottom: 14px;
    }

    label { font-size: 10px; }
    .form-control { font-size: 14px; }
    .error { font-size: 12px; }

    .avatar {
        width: 70px;
        height: 70px;
        font-size: 26px;
    }

    .btn-register {
        padding: 13px;
        font-size: 14px;
        letter-spacing: 0.5px;
    }

    .login-link { font-size: 13px; margin-top: 16px; }
}

/* Extra small phones */
@media (max-width: 360px) {
    .promo h1 { font-size: 38px; }
    h2 { font-size: 32px; }
    .formside { padding: 20px 16px 22px; }
    .photo-row { gap: 10px; }
    .avatar {
        width: 64px;
        height: 64px;
        font-size: 24px;
    }
}
</style>
<script>
function previewImage(event){
    const f = event.target.files && event.target.files[0];
    const output = document.getElementById('preview');
    const emoji = document.getElementById('previewEmoji');
    if(!f){
        output.style.display = 'none';
        emoji.style.display = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = function(){
        output.src = reader.result;
        output.style.display = 'block';
        emoji.style.display = 'none';
    }
    reader.readAsDataURL(f);
}
</script>
</head>
<body>

<div class="top">
    <a href="index.php" class="brand">
        <img src="Assets/logo.png" alt="logo">
        <span>B K's Burger House</span>
    </a>
</div>

<div class="wrap">
<div class="box">

    <div class="promo">
        <span class="float f1">🍔</span>
        <span class="float f2">🍟</span>
        <span class="hi">Burger House</span>
        <h1>Join the <span>club</span></h1>
        <p>Create your free account and start ordering the best burgers in town.</p>
        <ul class="perks">
            <li><span>🍔</span> Order your favourite burgers</li>
            <li><span>🛵</span> Track every order live</li>
            <li><span>❤️</span> Save items to your wishlist</li>
        </ul>
    </div>

    <div class="formside">
        <span class="tag">NEW MEMBER</span>
        <h2>Register</h2>

        <form method="post" enctype="multipart/form-data">

            <div class="field">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" class="form-control" value="<?= htmlspecialchars($fullname) ?>">
                <span class="error"><?= $fullname_err ?></span>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>">
                <span class="error"><?= $email_err ?></span>
            </div>

            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>">
                <span class="error"><?= $username_err ?></span>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input type="password" id="password" name="password" class="form-control" placeholder="At least 8 characters">
                    <button type="button" class="eye" id="eye" aria-label="Show password">👁️</button>
                </div>
                <span class="error"><?= $password_err ?></span>
            </div>

            <div class="field">
                <label for="image">Profile Image</label>
                <div class="photo-row">
                    <div class="avatar">
                        <span id="previewEmoji">📷</span>
                        <img id="preview" src="#" alt="Image Preview" style="display:none;">
                    </div>
                    <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                </div>
                <span class="error"><?= $image_err ?></span>
            </div>

            <button class="btn-register">Register</button>

            <div class="login-link">
                <p>Already have account? <a href="login.php">Login</a></p>
            </div>
            <div class="error general-error"><?= $err ?></div>
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
        eye.textContent = show ? "🙈" : "👁️";
        eye.setAttribute("aria-label", show ? "Hide password" : "Show password");
    });
})();
</script>

</body>
</html>