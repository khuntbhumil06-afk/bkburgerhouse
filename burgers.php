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

?>

<!DOCTYPE html>
<html>
<head>
    <title>Burger & Menu List</title>
    <style>
        body{
    font-family: Arial;
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                url('Assets/images.png') no-repeat center center fixed;
    background-size: cover;
    color:black;
    margin:0;
    text-align:center;
}

        h2 {
            margin-top: 30px;
            color: #2f3542;
        }

        table {
            margin: 30px auto;
            border-collapse: collapse;
            width: 50%;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        th {
            background: #ff6b6b;
            color: white;
            padding: 12px;
        }

        td {
            padding:8px 10px;
            border-bottom: 1px solid #ddd;
            text-align: left; 
        }

        tr:hover {
            background: #f1f2f6;
        }

        .veg { color: green; font-weight: bold; }
        .nonveg { color: red; font-weight: bold; }
        .combo { color: blue; font-weight: bold; }
        .drink { color: orange; font-weight: bold; }

        .category-header {
            background: #ffa502;
            color: white;
            font-size: 18px;
            text-align: center;
            padding: 6px 10px;
            padding-left: 15px;
        }

        .home-btn {
            display: inline-block;
            margin: 20px auto;
            padding: 10px 25px;
            background: #ff6b6b;
            color: white;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            transition: 0.3s;
        }

        .home-btn:hover {
            background: #ff4757;
        }
    </style>
</head>
<body>

<h2 style="color:red;">Menu List</h2>

<table>
<?php
foreach(["Veg Burger","Non-Veg Burger","Combo","Drink"] as $cat){

    echo "<tr><td colspan='3' class='category-header'>{$cat}</td></tr>";

    foreach($allItems as $row){
        $category = getCategory($row['name'], $categories);
        if($category == $cat){

            $class = "";
            if($category == "Veg Burger") $class="veg";
            elseif($category == "Non-Veg Burger") $class="nonveg";
            elseif($category == "Combo") $class="combo";
            else $class="drink";

            echo "<tr>
                    <td>{$row['name']}</td>
                    <td>₹{$row['price']}</td>
                  </tr>";
        }
    }
}
?>
</table>
<a href="index.php" class="home-btn">🏠 Home</a>
</body>
</html>