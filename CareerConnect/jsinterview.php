<?php
// Interviews Page
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "careerconnect";
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get logged-in username
$loggedInUsername = $_SESSION['username'] ?? '';

// Get search value
$search = trim($_GET['search'] ?? '');

// Get interview count for this user (not filtered by search)
$interviewCount = 0;
if ($loggedInUsername) {
    $sqlCount = "SELECT COUNT(*) AS cnt FROM interviews WHERE username = '" . mysqli_real_escape_string($conn, $loggedInUsername) . "'";
    $resultCount = mysqli_query($conn, $sqlCount);
    if ($resultCount && $row = mysqli_fetch_assoc($resultCount)) {
        $interviewCount = intval($row['cnt']);
    }
}

// Fetch interviews with companyName via JOIN, with search
$interviews = [];
if ($loggedInUsername) {
    $where = "i.username = '" . mysqli_real_escape_string($conn, $loggedInUsername) . "'";
    if ($search !== '') {
        $safeSearch = mysqli_real_escape_string($conn, $search);
        $where .= " AND (i.position LIKE '%$safeSearch%' OR c.companyName LIKE '%$safeSearch%')";
    }
    $sql = "SELECT i.interview_date, i.position, i.interview_time, i.interview_type, i.location, i.interviewers, i.notes, i.company_username, c.companyName
            FROM interviews i
            LEFT JOIN companyusers c ON i.company_username = c.username
            WHERE $where
            ORDER BY i.interview_date DESC, i.interview_time DESC";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $interviews[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareerConnect | Interviews</title>
    <link rel="stylesheet" href="Try_Rakib.css">
    <link rel="stylesheet" href="jsinterview.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo" id="homeBtn">
                    <i class="fas fa-briefcase"></i>
                    <span>CareerConnect</span>
                </div>
                <nav>
                    <ul>
                        <li><a href="jsdash.php" class="nav-link">Dashboard</a></li>
                        <li><a href="jsjobs.php" class="nav-link">Jobs</a></li>
                        <li><a href="jscompany.php" class="nav-link">Companies</a></li>
                        <li><a href="jsinterview.php" class="nav-link active">Interviews</a></li>
                        <li><a href="jsprofile.php" class="nav-link">Profile</a></li>
                    </ul>
                </nav>
                <div class="user-actions">
                    <span>Welcome, <strong id="userName"><?php echo htmlspecialchars($loggedInUsername ? $loggedInUsername : 'Guest'); ?></strong></span>
                    <a href="Login_Try.php" class="btn btn-outline" id="logoutBtn">Logout</a>
                </div>
            </div>
        </div>
    </header>
    <main>
        <section class="dashboard active" id="interviews">
            <div class="container">
                <div class="dashboard-header">
                    <h1 class="dashboard-title">Interview Management</h1>
                    <div class="header-actions">
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-title">Total Interviews</div>
                        <div class="stat-value"><?php echo $interviewCount; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Upcoming Interviews</div>
                        <div class="stat-value"><?php 
                            $upcomingCount = 0;
                            $today = date('Y-m-d');
                            foreach ($interviews as $interview) {
                                if ($interview['interview_date'] >= $today) {
                                    $upcomingCount++;
                                }
                            }
                            echo $upcomingCount;
                        ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Companies</div>
                        <div class="stat-value"><?php 
                            $companies = [];
                            foreach ($interviews as $interview) {
                                $company = $interview['companyName'] ?? $interview['company_username'];
                                if (!in_array($company, $companies)) {
                                    $companies[] = $company;
                                }
                            }
                            echo count($companies);
                        ?></div>
                    </div>
                </div>
                
                <!-- Search Section -->
                <div class="search-container">
                    <form method="get" action="jsinterview.php">
                        <div class="search-box-interview">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" name="search" placeholder="Search by position or company name..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit">Search</button>
                            <?php if($search !== ''): ?>
                                <a href="jsinterview.php" class="clear-search">Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <!-- Interviews Table Section -->
                <div class="content-card">
                    <div class="section-header">
                        <h2 class="section-title">Interview Schedule</h2>
                        <div class="interview-count">
                            <span style="color: #64748b; font-weight: 500;">Showing <?php echo count($interviews); ?> interview(s)</span>
                        </div>
                    </div>
                    
                    <?php if (count($interviews) > 0): ?>
                        <div class="table-container">
                            <table class="interviews-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Position</th>
                                        <th>Time</th>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Company</th>
                                        <th>Interviewers</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($interviews as $interview) {
                                    echo "<tr>";
                                    echo "<td><strong>" . htmlspecialchars($interview['interview_date']) . "</strong></td>";
                                    echo "<td>" . htmlspecialchars($interview['position']) . "</td>";
                                    echo "<td>" . htmlspecialchars($interview['interview_time']) . "</td>";
                                    
                                    // Add badge styling for interview type
                                    $interviewType = htmlspecialchars($interview['interview_type']);
                                    $badgeClass = '';
                                    if (strpos(strtolower($interviewType), 'onsite') !== false) {
                                        $badgeClass = 'badge-onsite';
                                    } elseif (strpos(strtolower($interviewType), 'online') !== false || strpos(strtolower($interviewType), 'virtual') !== false) {
                                        $badgeClass = 'badge-online';
                                    } elseif (strpos(strtolower($interviewType), 'phone') !== false) {
                                        $badgeClass = 'badge-phone';
                                    }
                                    
                                    echo "<td><span class='badge $badgeClass'>" . $interviewType . "</span></td>";
                                    echo "<td>" . htmlspecialchars($interview['location']) . "</td>";
                                    
                                    // Show companyName if available, fallback to company_username
                                    echo "<td><strong>" . htmlspecialchars($interview['companyName'] ?? $interview['company_username']) . "</strong></td>";
                                    echo "<td>" . htmlspecialchars($interview['interviewers']) . "</td>";
                                    echo "<td>" . htmlspecialchars($interview['notes']) . "</td>";
                                    echo "</tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <h3>No interviews found</h3>
                            <p>
                                <?php if($search !== ''): ?>
                                    No interviews match your search criteria. Try adjusting your search terms.
                                <?php else: ?>
                                    You haven't scheduled any interviews yet. Check your job applications to schedule interviews.
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
    
<!-- Footer -->
                <link rel="stylesheet" href="jsdash.css">
    <?php include 'footer.php'; ?>
</body>
</html>