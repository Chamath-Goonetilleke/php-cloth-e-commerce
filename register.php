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

<div class="auth-container">
    <div class="auth-wrapper">
        <!-- Left Side - Welcome Content -->
        <div class="auth-left">
            <div class="welcome-content">
                <h1>Welcome to OneFit Clothing</h1>
                <p>Join our community and enjoy a range of benefits designed exclusively for our members.</p>

                <div class="benefits-list">
                    <div class="benefit-item">
                        <div class="benefit-icon">🎁</div>
                        <span>Exclusive discounts and offers</span>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">🚚</div>
                        <span>Free shipping on all orders</span>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">🔔</div>
                        <span>Early access to new collections</span>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">🏆</div>
                        <span>Loyalty rewards program</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Register Form -->
        <div class="auth-right">
            <div class="form-container">
                <!-- Tab Navigation -->
                <div class="auth-tabs">
                    <a href="login.php" class="tab-link">Login</a>
                    <a href="register.php" class="tab-link active">Register</a>
                </div>

                <div class="form-content">
                    <h2>Create Account</h2>
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

                    <form method="POST" action="register.php" class="auth-form">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($fullName); ?>" placeholder="Enter your full name" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email" required>
                        </div>

                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" placeholder="Choose a username" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="Create a password" required>
                            <small>Password must be at least 6 characters long</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                        </div>

                        <button type="submit" class="auth-btn">Create Account</button>
                    </form>

                    <!-- <div class="social-login">
                        <p>Or register with</p>
                        <div class="social-buttons">
                            <button type="button" class="social-btn google-btn">
                                <span class="social-icon">G</span>
                            </button>
                            <button type="button" class="social-btn facebook-btn">
                                <span class="social-icon">f</span>
                            </button>
                            <button type="button" class="social-btn apple-btn">
                                <span class="social-icon">🍎</span>
                            </button>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .auth-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
        padding: 20px;
    }

    .auth-wrapper {
        display: flex;
        max-width: 1200px;
        width: 100%;
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        min-height: 700px;
    }

    .auth-left {
        flex: 0.5;
        background: linear-gradient(135deg, #6b8f71 0%, #4a6b52 100%);
        color: white;
        display: flex;
        justify-content: center;
        padding: 60px 40px;
        position: relative;
    }

    .auth-left::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23pattern)"/></svg>');
        opacity: 0.3;
    }

    .welcome-content {
        text-align: center;
        z-index: 1;
        position: relative;
    }

    .welcome-content h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 16px;
        line-height: 1.2;
    }

    .welcome-content>p {
        font-size: 1.1rem;
        margin-bottom: 40px;
        opacity: 0.9;
        line-height: 1.6;
    }

    .benefits-list {
        text-align: left;
        max-width: 320px;
        margin: 0 auto;
    }

    .benefit-item {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        font-size: 1rem;
    }

    .benefit-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        font-size: 1.2rem;
    }

    .auth-right {
        flex: 1;
        padding: 0;
        display: flex;
        flex-direction: column;
    }

    .form-container {
        padding: 40px 50px;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .auth-tabs {
        display: flex;
        margin-bottom: 30px;
        border-bottom: 1px solid #e5e7eb;
    }

    .tab-link {
        padding: 12px 0;
        margin-right: 40px;
        text-decoration: none;
        color: #9ca3af;
        font-weight: 500;
        border-bottom: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .tab-link.active {
        color: #1f2937;
        border-bottom-color: #6b8f71;
    }

    .form-content h2 {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 8px;
    }

    .form-subtitle {
        color: #6b7280;
        margin-bottom: 24px;
        font-size: 1rem;
        line-height: 1.5;
    }

    .auth-form {
        margin-bottom: 24px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #374151;
        font-size: 0.95rem;
    }

    .form-group input {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background-color: #fff;
    }

    .form-group input:focus {
        outline: none;
        border-color: #6b8f71;
        box-shadow: 0 0 0 3px rgba(107, 143, 113, 0.1);
    }

    .form-group input::placeholder {
        color: #9ca3af;
    }

    .form-group small {
        display: block;
        margin-top: 4px;
        color: #6b7280;
        font-size: 0.85rem;
    }

    .auth-btn {
        width: 100%;
        background: #6b8f71;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 14px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 8px;
    }

    .auth-btn:hover {
        background: #5a7a61;
        transform: translateY(-1px);
    }

    .social-login {
        text-align: center;
        margin-top: auto;
    }

    .social-login p {
        color: #9ca3af;
        margin-bottom: 16px;
        font-size: 0.9rem;
    }

    .social-buttons {
        display: flex;
        justify-content: center;
        gap: 12px;
    }

    .social-btn {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        border: 1px solid #e5e7eb;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .social-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .google-btn:hover {
        border-color: #ea4335;
    }

    .facebook-btn:hover {
        border-color: #1877f2;
    }

    .apple-btn:hover {
        border-color: #000;
    }

    .social-icon {
        font-weight: bold;
        font-size: 1.2rem;
    }

    .google-btn .social-icon {
        color: #ea4335;
    }

    .facebook-btn .social-icon {
        color: #1877f2;
    }

    .apple-btn .social-icon {
        color: #000;
    }

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 0.9rem;
    }

    .alert-success {
        background-color: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }

    .alert-error {
        background-color: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .alert ul {
        margin: 0;
        padding-left: 20px;
    }

    .alert li {
        margin-bottom: 4px;
    }

    .alert li:last-child {
        margin-bottom: 0;
    }

    .alert a {
        color: inherit;
        text-decoration: underline;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .auth-wrapper {
            flex-direction: column;
            max-width: 480px;
            min-height: auto;
        }

        .auth-left {
            padding: 40px 30px;
        }

        .welcome-content h1 {
            font-size: 2rem;
        }

        .form-container {
            padding: 30px 30px;
        }

        .form-content h2 {
            font-size: 1.75rem;
        }

        .tab-link {
            margin-right: 30px;
        }
    }

    @media (max-width: 480px) {
        .auth-container {
            padding: 10px;
        }

        .auth-left {
            padding: 30px 20px;
        }

        .form-container {
            padding: 30px 20px;
        }

        .welcome-content h1 {
            font-size: 1.75rem;
        }

        .benefits-list {
            max-width: 280px;
        }

        .tab-link {
            margin-right: 20px;
        }
    }