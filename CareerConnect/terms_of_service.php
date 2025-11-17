<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - CareerConnect Bangladesh</title>
    <link rel="stylesheet" href="Try_Rakib.css">
    <style>
        .terms-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px #e2e8f0;
            position: relative;
        }
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background: #2563eb;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        .back-button:hover {
            background: #1d4ed8;
        }
        .terms-container h1 {
            color: #2563eb;
            margin-bottom: 20px;
            text-align: center;
            margin-top: 10px;
        }
        .terms-section {
            margin-bottom: 25px;
        }
        .terms-section h2 {
            color: #374151;
            margin-bottom: 10px;
            font-size: 1.3em;
        }
        .terms-section p, .terms-section ul {
            color: #6b7280;
            line-height: 1.6;
        }
        .terms-section ul {
            margin-left: 20px;
        }
    </style>
</head>
<body>
    
    <div class="container">
        <div class="terms-container">
            <button onclick="goBack()" class="back-button">← Back</button>
            <h1>Terms of Service</h1>
            
            <div class="terms-section">
                <h2>1. Acceptance of Terms</h2>
                <p>By accessing and using CareerConnect Bangladesh, you agree to be bound by these Terms of Service and all applicable laws and regulations of Bangladesh.</p>
            </div>

            <div class="terms-section">
                <h2>2. User Accounts</h2>
                <p>You must be at least 18 years old to create an account. You are responsible for maintaining the confidentiality of your account and password.</p>
            </div>

            <div class="terms-section">
                <h2>3. Job Seeker Responsibilities</h2>
                <ul>
                    <li>Provide accurate and truthful information in your profile</li>
                    <li>Do not misrepresent your qualifications or experience</li>
                    <li>Respect the privacy of employers and other users</li>
                    <li>Do not spam employers with irrelevant applications</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>4. Prohibited Activities</h2>
                <ul>
                    <li>Posting false or misleading information</li>
                    <li>Harassing other users or employers</li>
                    <li>Using the platform for illegal activities</li>
                    <li>Attempting to access others' accounts</li>
                    <li>Violating any Bangladeshi laws</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>5. Intellectual Property</h2>
                <p>All content on CareerConnect Bangladesh is protected by copyright laws of Bangladesh. You may not reproduce, distribute, or create derivative works without permission.</p>
            </div>

            <div class="terms-section">
                <h2>6. Termination</h2>
                <p>We reserve the right to terminate or suspend your account for violation of these terms or for any unlawful activities.</p>
            </div>

            <div class="terms-section">
                <h2>7. Governing Law</h2>
                <p>These terms are governed by and construed in accordance with the laws of the People's Republic of Bangladesh.</p>
            </div>
        </div>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>