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
<title>Payment Success - Ajay's Burger House</title>
<link rel="icon" href="Assets/logo.png">
<style>
body{
    text-align:center;
    background:#0f0f0f;
    font-family:'Segoe UI', sans-serif;
}

.card{
    background:#111;
    padding:40px;
    border-radius:20px;
    box-shadow:0 0 30px rgba(0,255,100,0.2);
    display:inline-block;
    animation:fadeIn 1s ease-in-out;
}

.success-circle{
    border-radius:50%;
    height:200px;
    width:200px;
    background:#0f2;
    margin:0 auto;
    display:flex;
    align-items:center;
    justify-content:center;
    animation:pop 0.5s ease;
}

.checkmark{
    color:white;
    font-size:80px;
    animation:tick 0.6s ease;
}

h1{
    color:#0f2;
    margin-top:20px;
}

p{
    color:#ccc;
    font-size:16px;
}

a.download-link{
    color:#0f2;
    text-decoration:none;
    font-weight:bold;
    display:inline-block;
    margin-top:15px;
    font-size:18px;
}

@keyframes pop{
    0%{transform:scale(0);}
    100%{transform:scale(1);}
}

@keyframes tick{
    0%{transform:scale(0) rotate(-45deg);}
    100%{transform:scale(1) rotate(0);}
}

@keyframes fadeIn{
    from{opacity:0; transform:translateY(20px);}
    to{opacity:1; transform:translateY(0);}
}
</style>
</head>
<body>
<div class="container" style="margin-top: 20vh;">
    <div class="card">
        <div class="success-circle">
            <i class="checkmark">✓</i>
        </div>
        <h1>Success!</h1>
        <p>We've received your order and payment!<br>Please don't close or move away from this page.</p>

]        <?php if($receipt_pdf && file_exists($receipt_pdf)): ?>
            <a href="<?php echo $receipt_pdf; ?>" download class="download-link">
                📄 Download Your Receipt
            </a>
        <?php endif; ?>
    </div>
</div>

]<audio id="successSound">
    <source src="Assets/sound/success.mp3" type="audio/mpeg">
</audio>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
window.onload = function(){

    let sound = document.getElementById("successSound");
    sound.play().catch(function(error){
        console.log("Autoplay blocked:", error);
    });

    confetti({
        particleCount: 150,
        spread: 80,
        origin: { y: 0.6 }
    });

    setTimeout(function(){
        window.location.href = 'index.php';
    }, 10000);
}
</script>
</body>
</html>