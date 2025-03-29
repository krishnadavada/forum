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
<style>
    h1 {
        font-size: 32px;
        color: #2c3e50;
        margin: 40px 0 20px;
        font-weight: 700;
        text-align: center;
    }

    .profile-form {
        width: 800px;
        margin: 30px auto;
        padding: 30px;
        background: linear-gradient(135deg, #ffffff, #f9f9f9);
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        border: 1px solid #e0e0e0;
    }

    .profile-form h2 {
        margin-bottom: 20px;
        color: #2c3e50;
        font-size: 24px;
        font-weight: 600;
    }

    .profile-form input {
        width: 100%;
        padding: 14px;
        margin: 12px 0;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
        background: #fff;
        transition: all 0.3s ease*.
    }

    .profile-form input:focus {
        border-color: #3498db;
        box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        outline: none;
    }

    .profile-form button {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .profile-form button:hover {
        background: linear-gradient(135deg, #2980b9, #1e6f9f);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    .error {
        color: #e74c3c;
        background: #fadbd8;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        text-align: center;
        border-left: 4px solid #e74c3c;
    }

    .message {
        color: #27ae60;
        background: #d5f5e3;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        text-align: center;
        border-left: 4px solid #27ae60;
    }

    .user-activity {
        margin-top: 40px;
    }

    .user-activity h3 {
        color: #2c3e50;
        font-size: 20px;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .activity-item {
        background: #f9f9f9;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        border-left: 4px solid #3498db;
    }

    .activity-item p {
        color: #333;
        font-size: 15px;
        margin-bottom: 5px;
    }

    .activity-item span {
        color: #777;
        font-size: 13px;
    }
</style>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('.profile-form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const inputs = this.querySelectorAll('input');
                inputs.forEach(input => {
                    if(!input.value.trim()) {
                        e.preventDefault();
                        input.style.borderColor = '#e74c3c';
                    }
                });
            });
        });
    });
</script>
<?php require_once 'includes/footer.php'; ?>