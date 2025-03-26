<?php 
require_once 'includes/header.php';
$per_page = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $per_page;

// Get total number of categories
$stmt = prepare_query($conn, "SELECT COUNT(*) as total FROM categories");
mysqli_stmt_execute($stmt);
$total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
$total_pages = ceil($total / $per_page);

// Get categories for the current page
$stmt = prepare_query($conn, "SELECT * FROM categories LIMIT ?, ?", [$start, $per_page]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
$num_categories = count($categories); // Number of categories on this page
?>
<style>
    .categories {
        flex: 1;
        display: grid;
        gap: 25px;
        margin: 40px 0;
        justify-items: center; /* Center cards horizontally */
    }

    /* Dynamic grid layout based on number of categories */
    <?php if ($num_categories == 1): ?>
    .categories {
        grid-template-columns: 1fr; /* 1 card in a single line */
        max-width: 400px; /* Limit width for single card */
        margin-left: auto;
        margin-right: auto;
    }
    <?php elseif ($num_categories == 2): ?>
    .categories {
        grid-template-columns: repeat(2, 1fr); /* 2 cards in a single line */
    }
    <?php elseif ($num_categories == 3): ?>
    .categories {
        grid-template-columns: repeat(3, 1fr); /* 3 cards in a single line */
    }
    <?php elseif ($num_categories >= 4): ?>
    .categories {
        grid-template-columns: repeat(3, 1fr); /* 3 cards per line, remaining in next line */
    }
    <?php endif; ?>

    .card {
        background: linear-gradient(135deg, #ffffff, #f9f9f9);
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        border: 1px solid #e0e0e0;
        width: 100%; /* Ensure card takes full column width */
        max-width: 400px; /* Consistent width for all cards */
    }

    .card:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        border-color: #3498db;
    }

    .card h2 {
        color: #2c3e50;
        margin-bottom: 15px;
        font-size: 24px;
        font-weight: 600;
    }

    .card p {
        color: #666;
        font-size: 16px;
        line-height: 1.5;
    }

    .card a {
        text-decoration: none;
        color: inherit;
        display: block;
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
    <div class="categories">
        <?php foreach($categories as $row): ?>
            <div class="card">
                <a href="category.php?id=<?php echo $row['id']; ?>">
                    <h2><?php echo htmlspecialchars($row['name']); ?></h2>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="pagination">
        <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="<?php echo $page == $i ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
</script>
<?php require_once 'includes/footer.php'; ?>