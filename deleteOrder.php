<?php
session_start();
require_once "config.php";

if(!isset($_SESSION['username']) || $_SESSION['admin'] == 'NO'){
    header("location: index.php");
    exit();
}

if(isset($_GET['order_id'])){
    $order_id = $_GET['order_id'];
    $sql = "DELETE FROM orders WHERE order_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $order_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header("location: sales.php"); 
exit();
?>