<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is already logged in
if (isLoggedIn()) {
    // Redirect to account page
    header('Location: myaccount.php');
    exit;
}

// Initialize variables
$username = $email = $fullName = '';
$errors = [];
$success = false;

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }

    if (empty($fullName)) {
        $errors[] = "Full name is required";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }

    // If no errors, register the user
    if (empty($errors)) {
        // First check if the registerUser function exists and supports all these parameters
        if (!function_exists('registerUser')) {
            $errors[] = "Registration functionality is not available. Please contact the administrator.";
        } else {
            $userId = registerUser($username, $email, $password, $fullName);

            if ($userId) {
                // Set success message
                $success = true;

                // Clear form fields
                $username = $email = $fullName = '';

                // Auto login (optional)
                login($username, $password);

                // Redirect to account page after successful registration
                header('Location: myaccount.php');
                exit;
            } else {
                $errors[] = "Registration failed. Username or email may already be in use.";
            }
        }
    }
}

// Set page variables
$pageTitle = "OneFit Clothing - Register";
$showSaleBanner = false;

// Include header
include 'includes/header.php';
?>

<section class="register-container">
    <div class="form-wrapper">
        <h1>Create an Account</h1>
        <p class="form-subtitle">Join OneFit Clothing to enjoy exclusive offers, easy order tracking, and a faster checkout process.</p>

        <?php if ($success): ?>
            <div class="alert alert-success">
                Registration successful! You can now <a href="login.php">login</a> to your account.
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" class="register-form">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($fullName); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <small>Password must be at least 6 characters long</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="register-btn">Create Account</button>
        </form>

        <div class="form-footer">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</section>

<style>
    .register-container {
        max-width: 960px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .form-wrapper {
        background-color: white;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: 40px;
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-wrapper h1 {
        color: #1D503A;
        margin-bottom: 10px;
        font-size: 32px;
    }

    .form-subtitle {
        color: #666;
        margin-bottom: 30px;
    }

    .register-form {
        margin-top: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }

    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s;
    }

    .form-group input:focus {
        border-color: #1D503A;
        outline: none;
    }

    .form-group small {
        display: block;
        margin-top: 5px;
        color: #777;
        font-size: 13px;
    }

    .register-btn {
        background-color: #1D503A;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 15px 25px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        margin-top: 10px;
        transition: background-color 0.3s, transform 0.2s;
    }

    .register-btn:hover {
        background-color: #164226;
        transform: translateY(-2px);
    }

    .form-footer {
        margin-top: 25px;
        text-align: center;
        border-top: 1px solid #eee;
        padding-top: 20px;
    }

    .form-footer a {
        color: #1D503A;
        text-decoration: none;
        font-weight: 600;
    }

    .form-footer a:hover {
        text-decoration: underline;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert ul {
        margin: 0;
        padding-left: 20px;
    }

    @media (max-width: 768px) {
        .register-container {
            margin: 20px auto;
        }

        .form-wrapper {
            padding: 25px;
        }

        .form-wrapper h1 {
            font-size: 28px;
        }
    }
</style>

<?php
// Include footer
include 'includes/footer.php';
?>