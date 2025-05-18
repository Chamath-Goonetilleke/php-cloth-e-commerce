<?php
// Set page variables
$pageTitle = "Admin Login";
$isLoginPage = true;

// Include header (will not show navigation for login page)
require_once 'includes/header.php';

// Process login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Check if admin table exists
        $tableExists = false;
        $result = $conn->query("SHOW TABLES LIKE 'admins'");
        $tableExists = $result && $result->num_rows > 0;

        if (!$tableExists) {
            // Create admin table if it doesn't exist
            $sql = "CREATE TABLE admins (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) NOT NULL,
                full_name VARCHAR(100) NOT NULL,
                role VARCHAR(20) NOT NULL DEFAULT 'admin',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";

            if ($conn->query($sql) === TRUE) {
                // Create default admin account
                $defaultUsername = 'admin';
                $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
                $defaultEmail = 'admin@onefitclothing.com';
                $defaultName = 'Administrator';

                $sql = "INSERT INTO admins (username, password, email, full_name, role) 
                        VALUES (?, ?, ?, ?, 'super_admin')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $defaultUsername, $defaultPassword, $defaultEmail, $defaultName);

                if ($stmt->execute()) {
                    $message = "Created default admin account. Username: admin, Password: admin123";
                    error_log($message);
                }
            }
        }

        // Continue with login
        $sql = "SELECT * FROM admins WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            if (password_verify($password, $admin['password'])) {
                // Set session variables
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_role'] = $admin['role'];

                // Set success message
                $_SESSION['admin_message'] = "Welcome back, {$admin['full_name']}!";
                $_SESSION['admin_message_type'] = 'success';

                // Redirect to dashboard
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Username not found';
        }
    }
}
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-logo">
            <h1>OneFit Clothing</h1>
            <p>Admin Panel</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-field">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                    <button type="button" class="toggle-password" data-toggle="#password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Sign In</button>

            <a href="<?php echo SITE_URL; ?>" class="forgot-password">Return to Main Site</a>
        </form>
    </div>
</div>

<style>
    .password-field {
        position: relative;
    }

    .toggle-password {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
    }
</style>

<?php
// Include footer
require_once 'includes/footer.php';
?>