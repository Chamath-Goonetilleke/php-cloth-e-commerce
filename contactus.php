<?php
// Set page variables
$pageTitle = "OneFit Clothing - Contact Us";
$showSaleBanner = false;

// Include header
include 'includes/header.php';
?>

<!-- Contact Us Content -->
<header class="cntus-header">
    <div class="cntus-container">
        <h1 class="cntus-title">Contact Us</h1>
        <p class="cntus-subtitle">We'd love to hear from you. Get in touch with our team.</p>
    </div>
</header>

<div class="cntus-container">
    <div class="cntus-contact-wrapper">
        <div class="cntus-contact-form">
            <h2 class="cntus-form-title">Send Us a Message</h2>
            <form id="cntus-contactForm">
                <div class="cntus-form-group">
                    <label for="cntus-name">Full Name</label>
                    <input type="text" id="cntus-name" placeholder="Your Name" required>
                </div>

                <div class="cntus-form-group">
                    <label for="cntus-email">Email Address</label>
                    <input type="email" id="cntus-email" placeholder="Your Email" required>
                </div>

                <div class="cntus-form-group">
                    <label for="cntus-phone">Phone Number</label>
                    <input type="tel" id="cntus-phone" placeholder="Your Phone Number">
                </div>

                <div class="cntus-form-group">
                    <label for="cntus-subject">Subject</label>
                    <select id="cntus-subject" required>
                        <option value="" disabled selected>Select a subject</option>
                        <option value="general">General Inquiry</option>
                        <option value="support">Technical Support</option>
                        <option value="billing">Billing Question</option>
                        <option value="feedback">Feedback</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="cntus-form-group">
                    <label for="cntus-message">Message</label>
                    <textarea id="cntus-message" placeholder="Your Message" required></textarea>
                </div>

                <div class="cntus-form-group">
                    <button type="submit" class="cntus-btn">Send Message</button>
                </div>
            </form>
        </div>

        <div class="cntus-contact-info">
            <h2 class="cntus-info-title">Contact Information</h2>

            <div class="cntus-contact-method">
                <h3 class="cntus-method-title">Our Office</h3>
                <p class="cntus-method-detail">123 Fashion Street</p>
                <p class="cntus-method-detail">Colombo 07</p>
                <p class="cntus-method-detail">Sri Lanka</p>
            </div>

            <div class="cntus-contact-method">
                <h3 class="cntus-method-title">Phone & Email</h3>
                <p class="cntus-method-detail">Phone: +94 11 222 3333</p>
                <p class="cntus-method-detail">Support: support@onefitclothing.com</p>
                <p class="cntus-method-detail">Sales: sales@onefitclothing.com</p>
            </div>

            <div class="cntus-contact-method">
                <h3 class="cntus-method-title">Business Hours</h3>
                <p class="cntus-method-detail">Monday - Friday: 9:00 AM - 6:00 PM</p>
                <p class="cntus-method-detail">Saturday: 10:00 AM - 4:00 PM</p>
                <p class="cntus-method-detail">Sunday: Closed</p>
            </div>
        </div>
    </div><br><br>

    <h2 class="cntus-visit-title">Visit OneFit Clothing</h2>
    <iframe
        class="cntus-map"
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12674189.06037902!2d76.92814650386995!3d7.873054137010355!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae2b70c4b2ddcaf%3A0xd50b4a67b75eb93a!2sSri%20Lanka!5e0!3m2!1sen!2slk!4v1712562815926!5m2!1sen!2slk"
        width="600"
        height="450"
        style="border:0;"
        allowfullscreen=""
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade">
    </iframe>
</div>

<style>
    /*container contact us*/
    .cntus-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .cntus-header {
        background-color: #2c3e50;
        color: white;
        padding: 20px 0;
        text-align: center;
    }

    .cntus-title {
        margin-bottom: 10px;
        color: white;
    }

    .cntus-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
    }

    .cntus-contact-wrapper {
        display: flex;
        flex-wrap: wrap;
        margin-top: 40px;
        gap: 30px;
    }

    .cntus-contact-form {
        flex: 1;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        min-width: 300px;
    }

    .cntus-contact-info {
        flex: 1;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        min-width: 300px;
    }

    .cntus-form-title,
    .cntus-info-title,
    .cntus-visit-title {
        margin-bottom: 20px;
        color: #2c3e50;
    }

    .cntus-form-group {
        margin-bottom: 15px;
    }

    .cntus-contact-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .cntus-contact-form input,
    .cntus-contact-form textarea,
    .cntus-contact-form select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    .cntus-contact-form textarea {
        height: 150px;
        resize: vertical;
    }

    .cntus-btn {
        background-color: #1D503A;
        color: white;
        border: none;
        padding: 12px 20px;
        font-size: 16px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .cntus-btn:hover {
        background-color: #2980b9;
    }

    .cntus-contact-method {
        margin-bottom: 25px;
    }

    .cntus-method-title {
        font-size: 18px;
        margin-bottom: 10px;
        color: #1D503A;
    }

    .cntus-method-detail {
        margin-bottom: 5px;
    }

    .cntus-map {
        width: 100%;
        max-width: 100%;
        margin-top: 20px;
    }

    @media (max-width: 768px) {
        .cntus-contact-wrapper {
            flex-direction: column;
        }

        .cntus-map {
            height: 300px;
        }
    }
</style>

<?php
// Include footer
include 'includes/footer.php';
?>