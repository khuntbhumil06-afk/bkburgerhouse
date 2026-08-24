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
<html>
<head>
<title>Burger Mania - Register</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {
    margin:0;
    font-family:'Segoe UI', sans-serif;
    background:url('Assets/bg.png');
    background-size:130%;
    animation:gradientBG 10s ease infinite;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}
@keyframes gradientBG {0%{background-position:0% 50%;}50%{background-position:100% 50%;}100%{background-position:0% 50%;}}
.container {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(12px);
    padding:40px;
    border-radius:20px;
    width:350px;
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
    animation:fadeSlide 0.8s ease;
}
@keyframes fadeSlide {from{opacity:0; transform:translateY(40px);}to{opacity:1; transform:translateY(0);}}
h2{text-align:center;color:white;margin-bottom:20px;}
.form-control{width:100%;padding:12px;margin:5px 0 12px;border-radius:8px;border:none;}
.btn-register{width:100%;padding:12px;background:#f80707;border:none;border-radius:8px;font-weight:bold;cursor:pointer;}
.error{color:yellow;font-size:14px;}
.login-link{text-align:center;margin-top:15px;}
.login-link a{color:white;font-weight:bold;}
img#preview{display:block;margin:10px auto;width:100px;height:100px;border-radius:50%;object-fit:cover;}
</style>
<script>
function previewImage(event){
    const reader = new FileReader();
    reader.onload = function(){
        const output = document.getElementById('preview');
        output.src = reader.result;
        output.style.display = 'block';
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>
</head>
<body>

<div class="container">
<h2>Register</h2>
<form method="post" enctype="multipart/form-data">
<label>Full Name</label>
<span class="error"><?= $fullname_err ?></span>
<input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($fullname) ?>">

<label>Email</label>
<span class="error"><?= $email_err ?></span>
<input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>">

<label>Username</label>
<span class="error"><?= $username_err ?></span>
<input type="text" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>">

<label>Password</label>
<span class="error"><?= $password_err ?></span>
<input type="password" name="password" class="form-control">

<label>Profile Image</label>
<span class="error"><?= $image_err ?></span>
<input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(event)">
<img id="preview" src="#" alt="Image Preview" style="display:none;">

<button class="btn-register">Register</button>

<div class="login-link">
<p>Already have account? <a href="login.php">Login</a></p>
</div>
<div class="error"><?= $err ?></div>
</form>
</div>

</body>
</html>