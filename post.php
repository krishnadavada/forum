<?php
require_once 'includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$post_id = $_GET['id'];

$stmt = prepare_query($conn, "SELECT p.*, u.username, c.name as category FROM posts p 
    JOIN users u ON p.user_id = u.id 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?", [$post_id]);

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$post = mysqli_fetch_assoc($result);

if (!$post) {
    echo "<h2>Post not found!</h2>";
    exit;
}
?>
<style>
    h1 {
        font-size: 32px;
        color: #2c3e50;
        margin: 40px 0 20px;
        font-weight: 700;
        text-align: center;
    }

    .post-meta {
        color: #777;
        font-size: 14px;
        margin-bottom: 25px;
        text-align: center;
    }

    .post-meta strong {
        color: #333;
    }

    .post-meta a {
        color: #3498db;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .post-meta a:hover {
        color: #2980b9;
        text-decoration: underline;
    }

    .post-content {
        background: linear-gradient(135deg, #ffffff, #f9f9f9);
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        border: 1px solid #e0e0e0;
        margin-bottom: 30px;
        font-size: 16px;
        color: #333;
        line-height: 1.8;
    }

    .back-btn {
        display: inline-block;
        padding: 12px 25px;
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
        text-decoration: none;
        border-radius: 25px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .back-btn:hover {
        background: linear-gradient(135deg, #2980b9, #1e6f9f);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
</style>
<div class="container">
    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    <div class="post-meta">
        By <strong><?php echo htmlspecialchars($post['username']); ?></strong> | 
        Category: <a href="category.php?id=<?php echo $post['category_id']; ?>">
            <?php echo htmlspecialchars($post['category']); ?>
        </a> | 
        <span><?php echo date('M d, Y H:i', strtotime($post['created_at'])); ?></span>
    </div>
    <div class="post-content">
        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
    </div>
    <a href="index.php" class="back-btn">Back to Home</a>
</div>
<?php require_once 'includes/footer.php'; ?>