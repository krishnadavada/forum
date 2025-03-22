<?php
require_once '../includes/config.php';
if(!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

if(isset($_POST['add_category'])) {
    if($_POST['csrf_token'] === $_SESSION['csrf_token']) {
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $desc = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
        $stmt = prepare_query($conn, "INSERT INTO categories (name, description) VALUES (?, ?)", [$name, $desc]);
        mysqli_stmt_execute($stmt);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Manage Categories</h1>
        <nav class="admin-nav">
            <a href="index.php">Dashboard</a>
            <a href="categories.php">Categories</a>
            <a href="users.php">Users</a>
            <a href="posts.php">Posts</a>
            <a href="comments.php">Comments</a>
        </nav>
        <form method="POST" class="category-form" style="max-width: 500px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center;">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    
    <input type="text" name="name" placeholder="Category Name" required style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
    
    <textarea name="description" placeholder="Description" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; resize: vertical; min-height: 100px;"></textarea>
    
    <button type="submit" name="add_category" style="width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; transition: background 0.3s;">
        Add Category
    </button>
</form>

        
        <div class="categories-list">
            <?php
            $stmt = prepare_query($conn, "SELECT * FROM categories");
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while($cat = mysqli_fetch_assoc($result)) {
                echo "<div class='category-item'>
                    <h3>" . htmlspecialchars($cat['name']) . "</h3>
                    <p>" . htmlspecialchars($cat['description']) . "</p>
                </div>";
            }
            ?>
        </div>
    </div>
</body>
</html>