<?php
require_once 'includes/header.php';
$message = '';

if(isset($_POST['reset'])) {
    if($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Invalid CSRF token";
    } else {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format";
        } else {
            $stmt = prepare_query($conn, "SELECT id FROM users WHERE email = ?", [$email]);
            mysqli_stmt_execute($stmt);
            if(mysqli_stmt_get_result($stmt)->num_rows > 0) {
                $token = bin2hex(random_bytes(32));
                $stmt = prepare_query($conn, "UPDATE users SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) 
                    WHERE email = ?", [$token, $email]);
                mysqli_stmt_execute($stmt);
                $message = "Password reset link has been sent to your email";
                // In production, send email with reset link: domain.com/reset.php?token=$token
            } else {
                $message = "Email not found";
            }
        }
    }
}
?>
<div class="container">
    <form method="POST" class="auth-form">
        <h2>Forgot Password</h2>
        <?php if($message): ?><p class="message"><?php echo $message; ?></p><?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="email" name="email" placeholder="Email" required>
        <button type="submit" name="reset">Reset Password</button>
    </form>
</div>
<?php require_once 'includes/footer.php'; ?>