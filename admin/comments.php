<?php
require_once '../includes/config.php';
if(!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $per_page;

$stmt = prepare_query($conn, "SELECT COUNT(*) as total FROM comments");
mysqli_stmt_execute($stmt);
$total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
$total_pages = ceil($total / $per_page);

$stmt = prepare_query($conn, "SELECT c.*, u.username, p.title FROM comments c 
    JOIN users u ON c.user_id = u.id 
    JOIN posts p ON c.post_id = p.id 
    ORDER BY c.created_at DESC LIMIT ?, ?", [$start, $per_page]);
mysqli_stmt_execute($stmt);
$comments = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Comments</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Manage Comments</h1>
        <nav class="admin-nav">
            <a href="index.php">Dashboard</a>
            <a href="categories.php">Categories</a>
            <a href="users.php">Users</a>
            <a href="posts.php">Posts</a>
            <a href="comments.php">Comments</a>
        </nav>

        <div class="comments-list">
            <?php while($comment = mysqli_fetch_assoc($comments)): ?>
                <div class="comment-item">
                    <p><?php echo htmlspecialchars($comment['content']); ?></p>
                    <div class="meta">
                        <span>By <?php echo htmlspecialchars($comment['username']); ?></span>
                        <span>On: <?php echo htmlspecialchars($comment['title']); ?></span>
                        <span><?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?></span>
                    </div>
                    <a href="index.php?delete_comment=<?php echo $comment['id']; ?>" class="delete-btn"
                       onclick="return confirm('Are you sure?')">Delete</a>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="pagination">
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="<?php echo $page == $i ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
</body>
</html>