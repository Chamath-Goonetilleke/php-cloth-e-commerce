<?php
require_once 'includes/config.php';
include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OneFit Clothing - Terms of Service</title>
    <link rel="shortcut icon" href="OneFit Clothing.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            text-align: center;
        }

        .terms-content {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
        }

        h1,
        h2,
        h3 {
            color: rgb(255, 255, 255);
            margin-bottom: 20px;
        }

        h2 {
            margin-top: 30px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        p {
            margin-bottom: 15px;
        }

        ul {
            margin-bottom: 20px;
            padding-left: 20px;
        }

        li {
            margin-bottom: 8px;
        }

        .effective-date {
            font-style: italic;
            margin-top: 40px;
            color: #777;
        }

        .back-to-top {
            background-color: #1D503A;
            color: white;
            border: none;
            padding: 10px 15px;
            position: fixed;
            bottom: 30px;
            right: 30px;
            border-radius: 4px;
            cursor: pointer;
            display: none;
            text-decoration: none;
            font-size: 14px;
        }

        .terms-navigation {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .terms-navigation h3 {
            margin-bottom: 15px;
        }

        .terms-navigation ul {
            list-style-type: none;
            padding-left: 0;
        }

        .terms-navigation ul li {
            margin-bottom: 8px;
        }

        .terms-navigation ul li a {
            color: #3498db;
            text-decoration: none;
        }

        .terms-navigation ul li a:hover {
            text-decoration: underline;
        }

        /* Animations */
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
    </style>
</head>

<body>
    <header>
        <div class="container">
            <h1>Privacy Policy</h1>
            <p>Please read these terms carefully before using our services.</p>
        </div>
    </header>

    <div class="container">
        <div class="terms-content">
            <div class="terms-navigation">
                <h3>Contents</h3>
                <ul>
                    <li><a href="#acceptance">Acceptance of Terms</a></li>
                    <li><a href="#services">Description of Services</a></li>
                    <li><a href="#user-accounts">User Accounts</a></li>
                    <li><a href="#prohibited-uses">Prohibited Uses</a></li>
                    <li><a href="#orders-payments">Orders and Payments</a></li>
                    <li><a href="#shipping-returns">Shipping and Returns</a></li>
                    <li><a href="#intellectual-property">Intellectual Property</a></li>
                    <li><a href="#disclaimer">Disclaimer of Warranties</a></li>
                    <li><a href="#limitation-liability">Limitation of Liability</a></li>
                    <li><a href="#governing-law">Governing Law</a></li>
                    <li><a href="#changes-terms">Changes to Terms</a></li>
                    <li><a href="#contact">Contact Information</a></li>
                </ul>
            </div>

            <section id="acceptance">
                <h2>1. Acceptance of Terms</h2>
                <p>By accessing and using OneFit Clothing's website and services, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>
                <p>These Terms of Service constitute a legally binding agreement between you and OneFit Clothing. Your continued use of our services indicates your acceptance of these terms.</p>
            </section>

            <section id="services">
                <h2>2. Description of Services</h2>
                <p>OneFit Clothing provides an online platform for purchasing clothing items, including but not limited to:</p>
                <ul>
                    <li>Printed t-shirts for women</li>
                    <li>Hoodies and sweatshirts</li>
                    <li>Accessories and related merchandise</li>
                    <li>Customer support services</li>
                    <li>Account management features</li>
                </ul>
                <p>We reserve the right to modify, suspend, or discontinue any aspect of our services at any time without prior notice.</p>
            </section>

            <section id="user-accounts">
                <h2>3. User Accounts</h2>
                <p>To access certain features of our services, you may be required to create an account. When creating an account, you agree to:</p>
                <ul>
                    <li>Provide accurate, current, and complete information</li>
                    <li>Maintain and promptly update your account information</li>
                    <li>Maintain the security of your password and account</li>
                    <li>Accept all risks of unauthorized access to your account</li>
                    <li>Notify us immediately of any unauthorized use of your account</li>
                </ul>
                <p>You are responsible for all activities that occur under your account, regardless of whether the activities are authorized by you.</p>
            </section>

            <section id="prohibited-uses">
                <h2>4. Prohibited Uses</h2>
                <p>You may not use our services for any unlawful purpose or to solicit others to perform unlawful acts. You agree not to:</p>
                <ul>
                    <li>Use our services in violation of any applicable laws or regulations</li>
                    <li>Transmit any worms, viruses, or any code of a destructive nature</li>
                    <li>Attempt to gain unauthorized access to our systems or networks</li>
                    <li>Use our services to transmit spam or unsolicited messages</li>
                    <li>Impersonate any person or entity or falsely represent your affiliation</li>
                    <li>Interfere with or disrupt our services or servers</li>
                    <li>Use automated systems to access our services without permission</li>
                    <li>Collect or harvest personal information of other users</li>
                </ul>
            </section>

            <section id="orders-payments">
                <h2>5. Orders and Payments</h2>
                <p>When you place an order through our services, you agree to the following terms:</p>
                <ul>
                    <li><strong>Order Acceptance:</strong> We reserve the right to refuse or cancel any order for any reason, including but not limited to product availability, errors in product information, or payment issues.</li>
                    <li><strong>Pricing:</strong> All prices are displayed in Sri Lankan Rupees (LKR) and are subject to change without notice. We strive to ensure pricing accuracy but reserve the right to correct any pricing errors.</li>
                    <li><strong>Payment:</strong> Payment must be made at the time of purchase using one of our accepted payment methods. You authorize us to charge your payment method for the total amount of your purchase.</li>
                    <li><strong>Order Confirmation:</strong> You will receive an order confirmation email once your order has been processed and accepted.</li>
                </ul>
            </section>

            <section id="shipping-returns">
                <h2>6. Shipping and Returns</h2>
                <p><strong>Shipping Policy:</strong></p>
                <ul>
                    <li>We offer shipping within Sri Lanka with delivery times of 3-7 business days</li>
                    <li>Free shipping is available on orders over RS 5,000.00</li>
                    <li>Shipping costs and delivery times may vary based on location and product availability</li>
                    <li>Risk of loss passes to you upon delivery to the carrier</li>
                </ul>
                <p><strong>Return Policy:</strong></p>
                <ul>
                    <li>Items may be returned within 30 days of purchase in original condition</li>
                    <li>Items must be unused, unwashed, and have original tags attached</li>
                    <li>Custom or personalized items are non-returnable</li>
                    <li>Return shipping costs are the responsibility of the customer unless the item is defective</li>
                    <li>Refunds will be processed within 5-10 business days after we receive returned items</li>
                </ul>
            </section>

            <section id="intellectual-property">
                <h2>7. Intellectual Property Rights</h2>
                <p>All content on our website, including but not limited to text, graphics, logos, images, audio clips, digital downloads, and software, is the property of OneFit Clothing or its content suppliers and is protected by copyright laws.</p>
                <p>You may not:</p>
                <ul>
                    <li>Reproduce, distribute, or display any content without written permission</li>
                    <li>Use our trademarks, trade names, or service marks without authorization</li>
                    <li>Create derivative works based on our content</li>
                    <li>Use our content for commercial purposes without permission</li>
                </ul>
                <p>Any unauthorized use of our intellectual property may result in legal action and termination of your access to our services.</p>
            </section>

            <section id="disclaimer">
                <h2>8. Disclaimer of Warranties</h2>
                <p>Our services are provided on an "as is" and "as available" basis. We make no representations or warranties of any kind, express or implied, including but not limited to:</p>
                <ul>
                    <li>Warranties of merchantability or fitness for a particular purpose</li>
                    <li>Warranties that our services will be uninterrupted or error-free</li>
                    <li>Warranties regarding the accuracy or reliability of any information</li>
                    <li>Warranties that defects will be corrected</li>
                </ul>
                <p>We do not warrant that our services will meet your requirements or that any errors will be corrected. Your use of our services is at your own risk.</p>
            </section>

            <section id="limitation-liability">
                <h2>9. Limitation of Liability</h2>
                <p>To the fullest extent permitted by law, OneFit Clothing shall not be liable for any indirect, incidental, special, consequential, or punitive damages, including but not limited to:</p>
                <ul>
                    <li>Loss of profits, data, or business opportunities</li>
                    <li>Personal injury or property damage</li>
                    <li>Costs of procurement of substitute goods or services</li>
                    <li>Any damages arising from your use or inability to use our services</li>
                </ul>
                <p>Our total liability to you for all claims arising from or relating to our services shall not exceed the amount you paid to us in the twelve months preceding the claim.</p>
            </section>

            <section id="governing-law">
                <h2>10. Governing Law</h2>
                <p>These Terms of Service shall be governed by and construed in accordance with the laws of Sri Lanka, without regard to its conflict of law provisions.</p>
                <p>Any disputes arising under or in connection with these Terms shall be subject to the exclusive jurisdiction of the courts of Colombo, Sri Lanka.</p>
            </section>

            <section id="changes-terms">
                <h2>11. Changes to Terms of Service</h2>
                <p>We reserve the right to modify these Terms of Service at any time. When we make changes, we will:</p>
                <ul>
                    <li>Post the updated terms on our website</li>
                    <li>Update the "Effective Date" at the bottom of this page</li>
                    <li>Notify users of material changes via email or website notice</li>
                </ul>
                <p>Your continued use of our services after any changes indicates your acceptance of the new terms. If you do not agree to the modified terms, you should discontinue your use of our services.</p>
            </section>

            <section id="contact">
                <h2>12. Contact Information</h2>
                <p>If you have any questions about these Terms of Service or our practices, please contact us:</p>
                <ul>
                    <li>By email: support@onefitclothing.com</li>
                    <li>By mail: 123 Fashion Street, Colombo 07, Sri Lanka</li>
                    <li>By phone: +94 11 222 3333</li>
                </ul>
                <p>We will respond to your inquiries within 2-3 business days.</p>
            </section>

            <p class="effective-date">Effective Date: April 1, 2025</p>
        </div>
    </div>

    <a href="#" class="back-to-top" id="backToTop">Back to Top</a>

    <script>
        // Back to top button functionality
        const backToTopButton = document.getElementById('backToTop');

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });

        backToTopButton.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('.terms-navigation a').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();

                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);

                window.scrollTo({
                    top: targetElement.offsetTop - 20,
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>

</html>
<?php
// Include footer
include 'includes/footer.php';
?>