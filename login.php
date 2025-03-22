<?php
require_once 'includes/header.php';
$error = '';

if(isset($_POST['login'])) {
    if($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token";
    } else {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } else {
            $stmt = prepare_query($conn, "SELECT * FROM users WHERE email = ?", [$email]);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if($user = mysqli_fetch_assoc($result)) {
                if(password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    if(isset($_POST['remember'])) {
                        $token = bin2hex(random_bytes(16));
                        setcookie('remember', $token, time() + (86400 * 30));
                        $stmt = prepare_query($conn, "UPDATE users SET remember_token = ? WHERE id = ?", [$token, $user['id']]);
                        mysqli_stmt_execute($stmt);
                    }
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Invalid credentials";
                }
            } else {
                $error = "User not found";
            }
        }
    }
}
?>
<div class="container">
    <form method="POST" class="auth-form">
        <h2>Login</h2>
        <?php if($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <div class="options">
            <label><input type="checkbox" name="remember"> Remember Me</label>
            <a href="forgot-password.php">Forgot Password?</a>
        </div>
        <button type="submit" name="login">Login</button>
    </form>
</div>
<?php require_once 'includes/footer.php'; ?>