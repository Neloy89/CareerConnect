<?php
// Job Seeker Dashboard Section moved from Try_Rakib.php
session_start();
$showModal = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['modal_action']) && $_POST['modal_action'] === 'open') {
        $showModal = true;
    }
    if (isset($_POST['modal_action']) && $_POST['modal_action'] === 'close') {
        $showModal = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareerConnect | Job Seeker Dashboard</title>
    <link rel="stylesheet" href="Try_Rakib.css">
    <link rel="stylesheet" href="jsdash.css">
</head>
<body>
    <?php
    // Ensure DB connection and session username are available for dashboard cards
    $conn = mysqli_connect("localhost", "root", "", "careerconnect");
    $loggedInUsername = $_SESSION['username'] ?? '';
        // Ensure jobseekerusers table exists
        $createJobseekerTable = "CREATE TABLE IF NOT EXISTS jobseekerusers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL,
            email VARCHAR(100),
            fullName VARCHAR(100),
            phone VARCHAR(20),
            location VARCHAR(100),
            professionalTitle VARCHAR(100),
            skills TEXT,
            experience TEXT,
            education TEXT,
            resume VARCHAR(255),
            coverLetter VARCHAR(255),
            portfolio VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        mysqli_query($conn, $createJobseekerTable);
        // Ensure interviews table exists
        $createInterviewsTable = "CREATE TABLE IF NOT EXISTS interviews (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            applicant VARCHAR(100) NOT NULL,
            position VARCHAR(50) NOT NULL,
            interview_date DATE NOT NULL,
            interview_time TIME NOT NULL,
            interview_type VARCHAR(50) NOT NULL,
            location VARCHAR(255) NOT NULL,
            interviewers VARCHAR(255) NOT NULL,
            notes TEXT,
            username VARCHAR(100) NOT NULL,
            company_username VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        mysqli_query($conn, $createInterviewsTable);
        // Ensure applications table exists
        $createTable = "CREATE TABLE IF NOT EXISTS applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            jobId INT,
            fullName VARCHAR(100),
            email VARCHAR(100),
            phone VARCHAR(20),
            location VARCHAR(100),
            interest TEXT,
            resume VARCHAR(255),
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        mysqli_query($conn, $createTable);
        // Add username column if not exists
        $checkUsernameCol = mysqli_query($conn, "SHOW COLUMNS FROM applications LIKE 'username'");
        if (mysqli_num_rows($checkUsernameCol) == 0) {
            mysqli_query($conn, "ALTER TABLE applications ADD COLUMN username VARCHAR(100) AFTER resume");
        }
        // Add company_username column if not exists
        $checkCompanyCol = mysqli_query($conn, "SHOW COLUMNS FROM applications LIKE 'company_username'");
        if (mysqli_num_rows($checkCompanyCol) == 0) {
            mysqli_query($conn, "ALTER TABLE applications ADD COLUMN company_username VARCHAR(100) AFTER username");
        }
    ?>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo" id="homeBtn">
                    <i class="fas fa-briefcase"></i>
                    <span>CareerConnect</span>
                </div>
                <nav>
                    <ul>
                        <li><a href="jsdash.php" class="nav-link active">Dashboard</a></li>
                        <li><a href="jsjobs.php" class="nav-link">Jobs</a></li>
                        <li><a href="jscompany.php" class="nav-link">Companies</a></li>
                         <li><a href="jsinterview.php" class="nav-link">Interviews</a></li>
                        <li><a href="jsprofile.php" class="nav-link">Profile</a></li>
                    </ul>
                </nav>
                <div class="user-actions">
    <span>Welcome, <strong><?php echo htmlspecialchars($loggedInUsername); ?></strong></span>
    <a href="Login_Try.php" class="btn btn-outline">Logout</a>
</div>
            </div>
        </div>
    </header>

    <!-- Main Content -->

        </main>
</body>
            <div class="container">
                <div class="dashboard-header">
                    <h1 class="dashboard-title">Job Seeker Dashboard</h1>
                    <div>
                        <button class="btn btn-primary" id="profileBtn">Edit Profile</button>
                    </div>
                </div>

                    <script>
                    // Redirect to jsprofile.php when Edit Profile is clicked
                    document.addEventListener('DOMContentLoaded', function() {
                        var profileBtn = document.getElementById('profileBtn');
                        if (profileBtn) {
                            profileBtn.addEventListener('click', function() {
                                window.location.href = 'jsprofile.php';
                            });
                        }
                    });
                    </script>
                <!-- Dashboard Cards -->
                <div class="stats-container" style="display: flex; gap: 24px; margin-bottom: 32px;">
                    <div class="stat-card" id="followingCompaniesCard" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #e2e8f0; padding: 24px; flex: 1;">
                        <div style="font-size: 18px; color: #444; margin-bottom: 8px;">Applications</div>
                        <div style="font-size: 32px; font-weight: bold; color: #222;">
                        <?php
                        // Show number of jobs applied for by logged-in user
                        $appliedCount = 0;
                        if ($conn && $loggedInUsername) {
                            $countQuery = "SELECT COUNT(*) AS cnt FROM applications WHERE username = '" . mysqli_real_escape_string($conn, $loggedInUsername) . "'";
                            $countResult = mysqli_query($conn, $countQuery);
                            if ($countResult && $row = mysqli_fetch_assoc($countResult)) {
                                $appliedCount = intval($row['cnt']);
                            }
                        }
                        echo $appliedCount;
                        ?>
                        </div>
                        <div style="color: #16a34a; font-size: 15px; margin-top: 4px;">&nbsp;</div>
                    </div>
                    <div class="stat-card" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #e2e8f0; padding: 24px; flex: 1;">
                        <div style="font-size: 18px; color: #444; margin-bottom: 8px;">Interviews</div>
                        <?php
                        // Show number of interviews for logged-in jobseeker
                        $interviewCount = 0;
                        if ($conn && $loggedInUsername) {
                            $countQuery = "SELECT COUNT(*) AS cnt FROM interviews WHERE username = '" . mysqli_real_escape_string($conn, $loggedInUsername) . "'";
                            $countResult = mysqli_query($conn, $countQuery);
                            if ($countResult && $row = mysqli_fetch_assoc($countResult)) {
                                $interviewCount = intval($row['cnt']);
                            }
                        }
                        echo '<div style="font-size: 32px; font-weight: bold; color: #222;">' . $interviewCount . '</div>';
                        ?>
                    </div>
                    <div class="stat-card" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #e2e8f0; padding: 24px; flex: 1;">
                        <div style="font-size: 18px; color: #444; margin-bottom: 8px;">Profile Strength</div>
                                <?php
                                // Calculate profile strength (10 fields, each 10%, email not counted)
                                $profileStrength = 0;
                                $missingFields = [];
                                $fieldLabels = [
                                    'fullName' => 'Full Name',
                                    'phone' => 'Phone',
                                    'location' => 'Location',
                                    'professionalTitle' => 'Professional Title',
                                    'skills' => 'Skills',
                                    'experience' => 'Experience',
                                    'education' => 'Education',
                                    'resume' => 'Resume',
                                    'coverLetter' => 'Cover Letter',
                                    'portfolio' => 'Portfolio'
                                ];
                                if ($conn && $loggedInUsername) {
                                    $sql = "SELECT fullName, phone, location, professionalTitle, skills, experience, education, resume, coverLetter, portfolio FROM jobseekerusers WHERE username = '" . mysqli_real_escape_string($conn, $loggedInUsername) . "' LIMIT 1";
                                    $result = mysqli_query($conn, $sql);
                                    if ($result && $row = mysqli_fetch_assoc($result)) {
                                        $fields = array_keys($fieldLabels);
                                        $filled = 0;
                                        foreach ($fields as $field) {
                                            if (!empty($row[$field])) {
                                                $filled++;
                                            } else {
                                                $missingFields[] = $fieldLabels[$field];
                                            }
                                        }
                                        $profileStrength = $filled * 10;
                                    }
                                }
                                ?>
                                <div id="profileStrengthCard" style="position:relative;">
                                    <div style="font-size: 32px; font-weight: bold; color: #222;"><?php echo $profileStrength; ?>%</div>
                                    <div style="margin-top: 8px;">
                                        <div style="height: 8px; background: #e2e8f0; border-radius: 4px;">
                                            <div style="height: 8px; width: <?php echo $profileStrength; ?>%; background: #2563eb; border-radius: 4px;"></div>
                                        </div>
                                    </div>
                                    <details style="display:block; width:100%; margin-top:24px;">
                                        <summary class="btn btn-outline" style="padding:4px 12px; font-size:15px; cursor:pointer; width:100%; text-align:left;">View profile strength details</summary>
                                        <div style="background:#fff; border-radius:8px; box-shadow:0 2px 8px #e2e8f0; margin-top:8px; min-width:220px; max-width:100%; position:absolute; left:0; width:100%; z-index:10; padding:12px; color:#222; font-size:16px;">
                                            <?php if ($profileStrength === 100) { ?>
                                                <span style="color:#16a34a; font-weight:500;">Congratulations! Your profile is complete and stands out to employers. Keep it up!</span>
                                            <?php } else { ?>
                                                <div style="margin-bottom:10px;">Your profile is not 100% because you have not filled the following fields:</div>
                                                <ul style="margin-left:18px;">
                                                    <?php foreach ($missingFields as $f) { echo '<li>' . htmlspecialchars($f) . '</li>'; } ?>
                                                </ul>
                                            <?php } ?>
                                        </div>
                                    </details>
                                </div>
                    </div>
                        <!-- Removed JS logic for enlarging Profile Strength card and inline info box -->
                    <div class="stat-card" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #e2e8f0; padding: 24px; flex: 1; position: relative;">
                        <div style="font-size: 18px; color: #444; margin-bottom: 8px;">Following Companies</div>
                        <div style="font-size: 32px; font-weight: bold; color: #222;">
                        <?php
                        // Show number of companies followed by logged-in user
                        $followedCount = 0;
                        if ($conn && $loggedInUsername) {
                            $countQuery = "SELECT COUNT(*) AS cnt FROM company_follows WHERE username = '" . mysqli_real_escape_string($conn, $loggedInUsername) . "'";
                            $countResult = mysqli_query($conn, $countQuery);
                            if ($countResult && $row = mysqli_fetch_assoc($countResult)) {
                                $followedCount = intval($row['cnt']);
                            }
                        }
                        echo $followedCount;
                        ?>
                        </div>
                        <div style="color: #222; font-size: 15px; margin-top: 4px;">
                            <details style="display:block; width:100%;">
                                <summary class="btn btn-outline" style="padding:4px 12px; font-size:15px; cursor:pointer; width:100%; text-align:left;">View followed companies</summary>
                                <div style="background:#fff; border-radius:8px; box-shadow:0 2px 8px #e2e8f0; margin-top:8px; min-width:220px; max-width:100%; position:absolute; left:0; width:100%; z-index:10;">
                                    <table style="width:100%; border-collapse:collapse;">
                                        <thead>
                                            <tr style="background:#f8fafc;">
                                                <th style="padding:8px 6px; text-align:left; color:#444; font-weight:500;">Company Name</th>
                                                <th style="padding:8px 6px; text-align:left; color:#444; font-weight:500;">Type</th>
                                                <th style="padding:8px 6px; text-align:left; color:#444; font-weight:500;">Location</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $conn = mysqli_connect('localhost', 'root', '', 'careerconnect');
                                        $loggedInUsername = $_SESSION['username'] ?? '';
                                        $rows = [];
                                        if ($conn && $loggedInUsername) {
                                            $sql = "SELECT cu.companyName, cu.type, cu.location FROM company_follows cf JOIN companyusers cu ON cf.company_id = cu.id WHERE cf.username = '" . mysqli_real_escape_string($conn, $loggedInUsername) . "'";
                                            $result = mysqli_query($conn, $sql);
                                            if ($result && mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    $rows[] = $row;
                                                }
                                            }
                                            mysqli_close($conn);
                                        }
                                        if (count($rows) > 0) {
                                            foreach ($rows as $row) {
                                                echo '<tr>';
                                                echo '<td style="padding:8px 6px;">' . htmlspecialchars($row['companyName']) . '</td>';
                                                echo '<td style="padding:8px 6px;">' . htmlspecialchars($row['type']) . '</td>';
                                                echo '<td style="padding:8px 6px;">' . htmlspecialchars($row['location']) . '</td>';
                                                echo '</tr>';
                                            }
                                        } else {
                                            echo '<tr><td colspan="3" style="text-align:center; color:#888; padding:16px;">You are not following any companies.</td></tr>';
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </details>
                        </div>
                    </div>
                </div>

                <!-- Applications Table -->
                <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #e2e8f0; padding: 24px; margin-bottom: 32px;">
                    <h2 style="font-size: 22px; font-weight: 600; margin-bottom: 16px;">Your Applications</h2>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th style="padding: 12px 8px; text-align: left; color: #444; font-weight: 500;">Position</th>
                                <th style="padding: 12px 8px; text-align: left; color: #444; font-weight: 500;">Company</th>
                                <th style="padding: 12px 8px; text-align: left; color: #444; font-weight: 500;">Date Applied</th>
                                <th style="padding: 12px 8px; text-align: left; color: #444; font-weight: 500;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Connect to DB
                            $conn = mysqli_connect("localhost", "root", "", "careerconnect");
                            $loggedInUsername = $_SESSION['username'] ?? '';
                            if ($conn && $loggedInUsername) {
                                // Get applications for logged-in user with company name
                                $appQuery = "SELECT a.*, c.companyName 
                                             FROM applications a 
                                             LEFT JOIN companyusers c ON a.company_username = c.username 
                                             WHERE a.username = '" . mysqli_real_escape_string($conn, $loggedInUsername) . "'";
                                $appResult = mysqli_query($conn, $appQuery);
                                if ($appResult && mysqli_num_rows($appResult) > 0) {
                                    while ($appRow = mysqli_fetch_assoc($appResult)) {
                                        $companyName = $appRow['companyName'] ?? $appRow['company_username'];
                                        $status = "";
                                        $dateApplied = $appRow['applied_at'];
                                        // Get job info from postjob table
                                        $jobQuery = "SELECT jobtitle, jobstatus FROM postjob WHERE companyName = '" . mysqli_real_escape_string($conn, $appRow['company_username']) . "' LIMIT 1";
                                        $jobResult = mysqli_query($conn, $jobQuery);
                                        $jobTitle = "";
                                        if ($jobResult && mysqli_num_rows($jobResult) > 0) {
                                            $jobRow = mysqli_fetch_assoc($jobResult);
                                            $jobTitle = $jobRow['jobtitle'];
                                            $status = isset($jobRow['jobstatus']) ? $jobRow['jobstatus'] : '';
                                        }
                                        echo "<tr>";
                                        echo "<td style='padding: 10px 8px;'>" . htmlspecialchars($jobTitle) . "</td>";
                                        echo "<td style='padding: 10px 8px;'>" . htmlspecialchars($companyName) . "</td>";
                                        echo "<td style='padding: 10px 8px;'>" . htmlspecialchars($dateApplied) . "</td>";
                                        echo "<td style='padding: 10px 8px;'>" . htmlspecialchars($status) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' style='text-align:center; color:#888; padding:32px;'>No applications found.</td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' style='text-align:center; color:#888; padding:32px;'>Unable to load applications.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Recent Applications Table -->
                <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #e2e8f0; padding: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h2 style="font-size: 22px; font-weight: 600;">Recent Applications</h2>
                        <a href="jsjobs.php" class="btn btn-primary" style="font-size: 12px; padding: 8px 20px; border-radius: 6px; text-decoration:none; display:inline-block;">New Application</a>
                    </div>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th style="padding: 12px 8px; text-align: left; color: #444; font-weight: 500;">Position</th>
                                <th style="padding: 12px 8px; text-align: left; color: #444; font-weight: 500;">Company</th>
                                <th style="padding: 12px 8px; text-align: left; color: #444; font-weight: 500;">Date Applied</th>
                                <th style="padding: 12px 8px; text-align: left; color: #444; font-weight: 500;">Status</th>
                                <th style="padding: 12px 8px; text-align: left; color: #444; font-weight: 500;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Recent Applications: order by most recent
                            if ($conn && $loggedInUsername) {
                                $recentQuery = "SELECT * FROM applications WHERE username = '" . mysqli_real_escape_string($conn, $loggedInUsername) . "' ORDER BY applied_at DESC";
                                $recentResult = mysqli_query($conn, $recentQuery);
                                if ($recentResult && mysqli_num_rows($recentResult) > 0) {
                                    while ($appRow = mysqli_fetch_assoc($recentResult)) {
                                        $companyUsername = $appRow['company_username'];
                                        $status = "";
                                        $dateApplied = $appRow['applied_at'];
                                        // Get job info from postjob table
                                        $jobQuery = "SELECT jobtitle, companyName, jobstatus FROM postjob WHERE companyName = '" . mysqli_real_escape_string($conn, $companyUsername) . "' LIMIT 1";
                                        $jobResult = mysqli_query($conn, $jobQuery);
                                        $jobTitle = "";
                                        $companyName = $companyUsername;
                                        if ($jobResult && mysqli_num_rows($jobResult) > 0) {
                                            $jobRow = mysqli_fetch_assoc($jobResult);
                                            $jobTitle = $jobRow['jobtitle'];
                                            $companyName = $jobRow['companyName'];
                                            $status = isset($jobRow['jobstatus']) ? $jobRow['jobstatus'] : '';
                                        }
                                        $rowId = 'recent_app_row_' . $appRow['id'];
                                        // Prepare all application info for JS
                                        $appInfo = htmlspecialchars(json_encode($appRow));
                                        echo "<tr id='" . $rowId . "'>";
                                        echo "<td style='padding: 10px 8px;'>" . htmlspecialchars($jobTitle) . "</td>";
                                        echo "<td style='padding: 10px 8px;'>" . htmlspecialchars($companyName) . "</td>";
                                        echo "<td style='padding: 10px 8px;'>" . htmlspecialchars($dateApplied) . "</td>";
                                        echo "<td style='padding: 10px 8px;'>" . htmlspecialchars($status) . "</td>";
                                        echo "<td style='padding: 10px 8px;'>"
                                            . "<button class='btn btn-outline edit-btn' data-appinfo='" . $appInfo . "' data-rowid='" . $rowId . "' style='margin-right:6px;'>Edit</button>"
                                            . "<button class='btn btn-outline view-btn' data-appinfo='" . $appInfo . "' style='margin-right:6px;'>View</button>"
                                            . "<button class='btn btn-outline delete-btn' data-appid='" . $appRow['id'] . "' data-rowid='" . $rowId . "'>Delete</button>"
                                            . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' style='text-align:center; color:#888; padding:32px;'>No recent applications found.</td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align:center; color:#888; padding:32px;'>Unable to load recent applications.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <!-- Edit Application Modal -->
                <div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:9999; align-items:center; justify-content:center;">
                    <div style="background:#fff; padding:32px; border-radius:12px; box-shadow:0 2px 8px #e2e8f0; min-width:320px; max-width:500px; text-align:left;">
                        <form id="editAppForm">
                            <div style="font-size:18px; font-weight:600; margin-bottom:12px;">Edit Application</div>
                            <input type="hidden" name="id" id="editAppId">
                            <div style="margin-bottom:10px;"><label>Full Name:</label><br><input type="text" name="fullName" id="editFullName" style="width:100%;padding:6px;"></div>
                            <div style="margin-bottom:10px;"><label>Email:</label><br><input type="email" name="email" id="editEmail" style="width:100%;padding:6px;"></div>
                            <div style="margin-bottom:10px;"><label>Phone:</label><br><input type="text" name="phone" id="editPhone" style="width:100%;padding:6px;"></div>
                            <div style="margin-bottom:10px;"><label>Location:</label><br><input type="text" name="location" id="editLocation" style="width:100%;padding:6px;"></div>
                            <div style="margin-bottom:10px;"><label>Interest:</label><br><input type="text" name="interest" id="editInterest" style="width:100%;padding:6px;"></div>
                            <button type="submit" class="btn btn-primary" style="margin-right:12px;">Save</button>
                            <button type="button" id="closeEditModal" class="btn btn-outline">Cancel</button>
                        </form>
                    </div>
                </div>
                <div id="viewModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:9999; align-items:center; justify-content:center;">
                    <div style="background:#fff; padding:32px; border-radius:12px; box-shadow:0 2px 8px #e2e8f0; min-width:320px; max-width:500px; text-align:left;">
                        <div id="viewModalContent" style="font-size:16px; margin-bottom:18px;"></div>
                        <button id="closeViewModal" class="btn btn-outline" style="float:right;">Close</button>
                    </div>
                </div>
                <!-- Delete Confirmation Modal -->
                <div id="deleteModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:9999; align-items:center; justify-content:center;">
                    <div style="background:#fff; padding:32px; border-radius:12px; box-shadow:0 2px 8px #e2e8f0; min-width:320px; text-align:center;">
                        <div style="font-size:18px; margin-bottom:18px;">Do you really want to delete the application?</div>
                        <button id="confirmDeleteYes" class="btn btn-primary" style="margin-right:16px;">Yes</button>
                        <button id="confirmDeleteNo" class="btn btn-outline">No</button>
                    </div>
                </div>
                <script>
                // Edit button logic
                let currentEditRowId = null;
                document.querySelectorAll('.edit-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        let appInfo = JSON.parse(this.getAttribute('data-appinfo'));
                        currentEditRowId = this.getAttribute('data-rowid');
                        document.getElementById('editAppId').value = appInfo.id;
                        document.getElementById('editFullName').value = appInfo.fullName || '';
                        document.getElementById('editEmail').value = appInfo.email || '';
                        document.getElementById('editPhone').value = appInfo.phone || '';
                        document.getElementById('editLocation').value = appInfo.location || '';
                        document.getElementById('editInterest').value = appInfo.interest || '';
                        document.getElementById('editModal').style.display = 'flex';
                    });
                });
                document.getElementById('closeEditModal').onclick = function() {
                    document.getElementById('editModal').style.display = 'none';
                };
                document.getElementById('editAppForm').onsubmit = function(e) {
                    e.preventDefault();
                    var formData = new FormData(document.getElementById('editAppForm'));
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'edit_application.php', true);
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            let resp;
                            try {
                                resp = JSON.parse(xhr.responseText);
                            } catch (e) {
                                alert('Server error.');
                                return;
                            }
                            if (resp.status === 'success') {
                                // Optionally update row in table (if fields shown)
                                document.getElementById('editModal').style.display = 'none';
                                alert('Application updated successfully.');
                                // Optionally reload page or update table
                            } else if (resp.errors) {
                                alert('Failed to validate: ' + resp.errors.join('\n'));
                            } else {
                                alert('Failed to update application.');
                            }
                        }
                    };
                    xhr.send(formData);
                };
                document.querySelectorAll('.view-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        let appInfo = JSON.parse(this.getAttribute('data-appinfo'));
                        let fields = [
                            { key: 'id', label: 'Application ID' },
                            { key: 'jobId', label: 'Job ID' },
                            { key: 'fullName', label: 'Full Name' },
                            { key: 'email', label: 'Email' },
                            { key: 'phone', label: 'Phone' },
                            { key: 'location', label: 'Location' },
                            { key: 'interest', label: 'Interest' }
                        ];
                        let html = '<div style="font-size:18px; font-weight:600; margin-bottom:12px;">Application Details</div>';
                        html += '<div style="padding:8px 0;">';
                        fields.forEach(f => {
                            if (appInfo[f.key] !== undefined) {
                                html += '<div style="margin-bottom:8px;"><span style="font-weight:500; color:#2563eb;">' + f.label + ':</span> ' + appInfo[f.key] + '</div>';
                            }
                        });
                        html += '</div>';
                        document.getElementById('viewModalContent').innerHTML = html;
                        document.getElementById('viewModal').style.display = 'flex';
                    });
                });
                document.getElementById('closeViewModal').onclick = function() {
                    document.getElementById('viewModal').style.display = 'none';
                };
                let deleteAppId = null;
                let deleteRowId = null;
                document.querySelectorAll('.delete-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        deleteAppId = this.getAttribute('data-appid');
                        deleteRowId = this.getAttribute('data-rowid');
                        document.getElementById('deleteModal').style.display = 'flex';
                    });
                });
                document.getElementById('confirmDeleteNo').onclick = function() {
                    document.getElementById('deleteModal').style.display = 'none';
                    deleteAppId = null;
                    deleteRowId = null;
                };
                document.getElementById('confirmDeleteYes').onclick = function() {
                    if (!deleteAppId) return;
                    // AJAX to delete_application.php
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'delete_application.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            // Remove row from table
                            var row = document.getElementById(deleteRowId);
                            if (row) row.remove();
                            document.getElementById('deleteModal').style.display = 'none';
                            // Optionally, update jobs part via JS or reload
                        }
                    };
                    xhr.send('id=' + encodeURIComponent(deleteAppId));
                };
                </script>
            </div>
        </section>
    </main>
                <!-- Footer -->
                <link rel="stylesheet" href="jsdash.css">
    <?php include 'footer.php'; ?>
</body>
</html>