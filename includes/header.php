<?php 
require_once(__DIR__ . "/includes/config.php");
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
    <link rel="stylesheet" href="C:\Users\Admin\Desktop\PhpForum\css\style.css">
</head>
<body>
    <header>
        <div class="container">
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