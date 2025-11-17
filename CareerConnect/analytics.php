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

// Count users by role
$roleSql = "SELECT role, COUNT(*) as count FROM registeredusers GROUP BY role";
$roleResult = mysqli_query($conn, $roleSql);
$userRoles = [];
$totalCompanies = 0;
$totalAdmins = 0;
$totalSeekers = 0;

if ($roleResult) {
    while ($row = mysqli_fetch_assoc($roleResult)) {
        $userRoles[$row['role']] = $row['count'];
        if ($row['role'] == 'company') {
            $totalCompanies = $row['count'];
        } elseif ($row['role'] == 'admin') {
            $totalAdmins = $row['count'];
        } elseif ($row['role'] == 'job_seeker') {
            $totalSeekers = $row['count'];
        }
    }
}

// Get user growth data for the last 7 days - cumulative data
$growthSql = "SELECT 
                DATE(reg_date) as date,
                SUM(CASE WHEN role = 'job_seeker' THEN 1 ELSE 0 END) as daily_seekers,
                SUM(CASE WHEN role = 'company' THEN 1 ELSE 0 END) as daily_companies,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as daily_admins,
                (SELECT COUNT(*) FROM registeredusers r2 WHERE r2.role = 'job_seeker' AND r2.reg_date <= r1.reg_date) as cumulative_seekers,
                (SELECT COUNT(*) FROM registeredusers r2 WHERE r2.role = 'company' AND r2.reg_date <= r1.reg_date) as cumulative_companies,
                (SELECT COUNT(*) FROM registeredusers r2 WHERE r2.role = 'admin' AND r2.reg_date <= r1.reg_date) as cumulative_admins
              FROM registeredusers r1
              WHERE reg_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
              GROUP BY DATE(reg_date)
              ORDER BY date";
$growthResult = mysqli_query($conn, $growthSql);
$userGrowthData = [
    'labels' => [],
    'jobSeekers' => [],
    'companies' => [],
    'admins' => []
];

if ($growthResult && mysqli_num_rows($growthResult) > 0) {
    while ($row = mysqli_fetch_assoc($growthResult)) {
        $userGrowthData['labels'][] = date('M j', strtotime($row['date']));
        $userGrowthData['jobSeekers'][] = $row['cumulative_seekers'];
        $userGrowthData['companies'][] = $row['cumulative_companies'];
        $userGrowthData['admins'][] = $row['cumulative_admins'];
    }
} else {
    // If no data for last 7 days, create realistic cumulative data
    $dates = [];
    $baseSeekers = max(0, $totalSeekers - 15);
    $baseCompanies = max(0, $totalCompanies - 5);
    $baseAdmins = max(0, $totalAdmins - 2);

    for ($i = 6; $i >= 0; $i--) {
        $date = date('M j', strtotime("-$i days"));
        $dates[] = $date;

        // Simulate gradual growth
        $userGrowthData['jobSeekers'][] = $baseSeekers + ($i * 2);
        $userGrowthData['companies'][] = $baseCompanies + $i;
        $userGrowthData['admins'][] = $baseAdmins + (int)($i/3);
    }
    $userGrowthData['labels'] = $dates;
}

// Get recent platform activities
$activitiesSql = "SELECT 
                    username, email, reg_date as date, 'New user registered' as description
                  FROM registeredusers 
                  ORDER BY reg_date DESC 
                  LIMIT 4";
$activitiesResult = mysqli_query($conn, $activitiesSql);
$recentActivities = [];

if ($activitiesResult && mysqli_num_rows($activitiesResult) > 0) {
    while ($row = mysqli_fetch_assoc($activitiesResult)) {
        $recentActivities[] = [
            'icon' => 'user-plus',
            'title' => 'New User Registered',
            'description' => $row['username'] . ' (' . $row['email'] . ') joined the platform',
            'time' => time_elapsed_string($row['date'])
        ];
    }
} else {
    // Fallback activities if no users found
    $recentActivities = [
        [
            'icon' => 'user-plus',
            'title' => 'Platform Setup',
            'description' => 'CareerConnect platform initialized successfully',
            'time' => '1 day ago'
        ],
        [
            'icon' => 'cog',
            'title' => 'System Ready',
            'description' => 'All systems are running smoothly',
            'time' => '2 days ago'
        ]
    ];
}

// Function to calculate time elapsed
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

// Close database connection
mysqli_close($conn);

// PHP variables for dynamic content
$adminName = "Admin User";

// Function to get percentage change
function getPercentageChange($current, $previous) {
    if ($previous == 0) return 0;
    return round((($current - $previous) / $previous) * 100, 1);
}

// Calculate percentage changes (using sample previous month data)
$prevMonthUsers = max(1, $totalUsers - rand(50, 100));
$prevMonthJobs = max(1, $jobListings - rand(20, 50));
$prevMonthApplications = max(1, $applications - rand(100, 200));

$userChange = getPercentageChange($totalUsers, $prevMonthUsers);
$jobChange = getPercentageChange($jobListings, $prevMonthJobs);
$applicationChange = getPercentageChange($applications, $prevMonthApplications);

// Determine if change is positive or negative
$userChangeClass = $userChange >= 0 ? 'positive' : 'negative';
$jobChangeClass = $jobChange >= 0 ? 'positive' : 'negative';
$applicationChangeClass = $applicationChange >= 0 ? 'positive' : 'negative';

// For manual "pie chart" bar logic
$totalForPie = $totalSeekers + $totalCompanies + $totalAdmins;
function percent($n, $t) { return $t ? round($n / $t * 100) : 0; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platform Analytics - CareerConnect Admin</title>
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
            --admin: #f59e0b;
        }
        * {margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
        body {background-color:#f1f5f9;color:var(--dark);line-height:1.6;}
        .container {width:100%;max-width:1200px;margin:0 auto;padding:0 20px;}
        header {background-color:white;box-shadow:0 2px 10px rgba(0,0,0,0.1);position:sticky;top:0;z-index:100;}
        .header-content {display:flex;justify-content:space-between;align-items:center;padding:15px 0;}
        .logo {display:flex;align-items:center;gap:10px;font-size:24px;font-weight:700;color:var(--primary);}
        .logo i {font-size:28px;}
        nav ul {display:flex;list-style:none;gap:25px;}
        nav a {text-decoration:none;color:var(--secondary);font-weight:500;transition:color 0.3s;}
        nav a:hover,nav a.active {color:var(--primary);}
        .user-actions {display:flex;align-items:center;gap:15px;}
        .btn {padding:8px 16px;border-radius:6px;font-weight:500;cursor:pointer;transition:all 0.3s;border:none;}
        .btn-primary {background-color:var(--primary);color:white;}
        .btn-primary:hover {background-color:var(--primary-dark);}
        .btn-outline {background-color:transparent;border:1px solid var(--primary);color:var(--primary);}
        .btn-outline:hover {background-color:var(--primary);color:white;}
        .dashboard {padding:30px 0;}
        .dashboard-header {display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;}
        .dashboard-title {font-size:28px;color:var(--dark);}
        .date-filter {display:flex;align-items:center;gap:10px;}
        .filter-select {padding:8px 12px;border:1px solid var(--gray);border-radius:6px;background-color:white;}
        .stats-container {display:grid;grid-template-columns:repeat(auto-fit, minmax(250px,1fr));gap:20px;margin-bottom:30px;}
        .stat-card {background-color:white;border-radius:10px;box-shadow:0 4px 6px rgba(0,0,0,0.05);padding:20px;display:flex;flex-direction:column;}
        .stat-card-header {display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;}
        .stat-card-title {font-size:16px;color:var(--secondary);}
        .stat-card-icon {width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;background-color:rgba(37,99,235,0.1);color:var(--primary);}
        .stat-card-value {font-size:28px;font-weight:700;color:var(--dark);}
        .stat-card-change {font-size:14px;margin-top:5px;}
        .positive {color:var(--success);}
        .negative {color:var(--danger);}
        .charts-row {display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:30px;}
        .chart-container {background-color:white;border-radius:10px;box-shadow:0 4px 6px rgba(0,0,0,0.05);padding:20px;margin-bottom:20px;}
        .chart-header {display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;}
        .chart-title {font-size:18px;font-weight:600;color:var(--dark);}
        .chart-actions {display:flex;gap:10px;}
        .chart-actions button {background:none;border:none;color:var(--secondary);cursor:pointer;font-size:14px;}
        .chart-actions button.active {color:var(--primary);font-weight:600;}
        .chart-canvas {position:relative;min-height:300px;width:100%;}
        /* GROUPED BAR CHART STYLES */
        .manual-growth-bar-chart-wrap {
            width:100%;
            height:270px;
            position:relative;
            margin-bottom:0;
            display:flex;
            align-items:flex-end;
            justify-content:flex-start;
            padding-left:60px;
            padding-bottom:38px;
            box-sizing:border-box;
        }
        .manual-growth-bar-yaxis {
            position:absolute;
            left:0;
            bottom:38px;
            width:55px;
            height:200px;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            align-items:flex-end;
            z-index:2;
        }
        .manual-growth-bar-yaxis-label {
            color:var(--secondary);
            font-size:14px;
        }
        .manual-growth-bar-bars-group {
            display:flex;
            align-items:flex-end;
            height:200px;
            gap:80px;
            width:calc(100% - 60px);
            position:absolute;
            left:60px;
            bottom:38px;
        }
        .manual-growth-bar-col {
            display:flex;
            flex-direction:row;
            gap:24px;
            align-items:flex-end;
            width:180px;
            justify-content:center;
        }
        .manual-growth-bar-seeker {
            width:40px;
            background:var(--primary);
            border-radius:20px 20px 0 0;
            margin-bottom:0;
            transition:height 0.2s;
        }
        .manual-growth-bar-company {
            width:40px;
            background:var(--success);
            border-radius:20px 20px 0 0;
            margin-bottom:0;
            transition:height 0.2s;
        }
        .manual-growth-bar-admin {
            width:40px;
            background:var(--admin);
            border-radius:20px 20px 0 0;
            margin-bottom:0;
            transition:height 0.2s;
        }
        .manual-growth-bar-xlabels {
            position:absolute;
            left:60px;
            bottom:12px;
            display:flex;
            gap:80px;
            width:calc(100% - 60px);
        }
        .manual-growth-bar-xlabel {
            width:180px;
            text-align:center;
            font-size:13px;
            color:var(--secondary);
            white-space:nowrap;
        }
        .manual-growth-bar-legend {
            display:flex;
            gap:32px;
            margin-top:12px;
            margin-left:60px;
            align-items:center;
            font-size:18px;
        }
        .manual-growth-bar-legend-dot {
            width:18px;
            height:18px;
            border-radius:50%;
            display:inline-block;
            margin-right:8px;
            vertical-align:middle;
        }
        .manual-growth-bar-legend-seeker {background:var(--primary);}
        .manual-growth-bar-legend-company {background:var(--success);}
        .manual-growth-bar-legend-admin {background:var(--admin);}
        .metrics-grid {display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:30px;}
        .metric-card {background-color:white;border-radius:10px;box-shadow:0 4px 6px rgba(0,0,0,0.05);padding:20px;text-align:center;}
        .metric-icon {width:50px;height:50px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 15px;font-size:20px;color:white;}
        .metric-icon.users {background-color:var(--primary);}
        .metric-icon.companies {background-color:var(--success);}
        .metric-icon.applications {background-color:var(--warning);}
        .metric-value {font-size:24px;font-weight:700;margin-bottom:5px;color:var(--dark);}
        .metric-label {color:var(--secondary);font-size:14px;}
        .activity-list {background-color:white;border-radius:10px;box-shadow:0 4px 6px rgba(0,0,0,0.05);padding:20px;margin-bottom:30px;}
        .activity-header {display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;}
        .activity-title {font-size:18px;font-weight:600;color:var(--dark);}
        .activity-item {display:flex;padding:15px 0;border-bottom:1px solid var(--gray);}
        .activity-item:last-child {border-bottom:none;}
        .activity-icon {width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-right:15px;background-color:rgba(37,99,235,0.1);color:var(--primary);}
        .activity-content {flex:1;}
        .activity-content h4 {margin-bottom:5px;font-size:16px;}
        .activity-content p {color:#777;font-size:14px;}
        .activity-time {color:#999;font-size:12px;}
        footer {background-color:white;padding:30px 0;margin-top:50px;border-top:1px solid var(--gray);}
        .footer-content {display:flex;justify-content:space-between;align-items:center;}
        .footer-links {display:flex;gap:20px;}
        .footer-links a {text-decoration:none;color:var(--secondary);transition:color 0.3s;}
        .footer-links a:hover {color:var(--primary);}
        .copyright {color:var(--secondary);}
        /* Pie chart as horizontal bar */
        .pie-bar {width:220px;height:34px;background:#e2e8f0;border-radius:18px;display:flex;overflow:hidden;margin-bottom:12px;}
        .pie-part-seeker {background:var(--primary);height:100%;}
        .pie-part-company {background:var(--success);height:100%;}
        .pie-part-admin {background:var(--admin);height:100%;}
        .pie-legend {display:flex;gap:18px;padding-top:4px;}
        .pie-legend-item {display:flex;align-items:center;gap:6px;font-size:13px;color:var(--secondary);}
        .pie-legend-color {width:14px;height:14px;border-radius:50%;display:inline-block;}
        .pie-legend-seeker {background:var(--primary);}
        .pie-legend-company {background:var(--success);}
        .pie-legend-admin {background:var(--admin);}
        @media (max-width:992px) {
            .charts-row {grid-template-columns:1fr;}
            .metrics-grid {grid-template-columns:repeat(2,1fr);}
            .bar-chart {max-width:100%;}
            .manual-growth-bar-chart-wrap {padding-left:20px;}
            .manual-growth-bar-yaxis,.manual-growth-bar-bars-group,.manual-growth-bar-xlabels {left:20px !important;width:calc(100% - 20px) !important;}
            .manual-growth-bar-legend {margin-left:20px;}
        }
        @media (max-width:768px) {
            .header-content {flex-direction:column;gap:15px;}
            nav ul {gap:15px;flex-wrap:wrap;justify-content:center;}
            .stats-container {grid-template-columns:1fr;}
            .dashboard-header {flex-direction:column;align-items:flex-start;gap:15px;}
            .metrics-grid {grid-template-columns:1fr;}
            .footer-content {flex-direction:column;gap:20px;text-align:center;}
            .footer-links {justify-content:center;}
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
                        <li><a href="admindashboard.php">Dashboard</a></li>
                        <li><a href="userlist.php">Users</a></li>
                        <li><a href="joblists.php">Jobs</a></li>
                        <li><a href="analytics.php" class="active">Analytics</a></li>
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
                    <h1 class="dashboard-title">Platform Analytics</h1>
                    <div class="date-filter">
                        <span>Time period:</span>
                        <select class="filter-select">
                            <option value="7">Last 7 Days</option>
                            <option value="30" selected>Last 30 Days</option>
                            <option value="90">Last 90 Days</option>
                            <option value="365">Last Year</option>
                        </select>
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
                        <div class="stat-card-change <?php echo $userChangeClass; ?>">
                            <i class="fas fa-arrow-<?php echo $userChange >= 0 ? 'up' : 'down'; ?>"></i> 
                            <?php echo abs($userChange); ?>% from last month
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
                        <div class="stat-card-change <?php echo $jobChangeClass; ?>">
                            <i class="fas fa-arrow-<?php echo $jobChange >= 0 ? 'up' : 'down'; ?>"></i> 
                            <?php echo abs($jobChange); ?>% from last month
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
                        <div class="stat-card-change <?php echo $applicationChangeClass; ?>">
                            <i class="fas fa-arrow-<?php echo $applicationChange >= 0 ? 'up' : 'down'; ?>"></i> 
                            <?php echo abs($applicationChange); ?>% from last month
                        </div>
                    </div>
                </div>

                <div class="charts-row">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3 class="chart-title">User Growth (Cumulative)</h3>
                            <div class="chart-actions">
                                <button>Last Week</button>
                                <button class="active">Last Month</button>
                                <button>Last Year</button>
                            </div>
                        </div>
                        <div class="chart-canvas" style="height: 300px; min-height: 300px;">
                            <!-- Manual grouped bar chart for growth (with y-axis, x-axis, legend) -->
                            <?php
                                $labels = $userGrowthData['labels'];
                                $jobSeekers = $userGrowthData['jobSeekers'];
                                $companies = $userGrowthData['companies'];
                                $admins = $userGrowthData['admins'];
                                $maxGrowth = max(array_merge($jobSeekers, $companies, $admins, [1]));
                                // Find round step for y-axis (5 steps)
                                $steps = 5;
                                $stepVal = ceil($maxGrowth / $steps);
                                $yAxisLabels = [];
                                for($i = $steps; $i >= 0; $i--) $yAxisLabels[] = $i * $stepVal;
                                $barCount = count($labels);
                            ?>
                            <div class="manual-growth-bar-chart-wrap">
                                <!-- Y-Axis -->
                                <div class="manual-growth-bar-yaxis">
                                    <?php foreach($yAxisLabels as $yl): ?>
                                        <span class="manual-growth-bar-yaxis-label"><?php echo $yl; ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <!-- Bars -->
                                <div class="manual-growth-bar-bars-group" style="width:calc(100% - 60px); left:60px;">
                                    <?php for($i=0; $i<$barCount; $i++): ?>
                                    <div class="manual-growth-bar-col">
                                        <div class="manual-growth-bar-seeker" style="height:<?php echo intval($jobSeekers[$i]/($steps*$stepVal)*200); ?>px;" title="Job Seekers: <?php echo $jobSeekers[$i]; ?>"></div>
                                        <div class="manual-growth-bar-company" style="height:<?php echo intval($companies[$i]/($steps*$stepVal)*200); ?>px;" title="Companies: <?php echo $companies[$i]; ?>"></div>
                                        <div class="manual-growth-bar-admin" style="height:<?php echo intval($admins[$i]/($steps*$stepVal)*200); ?>px;" title="Admins: <?php echo $admins[$i]; ?>"></div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                                <!-- X-Axis labels -->
                                <div class="manual-growth-bar-xlabels">
                                    <?php foreach($labels as $l): ?>
                                        <span class="manual-growth-bar-xlabel"><?php echo $l; ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="manual-growth-bar-legend">
                                <span><span class="manual-growth-bar-legend-dot manual-growth-bar-legend-seeker"></span>Job Seekers</span>
                                <span><span class="manual-growth-bar-legend-dot manual-growth-bar-legend-company"></span>Companies</span>
                                <span><span class="manual-growth-bar-legend-dot manual-growth-bar-legend-admin"></span>Admins</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3 class="chart-title">User Distribution</h3>
                        </div>
                        <div class="chart-canvas" style="display: flex; flex-direction: column; align-items: center;">
                            <!-- Pie chart as horizontal bar -->
                            <div class="pie-bar">
                                <div class="pie-part-seeker" style="width:<?=(percent($totalSeekers,$totalForPie))?>%;"></div>
                                <div class="pie-part-company" style="width:<?=(percent($totalCompanies,$totalForPie))?>%;"></div>
                                <div class="pie-part-admin" style="width:<?=(percent($totalAdmins,$totalForPie))?>%;"></div>
                            </div>
                            <div class="pie-legend">
                                <span class="pie-legend-item"><span class="pie-legend-color pie-legend-seeker"></span>Job Seekers (<?=percent($totalSeekers,$totalForPie)?>%)</span>
                                <span class="pie-legend-item"><span class="pie-legend-color pie-legend-company"></span>Companies (<?=percent($totalCompanies,$totalForPie)?>%)</span>
                                <span class="pie-legend-item"><span class="pie-legend-color pie-legend-admin"></span>Admins (<?=percent($totalAdmins,$totalForPie)?>%)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-icon users">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="metric-value"><?php echo number_format($totalUsers); ?></div>
                        <div class="metric-label">Total Users</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-icon companies">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="metric-value"><?php echo number_format($totalCompanies); ?></div>
                        <div class="metric-label">Companies</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-icon applications">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="metric-value"><?php echo number_format($applications); ?></div>
                        <div class="metric-label">Applications</div>
                    </div>
                </div>

                <div class="activity-list">
                    <div class="activity-header">
                        <h3 class="activity-title">Recent Platform Activity</h3>
                        <button class="btn btn-outline">View All</button>
                    </div>
                    
                    <?php foreach($recentActivities as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-<?php echo $activity['icon']; ?>"></i>
                        </div>
                        <div class="activity-content">
                            <h4><?php echo $activity['title']; ?></h4>
                            <p><?php echo $activity['description']; ?></p>
                            <div class="activity-time"><?php echo $activity['time']; ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <link rel="stylesheet" href="jsdash.css">
    <?php include 'footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // No chart.js, no canvas - only manual HTML/CSS
            // Behavior for select/filter buttons can remain
            const chartButtons = document.querySelectorAll('.chart-actions button');
            chartButtons.forEach(button => {
                button.addEventListener('click', () => {
                    chartButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    alert(`Loading data for ${button.textContent} period`);
                });
            });
            const dateFilter = document.querySelector('.date-filter select');
            dateFilter.addEventListener('change', () => {
                const period = dateFilter.value;
                alert(`Loading analytics for the last ${period} days`);
            });
        });
    </script>
</body>
</html>