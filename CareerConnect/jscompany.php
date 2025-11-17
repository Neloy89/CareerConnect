<?php
// Companies Page
session_start();

// Ensure company_follows table exists
$conn = mysqli_connect('localhost', 'root', '', 'careerconnect');
// Ensure company_follows table exists
$createCompanyFollowsTable = "CREATE TABLE IF NOT EXISTS company_follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    company_id INT NOT NULL,
    followed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $createCompanyFollowsTable);
if ($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS company_follows (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        company_id INT NOT NULL,
        followed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $sql);
    // Don't close here, used later
}

// Handle follow/unfollow POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['company_id'])) {
    $loggedInUsername = $_SESSION['username'] ?? '';
    if ($loggedInUsername) {
        $companyId = intval($_POST['company_id']);
        $action = $_POST['action'] ?? '';
        $conn = mysqli_connect('localhost', 'root', '', 'careerconnect');
        if ($conn && $companyId) {
            if ($action === 'follow') {
                // Insert if not exists
                $check = mysqli_query($conn, "SELECT * FROM company_follows WHERE username='$loggedInUsername' AND company_id=$companyId");
                if (mysqli_num_rows($check) === 0) {
                    mysqli_query($conn, "INSERT INTO company_follows (username, company_id) VALUES ('$loggedInUsername', $companyId)");
                }
            } elseif ($action === 'unfollow') {
                mysqli_query($conn, "DELETE FROM company_follows WHERE username='$loggedInUsername' AND company_id=$companyId");
            }
            mysqli_close($conn);
        }
    }
    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF'] . (isset($_GET['view_jobs_company_id']) ? '?view_jobs_company_id=' . $_GET['view_jobs_company_id'] : ''));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareerConnect | Companies</title>
    <link rel="stylesheet" href="Try_Rakib.css">
    <link rel="stylesheet" href="jscompany.css">
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
                        <li><a href="jscompany.php" class="nav-link active">Companies</a></li>
                        <li><a href="jsinterview.php" class="nav-link">Interviews</a></li>
                        <li><a href="jsprofile.php" class="nav-link">Profile</a></li>
                        
                    </ul>
                </nav>
                <div class="user-actions">
                    <span>Welcome, <strong id="userName"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></strong></span>
                    <a href="Login_Try.php" class="btn btn-outline" id="logoutBtn">Logout</a>
                </div>
            </div>
        </div>
    </header>
    
    <main>
        <section class="companies-hero">
            <div class="container">
                <h1>Explore Top Companies</h1>
                <p>Discover and follow companies that match your career aspirations</p>
            </div>
        </section>
        
        <div class="container">
            <div class="search-section">
                <div class="search-box">
                    <form method="get" action="jscompany.php" style="display:flex;">
                        <div style="position:relative; flex:1;">
                            <i class="fas fa-search search-icon" style="position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#6b7280;"></i>
                            <input type="text" class="search-input" id="companiesSearch" name="search" placeholder="Search companies by name..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" style="width:100%; padding:14px 14px 14px 45px; border-radius:8px; border:1px solid #d1d5db; font-size:1rem;">
                        </div>
                        <button type="submit" style="margin-left:12px; padding:0 24px; background:#2563eb; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Search</button>
                    </form>
                </div>
            </div>
            
            <section class="dashboard active" id="companies">
                <div class="company-grid" id="companiesContainer">
                    <?php
                    $loggedInUsername = $_SESSION['username'] ?? '';
                    $conn = mysqli_connect('localhost', 'root', '', 'careerconnect');
                    if (!$conn) {
                        echo '<div class="no-companies">Database connection failed. Please try again later.</div>';
                    } else {
                        $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
                        if ($searchTerm !== '') {
                            $sql = "SELECT * FROM companyusers WHERE companyName LIKE '%" . mysqli_real_escape_string($conn, $searchTerm) . "%' ORDER BY id DESC";
                        } else {
                            $sql = "SELECT * FROM companyusers ORDER BY id DESC";
                        }
                        $result = mysqli_query($conn, $sql);
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $companyId = $row['id'];
                                $companyName = htmlspecialchars($row['companyName'] ?? '');
                                $companyUsername = htmlspecialchars($row['username'] ?? '');
                                
                                // Get first letter for logo
                                $logoLetter = strtoupper(substr($companyName, 0, 1));
                                
                                // Check if user follows this company
                                $isFollowing = false;
                                if ($loggedInUsername) {
                                    $followCheck = mysqli_query($conn, "SELECT 1 FROM company_follows WHERE username='$loggedInUsername' AND company_id=$companyId LIMIT 1");
                                    $isFollowing = mysqli_num_rows($followCheck) > 0;
                                }
                                
                                echo '<div class="company-card">';
                                echo '<div class="company-header">';
                                echo '<div class="company-logo">' . $logoLetter . '</div>';
                                echo '<div class="company-title-section">';
                                echo '<div class="company-title">' . $companyName . '</div>';
                                if (!empty($row['type'])) {
                                    echo '<div class="company-type">' . htmlspecialchars($row['type']) . '</div>';
                                }
                                echo '</div>';
                                echo '</div>';
                                
                                echo '<div class="company-details">';
                                if (!empty($row['employee'])) {
                                    echo '<div class="detail-item"><i class="fas fa-users"></i> ' . htmlspecialchars($row['employee']) . ' employees</div>';
                                }
                                if (!empty($row['openPosition'])) {
                                    echo '<div class="detail-item"><i class="fas fa-briefcase"></i> ' . htmlspecialchars($row['openPosition']) . ' open positions</div>';
                                }
                                if (!empty($row['location'])) {
                                    echo '<div class="detail-item"><i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($row['location']) . '</div>';
                                }
                                if (!empty($row['website'])) {
                                    echo '<div class="detail-item"><i class="fas fa-globe"></i> <a href="' . htmlspecialchars($row['website']) . '" target="_blank" style="color:#2563eb;">Website</a></div>';
                                }
                                echo '</div>';
                                
                                if (!empty($row['description'])) {
                                    echo '<div class="company-description">' . nl2br(htmlspecialchars($row['description'])) . '</div>';
                                }
                                
                                echo '<div class="company-footer">';
                                echo '<div class="company-meta">';
                                if (!empty($row['experience'])) {
                                    echo '<span><i class="fas fa-star"></i> ' . htmlspecialchars($row['experience']) . '</span>';
                                }
                                if (!empty($row['date'])) {
                                    echo '<span><i class="fas fa-calendar"></i> ' . htmlspecialchars($row['date']) . '</span>';
                                }
                                echo '</div>';
                                
                                // Follow/unfollow form
                                echo '<form method="post" style="margin:0;">';
                                echo '<input type="hidden" name="company_id" value="' . $companyId . '">';
                                if ($isFollowing) {
                                    echo '<button type="submit" name="action" value="unfollow" class="follow-btn following"><i class="fas fa-check"></i> Following</button>';
                                } else {
                                    echo '<button type="submit" name="action" value="follow" class="follow-btn follow"><i class="fas fa-plus"></i> Follow</button>';
                                }
                                echo '</form>';
                                echo '</div>';
                                
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="no-companies">';
                            echo '<i class="fas fa-building"></i>';
                            echo '<h3>No companies found</h3>';
                            echo '<p>Try adjusting your search or browse all companies</p>';
                            echo '</div>';
                        }
                        mysqli_close($conn);
                    }
                    ?>
                </div>
            </section>
        </div>
    </main>
    
    <!-- Footer -->
                <link rel="stylesheet" href="jsdash.css">
    <?php include 'footer.php'; ?>
</body>
</html>