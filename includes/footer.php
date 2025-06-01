<?php
// OneFit Clothing - Advanced Footer Component
// You can include this file in other PHP pages or use it as a standalone page

// Set page title and meta information
$page_title = "OneFit Clothing - Premium Women's Fashion";
$page_description = "Elevate your wardrobe with our premium collection of printed t-shirts and hoodies designed exclusively for modern women.";

// Newsletter subscription handling
$newsletter_message = "";
if ($_POST && isset($_POST['newsletter_email'])) {
    $email = filter_var($_POST['newsletter_email'], FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Here you would typically save to database or send to email service
        // For demo purposes, we'll just show a success message
        $newsletter_message = "Thank you for subscribing! You'll receive 10% off your first purchase.";
    } else {
        $newsletter_message = "Please enter a valid email address.";
    }
}

// Company information (you can modify these as needed)
$company_info = [
    'name' => 'OneFit Clothing',
    'description' => 'Elevate your wardrobe with our premium collection of printed t-shirts and hoodies designed exclusively for modern women.',
    'address' => '123 Fashion Street, Colombo 07, Sri Lanka',
    'phone' => '+94 11 222 3333',
    'email' => 'support@onefitclothing.com',
    'copyright_year' => date('Y')
];

// Navigation links
$footer_links = [
    'orders' => [
        'Search' => 'index.php',
        'T shirts' => 'tshirts.php',
        'Hoodies' => 'hoodies.php',
        'Reviews' => 'reviews.php',
        'Contact Us' => 'contactus.php'
    ],
    'quick_links' => [
        'About Us' => 'aboutus.php',
        'Privacy Policy' => 'privacy-policy.php',
        'Terms of Service' => 'terms.php',
        'Size Guide' => 'size.php',
        'Log in' => 'login.php'
    ]
];

// FAQ data
$faqs = [
    [
        'question' => 'Shipping Information',
        'answer' => 'We offer free shipping on all orders over RS: 5000.00. Standard delivery takes 3-5 business days.'
    ],
    [
        'question' => 'Return Policy',
        'answer' => 'We accept returns within 30 days of purchase. Items must be unused with original tags.'
    ],
    [
        'question' => 'Size Guide',
        'answer' => 'Please refer to our size chart on each product page for accurate measurements.'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        /* Demo content styles */
        .demo-content {
            padding: 100px 0;
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .demo-content h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .demo-content p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Footer Styles */
        .footer {
            background-color: #1D503A;
            color: white;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
            z-index: -1;
        }

        .footer::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
            z-index: -1;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Footer Top - Newsletter */
        .footer-top {
            background-color: #143726;
            padding: 40px 0;
            position: relative;
            overflow: hidden;
        }

        .footer-top::before {
            content: '';
            position: absolute;
            top: -50px;
            left: -50px;
            width: 150px;
            height: 150px;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .footer-top::after {
            content: '';
            position: absolute;
            bottom: -80px;
            right: -80px;
            width: 200px;
            height: 200px;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .newsletter-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 30px;
        }

        .newsletter-content {
            flex: 1;
            min-width: 300px;
        }

        .newsletter-content h3 {
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .newsletter-content p {
            color: #ccc;
        }

        .newsletter-form {
            flex: 1;
            min-width: 600px;
        }

        .form-group {
            display: flex;
            position: relative;
            margin-bottom: 10px;
        }

        .form-group input {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            min-width: 600px;
        }

        .form-group button {
            position: absolute;
            right: 5px;
            top: 5px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #F9E4DA 0%, #F9C6A7 100%);
            color: #1D503A;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 200px;
        }

        .form-group button:hover {
            background: linear-gradient(135deg, #F9C6A7 0%, #F9E4DA 100%);
            transform: translateY(-2px);
        }

        /* Newsletter message styles */
        .newsletter-message {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }

        .newsletter-message.success {
            background-color: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }

        .newsletter-message.error {
            background-color: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }

        /* Footer Main */
        .footer-main {
            padding: 70px 0 50px;
            position: relative;
        }

        .footer-columns {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 40px;
        }

        .footer-column {
            flex: 1;
            min-width: 250px;
        }

        .footer-logo {
            font-family: 'Poppins', cursive;
            font-size: 28px;
            margin-bottom: 20px;
            background: linear-gradient(90deg, #ffffff, #F9E4DA);
            background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .footer-text {
            line-height: 1.6;
            color: #ddd;
            margin-bottom: 20px;
        }

        .footer-contact {
            margin-bottom: 20px;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .contact-item i {
            color: #F9E4DA;
            margin-right: 10px;
            margin-top: 4px;
        }

        .social-links {
            display: flex;
            gap: 12px;
        }

        .social-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .social-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #F9E4DA 0%, #F9C6A7 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .social-icon:hover {
            color: #1D503A;
            transform: translateY(-5px);
        }

        .social-icon:hover::before {
            opacity: 1;
        }

        .footer-column h4 {
            font-size: 18px;
            margin-bottom: 25px;
            position: relative;
            display: inline-block;
        }

        .footer-column h4::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 40px;
            height: 2px;
            background: linear-gradient(90deg, #F9E4DA, transparent);
        }

        .footer-column ul {
            list-style: none;
        }

        .footer-column ul li {
            margin-bottom: 12px;
            position: relative;
            transition: all 0.3s ease;
        }

        .footer-column ul li::before {
            content: '›';
            position: absolute;
            left: -15px;
            color: #F9E4DA;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .footer-column ul li:hover {
            padding-left: 20px;
        }

        .footer-column ul li:hover::before {
            opacity: 1;
            left: 0;
        }

        .footer-column ul li a {
            color: #ddd;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-column ul li a:hover {
            color: #F9E4DA;
        }

        /* Footer FAQ Accordion */
        .footer-accordion {
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        .footer-faq-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-faq-item:last-child {
            border-bottom: none;
        }

        .footer-faq-question {
            padding: 15px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .footer-faq-question:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .footer-faq-question i {
            transition: transform 0.3s ease;
        }

        .footer-faq-item.active .footer-faq-question i {
            transform: rotate(180deg);
        }

        .footer-faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            padding: 0 15px;
        }

        .footer-faq-item.active .footer-faq-answer {
            max-height: 100px;
            padding: 0 15px 15px;
        }

        .footer-faq-answer p {
            color: #ccc;
            font-size: 14px;
            line-height: 1.6;
        }

        /* Footer Middle - Payment Methods & App Downloads */
        .footer-middle {
            padding: 40px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-middle-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 30px;
        }

        .payment-methods,
        .app-download {
            flex: 1;
            min-width: 300px;
        }

        .payment-methods h5,
        .app-download h5 {
            font-size: 16px;
            margin-bottom: 15px;
            color: #F9E4DA;
        }

        .payment-icons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .payment-icons i {
            font-size: 30px;
            color: #ddd;
            transition: all 0.3s ease;
        }

        .payment-icons i:hover {
            color: white;
            transform: translateY(-3px);
        }

        .app-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .app-button {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
        }

        .app-button:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }

        .app-button i {
            font-size: 24px;
        }

        .app-button span {
            display: flex;
            flex-direction: column;
        }

        .app-button small {
            font-size: 10px;
            opacity: 0.8;
        }

        .app-button strong {
            font-size: 14px;
        }

        /* Footer Bottom */
        .footer-bottom {
            padding: 25px 0;
            font-size: 14px;
        }

        .footer-bottom-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .copyright p {
            color: #aaa;
        }

        .back-to-top {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .back-to-top:hover {
            color: #F9E4DA;
            transform: translateY(-3px);
        }

        .back-to-top i {
            background-color: rgba(255, 255, 255, 0.1);
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .back-to-top:hover i {
            background-color: #F9E4DA;
            color: #1D503A;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {

            .newsletter-wrapper,
            .footer-columns,
            .footer-middle-content,
            .footer-bottom-content {
                flex-direction: column;
                gap: 30px;
            }

            .footer-column,
            .payment-methods,
            .app-download {
                width: 100%;
                min-width: unset;
            }

            .copyright,
            .back-to-top {
                text-align: center;
                justify-content: center;
                width: 100%;
            }
        }

        /* Animation for Footer Elements */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .footer-column {
            animation: fadeInUp 0.5s ease forwards;
        }

        .footer-column:nth-child(2) {
            animation-delay: 0.2s;
        }

        .footer-column:nth-child(3) {
            animation-delay: 0.4s;
        }

        .footer-column:nth-child(4) {
            animation-delay: 0.6s;
        }

        /* Footer pulse animation for CTA elements */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(249, 228, 218, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(249, 228, 218, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(249, 228, 218, 0);
            }
        }

        .form-group button {
            animation: pulse 2s infinite;
        }
    </style>
</head>

<body>
   

    <!-- Footer starts here -->
    <footer class="footer">
        <!-- Footer Top - Newsletter -->
        <div class="footer-top">
            <div class="container">
                <div class="newsletter-wrapper">
                    <div class="newsletter-content">
                        <h3>Be the first to get Updates & Offers!</h3>
                        <p>Subscribe to our newsletter and get 10% off your first purchase</p>
                    </div>
                    <form class="newsletter-form" method="POST" action="">
                        <div class="form-group">
                            <input type="email" name="newsletter_email" placeholder="Your email address" required>
                            <button type="submit">Subscribe</button>
                        </div>
                        <?php if ($newsletter_message): ?>
                            <div class="newsletter-message <?php echo strpos($newsletter_message, 'Thank you') !== false ? 'success' : 'error'; ?>">
                                <?php echo htmlspecialchars($newsletter_message); ?>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer Main -->
        <div class="footer-main">
            <div class="container">
                <div class="footer-columns">

                    <!-- About Company -->
                    <div class="footer-column">
                        <div class="footer-logo"><?php echo $company_info['name']; ?></div>
                        <p class="footer-text"><?php echo $company_info['description']; ?></p>
                        <div class="footer-contact">
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo $company_info['address']; ?></span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone-alt"></i>
                                <span><?php echo $company_info['phone']; ?></span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <span><?php echo $company_info['email']; ?></span>
                            </div>
                        </div>
                        <div class="social-links">
                            <a href="#" class="social-icon" aria-label="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-icon" aria-label="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-icon" aria-label="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-icon" aria-label="Pinterest">
                                <i class="fab fa-pinterest-p"></i>
                            </a>
                            <a href="#" class="social-icon" aria-label="TikTok">
                                <i class="fab fa-tiktok"></i>
                            </a>
                        </div>
                    </div>
                    <!-- Orders -->
                    <div class="footer-column">
                        <h4>Orders</h4>
                        <ul>
                            <?php foreach ($footer_links['orders'] as $text => $link): ?>
                                <li><a href="<?php echo $link; ?>"><?php echo $text; ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Quick Links -->
                    <div class="footer-column">
                        <h4>Quick Links</h4>
                        <ul>
                            <?php foreach ($footer_links['quick_links'] as $text => $link): ?>
                                <li><a href="<?php echo $link; ?>"><?php echo $text; ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- FAQs -->
                    <div class="footer-column">
                        <h4>FAQs</h4>
                        <div class="footer-accordion">
                            <?php foreach ($faqs as $index => $faq): ?>
                                <div class="footer-faq-item">
                                    <div class="footer-faq-question">
                                        <span><?php echo $faq['question']; ?></span>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <div class="footer-faq-answer">
                                        <p><?php echo $faq['answer']; ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Middle - Payment Methods & App Downloads -->
        <div class="footer-middle">
            <div class="container">
                <div class="footer-middle-content">
                    <div class="payment-methods">
                        <h5>Payment Methods</h5>
                        <div class="payment-icons">
                            <i class="fab fa-cc-visa"></i>
                            <i class="fab fa-cc-mastercard"></i>
                            <i class="fab fa-cc-paypal"></i>
                            <i class="fab fa-cc-apple-pay"></i>
                        </div>
                    </div>
                    <div class="app-download">
                        <h5>Download Our App</h5>
                        <div class="app-buttons">
                            <a href="#" class="app-button">
                                <i class="fab fa-apple"></i>
                                <span>
                                    <small>Download on the</small>
                                    <strong>App Store</strong>
                                </span>
                            </a>
                            <a href="#" class="app-button">
                                <i class="fab fa-google-play"></i>
                                <span>
                                    <small>GET IT ON</small>
                                    <strong>Google Play</strong>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom - Copyright & Back to Top -->
        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-content">
                    <div class="copyright">
                        <p>&copy; <?php echo $company_info['copyright_year']; ?> <?php echo $company_info['name']; ?>. All Rights Reserved.</p>
                    </div>
                    <div class="back-to-top" id="backToTop">
                        <span>Back to top</span>
                        <i class="fas fa-chevron-up"></i>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript for Footer Functionality -->
    <script>
        // Interactive FAQ accordion in footer
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.footer-faq-item');

            faqItems.forEach(item => {
                const question = item.querySelector('.footer-faq-question');

                question.addEventListener('click', () => {
                    const isActive = item.classList.contains('active');

                    // Close all items
                    faqItems.forEach(faqItem => {
                        faqItem.classList.remove('active');
                    });

                    // If clicked item wasn't active before, open it
                    if (!isActive) {
                        item.classList.add('active');
                    }
                });
            });

            // Back to top functionality
            const backToTop = document.getElementById('backToTop');
            if (backToTop) {
                backToTop.addEventListener('click', () => {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }
        });
    </script>
</body>

</html>