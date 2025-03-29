<?php
require_once 'includes/config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables
$error = $message = '';

// Handle login logic before any output
if (isset($_POST['login'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token";
    } else {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        
        // Server-side validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } elseif (empty($password)) {
            $error = "Password cannot be empty";
        } else {
            $stmt = prepare_query($conn, "SELECT * FROM users WHERE email = ?", [$email]);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($user = mysqli_fetch_assoc($result)) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    if (isset($_POST['remember'])) {
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
                $error = "Email not found";
            }
        }
    }
}

// Handle reset password logic before any output
if (isset($_POST['reset'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token";
    } else {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Server-side validation for reset password
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } elseif (strlen($new_password) < 8) {
            $error = "Password must be at least 8 characters long";
        } elseif (!preg_match("/[A-Z]/", $new_password) || !preg_match("/[a-z]/", $new_password) || !preg_match("/[0-9]/", $new_password)) {
            $error = "Password must contain at least one uppercase letter, one lowercase letter, and one number";
        } elseif ($new_password !== $confirm_password) {
            $error = "New password and confirm password do not match";
        } else {
            $stmt = prepare_query($conn, "SELECT id FROM users WHERE email = ?", [$email]);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($user = mysqli_fetch_assoc($result)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = prepare_query($conn, "UPDATE users SET password = ? WHERE email = ?", [$hashed_password, $email]);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Password updated successfully. Please log in with your new password.";
                } else {
                    $error = "Failed to update password. Please try again.";
                }
            } else {
                $error = "Email not found";
            }
        }
    }
}

// Now include header.php after all header-modifying logic
require_once 'includes/header.php';
?>
<style>
    .auth-container {
        max-width: 450px;
        width: 400px; /* Fixed the syntax from width:400 */
        margin: 60px auto;
        padding: 35px;
        background: linear-gradient(135deg, #ffffff, #f9f9f9);
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        border: 1px solid #e0e0e0;
    }

    .auth-container h2 {
        margin-bottom: 25px;
        color: #2c3e50;
        font-size: 28px;
        font-weight: 600;
        text-align: center;
    }

    .auth-container input {
        width: 100%;
        padding: 14px;
        margin: 12px 0;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
        background: #fff;
        box-sizing: border-box; /* Added to prevent size changes */
        transition: border-color 0.3s ease, box-shadow 0.3s ease; /* Limited transition properties */
    }

    .auth-container input:focus {
        border: 1px solid #3498db; /* Consistent border width */
        box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        outline: none;
    }

    .auth-container button {
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

    .auth-container button:hover {
        background: linear-gradient(135deg, #2980b9, #1e6f9f);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    .options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 15px 0;
        font-size: 14px;
    }

    .options label {
        color: #666;
    }

    .options a {
        color: #3498db;
        text-decoration: none;
        cursor: pointer;
        transition: color 0.3s ease;
    }

    .options a:hover {
        color: #2980b9;
        text-decoration: underline;
    }

    .sign {
        margin: 15px 0;
        font-size: 14px;
        text-align: center;
    }

    .sign a {
        color: #3498db;
        text-decoration: none;
        cursor: pointer;
        transition: color 0.3s ease;
    }

    .sign a:hover {
        color: #2980b9;
        text-decoration: underline;
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

    .login-form {
        display: block;
    }

    .reset-form {
        display: none;
    }

    .form-toggle {
        transition: all 0.3s ease;
    }

    .error-field {
        border-color: #e74c3c !important;
    }

    .error-message {
        color: #e74c3c;
        font-size: 12px;
        margin-top: -10px;
        margin-bottom: 10px;
        display: none;
    }
</style>
<div class="container">
    <div class="auth-container">
        <!-- Login Form -->
        <form method="POST" class="login-form form-toggle" id="login-form">
            <h2>Login</h2>
            <?php if ($error && !isset($_POST['reset'])): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
            <?php if ($message): ?><p class="message"><?php echo $message; ?></p><?php endif; ?>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="email" name="email" id="email" placeholder="Email" required>
            <p class="error-message" id="email-error"></p>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <p class="error-message" id="password-error"></p>
            <div class="options">
                &nbsp;
                <a onclick="toggleForms()">Forgot Password?</a>
            </div>
            <button type="submit" name="login">Login</button>
            <div class="sign">
                Create a new account? <a href="signup.php">Sign up</a>
            </div>
        </form>

        <!-- Reset Password Form -->
        <form method="POST" class="reset-form form-toggle" id="reset-form">
            <h2>Reset Password</h2>
            <?php if ($error && isset($_POST['reset'])): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
            <?php if ($message): ?><p class="message"><?php echo $message; ?></p><?php endif; ?>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="new_password" id="new_password" placeholder="New Password" required>
            <p class="error-message" id="new-password-error"></p>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
            <p class="error-message" id="confirm-password-error"></p>
            <button type="submit" name="reset">Save</button>
            <div class="options">
                <a onclick="toggleForms()">Back to Login</a>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('login-form');
        const resetForm = document.getElementById('reset-form');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const emailError = document.getElementById('email-error');
        const passwordError = document.getElementById('password-error');
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        const newPasswordError = document.getElementById('new-password-error');
        const confirmPasswordError = document.getElementById('confirm-password-error');

        // Login form validation
        loginForm.addEventListener('submit', function(e) {
            let hasError = false;

            // Reset error states
            email.classList.remove('error-field');
            password.classList.remove('error-field');
            emailError.style.display = 'none';
            passwordError.style.display = 'none';

            // Validate email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                email.classList.add('error-field');
                emailError.textContent = 'Invalid email format';
                emailError.style.display = 'block';
                hasError = true;
            }

            // Validate password
            if (password.value.trim() === '') {
                password.classList.add('error-field');
                passwordError.textContent = 'Password cannot be empty';
                passwordError.style.display = 'block';
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
            }
        });

        // Real-time validation for login form
        email.addEventListener('input', function() {
            email.classList.remove('error-field');
            emailError.style.display = 'none';

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                email.classList.add('error-field');
                emailError.textContent = 'Invalid email format';
                emailError.style.display = 'block';
            }
        });

        password.addEventListener('input', function() {
            password.classList.remove('error-field');
            passwordError.style.display = 'none';

            if (password.value.trim() === '') {
                password.classList.add('error-field');
                passwordError.textContent = 'Password cannot be empty';
                passwordError.style.display = 'block';
            }
        });

        // Reset form validation (already present, kept for consistency)
        resetForm.addEventListener('submit', function(e) {
            let hasError = false;

            newPassword.classList.remove('error-field');
            confirmPassword.classList.remove('error-field');
            newPasswordError.style.display = 'none';
            confirmPasswordError.style.display = 'none';

            const passwordRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/;
            if (!passwordRegex.test(newPassword.value)) {
                newPassword.classList.add('error-field');
                newPasswordError.textContent = 'Password must be at least 8 characters, with 1 uppercase, 1 lowercase, and 1 number';
                newPasswordError.style.display = 'block';
                hasError = true;
            }

            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.classList.add('error-field');
                confirmPasswordError.textContent = 'Passwords do not match';
                confirmPasswordError.style.display = 'block';
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
            }
        });

        // Real-time validation for reset form
        newPassword.addEventListener('input', function() {
            newPassword.classList.remove('error-field');
            newPasswordError.style.display = 'none';

            const passwordRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/;
            if (!passwordRegex.test(newPassword.value)) {
                newPassword.classList.add('error-field');
                newPasswordError.textContent = 'Password must be at least 8 characters, with 1 uppercase, 1 lowercase, and 1 number';
                newPasswordError.style.display = 'block';
            }
        });

        confirmPassword.addEventListener('input', function() {
            confirmPassword.classList.remove('error-field');
            confirmPasswordError.style.display = 'none';

            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.classList.add('error-field');
                confirmPasswordError.textContent = 'Passwords do not match';
                confirmPasswordError.style.display = 'block';
            }
        });
    });

    function toggleForms() {
        const loginForm = document.querySelector('.login-form');
        const resetForm = document.querySelector('.reset-form');
        
        if (loginForm.style.display === 'block' || loginForm.style.display === '') {
            loginForm.style.display = 'none';
            resetForm.style.display = 'block';
        } else {
            loginForm.style.display = 'block';
            resetForm.style.display = 'none';
        }
    }
</script>
<?php require_once 'includes/footer.php'; ?>