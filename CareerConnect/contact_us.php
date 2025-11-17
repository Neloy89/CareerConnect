<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - CareerConnect Bangladesh</title>
    <link rel="stylesheet" href="Try_Rakib.css">
    <style>
        .contact-container {
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
        .contact-container h1 {
            color: #2563eb;
            margin-bottom: 30px;
            text-align: center;
            margin-top: 10px;
        }
        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .contact-card {
            padding: 20px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #2563eb;
        }
        .contact-card h3 {
            color: #374151;
            margin-bottom: 10px;
        }
        .contact-card p {
            color: #6b7280;
            margin: 5px 0;
        }
        .office-hours {
            background: #fff7ed;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    
    <div class="container">
        <div class="contact-container">
            <button onclick="goBack()" class="back-button">← Back</button>
            <h1>Contact CareerConnect Bangladesh</h1>
            
            <div class="contact-info">
                <div class="contact-card">
                    <h3>Head Office</h3>
                    <p>House 123, Road 12, Block D</p>
                    <p>Bashundhara R/A, Dhaka 1229</p>
                    <p>Bangladesh</p>
                </div>

                <div class="contact-card">
                    <h3>Contact Numbers</h3>
                    <p>Main: +880 2 5566 7788</p>
                    <p>Hotline: +880 1966 123456</p>
                    <p>WhatsApp: +880 1911 223344</p>
                </div>

                <div class="contact-card">
                    <h3>Email Addresses</h3>
                    <p>General: info@careerconnect.bd</p>
                    <p>Support: support@careerconnect.bd</p>
                    <p>Partnership: partners@careerconnect.bd</p>
                </div>

                <div class="contact-card">
                    <h3>Regional Offices</h3>
                    <p><strong>Chittagong:</strong> +880 31 654321</p>
                    <p><strong>Sylhet:</strong> +880 821 712345</p>
                    <p><strong>Khulna:</strong> +880 41 765432</p>
                </div>
            </div>

            <div class="office-hours">
                <h3>Office Hours</h3>
                <p>Sunday - Thursday: 9:00 AM - 6:00 PM</p>
                <p>Friday: 9:00 AM - 1:00 PM & 3:00 PM - 6:00 PM</p>
                <p>Saturday: Closed</p>
                <p><em>All times are Bangladesh Standard Time (BST)</em></p>
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