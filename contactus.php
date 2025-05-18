<?php
// Set page variables
$pageTitle = "OneFit Clothing - Contact Us";
$showSaleBanner = false;

// Include header
include 'includes/header.php';
?>

<!-- Contact Us Content -->
<header>
    <div class="container">
        <h1>Contact Us</h1>
        <p>We'd love to hear from you. Get in touch with our team.</p>
    </div>
</header>

<div class="container">
    <div class="contact-wrapper">
        <div class="contact-form">
            <h2>Send Us a Message</h2>
            <form id="contactForm">
                <div>
                    <label for="name">Full Name</label>
                    <input type="text" id="name" placeholder="Your Name" required>
                </div>

                <div>
                    <label for="email">Email Address</label>
                    <input type="email" id="email" placeholder="Your Email" required>
                </div>

                <div>
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" placeholder="Your Phone Number">
                </div>

                <div>
                    <label for="subject">Subject</label>
                    <select id="subject" required>
                        <option value="" disabled selected>Select a subject</option>
                        <option value="general">General Inquiry</option>
                        <option value="support">Technical Support</option>
                        <option value="billing">Billing Question</option>
                        <option value="feedback">Feedback</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label for="message">Message</label>
                    <textarea id="message" placeholder="Your Message" required></textarea>
                </div>

                <div>
                    <button type="submit" class="btn">Send Message</button>
                </div>
            </form>
        </div>

        <div class="contact-info">
            <h2>Contact Information</h2>

            <div class="contact-method">
                <h3>Our Office</h3>
                <p>123 Fashion Street</p>
                <p>Colombo 07</p>
                <p>Sri Lanka</p>
            </div>

            <div class="contact-method">
                <h3>Phone & Email</h3>
                <p>Phone: +94 11 222 3333</p>
                <p>Support: support@onefitclothing.com</p>
                <p>Sales: sales@onefitclothing.com</p>
            </div>

            <div class="contact-method">
                <h3>Business Hours</h3>
                <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                <p>Saturday: 10:00 AM - 4:00 PM</p>
                <p>Sunday: Closed</p>
            </div>

        </div>
    </div><br><br>

    <h2>Visit OneFit Clothing</h2>
    <iframe
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
    body {
        color: #333;
        line-height: 1.6;
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

    .contact-wrapper {
        display: flex;
        flex-wrap: wrap;
        margin-top: 40px;
        gap: 30px;
    }

    .contact-form {
        flex: 1;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        min-width: 300px;
    }

    .contact-info {
        flex: 1;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        min-width: 300px;
    }

    h1,
    h2,
    h3 {
        margin-bottom: 20px;
        color: #2c3e50;
    }

    form div {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    input,
    textarea,
    select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    textarea {
        height: 150px;
        resize: vertical;
    }

    .btn {
        background-color: #1D503A;
        color: white;
        border: none;
        padding: 12px 20px;
        font-size: 16px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .btn:hover {
        background-color: #2980b9;
    }

    .contact-method {
        margin-bottom: 25px;
    }

    .contact-method h3 {
        font-size: 18px;
        margin-bottom: 10px;
        color: #1D503A;
    }

    .contact-method p {
        margin-bottom: 5px;
    }


    footer {
        margin-top: 50px;
        padding: 20px;
        background-color: #2c3e50;
        color: white;
    }

    @media (max-width: 768px) {
        .contact-wrapper {
            flex-direction: column;
        }
    }
</style>

<?php
// Include footer
include 'includes/footer.php';
?>