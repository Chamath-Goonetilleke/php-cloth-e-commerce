<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $loginResult = login($username, $password);
    if ($loginResult === true) {
        // Redirect to appropriate page based on user role
        if (isAdmin()) {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } elseif ($loginResult === 'blocked') {
        $error = "Your account has been blocked. Please contact support.";
    } else {
        $error = "Invalid username or password.";
    }
}

// Include header
$pageTitle = "Login";
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

        <!-- Right Side - Login Form -->
        <div class="auth-right">
            <div class="form-container">
                <!-- Tab Navigation -->
                <div class="auth-tabs">
                    <a href="login.php" class="tab-link active">Login</a>
                    <a href="register.php" class="tab-link">Register</a>
                </div>

                <div class="form-content">
                    <h2>Welcome Back</h2>
                    <p class="form-subtitle">Login to your account to access your profile, orders, and wishlist.</p>

                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="login.php" class="auth-form">
                        <div class="login-form-group">
                            <label for="username">Email Address</label>
                            <input type="text" id="username" name="username" placeholder="Enter your email" required>
                        </div>

                        <div class="login-form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        </div>

                        <div class="form-options">
                            <label class="checkbox-label">
                                <input type="checkbox" name="remember_me">
                                <span class="checkmark"></span>
                                Remember me
                            </label>

                        </div>

                        <button type="submit" class="auth-btn">Login</button>
                    </form>

                    <!-- <div class="social-login">
                        <p>Or login with</p>
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
        min-height: 600px;
    }

    .auth-left {
        flex: 0.5;
        background: linear-gradient(135deg, #6b8f71 0%, #4a6b52 100%);
        color: white;
        display: flex;
        align-items: center;
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
        padding: 60px 50px;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .auth-tabs {
        display: flex;
        margin-bottom: 40px;
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
        margin-bottom: 32px;
        font-size: 1rem;
        line-height: 1.5;
    }

    .auth-form {
        margin-bottom: 32px;
    }

    .login-form-group {
        margin-bottom: 20px;
    }

    .login-form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #374151;
        font-size: 0.95rem;
    }

    .login-form-group input {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background-color: #fff;
    }

    .login-form-group input:focus {
        outline: none;
        border-color: #6b8f71;
        box-shadow: 0 0 0 3px rgba(107, 143, 113, 0.1);
    }

    .login-form-group input::placeholder {
        color: #9ca3af;
    }

    .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        cursor: pointer;
        font-size: 0.9rem;
        color: #6b7280;
    }

    .checkbox-label input[type="checkbox"] {
        margin-right: 8px;
        width: auto;
    }

    .forgot-link {
        color: #6b8f71;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .forgot-link:hover {
        text-decoration: underline;
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
        margin-bottom: 20px;
        font-size: 0.9rem;
    }

    .alert-error {
        background-color: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .auth-wrapper {
            flex-direction: column;
            max-width: 480px;
        }

        .auth-left {
            padding: 40px 30px;
        }

        .welcome-content h1 {
            font-size: 2rem;
        }

        .form-container {
            padding: 40px 30px;
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
    }
</style>

<?php
// Include footer
include 'includes/footer.php';
?>