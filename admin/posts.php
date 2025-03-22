<?php
require_once '../includes/config.php';
if(!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $per_page;

$stmt = prepare_query($conn, "SELECT COUNT(*) as total FROM posts");
mysqli_stmt_execute($stmt);
$total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
$total_pages = ceil($total / $per_page);

$stmt = prepare_query($conn, "SELECT p.*, u.username, c.name as category FROM posts p 
    JOIN users u ON p.user_id = u.id 
    JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC LIMIT ?, ?", [$start, $per_page]);
mysqli_stmt_execute($stmt);
$posts = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Posts</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Manage Posts</h1>
        <nav class="admin-nav">
            <a href="index.php">Dashboard</a>
            <a href="categories.php">Categories</a>
            <a href="users.php">Users</a>
            <a href="posts.php">Posts</a>
            <a href="comments.php">Comments</a>
        </nav>

        <div class="posts-list">
            <?php while($post = mysqli_fetch_assoc($posts)): ?>
                <div class="post-item">
                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($post['content'], 0, 100)) . '...'; ?></p>
                    <div class="meta">
                        <span>By <?php echo htmlspecialchars($post['username']); ?></span>
                        <span>Category: <?php echo htmlspecialchars($post['category']); ?></span>
                        <span><?php echo date('M d, Y H:i', strtotime($post['created_at'])); ?></span>
                    </div>
                    <a href="index.php?delete_post=<?php echo $post['id']; ?>" class="delete-btn"
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