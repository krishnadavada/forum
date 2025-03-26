<?php
require_once 'includes/header.php';

if (!isset($_GET['q'])) {
    header("Location: index.php");
    exit;
}

$query = filter_var($_GET['q'], FILTER_SANITIZE_STRING);
$per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $per_page;

$stmt = prepare_query($conn, "SELECT COUNT(*) as total FROM posts WHERE title LIKE ? OR content LIKE ?", 
    ["%$query%", "%$query%"]);
mysqli_stmt_execute($stmt);
$total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
$total_pages = ceil($total / $per_page);

$stmt = prepare_query($conn, "SELECT p.*, u.username, c.name as category, c.id as category_id FROM posts p 
    JOIN users u ON p.user_id = u.id 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.title LIKE ? OR p.content LIKE ? 
    LIMIT ?, ?", ["%$query%", "%$query%", $start, $per_page]);
mysqli_stmt_execute($stmt);
$results = mysqli_stmt_get_result($stmt);
?>
<style>
    .pagination {
        margin-top: 20px;
        text-align: center;
    }

    .pagination a {
        padding: 8px 12px;
        margin: 5px;
        text-decoration: none;
        border: 1px solid #3498db;
        color: #3498db;
        border-radius: 4px;
        display: inline-block;
    }

    .pagination a.active {
        background-color: #3498db;
        color: white;
    }
</style>
<div class="container">
    <h1>Search Results for "<span style="color: #e74c3c;"><?php echo htmlspecialchars($query); ?></span>"</h1>
    
    <div class="posts">
        <?php if ($total > 0): ?>
            <?php while ($post = mysqli_fetch_assoc($results)): ?>
                <div class="post" style="border-bottom: 1px solid #ddd; padding: 15px 0">
                    <div style="margin-left: 20px;">
                        <h2>
                            <a href="post.php?id=<?php echo $post['id']; ?>" 
                               style="text-decoration: none; color: #3498db;">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h2>
                        <p><?php echo htmlspecialchars(substr($post['content'], 0, 200)) . '...'; ?></p>
                        <div class="meta" style="color: #777; font-size: 14px;">
                            <span>By <strong><?php echo htmlspecialchars($post['username']); ?></strong></span> | 
                            <span>
                                Category: 
                                <a href="category.php?id=<?php echo $post['category_id']; ?>"
                                   style="font-weight: bold; text-decoration: none; <?php echo (strcasecmp($post['category'], $query) == 0) ? 'color: #e74c3c;' : 'color: #3498db;'; ?>">
                                    <?php echo htmlspecialchars($post['category']); ?>
                                </a>
                            </span> | 
                            <span><?php echo date('M d, Y H:i', strtotime($post['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No results found for "<strong><?php echo htmlspecialchars($query); ?></strong>"</p>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?q=<?php echo urlencode($query); ?>&page=<?php echo $i; ?>" 
                   class="<?php echo $page == $i ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>