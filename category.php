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
<style>
    h1 {
        font-size: 32px;
        color: #2c3e50;
        margin: 40px 0 20px;
        font-weight: 700;
        text-align: center;
    }

    p {
        color: #666;
        font-size: 16px;
        text-align: center;
        margin-bottom: 30px;
    }

    .post-form,
    .comment-form {
        background: linear-gradient(135deg, #ffffff, #f9f9f9);
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        margin: 30px auto;
        max-width: 1200px;
        border: 1px solid #e0e0e0;
    }

    .post-form textarea,
    .comment-form textarea {
        width: 100%;
        min-height: 120px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        margin: 15px 0;
        font-size: 16px;
        background: #fff;
        transition: border-color 0.3s ease;
    }

    .post-form textarea:focus,
    .comment-form textarea:focus {
        border-color: #3498db;
        outline: none;
        box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
    }

    .post-form input,
    .comment-form button {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .post-form button {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .post-form input {
        background: #fff;
        color: #333;
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .post-form button:hover,
    .comment-form button:hover {
        background: linear-gradient(135deg, #2980b9, #1e6f9f);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    .posts .post {
        background: linear-gradient(135deg, #ffffff, #f9f9f9);
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
        width: 900px;
    }

    .posts .post:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    }

    .posts .post h2 {
        color: #2c3e50;
        margin-bottom: 15px;
        font-size: 24px;
        font-weight: 600;
    }

    .posts .meta {
        color: #777;
        font-size: 14px;
        margin-top: 15px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .posts .meta a.like-btn {
        color: #3498db;
        text-decoration: none;
        padding: 6px 12px;
        border: 1px solid #3498db;
        border-radius: 25px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .posts .meta a.like-btn:hover {
        background: #3498db;
        color: white;
        transform: translateY(-2px);
    }

    .comments .comment {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        margin-top: 15px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        border-left: 4px solid #3498db;
    }

    .comments .comment p {
        margin-bottom: 10px;
        color: #333;
        font-size: 15px;
        text-align: left;
    }

    .comments .comment span {
        color: #777;
        font-size: 13px;
    }

    .pagination {
        margin: 50px 0;
        text-align: center;
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
<div class="container">
    <h1><?php echo htmlspecialchars($category['name']); ?></h1>
    <p><?php echo htmlspecialchars($category['description']); ?></p>
    
    <?php if(isset($_SESSION['user_id'])): ?>
    <form method="POST" class="post-form">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="text" name="title" placeholder="Post Title" required>
        <textarea name="content" placeholder="Your post..." required></textarea>
        <button type="submit" name="new_post">Post</button>
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
                    <form method="POST" class="comment-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <textarea name="content" placeholder="Your comment..." required></textarea>
                        <button type="submit" name="comment">Comment</button>
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