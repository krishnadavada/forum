<?php
require_once '../includes/config.php';
if(!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

if(isset($_GET['delete_post']) && is_numeric($_GET['delete_post'])) {
    $stmt = prepare_query($conn, "DELETE FROM posts WHERE id = ?", [$_GET['delete_post']]);
    mysqli_stmt_execute($stmt);
    header("Location: index.php");
    exit;
}

if(isset($_GET['delete_comment']) && is_numeric($_GET['delete_comment'])) {
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
            foreach($tables as $table) {
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
            while($post = mysqli_fetch_assoc($posts)) {
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
            while($comment = mysqli_fetch_assoc($comments)) {
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