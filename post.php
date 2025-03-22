<?php
require_once 'includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php"); // Redirect if no valid ID is given
    exit;
}

$post_id = $_GET['id'];

// Fetch post details
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

<div class="container">
    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    <p style="color: #777;">By <strong><?php echo htmlspecialchars($post['username']); ?></strong> | 
       Category: <a href="category.php?id=<?php echo $post['category_id']; ?>" style="color: #3498db;">
       <?php echo htmlspecialchars($post['category']); ?></a> | 
       <span><?php echo date('M d, Y H:i', strtotime($post['created_at'])); ?></span>
    </p>

    <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>

    <a href="index.php" style="display: inline-block; margin-top: 20px; padding: 10px 15px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px;">Back to Home</a>
</div>

<?php require_once 'includes/footer.php'; ?>
