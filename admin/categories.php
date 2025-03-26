<?php
require_once '../includes/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

if (isset($_POST['add_category'])) {
    if ($_POST['csrf_token'] === $_SESSION['csrf_token']) {
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

        .category-form {
            max-width: 500px;
            margin: 20px auto;
            padding: 25px;
            background: linear-gradient(135deg, #ffffff, #f9f9f9);
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .category-form input,
        .category-form textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .category-form input:focus,
        .category-form textarea:focus {
            border-color: #28a745;
            outline: none;
            box-shadow: 0 0 5px rgba(40, 167, 69, 0.3);
        }

        .category-form textarea {
            resize: vertical;
            min-height: 120px;
        }

        .category-form button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #28a745, #218838);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .category-form button:hover {
            background: linear-gradient(135deg, #218838, #1c7430);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }

        .categories-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 30px;
        }

        .category-item {
            background: linear-gradient(135deg, #ffffff, #f9f9f9);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .category-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            border-color: #28a745;
        }

        .category-item h3 {
            color: #2c3e50;
            font-size: 22px;
            margin-bottom: 10px;
        }

        .category-item p {
            color: #666;
            font-size: 16px;
            line-height: 1.5;
        }
    </style>
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
        <form method="POST" class="category-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="text" name="name" placeholder="Category Name" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <button type="submit" name="add_category">Add Category</button>
        </form>
        <div class="categories-list">
            <?php
            $stmt = prepare_query($conn, "SELECT * FROM categories");
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($cat = mysqli_fetch_assoc($result)) {
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