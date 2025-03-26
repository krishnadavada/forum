<?php
require_once 'includes/config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables
$error = $message = '';

// Handle signup logic before any output
if (isset($_POST['signup'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token";
    } else {
        $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Server-side validation
        if (strlen($username) < 3 || strlen($username) > 20) {
            $error = "Username must be between 3 and 20 characters";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters long";
        } elseif (!preg_match("/[A-Z]/", $password) || !preg_match("/[a-z]/", $password) || !preg_match("/[0-9]/", $password)) {
            $error = "Password must contain at least one uppercase letter, one lowercase letter, and one number";
        } elseif ($password !== $confirm_password) {
            $error = "Password and confirm password do not match";
        } else {
            // Check if username or email already exists
            $stmt = prepare_query($conn, "SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                $error = "Username or email already exists";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = prepare_query($conn, "INSERT INTO users (username, email, password) VALUES (?, ?, ?)", [$username, $email, $hashed_password]);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Registration successful! Please log in.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    }
}

// Include header after all header-modifying logic
require_once 'includes/header.php';
?>
<style>
    .auth-container {
        max-width: 450px;
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
        transition: all 0.3s ease;
    }

    .auth-container input:focus {
        border-color: #3498db;
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
        margin: 15px 0;
        font-size: 14px;
        text-align: center;
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
        <form method="POST" id="signup-form">
            <h2>Sign Up</h2>
            <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
            <?php if ($message): ?><p class="message"><?php echo $message; ?></p><?php endif; ?>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="text" name="username" id="username" placeholder="Username" required>
            <p class="error-message" id="username-error"></p>
            <input type="email" name="email" id="email" placeholder="Email" required>
            <p class="error-message" id="email-error"></p>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <p class="error-message" id="password-error"></p>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
            <p class="error-message" id="confirm-password-error"></p>
            <button type="submit" name="signup">Sign Up</button>
            <div class="options">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const signupForm = document.getElementById('signup-form');
        const username = document.getElementById('username');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const usernameError = document.getElementById('username-error');
        const emailError = document.getElementById('email-error');
        const passwordError = document.getElementById('password-error');
        const confirmPasswordError = document.getElementById('confirm-password-error');

        signupForm.addEventListener('submit', function(e) {
            let hasError = false;

            // Reset error states
            username.classList.remove('error-field');
            email.classList.remove('error-field');
            password.classList.remove('error-field');
            confirmPassword.classList.remove('error-field');
            usernameError.style.display = 'none';
            emailError.style.display = 'none';
            passwordError.style.display = 'none';
            confirmPasswordError.style.display = 'none';

            // Validate username
            if (username.value.length < 3 || username.value.length > 20) {
                username.classList.add('error-field');
                usernameError.textContent = 'Username must be between 3 and 20 characters';
                usernameError.style.display = 'block';
                hasError = true;
            }

            // Validate email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                email.classList.add('error-field');
                emailError.textContent = 'Invalid email format';
                emailError.style.display = 'block';
                hasError = true;
            }

            // Validate password
            const passwordRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/;
            if (!passwordRegex.test(password.value)) {
                password.classList.add('error-field');
                passwordError.textContent = 'Password must be at least 8 characters, with 1 uppercase, 1 lowercase, and 1 number';
                passwordError.style.display = 'block';
                hasError = true;
            }

            // Validate confirm password
            if (password.value !== confirmPassword.value) {
                confirmPassword.classList.add('error-field');
                confirmPasswordError.textContent = 'Passwords do not match';
                confirmPasswordError.style.display = 'block';
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
            }
        });

        // Real-time validation
        username.addEventListener('input', function() {
            username.classList.remove('error-field');
            usernameError.style.display = 'none';

            if (username.value.length < 3 || username.value.length > 20) {
                username.classList.add('error-field');
                usernameError.textContent = 'Username must be between 3 and 20 characters';
                usernameError.style.display = 'block';
            }
        });

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

            const passwordRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/;
            if (!passwordRegex.test(password.value)) {
                password.classList.add('error-field');
                passwordError.textContent = 'Password must be at least 8 characters, with 1 uppercase, 1 lowercase, and 1 number';
                passwordError.style.display = 'block';
            }
        });

        confirmPassword.addEventListener('input', function() {
            confirmPassword.classList.remove('error-field');
            confirmPasswordError.style.display = 'none';

            if (password.value !== confirmPassword.value) {
                confirmPassword.classList.add('error-field');
                confirmPasswordError.textContent = 'Passwords do not match';
                confirmPasswordError.style.display = 'block';
            }
        });
    });
</script>
<?php require_once 'includes/footer.php'; ?>