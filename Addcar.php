<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$success = '';
$error = '';
$user_id = $_SESSION['user_id'];

$uploadDir = __DIR__ . '/uploads/';
$uploadWebPath = 'uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$checkColumn = $conn->query("SHOW COLUMNS FROM cars LIKE 'image_url'");
if ($checkColumn && $checkColumn->num_rows == 0) {
    $conn->query("ALTER TABLE cars ADD image_url VARCHAR(255)");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $color = trim($_POST['color'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $mileage = trim($_POST['mileage'] ?? '');
    $battery_range = trim($_POST['battery_range'] ?? '');

    $carImage = $_FILES['carImage'] ?? null;

    $patt_x = '/^[A-Za-z\s]+$/';
    $patt_z = '/^\d{4}$/';
    $patt_n = '/^\d+(\.\d{1,2})?$/';

    $valid = true;

    if (!preg_match($patt_x, $color)) {
        $error = "The color is not valid!";
        $valid = false;
    }
    elseif ($model === '') {
        $error = "Please enter the model!";
        $valid = false;
    }
    elseif (!preg_match($patt_z, $year)) {
        $error = "The year is not valid!";
        $valid = false;
    }
    elseif ($location === '') {
        $error = "Please enter the location!";
        $valid = false;
    }
    elseif (!preg_match($patt_n, $price)) {
        $error = "The price is not valid!";
        $valid = false;
    }
    elseif (!preg_match($patt_n, $mileage)) {
        $error = "The mileage is not valid!";
        $valid = false;
    }
    elseif (!preg_match($patt_n, $battery_range)) {
        $error = "The battery range is not valid!";
        $valid = false;
    }
    elseif (!$carImage || $carImage['error'] !== UPLOAD_ERR_OK) {
        $error = "Please upload your car image!";
        $valid = false;
    }

    if ($valid) {

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (in_array($carImage['type'], $allowedTypes)) {

            $originalName = basename($carImage['name']);
            $safeName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $originalName);

            $fileName = uniqid() . '_' . $safeName;

            $serverUploadPath = $uploadDir . $fileName;
            $databaseImagePath = $uploadWebPath . $fileName;

            if (move_uploaded_file($carImage['tmp_name'], $serverUploadPath)) {

                $parts = explode(' ', $model);
                $brand = $parts[0] ?? 'Unknown';

                $seller_id = $user_id;

                $yearInt = intval($year);
                $priceFloat = floatval($price);
                $mileageInt = intval($mileage);
                $batteryRangeInt = intval($battery_range);

                $description = "Color: " . $color . ". Location: " . $location . ".";

                $sql = "INSERT INTO cars
                (seller_id, brand, model, year, price, mileage, battery_range, description, image_url)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);

                if ($stmt) {

                    $stmt->bind_param(
                        "issidiiss",
                        $seller_id,
                        $brand,
                        $model,
                        $yearInt,
                        $priceFloat,
                        $mileageInt,
                        $batteryRangeInt,
                        $description,
                        $databaseImagePath
                    );

                    if ($stmt->execute()) {
                        $success = "successful submission!";
                        $_POST = [];
                    } else {
                        $error = "Database insert failed: " . $stmt->error;
                    }

                } else {
                    $error = "SQL prepare failed: " . $conn->error;
                }

            } else {
                $error = "Failed to upload image!";
            }

        } else {
            $error = "Only JPG, PNG, GIF, WEBP images are allowed!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Add Car</title>

<style>

*{
    box-sizing:border-box;
    font-family:Arial,sans-serif;
}

body{
    background-image:url("background-img.png");
    padding:0 1rem;
    background-color:whitesmoke;
}

.nav{
    background-color:midnightblue;
    overflow:hidden;
    margin-bottom:2rem;
}

.nav a{
    float:left;
    display:block;
    color:white;
    text-align:center;
    padding:20px 20px;
    text-decoration:none;
}

.nav a:hover{
    background-color:wheat;
    color:black;
}

h1{
    text-align:center;
    color:#333;
    margin-bottom:2rem;
}

.form-container{
    max-width:550px;
    margin:2rem auto;
    background-color:white;
    padding:24px;
    border-radius:8px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

.form-group{
    margin:1.5rem;
}

input{
    width:100%;
    padding:1rem;
    border:1px solid #ddd;
    border-radius:4px;
    font-size:1rem;
}

label{
    display:block;
    color:#555;
    margin:0.5rem;
    font-weight:700;
}

button{
    background-color:dodgerblue;
    width:100%;
    padding:1rem;
    color:white;
    border:none;
    border-radius:4px;
    font-size:1rem;
    cursor:pointer;
}

button:hover{
    background-color:mediumblue;
}

.success{
    color:green;
    text-align:center;
    margin-bottom:1rem;
}

.error{
    color:red;
    text-align:center;
    margin-bottom:1rem;
}

</style>

<script>

function checkForm(){

    x=document.getElementById("color").value;
    y=document.getElementById("model").value;
    z=document.getElementById("year").value;
    m=document.getElementById("location").value;
    n=document.getElementById("price").value;
    q=document.getElementById("mileage").value;
    r=document.getElementById("battery_range").value;

    t=document.getElementById("carImage").files;

    patt_x=/^[A-Za-z\s]+$/;
    patt_z=/^\d{4}$/;
    patt_n=/^\d+(\.\d{1,2})?$/;

    if(!patt_x.test(x)){
        alert("The color is not valid!");
        return false;
    }

    if(y==""){
        alert("Please enter the model!");
        return false;
    }

    if(!patt_z.test(z)){
        alert("The year is not valid!");
        return false;
    }

    if(m==""){
        alert("Please enter the location!");
        return false;
    }

    if(!patt_n.test(n)){
        alert("The price is not valid!");
        return false;
    }

    if(!patt_n.test(q)){
        alert("The mileage is not valid!");
        return false;
    }

    if(!patt_n.test(r)){
        alert("The battery range is not valid!");
        return false;
    }

    if(t.length==0){
        alert("Please upload your car image!");
        return false;
    }

    return true;
}

</script>
</head>

<body>

<div class="nav">
    <a href="Homepage.html">Home</a>
    <a href="Login.php">Login</a>
    <a href="Registration.php">Seller Register</a>
    <a href="Addcar.php">Publish Car</a>
    <a href="Search.php">Search</a>
    <a style="float:right" href="Login.php">
        Logout (<?= htmlspecialchars($_SESSION['username'] ?? 'Seller') ?>)
    </a>
</div>

<form class="form-container"
      onsubmit="return checkForm()"
      method="POST"
      enctype="multipart/form-data">

    <h1>Add a New Car</h1>

    <?php if($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="form-group">
        <label for="color">Color:</label>
        <input type="text"
               id="color"
               name="color"
               placeholder="e.g.:Red, Blue, Black">
    </div>

    <div class="form-group">
        <label for="model">Model:</label>
        <input type="text"
               id="model"
               name="model"
               placeholder="e.g.:BMW M5">
    </div>

    <div class="form-group">
        <label for="year">Year:</label>
        <input type="text"
               id="year"
               name="year"
               placeholder="e.g.:2022">
    </div>

    <div class="form-group">
        <label for="location">Location:</label>
        <input type="text"
               id="location"
               name="location"
               placeholder="e.g.:California">
    </div>

    <div class="form-group">
        <label for="price">Price:</label>
        <input type="text"
               id="price"
               name="price"
               placeholder="e.g.:250000">
    </div>

    <div class="form-group">
        <label for="mileage">Mileage:</label>
        <input type="text"
               id="mileage"
               name="mileage"
               placeholder="e.g.:12000">
    </div>

    <div class="form-group">
        <label for="battery_range">Battery Range:</label>
        <input type="text"
               id="battery_range"
               name="battery_range"
               placeholder="e.g.:650">
    </div>

    <div class="form-group">
        <label for="carImage">Image of car:</label>
        <input type="file"
               id="carImage"
               name="carImage"
               accept="image/*">
    </div>

    <button type="submit">
        Submit Car Details
    </button>

</form>

</body>
</html>