<?php
// Profile Page
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "careerconnect";
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$allowedTypes = ['pdf', 'doc', 'docx'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

function validateFile($file) {
    global $allowedTypes, $maxFileSize;
    if ($file['error'] === UPLOAD_ERR_NO_FILE) return '';
    if ($file['error'] !== UPLOAD_ERR_OK) return 'File upload error.';
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) return 'Invalid file type.';
    if ($file['size'] > $maxFileSize) return 'File size exceeds 5MB.';
    return '';
}

// Get logged-in username
$loggedInUsername = $_SESSION['username'] ?? '';
$email = '';
$fullName = '';
$phone = '';
$location = '';
$professionalTitle = '';
$skills = '';
$experience = '';
$education = '';
if ($loggedInUsername) {
    // Get profile data from jobseekerusers
    $sql = "SELECT * FROM jobseekerusers WHERE username = '" . mysqli_real_escape_string($conn, $loggedInUsername) . "' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $email = $row['email'] ?? '';
        $fullName = $row['fullName'] ?? '';
        $phone = $row['phone'] ?? '';
        $location = $row['location'] ?? '';
        $professionalTitle = $row['professionalTitle'] ?? '';
        $skills = $row['skills'] ?? '';
        $experience = $row['experience'] ?? '';
        $education = $row['education'] ?? '';
        $resumePath = $row['resume'] ?? '';
        $coverLetterPath = $row['coverLetter'] ?? '';
        $portfolioPath = $row['portfolio'] ?? '';
    } else {
        // Fallback: get email from registeredusers
        $sql = "SELECT email FROM registeredusers WHERE username = '" . mysqli_real_escape_string($conn, $loggedInUsername) . "' LIMIT 1";
        $result = mysqli_query($conn, $sql);
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $email = $row['email'];
        }
        $resumePath = '';
        $coverLetterPath = '';
        $portfolioPath = '';
    }
}

// Handle Save Changes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['saveProfile'])) {
    // Collect and validate fields
    $fullName = mysqli_real_escape_string($conn, trim($_POST['profileName'] ?? ''));
    $profileEmail = mysqli_real_escape_string($conn, trim($_POST['profileEmail'] ?? ''));
    $phone = mysqli_real_escape_string($conn, trim($_POST['profilePhone'] ?? ''));
    $location = mysqli_real_escape_string($conn, trim($_POST['profileLocation'] ?? ''));
    $professionalTitle = mysqli_real_escape_string($conn, trim($_POST['profileTitle'] ?? ''));
    $skills = mysqli_real_escape_string($conn, trim($_POST['profileSkills'] ?? ''));
    $experience = mysqli_real_escape_string($conn, trim($_POST['profileExperience'] ?? ''));
    $education = mysqli_real_escape_string($conn, trim($_POST['profileEducation'] ?? ''));

    // File upload handling
    function handleUpload($file, $field, &$errors) {
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['pdf', 'doc', 'docx'];
            if (!in_array($ext, $allowed)) {
                $errors[] = ucfirst($field) . ': Invalid file type.';
                return '';
            }
            if ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = ucfirst($field) . ': File size exceeds 5MB.';
                return '';
            }
            $uploadDir = 'uploads/jobseeker/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $filename = $field . '_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                return mysqli_real_escape_string($GLOBALS['conn'], $targetPath);
            }
        }
        return '';
    }
    $resume = handleUpload($_FILES['resume'] ?? null, 'resume', $errors);
    $coverLetter = handleUpload($_FILES['coverLetter'] ?? null, 'coverLetter', $errors);
    $portfolio = handleUpload($_FILES['portfolio'] ?? null, 'portfolio', $errors);

    // Additional server-side file size validation
    if (isset($_FILES['resume']) && $_FILES['resume']['size'] > $maxFileSize) {
        $errors[] = 'Resume file size must be less than 5MB.';
    }
    if (isset($_FILES['coverLetter']) && $_FILES['coverLetter']['size'] > $maxFileSize) {
        $errors[] = 'Cover Letter file size must be less than 5MB.';
    }
    if (isset($_FILES['portfolio']) && $_FILES['portfolio']['size'] > $maxFileSize) {
        $errors[] = 'Portfolio file size must be less than 5MB.';
    }

    $errors = [];
    // Full Name validation
    if (empty($fullName)) {
        $errors['profileName'] = "Full Name is required.";
    } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $fullName)) {
        $errors['profileName'] = "Only letters and white space allowed in Full Name.";
    }
    // Email validation
    if (empty($profileEmail)) {
        $errors['profileEmail'] = "Email is required.";
    } elseif (!filter_var($profileEmail, FILTER_VALIDATE_EMAIL)) {
        $errors['profileEmail'] = "Invalid email format.";
    }
    // Phone validation
    if (empty($phone)) {
        $errors['profilePhone'] = "Phone is required.";
    } elseif (!preg_match("/^01[0-9]{9}$/", $phone)) {
        $errors['profilePhone'] = "Phone must be a valid Bangladeshi number (e.g. 01XXXXXXXXX).";
    }
    // Location validation
    if (empty($location)) {
        $errors['profileLocation'] = "Location is required.";
    } elseif (!preg_match("/^[a-zA-Z0-9,.' -]+$/", $location)) {
        $errors['profileLocation'] = "Location contains invalid characters.";
    }

    if (empty($errors)) {
        // Check if user exists in jobseekerusers
        $sql = "SELECT id FROM jobseekerusers WHERE username = '" . mysqli_real_escape_string($conn, $loggedInUsername) . "' LIMIT 1";
        $result = mysqli_query($conn, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            // Update
            $sql = "UPDATE jobseekerusers SET email='$profileEmail', fullName='$fullName', phone='$phone', location='$location', professionalTitle='$professionalTitle', skills='$skills', experience='$experience', education='$education'";
            if ($resume) $sql .= ", resume='$resume'";
            if ($coverLetter) $sql .= ", coverLetter='$coverLetter'";
            if ($portfolio) $sql .= ", portfolio='$portfolio'";
            $sql .= " WHERE username='$loggedInUsername'";
            mysqli_query($conn, $sql);
        } else {
            // Insert
            $sql = "INSERT INTO jobseekerusers (username, email, fullName, phone, location, professionalTitle, skills, experience, education, resume, coverLetter, portfolio) VALUES ('$loggedInUsername', '$profileEmail', '$fullName', '$phone', '$location', '$professionalTitle', '$skills', '$experience', '$education', '$resume', '$coverLetter', '$portfolio')";
            mysqli_query($conn, $sql);
        }
        $successMsg = "Profile saved successfully.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareerConnect | Profile</title>
    <link rel="stylesheet" href="Try_Rakib.css">
    <link rel="stylesheet" href="jsprofile.css">
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
                        <li><a href="jsdash.php" class="nav-link">Dashboard</a></li>
                        <li><a href="jsjobs.php" class="nav-link">Jobs</a></li>
                        <li><a href="jscompany.php" class="nav-link">Companies</a></li>
                        <li><a href="jsinterview.php" class="nav-link">Interviews</a></li>
                        <li><a href="jsprofile.php" class="nav-link active">Profile</a></li>
                    </ul>
                </nav>
                <div class="user-actions">
    <span>Welcome, <strong><?php echo htmlspecialchars($loggedInUsername); ?></strong></span>
    <a href="Login_Try.php" class="btn btn-outline">Logout</a>
</div>
            </div>
        </div>
    </header>
    
    <main>
        <div class="profile-container">
            <?php if (!empty($errors) && is_array($errors) && count(array_diff_key($errors, ['profileName'=>1,'profileEmail'=>1,'profilePhone'=>1,'profileLocation'=>1]))): ?>
                <div class="error-messages">
                    <strong>Please fix the following errors:</strong>
                    <ul style="margin:10px 0 0 20px;">
                    <?php foreach ($errors as $key => $err): ?>
                        <?php if (!in_array($key, ['profileName','profileEmail','profilePhone','profileLocation'])): ?>
                            <li><?php echo htmlspecialchars($err); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($successMsg)): ?>
                <div class="success-message">
                    <strong>Success!</strong> <?php echo htmlspecialchars($successMsg); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data" id="profileForm">
                <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
                <input type="hidden" name="saveProfile" value="1">
                
                <!-- Profile Header Card -->
                <div class="profile-header-card">
                    <div class="profile-header-content">
                        <div class="profile-avatar-large">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="profile-info-main">
                            <h1 id="profileNameDisplay">
                                <?php echo !empty($fullName) ? htmlspecialchars($fullName) : "Complete Your Profile"; ?>
                            </h1>
                            <div class="title" id="profileTitleDisplay">
                                <?php
                                    $titleText = !empty($professionalTitle) ? htmlspecialchars($professionalTitle) : "Professional Title";
                                    $locationText = !empty($location) ? htmlspecialchars($location) : "Location";
                                    echo $titleText . " • " . $locationText;
                                ?>
                            </div>
                            <div class="profile-stats">
                                <div class="stat-item">
                                    
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value"><?php echo !empty($skills) ? substr_count($skills, ',') + 1 : "0"; ?></span>
                                    <span class="stat-label">Skills</span>
                                </div>
                                <div class="stat-item">
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Content Grid -->
                <div class="profile-content">
                    <!-- Personal Information -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-user-circle"></i>
                            Personal Information
                        </h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="profileNameInput">Full Name *</label>
                                <input type="text" name="profileName" id="profileNameInput" class="form-control" 
                                       value="<?php echo isset($fullName) ? htmlspecialchars($fullName) : ''; ?>" 
                                       placeholder="Enter your full name" required>
                                <?php if (!empty($errors['profileName'])): ?>
                                    <div class="field-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?php echo htmlspecialchars($errors['profileName']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="profileEmail">Email Address *</label>
                                <input type="email" name="profileEmail" id="profileEmail" class="form-control" 
                                       value="<?php echo htmlspecialchars($email); ?>" 
                                       placeholder="your.email@example.com" required>
                                <?php if (!empty($errors['profileEmail'])): ?>
                                    <div class="field-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?php echo htmlspecialchars($errors['profileEmail']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="profilePhone">Phone Number *</label>
                                <input type="tel" name="profilePhone" id="profilePhone" class="form-control" 
                                       value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" 
                                       placeholder="01XXXXXXXXX" required>
                                <?php if (!empty($errors['profilePhone'])): ?>
                                    <div class="field-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?php echo htmlspecialchars($errors['profilePhone']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="profileLocation">Location *</label>
                                <input type="text" name="profileLocation" id="profileLocation" class="form-control" 
                                       value="<?php echo isset($location) ? htmlspecialchars($location) : ''; ?>" 
                                       placeholder="Your city and country" required>
                                <?php if (!empty($errors['profileLocation'])): ?>
                                    <div class="field-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?php echo htmlspecialchars($errors['profileLocation']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Professional Information -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-briefcase"></i>
                            Professional Information
                        </h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="profileTitleInput">Professional Title</label>
                                <input type="text" name="profileTitle" id="profileTitleInput" class="form-control" 
                                       value="<?php echo isset($professionalTitle) ? htmlspecialchars($professionalTitle) : ''; ?>" 
                                       placeholder="e.g. Senior Web Developer">
                            </div>
                            
                            <div class="form-group">
                                <label for="profileSkills">Skills & Expertise</label>
                                <input type="text" name="profileSkills" id="profileSkills" class="form-control" 
                                       value="<?php echo isset($skills) ? htmlspecialchars($skills) : ''; ?>" 
                                       placeholder="e.g. PHP, JavaScript, MySQL, React">
                            </div>
                            
                            <div class="form-group">
                                <label for="profileExperience">Years of Experience</label>
                                <select name="profileExperience" id="profileExperience" class="form-control">
                                    <option value="">Select your experience level</option>
                                    <option value="1-2 years" <?php if(isset($experience) && $experience=="1-2 years") echo "selected"; ?>>1-2 years</option>
                                    <option value="3-5 years" <?php if(isset($experience) && $experience=="3-5 years") echo "selected"; ?>>3-5 years</option>
                                    <option value="5+ years" <?php if(isset($experience) && $experience=="5+ years") echo "selected"; ?>>5+ years</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="profileEducation">Education Level</label>
                                <select name="profileEducation" id="profileEducation" class="form-control">
                                    <option value="">Select your highest education</option>
                                    <option value="Bachelor's Degree" <?php if(isset($education) && $education=="Bachelor's Degree") echo "selected"; ?>>Bachelor's Degree</option>
                                    <option value="Master's Degree" <?php if(isset($education) && $education=="Master's Degree") echo "selected"; ?>>Master's Degree</option>
                                    <option value="PhD" <?php if(isset($education) && $education=="PhD") echo "selected"; ?>>PhD</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Documents Section -->
                    <div class="form-section full-width">
                        <h3 class="section-title">
                            <i class="fas fa-file-alt"></i>
                            Resume & Documents
                        </h3>
                        <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
                            
                            <!-- Resume Upload -->
                            <div class="form-group">
                                <label>Resume</label>
                                <div class="file-upload-area" onclick="document.getElementById('resumeInput').click()">
                                    <i class="fas fa-file-pdf"></i>
                                    <h4>Upload Your Resume</h4>
                                    <p>PDF, DOC, DOCX (max 5MB)</p>
                                    <input type="file" name="resume" id="resumeInput" class="file-input" accept=".pdf,.doc,.docx">
                                </div>
                                <?php if (!empty($resumePath)): ?>
                                    <div class="existing-files">
                                        <div class="file-item">
                                            <i class="fas fa-file-pdf"></i>
                                            <a href="<?php echo htmlspecialchars($resumePath); ?>" target="_blank">Download Current Resume</a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Cover Letter Upload -->
                            <div class="form-group">
                                <label>Cover Letter</label>
                                <div class="file-upload-area" onclick="document.getElementById('coverLetterInput').click()">
                                    <i class="fas fa-file-word"></i>
                                    <h4>Upload Cover Letter</h4>
                                    <p>PDF, DOC, DOCX (max 5MB)</p>
                                    <input type="file" name="coverLetter" id="coverLetterInput" class="file-input" accept=".pdf,.doc,.docx">
                                </div>
                                <?php if (!empty($coverLetterPath)): ?>
                                    <div class="existing-files">
                                        <div class="file-item">
                                            <i class="fas fa-file-word"></i>
                                            <a href="<?php echo htmlspecialchars($coverLetterPath); ?>" target="_blank">Download Current Cover Letter</a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Portfolio Upload -->
                            <div class="form-group">
                                <label>Portfolio</label>
                                <div class="file-upload-area" onclick="document.getElementById('portfolioInput').click()">
                                    <i class="fas fa-folder-open"></i>
                                    <h4>Upload Portfolio</h4>
                                    <p>PDF, DOC, DOCX (max 5MB)</p>
                                    <input type="file" name="portfolio" id="portfolioInput" class="file-input" accept=".pdf,.doc,.docx">
                                </div>
                                <?php if (!empty($portfolioPath)): ?>
                                    <div class="existing-files">
                                        <div class="file-item">
                                            <i class="fas fa-folder-open"></i>
                                            <a href="<?php echo htmlspecialchars($portfolioPath); ?>" target="_blank">Download Current Portfolio</a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="button" class="btn-cancel" onclick="window.history.back()">Cancel</button>
                    <button type="submit" class="btn-save" id="saveProfileBtn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </main>
    
   <!-- Footer -->
                <link rel="stylesheet" href="jsdash.css">
    <?php include 'footer.php'; ?>

    <script>
    // Client-side file size validation for 5MB
    function validateFileSize(inputId, label) {
        var input = document.getElementById(inputId);
        input.addEventListener('change', function(e) {
            if (e.target.files[0] && e.target.files[0].size > 5 * 1024 * 1024) {
                alert(label + ' file size must be less than 5MB.');
                e.target.value = '';
            }
        });
    }
    validateFileSize('resumeInput', 'Resume');
    validateFileSize('coverLetterInput', 'Cover Letter');
    validateFileSize('portfolioInput', 'Portfolio');

    // Real-time profile preview updates
    document.getElementById('profileNameInput').addEventListener('input', function(e) {
        document.getElementById('profileNameDisplay').textContent = e.target.value || 'Complete Your Profile';
    });

    document.getElementById('profileTitleInput').addEventListener('input', function(e) {
        updateProfileTitle();
    });

    document.getElementById('profileLocation').addEventListener('input', function(e) {
        updateProfileTitle();
    });

    function updateProfileTitle() {
        var title = document.getElementById('profileTitleInput').value || 'Professional Title';
        var location = document.getElementById('profileLocation').value || 'Location';
        document.getElementById('profileTitleDisplay').textContent = title + ' • ' + location;
    }
    </script>
</body>
</html>