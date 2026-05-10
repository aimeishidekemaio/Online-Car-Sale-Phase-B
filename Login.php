<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "Username and password cannot be empty.";
    } else {
        $sql = "SELECT seller_id, username, password FROM sellers WHERE username = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("SQL prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['seller_id'];
                $_SESSION['username'] = $user['username'];

                header("Location: AddCar.php");
                exit();
            } else {
                $error = "Wrong password.";
            }
        } else {
            $error = "User not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background-color: #f5f7fa;
      background-image: url("bg.png");
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      background-attachment: fixed;
      min-height: 100vh;
      padding: 0;
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
      width: 100%;
    }

    .nav-left,
    .nav-right {
      display: contents;
      gap: 0;
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
      transform: scale(1.10);
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
      padding: 120px 20px 20px;
    }

    .login-card {
      width: 100%;
      max-width: 420px;
      background-color: #ffffff;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      padding: 48px 36px;
    }

    .login-title {
      font-size: 26px;
      text-align: center;
      color: #333;
      margin-bottom: 32px;
      font-weight: 600;
    }

    .input-group {
      margin-bottom: 24px;
    }

    .input-group input {
      width: 100%;
      height: 50px;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      padding: 0 18px;
      font-size: 15px;
      outline: none;
    }

    .input-group input:focus {
      border-color: #409eff;
    }

    .login-btn {
      width: 100%;
      height: 50px;
      background-color: #e72d48;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      margin-top: 8px;
    }

    .login-btn:hover {
      background-color: #d1243e;
    }

    .error {
      color: #e72d48;
      text-align: center;
      margin-bottom: 16px;
    }

    @media (max-width: 768px) {
      .nav-container {
        flex-direction: column;
        gap: 15px;
      }

      .nav-link {
        font-size: 15px;
      }

      .login-card {
        padding: 32px 24px;
      }
    }

    @media (max-width: 480px) {
      .navbar {
        padding: 16px 0;
      }

      .page-content {
        padding: 100px 15px 20px;
      }

      .login-card {
        padding: 28px 20px;
      }

      .login-title {
        font-size: 22px;
      }
    }
  </style>
</head>

<body>
  <nav class="navbar">
    <div class="nav-container">
      <div class="nav-left">
        <a href="Homepage.html" class="nav-link">homepage</a>
        <a href="Search.php" class="nav-link">search-page</a>
      </div>

      <a href="Login.php" class="nav-link active">login-page</a>

      <div class="nav-right">
        <a href="Addcar.php" class="nav-link">add-car-page</a>
        <a href="Registration.php" class="nav-link">register-page</a>
      </div>
    </div>
  </nav>

  <div class="page-content">
    <div class="login-card">
      <h2 class="login-title">Account Log In</h2>

      <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="POST" action="Login.php">
        <div class="input-group">
          <input type="text" name="username" placeholder="Please enter your account" required>
        </div>

        <div class="input-group">
          <input type="password" name="password" placeholder="Please enter your password" required>
        </div>

        <button class="login-btn" type="submit">Log in</button>
      </form>
    </div>
  </div>
</body>
</html>