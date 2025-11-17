<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - CareerConnect Bangladesh</title>
    <link rel="stylesheet" href="Try_Rakib.css">
    <style>
        .help-container {
            max-width: 900px;
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
        .help-container h1 {
            color: #2563eb;
            margin-bottom: 30px;
            text-align: center;
            margin-top: 10px;
        }
        .help-section {
            margin-bottom: 30px;
        }
        .help-section h2 {
            color: #374151;
            margin-bottom: 15px;
            font-size: 1.3em;
        }
        .faq-item {
            margin-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 15px;
        }
        .faq-question {
            font-weight: bold;
            color: #4b5563;
            margin-bottom: 5px;
        }
        .faq-answer {
            color: #6b7280;
            line-height: 1.6;
        }
        .support-channels {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .channel-card {
            padding: 20px;
            background: #f0f9ff;
            border-radius: 8px;
            text-align: center;
        }
        .channel-card h3 {
            color: #0369a1;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    
    <div class="container">
        <div class="help-container">
            <button onclick="goBack()" class="back-button">← Back</button>
            <h1>Help Center - CareerConnect Bangladesh</h1>
            
            <div class="help-section">
                <h2>Frequently Asked Questions</h2>
                
                <div class="faq-item">
                    <div class="faq-question">How do I create a job seeker profile?</div>
                    <div class="faq-answer">Go to your profile page and fill in all the required information including your personal details, education, work experience, and upload your resume.</div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">How do I apply for jobs?</div>
                    <div class="faq-answer">Browse the Jobs section, find a position that matches your skills, and click "Apply Now" to submit your application.</div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">Can I edit my application after submitting?</div>
                    <div class="faq-answer">Yes, you can edit your applications from the Dashboard under "Recent Applications" section.</div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">How do I know if I have an interview?</div>
                    <div class="faq-answer">Check the Interviews section in your dashboard. You will also receive email notifications.</div>
                </div>
            </div>

            <div class="help-section">
                <h2>Support Channels</h2>
                <div class="support-channels">
                    <div class="channel-card">
                        <h3>Phone Support</h3>
                        <p>+880 2 5566 7788</p>
                        <p>+880 1966 123456</p>
                        <p>9 AM - 6 PM (BST)</p>
                    </div>
                    <div class="channel-card">
                        <h3>Email Support</h3>
                        <p>support@careerconnect.bd</p>
                        <p>help@careerconnect.bd</p>
                        <p>24/7 Response</p>
                    </div>
                    <div class="channel-card">
                        <h3>Live Chat</h3>
                        <p>Available on website</p>
                        <p>10 AM - 4 PM (BST)</p>
                        <p>Quick assistance</p>
                    </div>
                    <div class="channel-card">
                        <h3>Visit Our Office</h3>
                        <p>House 123, Road 12</p>
                        <p>Bashundhara R/A</p>
                        <p>Dhaka 1229</p>
                    </div>
                </div>
            </div>

            <div class="help-section">
                <h2>Emergency Technical Support</h2>
                <p>For urgent technical issues affecting your job applications:</p>
                <p><strong>Emergency Hotline:</strong> +880 1911 998877 (24/7)</p>
                <p><strong>Emergency Email:</strong> emergency@careerconnect.bd</p>
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