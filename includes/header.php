<?php 
<<<<<<< HEAD
require_once("includes/config.php");

=======
require_once('../includes/config.php');
>>>>>>> 32adb73f0ce782c2f89b34690fb5e17b11960003
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Website</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            display: flex;
            flex-direction: column;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            line-height: 1.6;
            background: #f0f2f5;
            color: #333;
        }

        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 15px 0; /* Reduced padding for a slimmer header */
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        header .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: nowrap; /* Prevent wrapping to ensure single line */
        }

        .logo a {
            color: white;
            text-decoration: none;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 1px;
            transition: color 0.3s ease;
        }

        .logo a:hover {
            color: #ecf0f1;
        }

        nav {
            flex-grow: 1; /* Allow nav to take available space */
            margin: 0 20px;
        }

        nav ul {
            display: flex;
            list-style: none;
            align-items: center;
            justify-content: center; /* Center the nav links */
        }

        nav ul li {
            position: relative;
            margin: 0 15px; /* Reduced margin for tighter spacing */
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        nav ul li a:hover {
            background: rgba(255,255,255,0.1);
            color: #3498db;
        }

        .dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            background: #34495e;
            display: none;
            min-width: 220px;
            border-radius: 6px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.25);
            padding: 10px 0;
            z-index: 1000;
        }

        nav ul li:hover .dropdown {
            display: block;
        }

        .dropdown li a {
            padding: 10px 20px;
            display: block;
            font-size: 14px;
            color: #ecf0f1;
        }

        .dropdown li a:hover {
            background: #2c3e50;
            color: #3498db;
        }

        .search {
            margin: 0 20px; /* Space between nav and search */
        }

        .search form {
            display: flex;
            align-items: center;
        }

        .search input {
            padding: 8px 15px; /* Slightly smaller padding */
            border: none;
            border-radius: 25px 0 0 25px;
            font-size: 14px;
            background: #ecf0f1;
            outline: none;
            width: 150px; /* Reduced width to fit in single line */
            transition: width 0.3s ease, background 0.3s ease;
        }

        .search input:focus {
            width: 180px;
            background: white;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
        }

        .auth {
            display: flex;
            align-items: center;
            gap: 10px; /* Space between auth elements */
        }

        .auth span {
            margin-right: 15px;
            font-weight: 500;
            font-size: 14px;
        }

        .auth a {
            color: white;
            text-decoration: none;
            padding: 8px 15px; /* Smaller padding for buttons */
            border-radius: 25px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .auth a:hover {
            background: linear-gradient(135deg, #2980b9, #1e6f9f);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <header>
        <div class="container" style="flex-direction: row;">
            <div class="logo"><a href="index.php">Forum</a></div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li>
                        <a href="#">Top Categories ▾</a>
                        <ul class="dropdown">
                            <?php
                            $stmt = prepare_query($conn, "SELECT * FROM categories LIMIT 5");
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            while ($cat = mysqli_fetch_assoc($result)) {
                                echo "<li><a href='category.php?id=" . htmlspecialchars($cat['id']) . "'>" . htmlspecialchars($cat['name']) . "</a></li>";
                            }
                            ?>
                        </ul>
                    </li>
                    <li><a href="about.php">About Us</a></li>
                </ul>
            </nav>
            <div class="search">
                <form method="GET" action="search.php">
                    <input type="text" name="q" placeholder="Search..." required>
                </form>
            </div>
            <div class="auth">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span>Hi, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
                    <a href="profile.php">Profile</a>
                    <?php if ($_SESSION['username'] === 'admin'): ?>
                        <a href="admin/index.php">Admin</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="signup.php">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
