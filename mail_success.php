<!DOCTYPE html>
<html>
<head>
<title>Success</title>

<style>
body{
    margin:0;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    font-family:sans-serif;
    background:linear-gradient(135deg,#ff416c,#ff4b2b);
    overflow:hidden;
    color:white;
}

.card{
    background:rgba(255,255,255,0.1);
    padding:40px;
    border-radius:20px;
    text-align:center;
}

.btn{
    margin-top:20px;
    padding:10px 25px;
    border:none;
    border-radius:20px;
    background:#00ffcc;
    cursor:pointer;
}

.confetti{
    position:absolute;
    width:10px;
    height:10px;
    background:red;
    top:-10px;
    animation: fall linear infinite;
}

@keyframes fall{
    to{
        transform:translateY(100vh) rotate(360deg);
    }
}
</style>

</head>

<body>

<div id="confetti-container"></div>

<div class="card">
    <h2>🎉 Success!</h2>
    <p>Subscribed Successfully</p>
</div>

<audio id="successSound" preload="auto">
    <source src="Assets\sound\success.mp3" type="audio/mpeg">
</audio>

<script>
function createConfetti(){
    const container = document.getElementById("confetti-container");

    for(let i=0;i<50;i++){
        let c = document.createElement("div");
        c.classList.add("confetti");

        c.style.left = Math.random()*100 + "vw";
        c.style.backgroundColor = ["#ff0","#0ff","#f0f","#0f0","#f00"][Math.floor(Math.random()*5)];
        c.style.animationDuration = (Math.random()*3+2)+"s";

        container.appendChild(c);
    }
}

window.onload = function(){
    createConfetti();
    let sound = document.getElementById("successSound");

    if(sessionStorage.getItem("playSound") === "true"){
        sound.currentTime = 0;
        sound.play().catch(()=>{});
        setTimeout(()=>{
            window.location.href = "index.php";
        },10000);
        sessionStorage.removeItem("playSound");
    }
};
</script>

</body>
</html>