<?php
require_once 'includes/header.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$error = '';

if(isset($_POST['signup'])) {
    if($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token";
    } else {
        $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $pass1 = $_POST['password'];
        $pass2 = $_POST['password2'];

        if (!preg_match("/^[a-zA-Z0-9]{3,20}$/", $username)) {
            $error = "Username must be 3-20 alphanumeric characters";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } elseif (strlen($pass1) < 8) {
            $error = "Password must be at least 8 characters";
        } elseif ($pass1 !== $pass2) {
            $error = "Passwords don't match";
        } else {
            $stmt = prepare_query($conn, "SELECT id FROM users WHERE email = ?", [$email]);
            mysqli_stmt_execute($stmt);
            if (mysqli_stmt_get_result($stmt)->num_rows > 0) {
                $error = "Email already exists";
            } else {
                $password = password_hash($pass1, PASSWORD_DEFAULT);
                $stmt = prepare_query($conn, "INSERT INTO users (username, email, password) VALUES (?, ?, ?)", 
                    [$username, $email, $password]);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Get the user ID of the newly registered user
                    $new_user_id = mysqli_insert_id($conn);

                    // Set session variables for logged-in user
                    $_SESSION['user_id'] = $new_user_id;
                    $_SESSION['username'] = $username;

                    // Redirect to home page
                    header("Location: index.php");
                    exit;
                }
            }
        }
    }
}
?>
<div class="container">
    <form method="POST" class="auth-form">
        <h2>Sign Up</h2>
        <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="password2" placeholder="Confirm Password" required>
        <button type="submit" name="signup">Sign Up</button>
    </form>
</div>
<?php require_once 'includes/footer.php'; ?>
