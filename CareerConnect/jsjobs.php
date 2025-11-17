<?php
// Start session at the top
session_start();
$loggedInUsername = $_SESSION['username'] ?? '';

// Handle AJAX application form submission
if (isset($_POST['applyJobAjax']) && $_POST['applyJobAjax'] == '1') {
    $response = ['success' => false, 'errors' => []];
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "careerconnect";
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    $jobId = $_POST['jobId'] ?? '';
    $fullName = trim($_POST['applicantName'] ?? '');
    $email = trim($_POST['applicantEmail'] ?? '');
    $phone = trim($_POST['applicantPhone'] ?? '');
    $location = trim($_POST['applicantLocation'] ?? '');
    $interest = trim($_POST['applicantInterest'] ?? '');
    $username = $_SESSION['username'] ?? '';
    $company_username = '';
    if ($jobId) {
        $jobRes = mysqli_query($conn, "SELECT companyName FROM postjob WHERE id = '" . mysqli_real_escape_string($conn, $jobId) . "'");
        if ($jobRes && $jobRow = mysqli_fetch_assoc($jobRes)) {
            $company_username = $jobRow['companyName'];
        }
    }
    // Resume validation
    $resumePath = '';
    if (isset($_FILES['applicantResume']) && $_FILES['applicantResume']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['applicantResume'];
        $allowedTypes = ['pdf', 'doc', 'docx'];
        $maxFileSize = 5 * 1024 * 1024;
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedTypes)) {
            $response['errors']['resume'] = "Invalid file type.";
        } elseif ($file['size'] > $maxFileSize) {
            $response['errors']['resume'] = "File size exceeds 5MB.";
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $response['errors']['resume'] = "File upload error.";
        } else {
            $uploadDir = 'uploads/jobseeker/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $filename = 'application_resume_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $resumePath = $targetPath;
            } else {
                $response['errors']['resume'] = "Failed to upload file.";
            }
        }
    }
    // Validation
    if (empty($fullName)) {
        $response['errors']['name'] = "Name is required.";
    } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $fullName)) {
        $response['errors']['name'] = "Only letters and white space allowed.";
    }
    if (empty($email)) {
        $response['errors']['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['errors']['email'] = "Invalid email format.";
    }
    if (empty($phone)) {
        $response['errors']['phone'] = "Phone is required.";
    } elseif (!preg_match("/^01[0-9]{9}$/", $phone)) {
        $response['errors']['phone'] = "Phone must be a valid Bangladeshi number (e.g. 01XXXXXXXXX).";
    }
    if (empty($location)) {
        $response['errors']['location'] = "Location is required.";
    } elseif (!preg_match("/^[a-zA-Z0-9,.' -]+$/", $location)) {
        $response['errors']['location'] = "Location contains invalid characters.";
    }
    if (empty($response['errors'])) {
        // Create applications table if not exists
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
        // Insert application
        $sql = "INSERT INTO applications (jobId, fullName, email, phone, location, interest, resume, username, company_username) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "issssssss", $jobId, $fullName, $email, $phone, $location, $interest, $resumePath, $username, $company_username);
        mysqli_stmt_execute($stmt);
        $response['success'] = true;
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Handle AJAX withdraw
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdrawJobId'])) {
    $username = $_SESSION['username'] ?? '';
    $jobId = $_POST['withdrawJobId'];
    $conn = mysqli_connect('localhost', 'root', '', 'careerconnect');
    $success = false;
    if ($username && $jobId) {
        mysqli_query($conn, "DELETE FROM applications WHERE jobId = '" . mysqli_real_escape_string($conn, $jobId) . "' AND username = '" . mysqli_real_escape_string($conn, $username) . "'");
        $success = true;
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => $success]);
    exit;
}
// Jobs Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareerConnect | Jobs</title>
    <link rel="stylesheet" href="Try_Rakib.css">
    <link rel="stylesheet" href="companycard.css">
    <link rel="stylesheet" href="jsjobs.css">
</head>
<body>
    <div id="applyModal" class="modal">
        <div class="modal-content">
            <h2>Apply for Job</h2>
            <?php if (!empty($errors)): ?>
                <div class="error-messages" style="color: #d8000c; background: #ffd2d2; border-radius: 6px; padding: 12px; margin-bottom: 18px;">
                    <ul style="margin:0; padding-left:18px;">
                    <?php foreach ($errors as $key => $err): ?>
                        <?php if (!in_array($key, ['name','email','phone','location','resume'])): ?>
                            <li><?php echo htmlspecialchars($err); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if (!empty($successMsg)): ?>
                <div class="success-message" style="color: #155724; background: #d4edda; border-radius: 6px; padding: 12px; margin-bottom: 18px;">
                    <?php echo htmlspecialchars($successMsg); ?>
                </div>
            <?php endif; ?>
            <form enctype="multipart/form-data" id="applyForm">
                <input type="hidden" name="jobId" id="modalJobId" value="">
                <div class="form-group">
                    <label for="applicantName">Full Name *</label>
                    <input type="text" name="applicantName" id="applicantName" required value="">
                    <div class="field-error" id="errorName"></div>
                </div>
                <div class="form-group">
                    <label for="applicantEmail">Email *</label>
                    <input type="email" name="applicantEmail" id="applicantEmail" required value="">
                    <div class="field-error" id="errorEmail"></div>
                </div>
                <div class="form-group">
                    <label for="applicantPhone">Phone *</label>
                    <input type="tel" name="applicantPhone" id="applicantPhone" required value="">
                    <div class="field-error" id="errorPhone"></div>
                </div>
                <div class="form-group">
                    <label for="applicantLocation">Location *</label>
                    <input type="text" name="applicantLocation" id="applicantLocation" required value="">
                    <div class="field-error" id="errorLocation"></div>
                </div>
                <div class="form-group">
                    <label for="applicantInterest">Why you are interested for this job</label>
                    <textarea name="applicantInterest" id="applicantInterest"></textarea>
                </div>
                <div class="form-group">
                    <label for="applicantResume">Resume (PDF/DOC/DOCX, max 5MB)</label>
                    <input type="file" name="applicantResume" id="applicantResume" accept=".pdf,.doc,.docx">
                    <div class="field-error" id="errorResume"></div>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary" id="doneBtn">Done</button>
                    <button type="button" class="btn btn-outline" id="backBtn">Back</button>
                </div>
            </form>
        </div>
    </div>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-briefcase"></i>
                    <span>CareerConnect</span>
                </div>
                <nav>
                     <ul>
                        <li><a href="jsdash.php" class="nav-link">Dashboard</a></li>
                        <li><a href="jsjobs.php" class="nav-link active">Jobs</a></li>
                        <li><a href="jscompany.php" class="nav-link">Companies</a></li>
                        <li><a href="jsinterview.php" class="nav-link">Interviews</a></li>
                        <li><a href="jsprofile.php" class="nav-link">Profile</a></li>
                    </ul>
                </nav>
                <div>
                    <span>Welcome, <strong><?php echo htmlspecialchars($loggedInUsername);?></strong></span>
                    <a href="Login_Try.php" class="btn btn-outline"> Logout</a>
                </div>
            </div>
        </div>
    </header>
    <main>
        <section class="dashboard active" id="jobs">
            <div class="container">
                <div class="dashboard-header">
                    <h1 class="dashboard-title">Job Listings</h1>
                    <div class="search-box">
                        <form method="get" action="jsjobs.php" id="filterForm" style="display:flex;align-items:center;gap:8px;">
                            <input type="text" class="search-input" id="jobsSearch" name="search" placeholder="Search jobs..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button type="submit" style="display:none;"></button>
                            <i class="fas fa-search search-icon"></i>
                        </form>
                    </div>
                </div>
                <?php
                // Fetch unique dropdown values
                $conn = mysqli_connect("localhost", "root", "", "careerconnect");
                $categories = $locations = $types = [];
                if ($conn) {
                    $catRes = mysqli_query($conn, "SELECT DISTINCT jobCategory FROM postjob");
                    while ($row = mysqli_fetch_assoc($catRes)) {
                        $categories[] = $row['jobCategory'];
                    }
                    $locRes = mysqli_query($conn, "SELECT DISTINCT jobLocation FROM postjob");
                    while ($row = mysqli_fetch_assoc($locRes)) {
                        $locations[] = $row['jobLocation'];
                    }
                    $typeRes = mysqli_query($conn, "SELECT DISTINCT jobType FROM postjob");
                    while ($row = mysqli_fetch_assoc($typeRes)) {
                        $types[] = $row['jobType'];
                    }
                }
                ?>
                <div class="job-filters">
                    <form method="get" action="jsjobs.php" id="dropdownForm" style="display:flex;gap:20px;align-items:flex-end;flex-wrap:wrap;">
                        <div class="filter-group">
                            <label>Industry</label>
                            <select class="filter-select" name="industry" onchange="document.getElementById('dropdownForm').submit();">
                                <option value="">All Industries</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php if(isset($_GET['industry']) && $_GET['industry'] == $cat) echo 'selected'; ?>><?php echo htmlspecialchars($cat); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Location</label>
                            <select class="filter-select" name="location" onchange="document.getElementById('dropdownForm').submit();">
                                <option value="">Any Location</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?php echo htmlspecialchars($loc); ?>" <?php if(isset($_GET['location']) && $_GET['location'] == $loc) echo 'selected'; ?>><?php echo htmlspecialchars($loc); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Job Type</label>
                            <select class="filter-select" name="jobtype" onchange="document.getElementById('dropdownForm').submit();">
                                <option value="">Any Type</option>
                                <?php foreach ($types as $type): ?>
                                    <option value="<?php echo htmlspecialchars($type); ?>" <?php if(isset($_GET['jobtype']) && $_GET['jobtype'] == $type) echo 'selected'; ?>><?php echo htmlspecialchars($type); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php
                        // Preserve search term in dropdown form
                        if (isset($_GET['search'])) {
                            echo '<input type="hidden" name="search" value="' . htmlspecialchars($_GET['search']) . '" />';
                        }
                        ?>
                    </form>
                </div>
                <div class="job-listings" id="jobsListingsContainer">
                <?php
                // Database connection
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "careerconnect";

                $conn = mysqli_connect($servername, $username, $password, $dbname);
                if (!$conn) {
                    echo '<div style="color:red;">Database connection failed.</div>';
                } else {
                    $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
                    $industry = isset($_GET['industry']) ? trim($_GET['industry']) : '';
                    $location = isset($_GET['location']) ? trim($_GET['location']) : '';
                    $jobtype = isset($_GET['jobtype']) ? trim($_GET['jobtype']) : '';
                    $where = [];
                    if ($searchTerm !== '') {
                        $where[] = "p.jobTitle LIKE '%" . mysqli_real_escape_string($conn, $searchTerm) . "%'";
                    }
                    if ($industry !== '') {
                        $where[] = "p.jobCategory = '" . mysqli_real_escape_string($conn, $industry) . "'";
                    }
                    if ($location !== '') {
                        $where[] = "p.jobLocation = '" . mysqli_real_escape_string($conn, $location) . "'";
                    }
                    if ($jobtype !== '') {
                        $where[] = "p.jobType = '" . mysqli_real_escape_string($conn, $jobtype) . "'";
                    }
                    // --- JOIN to get company display name
                    $sql = "SELECT p.*, cu.companyName AS company_display_name 
                            FROM postjob p 
                            LEFT JOIN companyusers cu ON p.companyName = cu.username";
                    if (count($where) > 0) {
                        $sql .= " WHERE " . implode(" AND ", $where);
                    }
                    $sql .= " ORDER BY p.reg_date DESC";
                    $result = mysqli_query($conn, $sql);
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Check if user has already applied for this job
                            $currentUser = $_SESSION['username'] ?? '';
                            $appliedRes = mysqli_query($conn, "SELECT id FROM applications WHERE jobId = '" . $row['id'] . "' AND username = '" . mysqli_real_escape_string($conn, $currentUser) . "'");
                            $hasApplied = ($appliedRes && mysqli_num_rows($appliedRes) > 0);

                            echo '<div class="job-card">';
                            echo '<div class="job-header">';
                            echo '<div class="job-title-section">';
                            echo '<h2 class="job-title">' . htmlspecialchars($row['jobTitle']) . '</h2>';
                            if (!empty($row['company_display_name'])) {
                                echo '<p class="job-company">' . htmlspecialchars($row['company_display_name']) . '</p>';
                            } else {
                                echo '<p class="job-company">' . htmlspecialchars($row['companyName']) . '</p>';
                            }
                            echo '</div>';
                            echo '<div class="job-badge">' . htmlspecialchars($row['jobType']) . '</div>';
                            echo '</div>';

                            echo '<p class="job-description">' . nl2br(htmlspecialchars(substr($row['jobDescription'], 0, 200))) . (strlen($row['jobDescription']) > 200 ? '...' : '') . '</p>';

                            echo '<div class="job-details">';
                            echo '<div class="detail-item">';
                            echo '<i class="fas fa-map-marker-alt"></i>';
                            echo '<span class="detail-label">Location:</span>';
                            echo '<span>' . htmlspecialchars($row['jobLocation']) . '</span>';
                            echo '</div>';

                            echo '<div class="detail-item">';
                            echo '<i class="fas fa-briefcase"></i>';
                            echo '<span class="detail-label">Category:</span>';
                            echo '<span>' . htmlspecialchars($row['jobCategory']) . '</span>';
                            echo '</div>';

                            echo '<div class="detail-item">';
                            echo '<i class="fas fa-clock"></i>';
                            echo '<span class="detail-label">Deadline:</span>';
                            echo '<span>' . htmlspecialchars($row['applicationDeadline']) . '</span>';
                            echo '</div>';
                            echo '</div>';

                            echo '<div class="job-footer">';
                            echo '<span class="post-date">Posted on: ' . date('M j, Y', strtotime($row['reg_date'])) . '</span>';

                            if ($hasApplied) {
                                echo '<button class="apply-btn applied-btn" data-jobid="' . $row['id'] . '" onclick="showWithdrawModal(' . $row['id'] . ', this)">Applied</button>';
                            } else {
                                echo '<button class="apply-btn" data-jobid="' . $row['id'] . '" onclick="openApplyModal(' . $row['id'] . ', this)">Apply now</button>';
                            }
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="no-jobs">';
                        echo '<i class="fas fa-briefcase"></i>';
                        echo '<h3>No jobs found</h3>';
                        echo '<p>Try adjusting your search filters or check back later for new opportunities.</p>';
                        echo '</div>';
                    }
                    mysqli_close($conn);
                }
                ?>
                </div>
            </div>
        </section>
    </main>
</body>
<!-- Footer -->
                <link rel="stylesheet" href="jsdash.css">
    <?php include 'footer.php'; ?>
<script>
document.getElementById('applyModal').style.display = 'none';

function openApplyModal(jobId, btn) {
    document.getElementById('applyModal').style.display = 'flex';
    document.getElementById('modalJobId').value = jobId;
    ['errorName','errorEmail','errorPhone','errorLocation','errorResume'].forEach(id => document.getElementById(id).textContent = '');
    fetch('get_job_details.php?jobId=' + jobId)
        .then(res => res.json())
        .then(data => {
            document.getElementById('applicantName').value = data.fullName || '';
            document.getElementById('applicantEmail').value = data.email || '';
            document.getElementById('applicantPhone').value = data.phone || '';
            document.getElementById('applicantLocation').value = data.location || '';
        });
}

document.getElementById('backBtn').onclick = function() {
    document.getElementById('applyModal').style.display = 'none';
};

window.onclick = function(event) {
    var modal = document.getElementById('applyModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

document.getElementById('applyForm').onsubmit = function(e) {
    e.preventDefault();
    var form = document.getElementById('applyForm');
    var resumeInput = document.getElementById('applicantResume');
    var file = resumeInput.files[0];
    var maxFileSize = 5 * 1024 * 1024;
    ['errorName','errorEmail','errorPhone','errorLocation','errorResume'].forEach(id => document.getElementById(id).textContent = '');
    if (file && file.size > maxFileSize) {
        document.getElementById('errorResume').textContent = 'File size exceeds 5MB.';
        return;
    }
    var formData = new FormData(form);
    formData.append('applyJobAjax', '1');
    fetch('jsjobs.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('applyModal').style.display = 'none';
            var jobId = document.getElementById('modalJobId').value;
            var btns = document.querySelectorAll('.apply-btn[data-jobid="'+jobId+'"]');
            btns.forEach(function(btn){
                btn.textContent = 'Applied';
                btn.classList.add('applied-btn');
                btn.onclick = function(){ showWithdrawModal(jobId, btn); };
            });
        } else {
            if (data.errors) {
                if (data.errors.name) document.getElementById('errorName').textContent = data.errors.name;
                if (data.errors.email) document.getElementById('errorEmail').textContent = data.errors.email;
                if (data.errors.phone) document.getElementById('errorPhone').textContent = data.errors.phone;
                if (data.errors.location) document.getElementById('errorLocation').textContent = data.errors.location;
                if (data.errors.resume) document.getElementById('errorResume').textContent = data.errors.resume;
            }
        }
    });
};

function showWithdrawModal(jobId, btn) {
    let modal = document.createElement('div');
    modal.className = 'modal';
    modal.style.display = 'flex';
    modal.innerHTML = `<div class="modal-content"><h2>Withdraw Application</h2><p>Do you really want to withdraw the application?</p><div class="modal-actions"><button class="btn btn-primary" id="withdrawYes">Yes</button><button class="btn btn-outline" id="withdrawNo">No</button></div></div>`;
    document.body.appendChild(modal);
    document.getElementById('withdrawYes').onclick = function() {
        var fd = new FormData();
        fd.append('withdrawJobId', jobId);
        fetch('jsjobs.php', {
            method: 'POST',
            body: fd
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                btn.textContent = 'Apply now';
                btn.classList.remove('applied-btn');
                btn.onclick = function(){ openApplyModal(jobId, btn); };
            }
            document.body.removeChild(modal);
        });
    };
    document.getElementById('withdrawNo').onclick = function() {
        document.body.removeChild(modal);
    };
}
</script>
</html>