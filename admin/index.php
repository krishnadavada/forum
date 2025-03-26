<?php
require_once '../includes/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

if (isset($_GET['delete_post']) && is_numeric($_GET['delete_post'])) {
    $stmt = prepare_query($conn, "DELETE FROM posts WHERE id = ?", [$_GET['delete_post']]);
    mysqli_stmt_execute($stmt);
    header("Location: index.php");
    exit;
}

if (isset($_GET['delete_comment']) && is_numeric($_GET['delete_comment'])) {
    $stmt = prepare_query($conn, "DELETE FROM comments WHERE id = ?", [$_GET['delete_comment']]);
    mysqli_stmt_execute($stmt);
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f2f5;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            font-size: 32px;
            margin-bottom: 20px;
        }

        .admin-nav {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .admin-nav a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .admin-nav a:hover {
            background: #3498db;
            color: white;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(135deg, #ffffff, #f9f9f9);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .stat-card h3 {
            color: #2c3e50;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .stat-card p {
            color: #666;
            font-size: 24px;
            font-weight: 600;
        }

        h2 {
            color: #2c3e50;
            font-size: 28px;
            margin: 30px 0 20px;
        }

        .recent-activity h3 {
            color: #2c3e50;
            font-size: 22px;
            margin-bottom: 15px;
        }

        .activity-item {
            background: linear-gradient(135deg, #ffffff, #f9f9f9);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            position: relative;
            transition: all 0.3s ease;
        }

        .activity-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .activity-item p {
            color: #333;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .activity-item span {
            color: #666;
            font-size: 14px;
        }

        .delete-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 8px 15px;
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .delete-btn:hover {
            background: linear-gradient(135deg, #c82333, #b21f2d);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Panel</h1>
        <nav class="admin-nav">
            <a href="../index.php">Home</a>
            <a href="index.php">Dashboard</a>
            <a href="categories.php">Categories</a>
            <a href="users.php">Users</a>
            <a href="posts.php">Posts</a>
            <a href="comments.php">Comments</a>
        </nav>
        <div class="stats">
            <?php
            $tables = ['users', 'categories', 'posts', 'comments'];
            foreach ($tables as $table) {
                $stmt = prepare_query($conn, "SELECT COUNT(*) as count FROM $table");
                mysqli_stmt_execute($stmt);
                $count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count'];
                echo "<div class='stat-card'><h3>" . ucfirst($table) . "</h3><p>$count</p></div>";
            }
            ?>
        </div>
        <h2>Recent Activity</h2>
        <div class="recent-activity">
            <h3>Latest Posts</h3>
            <?php
            $stmt = prepare_query($conn, "SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id 
                ORDER BY p.created_at DESC LIMIT 5");
            mysqli_stmt_execute($stmt);
            $posts = mysqli_stmt_get_result($stmt);
            while ($post = mysqli_fetch_assoc($posts)) {
                echo "<div class='activity-item'>
                    <p>" . htmlspecialchars($post['title']) . " by " . htmlspecialchars($post['username']) . "</p>
                    <span>" . date('M d, Y H:i', strtotime($post['created_at'])) . "</span>
                    <a href='?delete_post={$post['id']}' class='delete-btn' 
                       onclick='return confirm(\"Are you sure?\")'>Delete</a>
                </div>";
            }
            ?>
            <h3>Latest Comments</h3>
            <?php
            $stmt = prepare_query($conn, "SELECT c.*, u.username, p.title FROM comments c 
                JOIN users u ON c.user_id = u.id 
                JOIN posts p ON c.post_id = p.id 
                ORDER BY c.created_at DESC LIMIT 5");
            mysqli_stmt_execute($stmt);
            $comments = mysqli_stmt_get_result($stmt);
            while ($comment = mysqli_fetch_assoc($comments)) {
                echo "<div class='activity-item'>
                    <p>" . htmlspecialchars(substr($comment['content'], 0, 50)) . "... on " . 
                        htmlspecialchars($comment['title']) . " by " . htmlspecialchars($comment['username']) . "</p>
                    <span>" . date('M d, Y H:i', strtotime($comment['created_at'])) . "</span>
                    <a href='?delete_comment={$comment['id']}' class='delete-btn' 
                       onclick='return confirm(\"Are you sure?\")'>Delete</a>
                </div>";
            }
            ?>
        </div>
    </div>
</body>
</html>