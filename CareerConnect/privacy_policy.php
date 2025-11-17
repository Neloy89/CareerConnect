<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - CareerConnect Bangladesh</title>
    <link rel="stylesheet" href="Try_Rakib.css">
    <style>
        .policy-container {
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
        .policy-container h1 {
            color: #2563eb;
            margin-bottom: 20px;
            text-align: center;
            margin-top: 10px;
        }
        .policy-section {
            margin-bottom: 25px;
        }
        .policy-section h2 {
            color: #374151;
            margin-bottom: 10px;
            font-size: 1.3em;
        }
        .policy-section p {
            color: #6b7280;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    
    <div class="container">
        <div class="policy-container">
            <button onclick="goBack()" class="back-button">← Back</button>
            <h1>Privacy Policy</h1>
            
            <div class="policy-section">
                <h2>1. Information We Collect</h2>
                <p>CareerConnect Bangladesh collects personal information including but not limited to: name, email address, phone number, location, educational background, work experience, and resume documents.</p>
            </div>

            <div class="policy-section">
                <h2>2. How We Use Your Information</h2>
                <p>We use your information to match you with potential employers, provide job recommendations, improve our services, and communicate with you about opportunities and platform updates.</p>
            </div>

            <div class="policy-section">
                <h2>3. Data Protection</h2>
                <p>We implement appropriate security measures to protect your personal information in accordance with Bangladesh's Digital Security Act 2018 and data protection regulations.</p>
            </div>

            <div class="policy-section">
                <h2>4. Information Sharing</h2>
                <p>Your information is shared only with employers you apply to and with your explicit consent. We do not sell your personal data to third parties.</p>
            </div>

            <div class="policy-section">
                <h2>5. Your Rights</h2>
                <p>You have the right to access, correct, or delete your personal information. Contact our data protection officer at dpo@careerconnect.bd for assistance.</p>
            </div>

            <div class="policy-section">
                <h2>6. Contact Information</h2>
                <p>For privacy-related concerns, contact us at:<br>
                Email: privacy@careerconnect.bd<br>
                Phone: +880 2 5566 7788<br>
                Address: House 123, Road 12, Block D, Bashundhara R/A, Dhaka 1229</p>
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