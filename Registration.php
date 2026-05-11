<?php
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($full_name === '' || $email === '' || $username === '' || $password === '' || $phone === '') {
        $error = "Please fill in all fields.";
    } else {
        $checkSql = "SELECT seller_id FROM sellers WHERE username = ? OR email = ?";
        $checkStmt = $conn->prepare($checkSql);

        if (!$checkStmt) {
            die("SQL prepare failed: " . $conn->error);
        }

        $checkStmt->bind_param("ss", $username, $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            $insertSql = "INSERT INTO sellers (full_name, email, username, password, phone)
                          VALUES (?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($insertSql);

            if (!$stmt) {
                die("SQL prepare failed: " . $conn->error);
            }

            $stmt->bind_param("sssss", $full_name, $email, $username, $password, $phone);

            if ($stmt->execute()) {
                echo "<script>
                        alert('Registration successful! Please login.');
                        window.location.href='Login.php';
                      </script>";
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Registration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            min-height: 100vh;
        }

        .navbar {
            width: 100%;
            background-color: #122e8a;
            padding: 22px 0;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 999;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-evenly;
            padding: 0 20px;
            align-items: center;
        }

        .nav-link {
            color: #f5efea;
            text-decoration: none;
            font-size: 17px;
            border-bottom: 2px solid transparent;
            padding-bottom: 5px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .nav-link:hover {
            color: #ffffff;
            border-bottom: 2px solid #e72d48;
            transform: scale(1.08);
        }

        .nav-link.active {
            color: #ffffff;
            border-bottom: 2px solid #e72d48;
            font-weight: bold;
        }

        .page-content {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 120px 20px 40px;
        }

        .register-card {
            width: 100%;
            max-width: 480px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 22px rgba(0, 0, 0, 0.12);
            padding: 42px 36px;
        }

        .register-title {
            text-align: center;
            font-size: 28px;
            color: #222;
            margin-bottom: 28px;
            font-weight: 600;
        }

        .input-group {
            margin-bottom: 18px;
        }

        .input-group input {
            width: 100%;
            height: 48px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 0 16px;
            font-size: 15px;
            outline: none;
        }

        .input-group input:focus {
            border-color: #409eff;
        }

        .register-btn {
            width: 100%;
            height: 50px;
            background-color: #e72d48;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 10px;
        }

        .register-btn:hover {
            background-color: #d1243e;
        }

        .error {
            color: #e72d48;
            text-align: center;
            margin-bottom: 16px;
            font-size: 15px;
        }

        .success {
            color: green;
            text-align: center;
            margin-bottom: 16px;
            font-size: 15px;
        }

        .login-link {
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
        }

        .login-link a {
            color: #122e8a;
            text-decoration: none;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 14px;
            }

            .register-card {
                padding: 30px 24px;
            }

            .register-title {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="Homepage.html" class="nav-link">homepage</a>
        <a href="Search.php" class="nav-link">search-page</a>
        <a href="Login.php" class="nav-link">login-page</a>
        <a href="Addcar.php" class="nav-link">add-car-page</a>
        <a href="Registration.php" class="nav-link active">register-page</a>
    </div>
</nav>

<div class="page-content">
    <div class="register-card">
        <h2 class="register-title">Seller Registration</h2>

        <?php if ($error !== ''): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="Registration.php">
            <div class="input-group">
                <input type="text" name="full_name" placeholder="Full Name" required>
            </div>

            <div class="input-group">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>

            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>

            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <div class="input-group">
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>

            <button type="submit" class="register-btn">Register</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="Login.php">Log in here</a>
        </div>
    </div>
</div>

</body>
</html>