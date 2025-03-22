<?php
require_once '../includes/config.php';
if(!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $per_page;

if(isset($_GET['delete_user']) && is_numeric($_GET['delete_user']) && $_GET['delete_user'] != $_SESSION['user_id']) {
    $stmt = prepare_query($conn, "DELETE FROM users WHERE id = ?", [$_GET['delete_user']]);
    mysqli_stmt_execute($stmt);
    header("Location: users.php");
    exit;
}

$stmt = prepare_query($conn, "SELECT COUNT(*) as total FROM users");
mysqli_stmt_execute($stmt);
$total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
$total_pages = ceil($total / $per_page);

$stmt = prepare_query($conn, "SELECT * FROM users ORDER BY created_at DESC LIMIT ?, ?", [$start, $per_page]);
mysqli_stmt_execute($stmt);
$users = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Manage Users</h1>
        <nav class="admin-nav">
            <a href="index.php">Dashboard</a>
            <a href="categories.php">Categories</a>
            <a href="users.php">Users</a>
            <a href="posts.php">Posts</a>
            <a href="comments.php">Comments</a>
        </nav>

        <div class="users-list">
            <?php while($user = mysqli_fetch_assoc($users)): ?>
                <div class="user-item">
                    <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                    <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
                    <div class="meta">
                        <span>Joined: <?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                    <?php if($user['id'] != $_SESSION['user_id']): ?>
                        <a href="?delete_user=<?php echo $user['id']; ?>" class="delete-btn"
                           onclick="return confirm('Are you sure? This will delete all user data!')">Delete</a>
                    <?php endif; ?>
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