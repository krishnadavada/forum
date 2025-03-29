<?php
require_once '../includes/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $per_page;

if (isset($_GET['delete_user']) && is_numeric($_GET['delete_user']) && $_GET['delete_user'] != $_SESSION['user_id']) {
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

        .users-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 30px;
        }

        .user-item {
            background: linear-gradient(135deg, #ffffff, #f9f9f9);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
        }

        .user-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .user-item h3 {
            color: #2c3e50;
            font-size: 22px;
            margin-bottom: 10px;
        }

        .user-item p {
            color: #666;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .meta {
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

        .pagination {
            text-align: center;
            margin: 30px 0;
        }

        .pagination a {
            padding: 10px 16px;
            margin: 0 8px;
            text-decoration: none;
            color: #3498db;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        .pagination a.active {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border-color: #3498db;
        }
    </style>
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
            <?php while ($user = mysqli_fetch_assoc($users)): ?>
                <div class="user-item">
                    <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                    <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
                    <div class="meta">
                        <span>Joined: <?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <a href="?delete_user=<?php echo $user['id']; ?>" class="delete-btn"
                           onclick="return confirm('Are you sure? This will delete all user data!')">Delete</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="<?php echo $page == $i ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
</body>
</html>