<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - Tradezell</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }
        h1 {
            color: #2D6A4F;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        h2 {
            color: #1B4332;
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 1.5em;
            border-bottom: 2px solid #52B788;
            padding-bottom: 5px;
        }
        h3 {
            color: #2D6A4F;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 1.2em;
        }
        .section {
            margin-bottom: 30px;
        }
        .highlight {
            background-color: #f0f4f2;
            padding: 15px;
            border-left: 4px solid #52B788;
            margin: 15px 0;
        }
        ul {
            margin-left: 20px;
        }
        li {
            margin-bottom: 8px;
        }
        a {
            color: #2D6A4F;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Terms of Service</h1>
    <p>Last updated: {{ date('F d, Y') }}</p>

    <div class="section">
        <h2>1. Acceptance of Terms</h2>
        <p>By accessing and using the Tradezell mobile application and related services (the "Service"), you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use the Service.</p>
        <p>Tradezell is a local marketplace platform that helps users discover, trade, sell, and buy pre-owned items. Tradezell connects users but is not a party to transactions between users.</p>
    </div>

    <div class="section">
        <h2>2. Account Registration and Login</h2>
        <div class="highlight">
            <h3>2.1 Account Creation</h3>
            <p>To use Tradezell, you must:</p>
            <ul>
                <li>Be at least 18 years old</li>
                <li>Provide accurate and complete information during registration</li>
                <li>Maintain the security of your account credentials</li>
                <li>Notify us immediately of any unauthorized access to your account</li>
            </ul>
        </div>

        <div class="highlight">
            <h3>2.2 Login Methods</h3>
            <p>We support the following login methods:</p>
            <ul>
                <li>Email and password authentication</li>
                <li>Google Sign-In</li>
                <li>Apple Sign-In</li>
            </ul>
            <p>By using these login methods, you agree to comply with the respective service providers' terms of service.</p>
        </div>

        <div class="highlight">
            <h3>2.3 Account Roles</h3>
            <p>Tradezell lets you choose how you use the platform — as a <strong>Trader</strong> (swap items), <strong>Seller</strong> (sell for money), or <strong>Buyer</strong> (browse and purchase). You may change your role in account settings at any time.</p>
        </div>
    </div>

    <div class="section">
        <h2>3. Marketplace Services</h2>
        <div class="highlight">
            <h3>3.1 Listings and Discovery</h3>
            <p>As a user of Tradezell, you may:</p>
            <ul>
                <li>Create listings to trade or sell items with photos and descriptions</li>
                <li>Browse nearby listings using our swipe-based discovery feature</li>
                <li>Like listings and receive matches when interest is mutual</li>
                <li>Chat with matched users to arrange trades or sales</li>
            </ul>
            <p>You agree to provide accurate listing information, including item condition, category, and price where applicable.</p>
        </div>

        <div class="highlight">
            <h3>3.2 User-to-User Transactions</h3>
            <p>Tradezell facilitates connections between users. When you trade or sell items:</p>
            <ul>
                <li>You are solely responsible for the items you list and the agreements you make with other users</li>
                <li>Tradezell does not inspect, guarantee, or take possession of items</li>
                <li>You are responsible for meeting safely and completing exchanges in person or as agreed with the other party</li>
                <li>For paid sales, payment processing may be handled through Stripe or other approved payment methods</li>
            </ul>
        </div>

        <div class="highlight">
            <h3>3.3 In-App Purchases</h3>
            <p>Tradezell may offer optional in-app purchases (for example, additional image uploads for listings). These purchases are processed through the Apple App Store or Google Play Store and are subject to the respective store's terms and refund policies.</p>
        </div>
    </div>

    <div class="section">
        <h2>4. User Conduct</h2>
        <p>You agree not to:</p>
        <ul>
            <li>List illegal, stolen, counterfeit, or prohibited items</li>
            <li>Misrepresent items, prices, or your identity</li>
            <li>Harass, abuse, threaten, or harm other users</li>
            <li>Use the Service for fraud, spam, or scams</li>
            <li>Attempt to gain unauthorized access to accounts or systems</li>
            <li>Interfere with the proper functioning of the Service</li>
            <li>Circumvent safety features, including reporting and blocking tools</li>
        </ul>
        <p>We reserve the right to remove listings or suspend accounts that violate these rules.</p>
    </div>

    <div class="section">
        <h2>5. Privacy and Data Protection</h2>
        <p>Your use of Tradezell is also governed by our <a href="{{ config('app.url') }}/privacy-policy">Privacy Policy</a>. By using the Service, you consent to the collection and use of your information as described in that policy.</p>
    </div>

    <div class="section">
        <h2>6. Intellectual Property</h2>
        <p>All content, features, and functionality of the Tradezell application — including text, graphics, logos, and software — are the exclusive property of Tradezell and its licensors and are protected by applicable copyright, trademark, and other intellectual property laws.</p>
        <p>You retain ownership of content you upload (such as listing photos), but grant Tradezell a license to display and distribute that content as needed to operate the Service.</p>
    </div>

    <div class="section">
        <h2>7. Disclaimer and Limitation of Liability</h2>
        <p>Tradezell is provided on an "as is" basis. We do not guarantee the quality, safety, or legality of items listed by users, nor the conduct of any user.</p>
        <p>To the fullest extent permitted by law, Tradezell shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising from your use of the Service, user interactions, or transactions between users.</p>
    </div>

    <div class="section">
        <h2>8. Modifications to Terms</h2>
        <p>We reserve the right to modify these terms at any time. We will notify users of material changes via email or through the application. Your continued use of the Service after such modifications constitutes your acceptance of the updated terms.</p>
    </div>

    <div class="section">
        <h2>9. Termination</h2>
        <p>We reserve the right to terminate or suspend your account and access to the Service at our sole discretion, without notice, for conduct that we believe violates these Terms of Service, is harmful to other users, or for any other reason.</p>
        <p>You may delete your account at any time through Account settings in the app or by contacting support.</p>
    </div>

    <div class="section">
        <h2>10. Contact Information</h2>
        <p>If you have any questions about these Terms of Service, please contact us at:</p>
        <p><strong>Email:</strong> <a href="mailto:support@tradezell.com">support@tradezell.com</a></p>
        <p><strong>Support page:</strong> <a href="{{ config('app.url') }}/support">{{ config('app.url') }}/support</a></p>
    </div>

    <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 2px solid #e0e0e0; color: #666;">
        <p>© {{ date('Y') }} Tradezell. All rights reserved.</p>
    </div>
</body>
</html>
