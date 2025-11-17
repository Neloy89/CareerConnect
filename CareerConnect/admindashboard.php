<?php
session_start();

// Database connection (W3Schools procedural style)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "careerconnect";

$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch statistics from database
$totalUsers = 0;
$jobListings = 0;
$applications = 0;

// Count total users
$userSql = "SELECT COUNT(*) as total FROM registeredusers";
$userResult = mysqli_query($conn, $userSql);
if ($userResult && mysqli_num_rows($userResult) > 0) {
    $userData = mysqli_fetch_assoc($userResult);
    $totalUsers = $userData['total'];
}

// Count total jobs
$jobSql = "SELECT COUNT(*) as total FROM postjob";
$jobResult = mysqli_query($conn, $jobSql);
if ($jobResult && mysqli_num_rows($jobResult) > 0) {
    $jobData = mysqli_fetch_assoc($jobResult);
    $jobListings = $jobData['total'];
}

// Count total applications
$appSql = "SELECT COUNT(*) as total FROM applications";
$appResult = mysqli_query($conn, $appSql);
if ($appResult && mysqli_num_rows($appResult) > 0) {
    $appData = mysqli_fetch_assoc($appResult);
    $applications = $appData['total'];
}

// Fetch recent job applications from applications table with job details
$recentAppsSql = "SELECT a.fullname, a.location, a.applied_at, a.phone, p.jobTitle, p.companyName 
                  FROM applications a 
                  JOIN postjob p ON a.id = p.id 
                  ORDER BY a.applied_at DESC 
                  LIMIT 5";
$recentAppsResult = mysqli_query($conn, $recentAppsSql);
$jobApplications = [];

if ($recentAppsResult) {
    while ($row = mysqli_fetch_assoc($recentAppsResult)) {
        $jobApplications[] = $row;
    }
}

// Close database connection
mysqli_close($conn);

// PHP variables for dynamic content
$adminName = "Admin User";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CareerConnect</title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray: #e2e8f0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f1f5f9;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }
        
        .logo i {
            font-size: 28px;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 25px;
        }
        
        nav a {
            text-decoration: none;
            color: var(--secondary);
            font-weight: 500;
            transition: color 0.3s;
        }
        
        nav a:hover, nav a.active {
            color: var(--primary);
        }
        
        .user-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }
        
        .dashboard {
            padding: 30px 0;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .dashboard-title {
            font-size: 28px;
            color: var(--dark);
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .stat-card-title {
            font-size: 16px;
            color: var(--secondary);
        }
        
        .stat-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }
        
        .stat-card-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
        }
        
        .stat-card-change {
            font-size: 14px;
            margin-top: 5px;
        }
        
        .positive {
            color: var(--success);
        }
        
        .negative {
            color: var(--danger);
        }
        
        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray);
        }
        
        .table th {
            background-color: #f8fafc;
            font-weight: 600;
            color: var(--secondary);
        }
        
        .table tr:hover {
            background-color: #f1f5f9;
        }
        
        .admin-services {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .service-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            transition: transform 0.3s;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
        }
        
        .service-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
            color: white;
        }
        
        .manage-users { background: var(--primary); }
        .monitor-jobs { background: var(--success); }
        .analytics { background: var(--warning); }
        
        .service-card h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: var(--secondary);
        }
        
        .service-card p {
            color: #777;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .service-link {
            display: inline-block;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }
        
        .service-link i {
            margin-left: 5px;
        }
        
        footer {
            background-color: white;
            padding: 30px 0;
            margin-top: 50px;
            border-top: 1px solid var(--gray);
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .footer-links {
            display: flex;
            gap: 20px;
        }
        
        .footer-links a {
            text-decoration: none;
            color: var(--secondary);
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: var(--primary);
        }
        
        .copyright {
            color: var(--secondary);
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            nav ul {
                gap: 15px;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .admin-services {
                grid-template-columns: 1fr;
            }
            
            .footer-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .footer-links {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-briefcase"></i>
                    <span>CareerConnect</span>
                </div>
                <nav>
                    <ul>
                        <li><a href="admindashboard.php" class="active">Dashboard</a></li>
                        <li><a href="userlist.php">Users</a></li>
                        <li><a href="joblists.php">Jobs</a></li>
                        <li><a href="analytics.php">Analytics</a></li>
                    </ul>
                </nav>
                <div class="user-actions">
                    <span>Admin Panel</span>
                    <a href="Login_Try.php" class="btn btn-outline">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section class="dashboard">
            <div class="container">
                <div class="dashboard-header">
                    <h1 class="dashboard-title">Admin Dashboard</h1>
                    <div>
                        <span>Welcome, <strong><?php echo $adminName; ?></strong></span>
                    </div>
                </div>

                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Total Users</div>
                            <div class="stat-card-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?php echo number_format($totalUsers); ?></div>
                        <div class="stat-card-change positive">
                            <i class="fas fa-arrow-up"></i> 12% from last month
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Job Listings</div>
                            <div class="stat-card-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?php echo number_format($jobListings); ?></div>
                        <div class="stat-card-change positive">
                            <i class="fas fa-arrow-up"></i> 8% from last month
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Applications</div>
                            <div class="stat-card-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?php echo number_format($applications); ?></div>
                        <div class="stat-card-change positive">
                            <i class="fas fa-arrow-up"></i> 15% from last month
                        </div>
                    </div>
                </div>

                <h2 style="margin-bottom: 20px; color: var(--dark);">Admin Services</h2>
                <div class="admin-services">
                    <div class="service-card">
                        <div class="service-icon manage-users">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h3>Manage User Accounts</h3>
                        <p>Create, edit, or delete user accounts. Manage permissions and access levels for all users on the platform.</p>
                        <a href="userlist.php" class="service-link">Manage Users <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="service-card">
                        <div class="service-icon monitor-jobs">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h3>Monitor Job Listings</h3>
                        <p>Review, approve, or remove job postings. Monitor active listings and ensure compliance with platform guidelines.</p>
                        <a href="joblists.php" class="service-link">Monitor Listings <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="service-card">
                        <div class="service-icon analytics">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h3>Platform Analytics Dashboard</h3>
                        <p>Access detailed analytics about platform usage, user engagement, and performance metrics.</p>
                        <a href="analytics.php" class="service-link">View Analytics <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Job Applications</h2>
                        <button class="btn btn-primary">View All</button>
                    </div>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Applicant Name</th>
                                <th>Job Title</th>
                                <th>Company</th>
                                <th>Location</th>
                                <th>Phone</th>
                                <th>Date Applied</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($jobApplications as $application): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($application['fullname'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($application['jobTitle'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($application['companyName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($application['location'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($application['phone'] ?? 'N/A'); ?></td>
                                <td><?php echo isset($application['applied_at']) ? date('M j, Y', strtotime($application['applied_at'])) : 'N/A'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($jobApplications)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px;">
                                    <h3>No recent applications found</h3>
                                    <p>No job applications have been submitted yet.</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

  <!-- Footer -->
                <link rel="stylesheet" href="jsdash.css">
    <?php include 'footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const serviceCards = document.querySelectorAll('.service-card');
            serviceCards.forEach(card => {
                card.addEventListener('click', function() {
                    const serviceName = this.querySelector('h3').textContent;
                    alert(`Navigating to: ${serviceName}`);
                });
            });
        });
    </script>
</body>
</html>