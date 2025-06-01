<?php
// You can add any PHP logic here if needed
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

        .tos-page * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        .tos-page-body {
            overflow-x: hidden;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        /* Custom font for logo */
        @font-face {
            font-family: 'Pogonia';
            src: url('https://fonts.cdnfonts.com/css/pogonia') format('woff2');
            font-weight: normal;
            font-style: normal;
        }

        .tos-page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .tos-page-header {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            text-align: center;
        }

        .tos-page-content {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
        }

        .tos-page h1,
        .tos-page h2,
        .tos-page h3 {
            color:rgb(255, 255, 255);
            margin-bottom: 20px;
        }

        .tos-page h2 {
            margin-top: 30px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .tos-page p {
            margin-bottom: 15px;
        }

        .tos-page ul,
        .tos-page ol {
            margin-bottom: 20px;
            padding-left: 20px;
        }

        .tos-page li {
            margin-bottom: 8px;
        }

        .tos-page-last-updated {
            font-style: italic;
            margin-top: 40px;
            color: #777;
        }

        .tos-page-back-to-top {
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

        .tos-page-navigation {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .tos-page-navigation h3 {
            margin-bottom: 15px;
        }

        .tos-page-navigation ul {
            list-style-type: none;
            padding-left: 0;
        }

        .tos-page-navigation ul li {
            margin-bottom: 8px;
        }

        .tos-page-navigation ul li a {
            color: #3498db;
            text-decoration: none;
        }

        .tos-page-navigation ul li a:hover {
            text-decoration: underline;
        }

        .tos-page-highlight-box {
            background-color: #f8f9fa;
            border-left: 4px solid #1D503A;
            padding: 15px;
            margin-bottom: 20px;
        }

        /* Scroll to Top Button */
        .tos-page-scroll-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background-color: #1D503A;
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .tos-page-scroll-top.tos-page-active {
            opacity: 1;
            visibility: visible;
        }

        .tos-page-scroll-top:hover {
            background-color: #143726;
            transform: translateY(-5px);
        }

        /* Animations */
        @keyframes tos-page-fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes tos-page-shine {
            0% {
                left: -100px;
            }

            20% {
                left: 100%;
            }

            100% {
                left: 100%;
            }
        }
    </style>
</head>

<body class="tos-page-body">
    <div class="tos-page">
        <header class="tos-page-header">
            <div class="tos-page-container">
                <h1>Terms of Service</h1>
                <p>Please read these terms carefully before using our services.</p>
            </div>
        </header>

        <div class="tos-page-container">
            <div class="tos-page-content">
                <div class="tos-page-navigation">
                    <h3>Contents</h3>
                    <ul>
                        <li><a href="#tos-introduction">Introduction</a></li>
                        <li><a href="#tos-definitions">Definitions</a></li>
                        <li><a href="#tos-account">Account Registration</a></li>
                        <li><a href="#tos-services">Services</a></li>
                        <li><a href="#tos-restrictions">User Restrictions</a></li>
                        <li><a href="#tos-payment">Payment Terms</a></li>
                        <li><a href="#tos-intellectual-property">Intellectual Property</a></li>
                        <li><a href="#tos-disclaimer">Disclaimer of Warranties</a></li>
                        <li><a href="#tos-limitation">Limitation of Liability</a></li>
                        <li><a href="#tos-indemnification">Indemnification</a></li>
                        <li><a href="#tos-termination">Termination</a></li>
                        <li><a href="#tos-changes">Changes to Terms</a></li>
                        <li><a href="#tos-governing-law">Governing Law</a></li>
                        <li><a href="#tos-disputes">Dispute Resolution</a></li>
                        <li><a href="#tos-contact">Contact Information</a></li>
                    </ul>
                </div>

                <div class="tos-page-highlight-box">
                    <p><strong>PLEASE READ CAREFULLY:</strong> By accessing or using our services, you agree to be bound by these Terms of Service. If you do not agree with any part of these terms, you may not use our services.</p>
                </div>

                <section id="tos-introduction">
                    <h2>1. Introduction</h2>
                    <p>Welcome to OneFit Clothing Store. These Terms of Service ("Terms") govern your access to and use of our website, products, and services (collectively, the "Services").</p>
                    <p>By accessing or using our Services, you agree to be bound by these Terms and our Privacy Policy. If you are using our Services on behalf of a company or other legal entity, you represent that you have the authority to bind that entity to these Terms.</p>
                </section>

                <section id="tos-definitions">
                    <h2>2. Definitions</h2>
                    <p>Throughout these Terms, the following terms shall have the meanings defined below:</p>
                    <ul>
                        <li><strong>"User,"</strong> "you," or "your" refers to any individual or entity that accesses or uses our Services.</li>
                        <li><strong>"Content"</strong> refers to any text, graphics, images, music, software, audio, video, information, or other materials that may be viewed on, accessed through, or contributed to our Services.</li>
                        <li><strong>"User Content"</strong> refers to any Content that a User posts, uploads, publishes, submits, or transmits to be made available through our Services.</li>
                        <li><strong>"Intellectual Property Rights"</strong> means all patent rights, copyright rights, moral rights, rights of publicity, trademark, trade dress and service mark rights, goodwill, trade secret rights and other intellectual property rights as may now exist or hereafter come into existence, and all applications therefore and registrations, renewals and extensions thereof, under the laws of any state, country, territory or other jurisdiction.</li>
                    </ul>
                </section>

                <section id="tos-account">
                    <h2>3. Account Registration</h2>
                    <p>To access certain features of our Services, you may be required to register for an account. You agree to provide accurate, current, and complete information during the registration process and to update such information to keep it accurate, current, and complete.</p>
                    <p>You are responsible for safeguarding your account credentials and for any activities or actions under your account. We reserve the right to disable any user account at any time if, in our opinion, you have violated any provision of these Terms.</p>
                </section>

                <section id="tos-services">
                    <h2>4. Services</h2>
                    <p>Subject to these Terms, we grant you a limited, non-exclusive, non-transferable, and revocable license to access and use our Services for your personal or business purposes.</p>
                    <p>We reserve the right to modify, suspend, or discontinue the Services (or any part or feature thereof) at any time, with or without notice. We will not be liable if for any reason all or any part of the Services are unavailable at any time or for any period.</p>
                </section>

                <section id="tos-restrictions">
                    <h2>5. User Restrictions</h2>
                    <p>When using our Services, you agree not to:</p>
                    <ol>
                        <li>Violate any applicable law, rule, or regulation;</li>
                        <li>Infringe upon or violate our intellectual property rights or the intellectual property rights of others;</li>
                        <li>Transmit or upload any viruses, malware, or other types of malicious software, or links to such software;</li>
                        <li>Use the Services to send unsolicited or unauthorized advertising, promotional materials, spam, junk mail, chain letters, or any other form of duplicative or unsolicited messages;</li>
                        <li>Interfere with or disrupt the operation of the Services or servers or networks connected to the Services;</li>
                        <li>Impersonate or attempt to impersonate us, our employees, another user, or any other person or entity;</li>
                        <li>Collect or store personal data about other users without their consent;</li>
                        <li>Attempt to gain unauthorized access to any portion or feature of the Services, other accounts, computer systems, or networks connected to the Services;</li>
                        <li>Use any automated means, including, but not limited to, agents, robots, scripts, or spiders, to access, monitor, or copy any part of the Services;</li>
                        <li>Use the Services in any manner that could disable, overburden, damage, or impair the Services.</li>
                    </ol>
                </section>

                <section id="tos-payment">
                    <h2>6. Payment Terms</h2>
                    <p>If you subscribe to any of our paid Services, the following terms apply:</p>
                    <ul>
                        <li>You agree to pay all fees or charges to your account based on the fees, charges, and billing terms in effect at the time a fee or charge is due and payable.</li>
                        <li>All payment obligations are non-cancelable and all amounts paid are non-refundable, except as specifically provided in these Terms.</li>
                        <li>We reserve the right to modify our pricing at any time. If we modify pricing for a service to which you subscribe, we will provide notice of the change through our website or by email at least 30 days before the change takes effect.</li>
                        <li>You are responsible for all taxes associated with your use of our Services (excluding taxes based on our net income).</li>
                    </ul>
                </section>

                <section id="tos-intellectual-property">
                    <h2>7. Intellectual Property</h2>
                    <p>Our Services and their entire contents, features, and functionality (including but not limited to all information, software, text, displays, images, video, and audio, and the design, selection, and arrangement thereof), are owned by us, our licensors, or other providers of such material and are protected by copyright, trademark, patent, trade secret, and other intellectual property or proprietary rights laws.</p>
                    <p><strong>User Content.</strong> By submitting, posting, or displaying User Content on or through our Services, you grant us a worldwide, non-exclusive, royalty-free license to use, reproduce, adapt, publish, translate, distribute, and display such User Content in connection with providing and promoting our Services.</p>
                    <p>You represent and warrant that you own or have the necessary rights to the User Content you submit through our Services and that such User Content does not violate the intellectual property rights or any other rights of any third party.</p>
                </section>

                <section id="tos-disclaimer">
                    <h2>8. Disclaimer of Warranties</h2>
                    <p>YOUR USE OF THE SERVICES IS AT YOUR SOLE RISK. THE SERVICES ARE PROVIDED ON AN "AS IS" AND "AS AVAILABLE" BASIS, WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, NON-INFRINGEMENT, OR THAT THE SERVICES WILL BE UNINTERRUPTED OR ERROR-FREE.</p>
                </section>

                <section id="tos-limitation">
                    <h2>9. Limitation of Liability</h2>
                    <p>IN NO EVENT SHALL WE, OUR AFFILIATES, SERVICE PROVIDERS, EMPLOYEES, AGENTS, OFFICERS, OR DIRECTORS BE LIABLE FOR DAMAGES OF ANY KIND, UNDER ANY LEGAL THEORY, ARISING OUT OF OR IN CONNECTION WITH YOUR USE, OR INABILITY TO USE, THE SERVICES, INCLUDING ANY DIRECT, INDIRECT, SPECIAL, INCIDENTAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, INCLUDING BUT NOT LIMITED TO, PERSONAL INJURY, PAIN AND SUFFERING, EMOTIONAL DISTRESS, LOSS OF REVENUE, LOSS OF PROFITS, LOSS OF BUSINESS OR ANTICIPATED SAVINGS, LOSS OF USE, LOSS OF GOODWILL, LOSS OF DATA, AND WHETHER CAUSED BY TORT (INCLUDING NEGLIGENCE), BREACH OF CONTRACT OR OTHERWISE, EVEN IF FORESEEABLE.</p>
                </section>

                <section id="tos-indemnification">
                    <h2>10. Indemnification</h2>
                    <p>You agree to defend, indemnify, and hold harmless us, our affiliates, licensors, and service providers, and our and their respective officers, directors, employees, contractors, agents, licensors, suppliers, successors, and assigns from and against any claims, liabilities, damages, judgments, awards, losses, costs, expenses, or fees (including reasonable attorneys' fees) arising out of or relating to your violation of these Terms or your use of the Services.</p>
                </section>

                <section id="tos-termination">
                    <h2>11. Termination</h2>
                    <p>We may terminate or suspend your access to all or part of our Services, with or without notice, for any conduct that we, in our sole discretion, believe violates these Terms or is harmful to other users of our Services, to us, or to third parties, or for any other reason.</p>
                    <p>Upon termination, your right to use the Services will immediately cease. If you wish to terminate your account, you may simply discontinue using the Services or contact us to request account deletion.</p>
                </section>

                <section id="tos-changes">
                    <h2>12. Changes to Terms</h2>
                    <p>We reserve the right to revise and update these Terms from time to time at our sole discretion. All changes are effective immediately when we post them. Your continued use of the Services following the posting of revised Terms means that you accept and agree to the changes.</p>
                </section>

                <section id="tos-governing-law">
                    <h2>13. Governing Law</h2>
                    <p>These Terms and any dispute or claim arising out of or in connection with them or their subject matter or formation shall be governed by and construed in accordance with the laws of [Your State/Country], without giving effect to any choice or conflict of law provision or rule.</p>
                </section>

                <section id="tos-disputes">
                    <h2>14. Dispute Resolution</h2>
                    <p>Any legal action or proceeding arising under these Terms shall be brought exclusively in the federal or state courts located in [Your Jurisdiction], and you hereby consent to personal jurisdiction and venue therein.</p>
                    <p>Any cause of action or claim you may have arising out of or relating to these Terms or the Services must be commenced within one (1) year after the cause of action accrues, otherwise, such cause of action or claim is permanently barred.</p>
                </section>

                <section id="tos-contact">
                    <h2>15. Contact Information</h2>
                    <p>If you have any questions about these Terms, please contact us:</p>
                    <ul>
                        <li>By email: support@onefitclothing.com </li>
                        <li>By mail: 123 Fashion Street, Colombo 07, Sri Lanka</li>
                        <li>By phone: +94 11 222 3333</li>
                    </ul>
                </section>

                <p class="tos-page-last-updated">Last Updated: March 10, 2025</p>
            </div>
        </div>
    </div>

    <!-- Scroll to Top Button -->
    <a href="#" class="tos-page-scroll-top" id="tosPageScrollTop">
        <i class="fas fa-chevron-up"></i>
    </a>

    <script>
        // Scroll to top functionality
        document.addEventListener('DOMContentLoaded', function() {
            const scrollTop = document.getElementById('tosPageScrollTop');

            // Show/hide scroll to top button
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    scrollTop.classList.add('tos-page-active');
                } else {
                    scrollTop.classList.remove('tos-page-active');
                }
            });

            // Scroll to top when clicked
            scrollTop.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
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