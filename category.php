<?php
require_once 'includes/header.php';
if(!isset($_GET['id'])) header("Location: index.php");

$cat_id = (int)$_GET['id'];
$per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $per_page;

if(isset($_POST['new_post']) && isset($_SESSION['user_id'])) {
    if($_POST['csrf_token'] === $_SESSION['csrf_token']) {
        $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
        $content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);
        $stmt = prepare_query($conn, "INSERT INTO posts (category_id, user_id, title, content) VALUES (?, ?, ?, ?)",
            [$cat_id, $_SESSION['user_id'], $title, $content]);
        mysqli_stmt_execute($stmt);
    }
}

if(isset($_POST['comment']) && isset($_SESSION['user_id'])) {
    if($_POST['csrf_token'] === $_SESSION['csrf_token']) {
        $post_id = (int)$_POST['post_id'];
        $content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);
        $stmt = prepare_query($conn, "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)",
            [$post_id, $_SESSION['user_id'], $content]);
        mysqli_stmt_execute($stmt);
    }
}

if(isset($_GET['like']) && isset($_SESSION['user_id'])) {
    $post_id = (int)$_GET['like'];
    $stmt = prepare_query($conn, "SELECT id FROM likes WHERE user_id = ? AND post_id = ?", 
        [$_SESSION['user_id'], $post_id]);
    mysqli_stmt_execute($stmt);
    if(mysqli_stmt_get_result($stmt)->num_rows == 0) {
        $stmt = prepare_query($conn, "INSERT INTO likes (user_id, post_id) VALUES (?, ?)",
            [$_SESSION['user_id'], $post_id]);
        mysqli_stmt_execute($stmt);
    }
}

$stmt = prepare_query($conn, "SELECT * FROM categories WHERE id = ?", [$cat_id]);
mysqli_stmt_execute($stmt);
$category = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$stmt = prepare_query($conn, "SELECT COUNT(*) as total FROM posts WHERE category_id = ?", [$cat_id]);
mysqli_stmt_execute($stmt);
$total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
$total_pages = ceil($total / $per_page);

$stmt = prepare_query($conn, "SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id 
    WHERE category_id = ? LIMIT ?, ?", [$cat_id, $start, $per_page]);
mysqli_stmt_execute($stmt);
$posts = mysqli_stmt_get_result($stmt);
?>
<div class="container">
    <h1><?php echo htmlspecialchars($category['name']); ?></h1>
    <p><?php echo htmlspecialchars($category['description']); ?></p>
    
    <?php if(isset($_SESSION['user_id'])): ?>
    <form method="POST" class="post-form" style="max-width: 1200px; margin: 20px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <input type="text" name="title" placeholder="Post Title" required 
            style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
        
        <textarea name="content" placeholder="Your post..." required 
            style="width: 100%; min-height: 150px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; margin-bottom: 10px;"></textarea>
        
        <button type="submit" name="new_post" 
            style="width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; transition: background 0.3s;">
            Post
        </button>
    </form>
<?php endif; ?>


    <div class="posts">
        <?php while($post = mysqli_fetch_assoc($posts)): ?>
            <div class="post">
                <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                <p><?php echo htmlspecialchars($post['content']); ?></p>
                <div class="meta">
                    <span>By <?php echo htmlspecialchars($post['username']); ?></span>
                    <span><?php echo date('M d, Y H:i', strtotime($post['created_at'])); ?></span>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="?id=<?php echo $cat_id; ?>&like=<?php echo $post['id']; ?>" class="like-btn">Like</a>
                    <?php endif; ?>
                    <?php
                    $stmt = prepare_query($conn, "SELECT COUNT(*) as likes FROM likes WHERE post_id = ?", [$post['id']]);
                    mysqli_stmt_execute($stmt);
                    $likes = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['likes'];
                    ?>
                    <span><?php echo $likes; ?> Likes</span>
                </div>
                
                <div class="comments">
                    <?php
                    $stmt = prepare_query($conn, "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id 
                        WHERE post_id = ?", [$post['id']]);
                    mysqli_stmt_execute($stmt);
                    $comments = mysqli_stmt_get_result($stmt);
                    while($comment = mysqli_fetch_assoc($comments)):
                    ?>
                        <div class="comment">
                            <p><?php echo htmlspecialchars($comment['content']); ?></p>
                            <span>By <?php echo htmlspecialchars($comment['username']); ?> - 
                                <?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?></span>
                        </div>
                    <?php endwhile; ?>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
    <form method="POST" class="comment-form" style="max-width: 1200px; margin: 20px auto; padding: 15px; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
        
        <textarea name="content" placeholder="Your comment..." required 
            style="width: 100%; min-height: 100px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; margin-bottom: 10px;"></textarea>
        
        <button type="submit" name="comment" 
            style="width: 100%; padding: 10px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; transition: background 0.3s;">
            Comment
        </button>
    </form>
<?php endif; ?>

                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="pagination">
        <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?id=<?php echo $cat_id; ?>&page=<?php echo $i; ?>" 
               class="<?php echo $page == $i ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>