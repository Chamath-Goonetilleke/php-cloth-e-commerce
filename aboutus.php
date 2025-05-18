<?php
// Set page variables
$pageTitle = "OneFit Clothing - About Us";
$showSaleBanner = false;

// Include header
include 'includes/header.php';
?>

<!-- About Us Content -->
<header>
    <div class="container">
        <h1>About Us</h1>
        <p>Learn more about our company, mission, and the dedicated team behind our success.</p>
    </div>
</header>

<div class="container">
    <div class="about-content">
        <section class="company-intro">
            <h2>Our Story</h2>
            <p>Founded in 2020, OneFit Clothing Store has grown from a small startup with big dreams to an industry leader in innovative solutions. Our journey began when our founders identified a critical gap in the market and developed a vision to address it with cutting-edge technology and exceptional service.</p>
            <p>Today, we serve thousands of clients worldwide, helping them achieve their goals through our comprehensive suite of products and services. We remain committed to the core values that have guided us from the beginning: innovation, integrity, and customer satisfaction.</p>
        </section>

        <section class="mission-vision">
            <div class="mission">
                <h2>Our Mission</h2>
                <p>To empower businesses and individuals with innovative solutions that solve complex problems, enhance productivity, and drive success in an ever-evolving digital landscape.</p>
            </div>
            <div class="vision">
                <h2>Our Vision</h2>
                <p>To be the global leader in our industry, recognized for excellence, innovation, and the positive impact we create for our clients, employees, and communities around the world.</p>
            </div>
        </section>

        <section class="values-section">
            <h2>Our Core Values</h2>
            <div class="values-grid">
                <div class="value-item">
                    <div class="value-icon">&#9733;</div>
                    <h3>Innovation</h3>
                    <p>We continuously push boundaries and embrace new technologies to create groundbreaking solutions.</p>
                </div>
                <div class="value-item">
                    <div class="value-icon">&#9829;</div>
                    <h3>Integrity</h3>
                    <p>We operate with honesty, transparency, and ethical standards in all our business dealings.</p>
                </div>
                <div class="value-item">
                    <div class="value-icon">&#9786;</div>
                    <h3>Customer Focus</h3>
                    <p>We place our customers at the center of everything we do, prioritizing their needs and success.</p>
                </div>
                <div class="value-item">
                    <div class="value-icon">&#9775;</div>
                    <h3>Excellence</h3>
                    <p>We strive for the highest quality in our products, services, and internal processes.</p>
                </div>
            </div>
        </section>

        <section class="team-section">
            <h2>Our Leadership Team</h2>
            <p>Meet the dedicated professionals who guide our company's vision and operations.</p>
            <div class="team-members">
                <div class="team-member">
                    <div class="team-photo"> <img src="mem1.jpg" alt="member 1"></div>
                    <h3>Methuli Lawanma</h3>
                    <p class="position">CEO & Founder</p>
                    <p>Methuli brings over 20 years of industry experience and a passion for innovative leadership.</p>
                </div>
                <div class="team-member">
                    <div class="team-photo"> <img src="mem2.jpg" alt="member 2"></div>
                    <h3>Vinugi Dhamsa</h3>
                    <p class="position">Creative Director</p>
                    <p>Vinugi designs trendy t-shirts and hoodies that reflect the bold, confident spirit of the OneFit brand.</p>
                </div>
                <div class="team-member">
                    <div class="team-photo"> <img src="mem4.jpg" alt="member 3"></div>
                    <h3>Emily Johnson</h3>
                    <p class="position">Marketing & Social Media Manager</p>
                    <p>Emily promotes OneFit across digital platforms, building brand awareness and engaging with a loyal customer base.</p>
                </div>
            </div>
        </section>

        <section class="milestones">
            <h2>Our Journey</h2>
            <p>A timeline of key milestones that have shaped our growth and success.</p>
            <div class="timeline">
                <div class="milestone left">
                    <div class="milestone-content">
                        <div class="milestone-year">2020</div>
                        <h3>Company Founded</h3>
                        <p>OneFit Clothing Store was established with a mission to revolutionize the industry.</p>
                    </div>
                </div>
                <div class="milestone right">
                    <div class="milestone-content">
                        <div class="milestone-year">2022</div>
                        <h3>First Major Product Launch</h3>
                        <p>We launched our flagship product to critical acclaim and market success.</p>
                    </div>
                </div>
                <div class="milestone left">
                    <div class="milestone-content">
                        <div class="milestone-year">2023</div>
                        <h3>International Expansion</h3>
                        <p>Opened offices in Europe and Asia to better serve our global client base.</p>
                    </div>
                </div>
                <div class="milestone right">
                    <div class="milestone-content">
                        <div class="milestone-year">2024</div>
                        <h3>Industry Innovation Award</h3>
                        <p>Recognized for our contributions to industry advancements and excellence.</p>
                    </div>
                </div>
                <div class="milestone left">
                    <div class="milestone-content">
                        <div class="milestone-year">2025</div>
                        <h3>Major Partnership</h3>
                        <p>Formed strategic alliance with industry leaders to expand our service offerings.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div><br><br><br>

<style>
    /*about us*/
    body {
        background-color: #f5f5f5;
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

    .about-content {
        background: white;
        padding: 40px;
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        margin-top: 40px;
    }

    h1,
    h2,
    h3 {
        color: #2c3e50;
        margin-bottom: 20px;
    }

    p {
        margin-bottom: 15px;
    }

    .company-intro {
        text-align: center;
        margin-bottom: 40px;
    }

    .company-intro p {
        font-size: 18px;
        max-width: 800px;
        margin: 0 auto 20px;
    }

    .mission-vision {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        margin-bottom: 40px;
    }

    .mission,
    .vision {
        flex: 1;
        min-width: 300px;
        background-color: rgb(255, 255, 255);
        padding: 25px;
        border-radius: 8px;
        border-left: 4px solid #87e1f7;
    }

    .team-section {
        margin-bottom: 40px;
    }

    .team-members {
        display: flex;
        flex-wrap: wrap;
        gap: 25px;
        margin-top: 30px;
    }

    .team-member {
        flex: 1;
        min-width: 250px;
        text-align: center;
    }

    .team-photo img {
        width: 200px;
        /* Adjust the size as needed */
        height: 200px;
        border-radius: 50%;
        /* Makes it perfectly round */
        object-fit: cover;
        /* Ensures image stays centered and fills the circle */
        border: 2px solid #ccc;
        /* Optional: adds a soft border */
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        /* Optional: subtle shadow */
    }


    .team-member h3 {
        margin-bottom: 10px;
    }

    .team-member p.position {
        color: #3498db;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .milestones {
        margin-bottom: 40px;
    }

    .timeline {
        position: relative;
        max-width: 1000px;
        margin: 30px auto 0;
    }

    .timeline::after {
        content: '';
        position: absolute;
        width: 6px;
        background-color: #e0e0e0;
        top: 0;
        bottom: 0;
        left: 50%;
        margin-left: -3px;
    }

    .milestone {
        padding: 10px 40px;
        position: relative;
        width: 50%;
    }

    .milestone::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        right: -10px;
        background-color: #1D503A;
        border: 4px solid #fff;
        top: 15px;
        border-radius: 50%;
        z-index: 1;
    }

    .left {
        left: 0;
    }

    .right {
        left: 50%;
    }

    .right::after {
        left: -10px;
    }

    .milestone-content {
        padding: 20px;
        background-color: white;
        position: relative;
        border-radius: 6px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .milestone-year {
        font-weight: bold;
        color: #3498db;
        margin-bottom: 5px;
    }

    .values-section {
        margin-bottom: 40px;
    }

    .values-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 30px;
    }

    .value-item {
        flex: 1;
        min-width: 250px;
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 8px;
        text-align: center;
    }

    .value-icon {
        font-size: 36px;
        color: #3498db;
        margin-bottom: 15px;
    }
</style>

<?php
// Include footer
include 'includes/footer.php';
?>