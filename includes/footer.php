    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>OneFit Clothing</h3>
                <p>Your destination for trendy and cozy wear. Quality clothes designed for comfort and style.</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/tshirts.php">T-Shirts</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/hoodies.php">Hoodies</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/aboutus.php">About Us</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contactus.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Customer Service</h3>
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>/size.php">Size Guide</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/terms.php">Terms & Conditions</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/privacy-policy.php">Privacy Policy</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/reviews.php">Reviews</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/faq.php">FAQs</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Newsletter</h3>
                <p>Subscribe to our newsletter for updates on new arrivals and special offers.</p>
                <form class="newsletter-form" action="<?php echo SITE_URL; ?>/subscribe.php" method="POST">
                    <input type="email" name="email" placeholder="Your Email" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> OneFit Clothing. All Rights Reserved.</p>
            <div class="payment-icons">
                <i class="fab fa-cc-visa"></i>
                <i class="fab fa-cc-mastercard"></i>
                <i class="fab fa-cc-amex"></i>
                <i class="fab fa-cc-paypal"></i>
            </div>
        </div>
    </footer>

    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    </body>

    </html>