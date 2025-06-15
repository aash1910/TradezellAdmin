<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - PiqDrop</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
        }
        .section {
            margin-bottom: 30px;
        }
        .highlight {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #55B086;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <h1>Terms of Service</h1>
    <p>Last updated: {{ date('F d, Y') }}</p>

    <div class="section">
        <h2>1. Acceptance of Terms</h2>
        <p>By accessing and using PiqDrop's mobile application and services, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our services.</p>
    </div>

    <div class="section">
        <h2>2. Account Registration and Login</h2>
        <div class="highlight">
            <h3>2.1 Account Creation</h3>
            <p>To use PiqDrop's services, you must:</p>
            <ul>
                <li>Be at least 18 years old</li>
                <li>Provide accurate and complete information during registration</li>
                <li>Maintain the security of your account credentials</li>
                <li>Notify us immediately of any unauthorized access</li>
            </ul>
        </div>

        <div class="highlight">
            <h3>2.2 Login Methods</h3>
            <p>We support multiple login methods:</p>
            <ul>
                <li>Email and password authentication</li>
                <li>Facebook login integration</li>
                <li>Phone number verification</li>
            </ul>
            <p>By using these login methods, you agree to comply with the respective service providers' terms of service.</p>
        </div>
    </div>

    <div class="section">
        <h2>3. App Usage and Services</h2>
        <div class="highlight">
            <h3>3.1 Delivery Services</h3>
            <p>As a user of PiqDrop, you agree to:</p>
            <ul>
                <li>Provide accurate delivery information</li>
                <li>Pay all applicable fees for services rendered</li>
                <li>Not use the service for illegal purposes</li>
                <li>Comply with all applicable laws and regulations</li>
            </ul>
        </div>

        <div class="highlight">
            <h3>3.2 User Conduct</h3>
            <p>You agree not to:</p>
            <ul>
                <li>Use the service for any illegal purpose</li>
                <li>Harass, abuse, or harm others</li>
                <li>Attempt to gain unauthorized access</li>
                <li>Interfere with the proper functioning of the service</li>
            </ul>
        </div>
    </div>

    <div class="section">
        <h2>4. Privacy and Data Protection</h2>
        <p>Your use of PiqDrop is also governed by our Privacy Policy, which can be found at {{ config('app.url') }}/privacy-policy. By using our services, you consent to the collection and use of your information as described in our Privacy Policy.</p>
    </div>

    <div class="section">
        <h2>5. Intellectual Property</h2>
        <p>All content, features, and functionality of the PiqDrop application, including but not limited to text, graphics, logos, and software, are the exclusive property of PiqDrop and are protected by international copyright, trademark, and other intellectual property laws.</p>
    </div>

    <div class="section">
        <h2>6. Limitation of Liability</h2>
        <p>PiqDrop shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of or inability to use the service.</p>
    </div>

    <div class="section">
        <h2>7. Modifications to Terms</h2>
        <p>We reserve the right to modify these terms at any time. We will notify users of any material changes via email or through the application. Your continued use of the service after such modifications constitutes your acceptance of the new terms.</p>
    </div>

    <div class="section">
        <h2>8. Termination</h2>
        <p>We reserve the right to terminate or suspend your account and access to the service at our sole discretion, without notice, for conduct that we believe violates these Terms of Service or is harmful to other users, us, or third parties, or for any other reason.</p>
    </div>

    <div class="section">
        <h2>9. Contact Information</h2>
        <p>If you have any questions about these Terms of Service, please contact us at:</p>
        <p>Email: support@piqdrop.com</p>
    </div>
</body>
</html> 