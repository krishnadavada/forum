<?php 
require_once 'includes/header.php';
$per_page = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $per_page;

$stmt = prepare_query($conn, "SELECT COUNT(*) as total FROM categories");
mysqli_stmt_execute($stmt);
$total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
$total_pages = ceil($total / $per_page);

$stmt = prepare_query($conn, "SELECT * FROM categories LIMIT ?, ?", [$start, $per_page]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<div class="container">
    <div class="categories" style="margin-top:40px;">
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="card">
                <a href="category.php?id=<?php echo $row['id']; ?>">
                    <h2><?php echo htmlspecialchars($row['name']); ?></h2>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
    <div class="pagination" style="margin-bottom:84px;margin-top:50px">
        <?php for($i = 1; $i <= $total_pages; $i++): ?>
       <?php if ($i > 1) { ?> 
    <div style="display: inline-block; margin-bottom: 132px;">
<?php } else { ?> 
    <div style="display: inline-block; margin-bottom: 4px;">
<?php } ?>

            <a href="?page=<?php echo $i; ?>" class="<?php echo $page == $i ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            </div>
         
        <?php endfor; ?>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>