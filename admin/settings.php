<?php
// Set page variables
$pageTitle = "Settings";
$contentTitle = "Store Settings";

// Include header
require_once 'includes/header.php';

// Settings categories
$settings_categories = [
    'general' => 'General',
    'payment' => 'Payment',
    'shipping' => 'Shipping',
    'email' => 'Email Notifications',
    'social' => 'Social Media'
];

// Get active category
$active_category = isset($_GET['category']) && array_key_exists($_GET['category'], $settings_categories)
    ? $_GET['category']
    : 'general';

// Check if settings table exists
$tableExists = false;
$result = $conn->query("SHOW TABLES LIKE 'settings'");
$tableExists = $result && $result->num_rows > 0;

// Create settings table if it doesn't exist
if (!$tableExists) {
    $sql = "CREATE TABLE settings (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        category VARCHAR(50) NOT NULL,
        setting_key VARCHAR(100) NOT NULL,
        setting_value TEXT,
        display_name VARCHAR(100) NOT NULL,
        type VARCHAR(20) DEFAULT 'text',
        options TEXT,
        UNIQUE KEY category_key (category, setting_key)
    )";

    if ($conn->query($sql) === TRUE) {
        // Insert default settings
        $defaultSettings = [
            // General Settings
            ["general", "site_title", "OneFit Clothing", "Site Title", "text", ""],
            ["general", "site_description", "Quality athletic and leisure wear", "Site Description", "textarea", ""],
            ["general", "contact_email", "info@onefitclothing.com", "Contact Email", "email", ""],
            ["general", "contact_phone", "+94 11 123 4567", "Contact Phone", "text", ""],
            ["general", "address", "123 Commerce St, Colombo, Sri Lanka", "Store Address", "textarea", ""],
            ["general", "currency", "LKR", "Currency", "select", "LKR,USD,EUR,GBP"],
            ["general", "tax_rate", "5", "Tax Rate (%)", "number", ""],

            // Payment Settings
            ["payment", "payment_methods", "cod,bank_transfer", "Payment Methods", "checkbox", "cod:Cash On Delivery,bank_transfer:Bank Transfer,paypal:PayPal,stripe:Stripe"],
            ["payment", "bank_details", "Bank: People's Bank\nAccount: 123456789\nBranch: Colombo", "Bank Account Details", "textarea", ""],
            ["payment", "paypal_email", "", "PayPal Email", "email", ""],
            ["payment", "min_order_amount", "1000", "Minimum Order Amount", "number", ""],

            // Shipping Settings
            ["shipping", "free_shipping_min", "5000", "Free Shipping Minimum Order", "number", ""],
            ["shipping", "standard_shipping_fee", "350", "Standard Shipping Fee", "number", ""],
            ["shipping", "express_shipping_fee", "650", "Express Shipping Fee", "number", ""],
            ["shipping", "enable_express", "1", "Enable Express Shipping", "toggle", ""],
            ["shipping", "shipping_countries", "Sri Lanka", "Shipping Countries", "textarea", ""],

            // Email Settings
            ["email", "admin_email", "admin@onefitclothing.com", "Admin Email", "email", ""],
            ["email", "send_order_confirmation", "1", "Send Order Confirmation", "toggle", ""],
            ["email", "send_shipping_notification", "1", "Send Shipping Notification", "toggle", ""],
            ["email", "send_order_delivered", "1", "Send Order Delivered Notification", "toggle", ""],
            ["email", "email_signature", "Thank you for shopping with OneFit Clothing!", "Email Signature", "textarea", ""],

            // Social Media Settings
            ["social", "facebook_url", "", "Facebook URL", "url", ""],
            ["social", "instagram_url", "", "Instagram URL", "url", ""],
            ["social", "twitter_url", "", "Twitter URL", "url", ""],
            ["social", "youtube_url", "", "YouTube URL", "url", ""],
            ["social", "enable_sharing", "1", "Enable Social Sharing", "toggle", ""]
        ];

        $stmt = $conn->prepare("INSERT INTO settings (category, setting_key, setting_value, display_name, type, options) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $category, $key, $value, $display_name, $type, $options);

        foreach ($defaultSettings as $setting) {
            [$category, $key, $value, $display_name, $type, $options] = $setting;
            $stmt->execute();
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $category = $_POST['category'];

    foreach ($_POST as $key => $value) {
        // Skip non-setting fields
        if ($key === 'save_settings' || $key === 'category') {
            continue;
        }

        // Handle array values like checkboxes
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        // Update setting
        $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE category = ? AND setting_key = ?");
        $stmt->bind_param("sss", $value, $category, $key);
        $stmt->execute();
    }

    $_SESSION['admin_message'] = "Settings updated successfully";
    $_SESSION['admin_message_type'] = "success";

    // Redirect to avoid form resubmission
    header("Location: settings.php?category=$category");
    exit;
}

// Get settings for active category
$settings = [];
$sql = "SELECT * FROM settings WHERE category = ? ORDER BY id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $active_category);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $settings[] = $row;
    }
}
?>

<div class="settings-container">
    <!-- Settings Navigation -->
    <div class="settings-nav card">
        <div class="card-body">
            <ul class="settings-menu">
                <?php foreach ($settings_categories as $key => $name): ?>
                    <li>
                        <a href="settings.php?category=<?php echo $key; ?>" class="<?php echo $active_category === $key ? 'active' : ''; ?>">
                            <i class="fas fa-<?php
                                                echo $key === 'general' ? 'cog' : ($key === 'payment' ? 'credit-card' : ($key === 'shipping' ? 'truck' : ($key === 'email' ? 'envelope' : ($key === 'social' ? 'share-alt' : 'cog'))));
                                                ?>"></i>
                            <?php echo $name; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Settings Form -->
    <div class="settings-content card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-<?php
                                    echo $active_category === 'general' ? 'cog' : ($active_category === 'payment' ? 'credit-card' : ($active_category === 'shipping' ? 'truck' : ($active_category === 'email' ? 'envelope' : ($active_category === 'social' ? 'share-alt' : 'cog'))));
                                    ?>"></i>
                <?php echo $settings_categories[$active_category]; ?> Settings
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="settings.php">
                <input type="hidden" name="category" value="<?php echo $active_category; ?>">

                <?php if (empty($settings)): ?>
                    <div class="alert alert-info">No settings found for this category.</div>
                <?php else: ?>
                    <?php foreach ($settings as $setting): ?>
                        <div class="form-group">
                            <label for="<?php echo $setting['setting_key']; ?>">
                                <?php echo $setting['display_name']; ?>
                            </label>

                            <?php if ($setting['type'] === 'text' || $setting['type'] === 'email' || $setting['type'] === 'url' || $setting['type'] === 'number'): ?>
                                <input type="<?php echo $setting['type']; ?>"
                                    name="<?php echo $setting['setting_key']; ?>"
                                    id="<?php echo $setting['setting_key']; ?>"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($setting['setting_value']); ?>">

                            <?php elseif ($setting['type'] === 'textarea'): ?>
                                <textarea name="<?php echo $setting['setting_key']; ?>"
                                    id="<?php echo $setting['setting_key']; ?>"
                                    class="form-control"
                                    rows="4"><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>

                            <?php elseif ($setting['type'] === 'select'): ?>
                                <select name="<?php echo $setting['setting_key']; ?>"
                                    id="<?php echo $setting['setting_key']; ?>"
                                    class="form-control">
                                    <?php
                                    $options = explode(',', $setting['options']);
                                    foreach ($options as $option):
                                        $selected = $option === $setting['setting_value'] ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $option; ?>" <?php echo $selected; ?>>
                                            <?php echo $option; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                            <?php elseif ($setting['type'] === 'checkbox'): ?>
                                <?php
                                $options = explode(',', $setting['options']);
                                $selectedValues = explode(',', $setting['setting_value']);

                                foreach ($options as $option):
                                    $optionParts = explode(':', $option);
                                    $optionValue = $optionParts[0];
                                    $optionLabel = isset($optionParts[1]) ? $optionParts[1] : $optionValue;
                                    $checked = in_array($optionValue, $selectedValues) ? 'checked' : '';
                                ?>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox"
                                                name="<?php echo $setting['setting_key']; ?>[]"
                                                value="<?php echo $optionValue; ?>"
                                                <?php echo $checked; ?>>
                                            <?php echo $optionLabel; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>

                            <?php elseif ($setting['type'] === 'toggle'): ?>
                                <div class="toggle-switch">
                                    <input type="checkbox"
                                        name="<?php echo $setting['setting_key']; ?>"
                                        id="<?php echo $setting['setting_key']; ?>"
                                        value="1"
                                        <?php echo $setting['setting_value'] == '1' ? 'checked' : ''; ?>>
                                    <label for="<?php echo $setting['setting_key']; ?>"></label>
                                </div>
                            <?php endif; ?>

                            <!-- Add helpful hint if needed -->
                            <?php if (isset($setting['hint'])): ?>
                                <small class="form-text text-muted"><?php echo $setting['hint']; ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" name="save_settings" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<style>
    .settings-container {
        display: flex;
        gap: 20px;
    }

    .settings-nav {
        width: 250px;
        flex-shrink: 0;
    }

    .settings-content {
        flex: 1;
    }

    .settings-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .settings-menu li {
        margin-bottom: 5px;
    }

    .settings-menu a {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        color: var(--text-color);
        text-decoration: none;
        border-radius: 5px;
        transition: all 0.2s ease;
    }

    .settings-menu a:hover {
        background-color: rgba(29, 80, 58, 0.1);
    }

    .settings-menu a.active {
        background-color: var(--primary-color);
        color: white;
    }

    .settings-menu a i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }

    /* Toggle Switch Styles */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-switch label {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    .toggle-switch label:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    .toggle-switch input:checked+label {
        background-color: var(--primary-color);
    }

    .toggle-switch input:checked+label:before {
        transform: translateX(26px);
    }

    .checkbox {
        margin-bottom: 8px;
    }

    .checkbox label {
        display: flex;
        align-items: center;
        font-weight: normal;
        cursor: pointer;
    }

    .checkbox input[type="checkbox"] {
        margin-right: 8px;
    }

    @media (max-width: 768px) {
        .settings-container {
            flex-direction: column;
        }

        .settings-nav {
            width: 100%;
        }

        .settings-menu {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .settings-menu li {
            margin-bottom: 0;
        }

        .settings-menu a {
            padding: 8px 12px;
            font-size: 14px;
        }
    }
</style>

<?php
// Include footer
require_once 'includes/footer.php';
?>