<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "online_car_sale";
$port = 3306;

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>