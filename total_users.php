<?php
session_start();
include "config.php";

$q = mysqli_query($conn,"SELECT * FROM loginform");
?>

<!DOCTYPE html>
<html>
<head>
<title>Total Users</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{
    font-family: Arial;
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                url('Assets/images.png') no-repeat center center fixed;
    background-size: cover;
    color:white;
    margin:0;
}
.table{background:white;color:black;}

.home-btn {
            display: block;          
            width: 120px;          
            margin: 20px auto; 
            padding: 10px 25px;
            background: #ff6b6b;
            color: white;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            text-align: center; 
            transition: 0.3s;
        }

        .home-btn:hover {
            background: #ff4757;
        }
</style>
</head>

<body>

<div class="container mt-5">

<h2>Total Users</h2>

<table class="table table-bordered">

<tr>
<th>ID</th>
<th>Username</th>
<th>Email</th>
</tr>

<?php
while($row=mysqli_fetch_assoc($q)){
?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo $row['username']; ?></td>
<td><?php echo $row['email']; ?></td>
</tr>
<?php
}
?>

</table>

</div>
<a href="dashboard.php" class="home-btn">🏠 Home</a>
</body>
</html>