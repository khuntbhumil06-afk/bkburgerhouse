<?php
include 'config.php';

$sql = "SELECT name, price FROM products";
$result = mysqli_query($conn, $sql);

if(!$result){
    die("Query Failed: " . mysqli_error($conn));
}

$categories = [
    "Veg Burger" => ["AlooTikkiBurger","CheeseVegBurger","ClassicVegBurger", "CrispyVegBurger","DoublePattyVegBurger", "GreenGardenBurger","JainVegBurger","MexicanVegBurger","MushroomVegBurger","PaneerTikkaBurger","SpicyVegburger","SpicyVegDelight","VegCrunchBurger","VegMaharajaBurger","VegSupremeBurger"],
    "Non-Veg Burger" => ["BBQChickenBurger","ChickenCheeseBurger","ChickenCrunchBurger","ChickenDeluxeBurger","ChickenMaharajaBurger","ChickenPattyBurger","ChickenSupremeBurger","ChickenTandooriBurger","ClassicChickenBurger","CrispyChickenBurger","DoubleChickenBurger","EggChickenBurger","GrilledChickenBurger","PeriPeriChickenBurger","SpicyChickenBurger"],
    "Combo" => ["ChickenCheeseCombo","BBQChickenCombo","ChickenClassicCombo","ChickenSupremeCombo","CrispyChickenCombo","DoubleVegCombo","FamilyChickenCombo","FamilyVegCombo","MaharajaChickenCombo","MaharajaVegCombo","PaneerBurgerCombo","PeriPeriChickenCombo","VegCheeseCombo","VegClassicCombo","VegSupremeCombo"],
    "Drink" => ["ChocolateShake","CocaCola","ColdCoffee","Fanta","LemonIcedTea","Limca","MangoShake","MineralWater","OreoShake","PeachIcedTea","Pepsi","Sprite","StrawberryShake","ThumsUp","VanillaShake"]
];

$allItems = [];
while($row = mysqli_fetch_assoc($result)){
    $allItems[] = $row;
}

function getCategory($name, $categories){
    foreach($categories as $cat => $items){
        if(in_array($name, $items)){
            return $cat;
        }
    }
    return "Drink"; 
}

// ---------- Design mate helpers ----------
function e($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// "AlooTikkiBurger" -> "Aloo Tikki Burger", "BBQChickenBurger" -> "BBQ Chicken Burger"
function prettyName($name){
    $name = preg_replace('/([a-z])([A-Z])/', '$1 $2', $name);
    $name = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1 $2', $name);
    return $name;
}

function money($v){
    return preg_replace('/\.00$/', '', number_format((float)$v, 2, '.', ''));
}

$catMeta = [
    "Veg Burger"     => ["emoji" => "🥬", "class" => "veg"],
    "Non-Veg Burger" => ["emoji" => "🍗", "class" => "nonveg"],
    "Combo"          => ["emoji" => "🍱", "class" => "combo"],
    "Drink"          => ["emoji" => "🥤", "class" => "drink"],
];

// Category pramane items vahenchi lo
$grouped = array_fill_keys(array_keys($catMeta), []);
foreach($allItems as $row){
    $grouped[getCategory($row['name'], $categories)][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Burger & Menu List</title>
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
                radial-gradient(circle at 100% 60%, rgba(255,183,3,.12), transparent 40%),
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
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 20px 90px;
        }

        .hero {
            padding: 48px 0 26px;
        }

        .hi {
            color: var(--gold);
            font-weight: 800;
            font-size: 13px;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .hero h1 {
            font-family: 'Bebas Neue', sans-serif;
            font-weight: 400;
            font-size: clamp(54px, 10vw, 96px);
            line-height: 1;
            letter-spacing: 2px;
        }

        .hero h1 span {
            color: transparent;
            -webkit-text-stroke: 2px var(--red);
        }

        /* ---------- TOOLS (search + chips) ---------- */
        .tools {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }

        .search {
            flex: 1;
            min-width: 220px;
            max-width: 340px;
            padding: 12px 20px;
            border-radius: 999px;
            border: 2px solid var(--line);
            background: var(--card);
            color: var(--ink);
            font-family: inherit;
            font-size: 14px;
            outline: none;
            transition: .2s;
        }

        .search:focus {
            border-color: var(--gold);
        }

        .filters {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filters button {
            font-family: inherit;
            font-weight: 800;
            font-size: 13px;
            cursor: pointer;
            color: var(--muted);
            background: var(--card);
            border: 2px solid var(--line);
            border-radius: 999px;
            padding: 9px 16px;
            transition: .2s;
        }

        .filters button b {
            color: var(--gold);
            margin-left: 4px;
        }

        .filters button:hover,
        .filters button.on {
            color: #000;
            background: var(--gold);
            border-color: var(--gold);
        }

        .filters button:hover b,
        .filters button.on b {
            color: #000;
        }

        /* ---------- CATEGORY SECTION ---------- */
        .section {
            margin-top: 36px;
        }

        .sec-head {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 14px;
        }

        .sec-head .ico {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: var(--card2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: inset 0 0 0 2px rgba(255,183,3,.4);
        }

        .sec-head h2 {
            font-family: 'Bebas Neue', sans-serif;
            font-weight: 400;
            font-size: 44px;
            letter-spacing: 2px;
        }

        .sec-head .count {
            background: var(--card2);
            border: 1px solid var(--line);
            color: var(--gold);
            font-weight: 800;
            font-size: 13px;
            padding: 4px 14px;
            border-radius: 999px;
        }

        .sec-head::after {
            content: "";
            flex: 1;
            height: 2px;
            background: repeating-linear-gradient(90deg, #3a2f28 0 10px, transparent 10px 18px);
        }

        .board {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 12px 26px;
            box-shadow: 0 14px 40px rgba(0,0,0,.45);
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 48px;
        }

        .item {
            display: flex;
            align-items: baseline;
            gap: 10px;
            padding: 13px 0;
            border-bottom: 1px solid var(--line);
        }

        .item .nm {
            font-weight: 700;
            font-size: 15px;
        }

        .item .dots {
            flex: 1;
            border-bottom: 2px dotted #4a3d34;
            transform: translateY(-4px);
            min-width: 20px;
        }

        .item .pr {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 26px;
            letter-spacing: 1px;
            color: var(--gold);
            white-space: nowrap;
        }

        .veg .nm::before,
        .nonveg .nm::before {
            content: "";
            display: inline-block;
            width: 11px;
            height: 11px;
            border-radius: 3px;
            margin-right: 10px;
            border: 2px solid;
            box-shadow: inset 0 0 0 2px var(--card);
        }

        .veg .nm::before    { background: #22c55e; border-color: #22c55e; }
        .nonveg .nm::before { background: #ef4444; border-color: #ef4444; }

        .none {
            grid-column: 1 / -1;
            text-align: center;
            color: var(--muted);
            padding: 22px 0;
        }

        .no-result {
            display: none;
            text-align: center;
            margin-top: 40px;
            background: var(--card);
            border: 2px dashed #3a2f28;
            border-radius: 24px;
            padding: 44px 20px;
            color: var(--muted);
            font-weight: 600;
        }

        /* ---------- HOME BUTTON ---------- */
        .btn-row {
            margin-top: 44px;
            text-align: center;
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

        @media (max-width: 760px) {
            .board { grid-template-columns: 1fr; padding: 8px 18px; }
            .sec-head h2 { font-size: 34px; }
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
        <p class="hi">Burger House</p>
        <h1>Menu <span>list</span></h1>
    </div>

    <div class="tools">
        <input type="text" class="search" id="search" placeholder="Search item...">
        <div class="filters" id="filters">
            <button type="button" class="on" data-cat="all">All <b><?php echo count($allItems); ?></b></button>
            <?php foreach($catMeta as $cat => $meta): ?>
                <button type="button" data-cat="<?php echo e($cat); ?>"><?php echo $meta['emoji']; ?> <?php echo e($cat); ?> <b><?php echo count($grouped[$cat]); ?></b></button>
            <?php endforeach; ?>
        </div>
    </div>

    <?php foreach($catMeta as $cat => $meta): ?>
        <section class="section" data-cat="<?php echo e($cat); ?>">
            <div class="sec-head">
                <div class="ico"><?php echo $meta['emoji']; ?></div>
                <h2><?php echo e($cat); ?></h2>
                <span class="count"><?php echo count($grouped[$cat]); ?></span>
            </div>

            <div class="board">
                <?php if(!$grouped[$cat]): ?>
                    <div class="none">No items in this category yet.</div>
                <?php endif; ?>

                <?php foreach($grouped[$cat] as $row): ?>
                    <div class="item <?php echo e($meta['class']); ?>" data-name="<?php echo e(strtolower(prettyName($row['name']))); ?>">
                        <span class="nm"><?php echo e(prettyName($row['name'])); ?></span>
                        <span class="dots"></span>
                        <span class="pr">₹<?php echo e(money($row['price'])); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <div class="no-result" id="noResult">🍽️ No items found.</div>

    <div class="btn-row">
        <a href="index.php" class="home-btn">🏠 Home</a>
    </div>

</div>

<script>
    const chips = document.querySelectorAll("#filters button");
    const search = document.getElementById("search");
    let activeCat = "all";

    function applyFilter() {
        const q = search.value.trim().toLowerCase();
        let shown = 0;

        document.querySelectorAll(".section").forEach(function (sec) {
            const catOk = (activeCat === "all" || sec.dataset.cat === activeCat);
            let visible = 0;

            sec.querySelectorAll(".item").forEach(function (it) {
                const ok = catOk && (q === "" || it.dataset.name.indexOf(q) !== -1);
                it.style.display = ok ? "" : "none";
                if (ok) visible++;
            });

            // Search chalu hoy to khali category chhupavo
            const hasItems = sec.querySelectorAll(".item").length > 0;
            const hide = !catOk || (q !== "" && visible === 0) || (!hasItems && q !== "");
            sec.style.display = hide ? "none" : "";
            if (!hide) shown++;
        });

        document.getElementById("noResult").style.display = shown === 0 ? "block" : "none";
    }

    chips.forEach(function (chip) {
        chip.addEventListener("click", function () {
            chips.forEach(function (c) { c.classList.remove("on"); });
            chip.classList.add("on");
            activeCat = chip.dataset.cat;
            applyFilter();
        });
    });

    search.addEventListener("input", applyFilter);
</script>

</body>
</html>