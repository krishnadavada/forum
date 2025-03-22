<?php
require_once 'includes/header.php';
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = $message = '';
$user_id = $_SESSION['user_id'];

$stmt = prepare_query($conn, "SELECT * FROM users WHERE id = ?", [$user_id]);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if(isset($_POST['update_profile'])) {
    if($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token";
    } else {
        $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        if(!preg_match("/^[a-zA-Z0-9]{3,20}$/", $username)) {
            $error = "Username must be 3-20 alphanumeric characters";
        } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } else {
            $stmt = prepare_query($conn, "SELECT id FROM users WHERE email = ? AND id != ?", [$email, $user_id]);
            mysqli_stmt_execute($stmt);
            if(mysqli_stmt_get_result($stmt)->num_rows > 0) {
                $error = "Email already in use";
            } else {
                $stmt = prepare_query($conn, "UPDATE users SET username = ?, email = ? WHERE id = ?", 
                    [$username, $email, $user_id]);
                if(mysqli_stmt_execute($stmt)) {
                    $_SESSION['username'] = $username;
                    $message = "Profile updated successfully";
                }
            }
        }
    }
}

if(isset($_POST['change_password'])) {
    if($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token";
    } else {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        
        if(!password_verify($current, $user['password'])) {
            $error = "Current password is incorrect";
        } elseif(strlen($new) < 8) {
            $error = "New password must be at least 8 characters";
        } elseif($new !== $confirm) {
            $error = "Passwords don't match";
        } else {
            $new_hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = prepare_query($conn, "UPDATE users SET password = ? WHERE id = ?", [$new_hash, $user_id]);
            if(mysqli_stmt_execute($stmt)) {
                $message = "Password changed successfully";
            }
        }
    }
}
?>
<div class="container">
    <h1>Your Profile</h1>
    
    <div class="profile-section">
        <h2>Update Profile</h2>
        <?php if($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        <?php if($message): ?><p class="message"><?php echo $message; ?></p><?php endif; ?>
        <form method="POST" class="profile-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <button type="submit" name="update_profile">Update Profile</button>
        </form>

        <h2>Change Password</h2>
        <form method="POST" class="profile-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" name="change_password">Change Password</button>
        </form>

        <h2>Your Activity</h2>
        <div class="user-activity">
            <h3>Your Posts</h3>
            <?php
            $stmt = prepare_query($conn, "SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$user_id]);
            mysqli_stmt_execute($stmt);
            $posts = mysqli_stmt_get_result($stmt);
            while($post = mysqli_fetch_assoc($posts)) {
                echo "<div class='activity-item'>
                    <p>" . htmlspecialchars($post['title']) . "</p>
                    <span>" . date('M d, Y H:i', strtotime($post['created_at'])) . "</span>
                </div>";
            }
            ?>

            <h3>Your Comments</h3>
            <?php
            $stmt = prepare_query($conn, "SELECT c.*, p.title FROM comments c 
                JOIN posts p ON c.post_id = p.id 
                WHERE c.user_id = ? ORDER BY c.created_at DESC LIMIT 5", [$user_id]);
            mysqli_stmt_execute($stmt);
            $comments = mysqli_stmt_get_result($stmt);
            while($comment = mysqli_fetch_assoc($comments)) {
                echo "<div class='activity-item'>
                    <p>" . htmlspecialchars(substr($comment['content'], 0, 50)) . "... on " . 
                        htmlspecialchars($comment['title']) . "</p>
                    <span>" . date('M d, Y H:i', strtotime($comment['created_at'])) . "</span>
                </div>";
            }
            ?>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>