<?php
include 'db.php';

$cars = [];
$searched = false;
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $searched = true;

    $model = trim($_POST["model"] ?? "");
    $year = trim($_POST["year"] ?? "");
    $price = trim($_POST["price"] ?? "");

    $sql = "SELECT * FROM cars WHERE 1=1";

    if ($model !== "") {
        $model_safe = mysqli_real_escape_string($conn, $model);
        $sql .= " AND (brand LIKE '%$model_safe%' OR model LIKE '%$model_safe%')";
    }

    if ($year !== "") {
        $year_safe = mysqli_real_escape_string($conn, $year);
        $sql .= " AND year = '$year_safe'";
    }

    if ($price !== "") {
        if ($price === "Under $100K") {
            $sql .= " AND price < 100000";
        } elseif ($price === "$100K - $200K") {
            $sql .= " AND price BETWEEN 100000 AND 200000";
        } elseif ($price === "$200K - $300K") {
            $sql .= " AND price BETWEEN 200000 AND 300000";
        } elseif ($price === "$300K - $500K") {
            $sql .= " AND price BETWEEN 300000 AND 500000";
        } elseif ($price === "Above $500K") {
            $sql .= " AND price > 500000";
        }
    }

    $result = mysqli_query($conn, $sql);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $cars[] = $row;
        }
    } else {
        $error = "Search failed: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Cars - Online Car Sale</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #222;
        }

        header {
            background: #111827;
            padding: 18px 60px;
            color: white;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #38bdf8;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 24px;
            font-size: 15px;
        }

        .nav-links a:hover {
            color: #38bdf8;
        }

        .hero {
            background: linear-gradient(135deg, #0f172a, #1e3a8a);
            color: white;
            text-align: center;
            padding: 70px 20px;
        }

        .hero h1 {
            font-size: 42px;
            margin-bottom: 12px;
        }

        .hero p {
            font-size: 18px;
            color: #dbeafe;
        }

        .search-box {
            max-width: 900px;
            margin: -40px auto 40px;
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.12);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        input, select, button {
            padding: 13px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 15px;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background: #1d4ed8;
        }

        .results {
            max-width: 1100px;
            margin: 0 auto 60px;
            padding: 0 20px;
        }

        .results h2 {
            margin-bottom: 20px;
            color: #111827;
        }

        .car-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px;
        }

        .car-card {
            background: white;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            border-top: 5px solid #2563eb;
        }

        .car-card h3 {
            margin-top: 0;
            color: #1e3a8a;
            font-size: 22px;
        }

        .car-card p {
            margin: 8px 0;
            color: #374151;
        }

        .price {
            color: #dc2626;
            font-size: 22px;
            font-weight: bold;
            margin-top: 14px;
        }

        .no-result {
            background: white;
            padding: 30px;
            border-radius: 14px;
            text-align: center;
            color: #6b7280;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        footer {
            background: #111827;
            color: white;
            text-align: center;
            padding: 22px;
        }

        @media (max-width: 768px) {
            header {
                padding: 16px 24px;
            }

            nav {
                flex-direction: column;
                gap: 12px;
            }

            .nav-links a {
                margin: 0 8px;
            }

            .hero h1 {
                font-size: 30px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .search-box {
                margin: -30px 16px 30px;
            }
        }
    </style>
</head>

<body>

<header>
    <nav>
        <div class="logo">Online Car Sale</div>
        <div class="nav-links">
            <a href="Homepage.html">Homepage</a>
            <a href="Registration.php">Registration</a>
            <a href="Login.php">Login</a>
            <a href="AddCar.php">Add Car</a>
            <a href="Search.php">Search</a>
        </div>
    </nav>
</header>

<section class="hero">
    <h1>Search Electric Cars</h1>
    <p>Find your ideal electric vehicle by model, year, or price range.</p>
</section>

<section class="search-box">
    <form method="POST" action="Search.php">
        <div class="form-grid">
            <input 
                type="text" 
                name="model" 
                placeholder="Enter brand or model"
                value="<?php echo htmlspecialchars($_POST['model'] ?? ''); ?>"
            >

            <input 
                type="number" 
                name="year" 
                placeholder="Enter year"
                value="<?php echo htmlspecialchars($_POST['year'] ?? ''); ?>"
            >

            <select name="price">
                <option value="">Any Price</option>
                <option value="Under $100K" <?php if(($_POST['price'] ?? '') === 'Under $100K') echo 'selected'; ?>>Under $100K</option>
                <option value="$100K - $200K" <?php if(($_POST['price'] ?? '') === '$100K - $200K') echo 'selected'; ?>>$100K - $200K</option>
                <option value="$200K - $300K" <?php if(($_POST['price'] ?? '') === '$200K - $300K') echo 'selected'; ?>>$200K - $300K</option>
                <option value="$300K - $500K" <?php if(($_POST['price'] ?? '') === '$300K - $500K') echo 'selected'; ?>>$300K - $500K</option>
                <option value="Above $500K" <?php if(($_POST['price'] ?? '') === 'Above $500K') echo 'selected'; ?>>Above $500K</option>
            </select>

            <button type="submit">Search</button>
        </div>
    </form>
</section>

<section class="results">
    <?php if ($error !== ""): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($searched): ?>
        <h2>Search Results: <?php echo count($cars); ?> car(s) found</h2>

        <?php if (count($cars) > 0): ?>
            <div class="car-grid">
                <?php foreach ($cars as $car): ?>
                    <div class="car-card">
                        <h3>
                            <?php echo htmlspecialchars($car["brand"] ?? "Unknown Brand"); ?>
                            <?php echo htmlspecialchars($car["model"] ?? "Unknown Model"); ?>
                        </h3>

                        <p><strong>Year:</strong> <?php echo htmlspecialchars($car["year"] ?? "N/A"); ?></p>
                        <p><strong>Mileage:</strong> <?php echo htmlspecialchars($car["mileage"] ?? "N/A"); ?> km</p>
                        <p><strong>Battery Range:</strong> <?php echo htmlspecialchars($car["battery_range"] ?? "N/A"); ?> km</p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($car["description"] ?? "No description available."); ?></p>

                        <div class="price">
                            $<?php echo htmlspecialchars($car["price"] ?? "N/A"); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-result">
                <h3>No matching cars found.</h3>
                <p>Please try another model, year, or price range.</p>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="no-result">
            <h3>Please enter search conditions above.</h3>
            <p>You can search by brand, model, year, or price range.</p>
        </div>
    <?php endif; ?>
</section>

<footer>
    <p>© 2026 Online Car Sale. All Rights Reserved.</p>
</footer>

</body>
</html>