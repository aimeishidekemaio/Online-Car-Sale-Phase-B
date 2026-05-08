<?php
// Add_car.php
session_start();  // 启动会话
require_once 'db_connect.php';  // 引入数据库连接

// 1. 验证登录状态：未登录则跳转登录页
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$success = '';
$error = '';
$user_id = $_SESSION['user_id'];  // 获取登录用户ID

// 2. 处理车辆信息提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单数据
    $color = trim($_POST['color']);
    $model = trim($_POST['model']);
    $year = trim($_POST['year']);
    $location = trim($_POST['location']);
    $price = trim($_POST['price']);
    $carImage = $_FILES['carImage'];  // 图片文件

    // 前端验证兜底（后端必须重新验证，防止绕过前端）
    $patt_x = '/^[A-Za-z\s]+$/';
    $patt_z = '/^\d{4}$/';
    $patt_n = '/^\d+(\.\d{1,2})?$/';
    $valid = true;

    // 验证颜色
    if (!preg_match($patt_x, $color)) {
        $error = "The color is not valid!";
        $valid = false;
    }
    // 验证型号
    elseif (empty($model)) {
        $error = "Please enter the model!";
        $valid = false;
    }
    // 验证年份
    elseif (!preg_match($patt_z, $year)) {
        $error = "The year is not valid!";
        $valid = false;
    }
    // 验证位置
    elseif (empty($location)) {
        $error = "Please enter the location!";
        $valid = false;
    }
    // 验证价格
    elseif (!preg_match($patt_n, $price)) {
        $error = "The price is not valid!";
        $valid = false;
    }
    // 验证图片
    elseif ($carImage['error'] !== UPLOAD_ERR_OK) {
        $error = "Please upload your car image!";
        $valid = false;
    }

    if ($valid) {
        // 处理图片上传
        $uploadDir = 'uploads/';  // 图片存储目录（需手动创建，权限755）
        // 生成唯一文件名（避免重复）
        $fileName = uniqid() . '_' . basename($carImage['name']);
        $uploadPath = $uploadDir . $fileName;
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];  // 允许的图片类型

        // 验证图片类型
        if (in_array($carImage['type'], $allowedTypes)) {
            // 移动上传文件到指定目录
            if (move_uploaded_file($carImage['tmp_name'], $uploadPath)) {
                // 3. 存入数据库
                try {
                    $stmt = $pdo->prepare("INSERT INTO cars (user_id, color, model, year, location, price, image_path) 
                                          VALUES (:user_id, :color, :model, :year, :location, :price, :image_path)");
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->bindParam(':color', $color);
                    $stmt->bindParam(':model', $model);
                    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
                    $stmt->bindParam(':location', $location);
                    $stmt->bindParam(':price', $price);
                    $stmt->bindParam(':image_path', $uploadPath);
                    $stmt->execute();

                    $success = "successful submission!";
                    // 清空表单（可选）
                    $_POST = [];
                } catch(PDOException $e) {
                    $error = "Database error: " . $e->getMessage();
                }
            } else {
                $error = "Failed to upload image!";
            }
        } else {
            $error = "Only JPG, PNG, GIF images are allowed!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <title>Add Car</title>
    <style>
        /* 保留原Add car.html的所有样式 */
        *{
          box-sizing:border-box;
          font-family:Arial, sans-serif;
        }
        body {
            background-image:url("background-img.png");
            padding: 0 1rem;
            background-color: whitesmoke;
        }
        .nav {
            background-color: midnightblue;
            overflow: hidden;
            margin-bottom:2rem;
        }
        .nav a{float:left;
               display: block;
               color: white;
               text-align:center;
               padding:20px 20px;
               text-decoration:none;
        }
        .nav a:hover{background-color:wheat;
                     color: black;
        }
        h1{
           text-align:center;
           color:#333;
           margin-bottom:2rem;
        }
        .form-container {
            max-width: 550px;
            margin: 2rem auto;
            background-color: white;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin: 1.5rem;
        }
        input {
            width: 100%;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        label{display:block;
              color:#555;
              margin:0.5rem;
              font-weight:700;
        }
        button {
            background-color: dodgerblue;
            width: 100%;
            padding: 1rem;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
        }
        button:hover{background-color:mediumblue;}
        @media(min-width:768px){.form-container{padding:2rem;}}
        @media(min-width:1024px){.form-container{padding:2rem; max-width:600px;}}
        .success {
            color: green;
            text-align: center;
            margin-bottom: 1rem;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 1rem;
        }
    </style>
    <script>
        // 保留原前端验证（兜底，核心验证在后端）
        function checkForm() {
            x = document.getElementById("color").value;
            y = document.getElementById("model").value;
            z = document.getElementById("year").value;
            m = document.getElementById("location").value;
            n = document.getElementById("price").value;
            t = document.getElementById("carImage").files;
            patt_x = /^[A-Za-z\s]+$/;
            patt_z = /^\d{4}$/;
            patt_n = /^\d+(\.\d{1,2})?$/;
            if (!patt_x.test(x)) {
                alert("The color is not valid!")
                document.getElementById("color").select();
                return false;
            }
            if (y == "") {
                alert("Please enter the model!")
                document.getElementById("model").select();
                return false;
            }
            if (!patt_z.test(z)) {
                alert("The year is not valid!")
                document.getElementById("year").select();
                return false;
            }
            if (m == "") {
                alert("Please enter the location!")
                document.getElementById("location").select();
                return false;
            }
            if (!patt_n.test(n)) {
                alert("The price is not valid!")
                document.getElementById("price").select();
                return false;
            }
            if (t.length == 0) {
                alert("Please upload your car image!")
                return false;
            }
            return true; // 后端处理成功/失败提示
        }
    </script>
</head>
<body>
    <div class="nav">
        <a href="Homepage.php">Home</a>
        <a href="Login.php">Login</a>
        <a href="Registration.php">Seller Register</a>
        <a href="Add car.php">Publish Car</a>
        <a href="Search Page.php">Search</a>
        <!-- 显示登录用户名 -->
        <a style="float:right" href="Logout.php">Logout (<?= $_SESSION['username'] ?>)</a>
    </div>

    <form class="form-container" onsubmit="return checkForm()" method="POST" enctype="multipart/form-data">
        <h1>Add a New Car</h1>
        <!-- 显示后端提示信息 -->
        <?php if ($success): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <div class="form-group">
            <label for="color">Color:</label>
            <input type="text" id="color" name="color" placeholder="e.g.:Red, Blue, Black" value="<?= $_POST['color'] ?? '' ?>"/>
        </div>
        <div class="form-group">
            <label for="model">Model:</label>
            <input type="text" id="model" name="model" placeholder="e.g.:Toyota Camry, BMW 3 Series" value="<?= $_POST['model'] ?? '' ?>"/>
        </div>
        <div class="form-group">
            <label for="year">Year:</label>
            <input type="text" id="year" name="year" placeholder="e.g.:2022" value="<?= $_POST['year'] ?? '' ?>"/>
        </div>
        <div class="form-group">
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" placeholder="e.g.:Los Angeles, California" value="<?= $_POST['location'] ?? '' ?>"/>
        </div>
        <div class="form-group">
            <label for="price">Price</label>
            <input type="text" id="price" name="price" placeholder="e.g.:25000, 200000" value="<?= $_POST['price'] ?? '' ?>"/>
        </div>
        <div class="form-group">
            <label for="carImage">Image of car:</label>
            <input type="file" id="carImage" name="carImage" accept="image/*"/>
        </div>

        <button type="submit">Submit Car Details</button>
    </form>
</body>
</html>
