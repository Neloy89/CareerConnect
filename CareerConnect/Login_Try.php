<?php
session_start();
$username = "";
$usernameErr = $passwordErr = $loginErr = "";
if (isset($_COOKIE['remembered_username'])) {
    $username = $_COOKIE['remembered_username'];
}
$page = 'home';
if (isset($_GET['page'])) {
    $page = $_GET['page'];
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    if (empty($_POST['username'])) {
        $usernameErr = "Username is required";
    } else {
        $username = trim($_POST['username']);
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            $usernameErr = "Only letters, numbers, underscores (3-20 chars) allowed";
        }
    }
    if (empty($_POST['password'])) {
        $passwordErr = "Password is required";
    }
    if (!$usernameErr && !$passwordErr) {
        $servername = "localhost";
        $dbusername = "root";
        $dbpassword = "";
        $dbname = "careerconnect";
        $conn = mysqli_connect($servername, $dbusername, $dbpassword, $dbname);
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        $createTableSql = "CREATE TABLE IF NOT EXISTS registeredUsers (
            username VARCHAR(30) NOT NULL PRIMARY KEY,
            email VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL,
            pet_name VARCHAR(50) NOT NULL,
            role VARCHAR(20) NOT NULL,
            reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        mysqli_query($conn, $createTableSql);
        $usernameEsc = mysqli_real_escape_string($conn, $username);
        $sql = "SELECT username, password, role FROM registeredUsers WHERE username='$usernameEsc'";
        $result = mysqli_query($conn, $sql);
        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($_POST['password'], $row['password'])) {
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                if (isset($_POST['remember'])) {
                    setcookie('remembered_username', $row['username'], time()+60*60*24*30, "/");
                } else {
                    setcookie('remembered_username', '', time()-3600, "/");
                }
                if (strtolower($row['role']) === 'admin') {
                    header('Location: admindashboard.php');
                } elseif (strtolower($row['role']) === 'company') {
                    header('Location: dashboard.php');
                } elseif (strtolower($row['role']) === 'job_seeker') {
                    header('Location: jsdash.php');
                } else {
                    $loginErr = "Unknown user role.";
                }
                mysqli_close($conn);
                exit;
            } else {
                $loginErr = "Invalid username or password";
            }
        } else {
            $loginErr = "Invalid username or password";
        }
        mysqli_close($conn);
    }
}
$regUsernameErr = $regPasswordErr = $regConfirmPasswordErr = $regEmailErr = $regPetNameErr = $regSuccess = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    if (empty($_POST['reg_username'])) {
        $regUsernameErr = "Username is required";
    } else {
        $regUsername = trim($_POST['reg_username']);
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $regUsername)) {
            $regUsernameErr = "Only letters, numbers, underscores (3-20 chars) allowed";
        } else {
            $servername = "localhost";
            $dbusername = "root";
            $dbpassword = "";
            $dbname = "careerconnect";
            $conn = mysqli_connect($servername, $dbusername, $dbpassword, $dbname);
            if ($conn) {
                $createTableSql = "CREATE TABLE IF NOT EXISTS registeredUsers (
                    username VARCHAR(30) NOT NULL PRIMARY KEY,
                    email VARCHAR(50) NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    pet_name VARCHAR(50) NOT NULL,
                    role VARCHAR(20) NOT NULL,
                    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                mysqli_query($conn, $createTableSql);
                $regUsernameEsc = mysqli_real_escape_string($conn, $regUsername);
                $checkSql = "SELECT username FROM registeredUsers WHERE username='$regUsernameEsc'";
                $checkResult = mysqli_query($conn, $checkSql);
                if (mysqli_num_rows($checkResult) > 0) {
                    $regUsernameErr = "username already exist";
                }
                mysqli_close($conn);
            }
        }
    }
    if (empty($_POST['reg_email'])) {
        $regEmailErr = "Email is required";
    } else {
        $regEmail = trim($_POST['reg_email']);
        if (!filter_var($regEmail, FILTER_VALIDATE_EMAIL)) {
            $regEmailErr = "Invalid email format";
        }
    }
    if (empty($_POST['reg_password'])) {
        $regPasswordErr = "Password is required";
    } else {
        $regPassword = $_POST['reg_password'];
        if (strlen($regPassword) < 6) {
            $regPasswordErr = "Password must be at least 6 characters";
        }
    }
    if (empty($_POST['reg_confirm_password'])) {
        $regConfirmPasswordErr = "Please confirm your password";
    } else {
        $regConfirmPassword = $_POST['reg_confirm_password'];
        if ($regPassword !== $regConfirmPassword) {
            $regConfirmPasswordErr = "Passwords do not match";
        }
    }
    if (empty($_POST['reg_pet_name'])) {
        $regPetNameErr = "Favorite pet name is required (for password recovery)";
    }
    if (empty($_POST['reg_role'])) {
        $regRoleErr = "Please select a role.";
    } else {
        $regRole = ($_POST['reg_role'] === 'employer') ? 'company' : $_POST['reg_role'];
    }
    if (empty($regUsernameErr) && empty($regEmailErr) && empty($regPasswordErr) && empty($regConfirmPasswordErr) && empty($regPetNameErr) && empty($regRoleErr)) {
        $servername = "localhost";
        $dbusername = "root";
        $dbpassword = "";
        $dbname = "careerconnect";
        $conn = mysqli_connect($servername, $dbusername, $dbpassword, $dbname);
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        $createTableSql = "CREATE TABLE IF NOT EXISTS registeredUsers (
            username VARCHAR(30) NOT NULL PRIMARY KEY,
            email VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL,
            pet_name VARCHAR(50) NOT NULL,
            role VARCHAR(20) NOT NULL,
            reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        mysqli_query($conn, $createTableSql);
        $regUsername = mysqli_real_escape_string($conn, $regUsername);
        $regEmail = mysqli_real_escape_string($conn, $regEmail);
        $regPassword = mysqli_real_escape_string($conn, $regPassword);
        $regPetName = mysqli_real_escape_string($conn, $_POST['reg_pet_name']);
        $regRole = mysqli_real_escape_string($conn, $regRole);
        $hashedPassword = password_hash($regPassword, PASSWORD_DEFAULT);
        $sql = "INSERT INTO registeredUsers (username, email, password, pet_name, role) VALUES ('$regUsername', '$regEmail', '$hashedPassword', '$regPetName', '$regRole')";
        if (mysqli_query($conn, $sql)) {
            $regSuccess = "Registration successful! You can now log in.";
        } else {
            $regSuccess = "Error: " . mysqli_error($conn);
        }
        mysqli_close($conn);
    }
}
$forgotUsernameErr = $forgotPetNameErr = $forgotPasswordErr = $forgotConfirmPasswordErr = $forgotSuccess = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forgot_password'])) {
    if (empty($_POST['forgot_username'])) {
        $forgotUsernameErr = "Username is required";
    }
    if (empty($_POST['forgot_pet_name'])) {
        $forgotPetNameErr = "Favorite pet name is required";
    }
    if (empty($_POST['new_password'])) {
        $forgotPasswordErr = "New password is required";
    } else if (strlen($_POST['new_password']) < 6) {
        $forgotPasswordErr = "Password must be at least 6 characters";
    }
    if (empty($_POST['confirm_new_password'])) {
        $forgotConfirmPasswordErr = "Please confirm your new password";
    } else if ($_POST['new_password'] !== $_POST['confirm_new_password']) {
        $forgotConfirmPasswordErr = "Passwords do not match";
    }
    if (empty($forgotUsernameErr) && empty($forgotPetNameErr) && empty($forgotPasswordErr) && empty($forgotConfirmPasswordErr)) {
        $servername = "localhost";
        $dbusername = "root";
        $dbpassword = "";
        $dbname = "careerconnect";
        $conn = mysqli_connect($servername, $dbusername, $dbpassword, $dbname);
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        $forgotUsername = mysqli_real_escape_string($conn, $_POST['forgot_username']);
        $forgotPetName = mysqli_real_escape_string($conn, $_POST['forgot_pet_name']);
        $sql = "SELECT username FROM registeredUsers WHERE username='$forgotUsername' AND pet_name='$forgotPetName'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0) {
            $newPassword = mysqli_real_escape_string($conn, $_POST['new_password']);
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateSql = "UPDATE registeredUsers SET password='$hashedPassword' WHERE username='$forgotUsername'";
            if (mysqli_query($conn, $updateSql)) {
                $forgotSuccess = "Password successfully reset! You can now log in with your new password.";
            } else {
                $forgotPasswordErr = "Error updating password. Please try again.";
            }
        } else {
            $forgotPetNameErr = "Username and pet name combination not found";
        }
        mysqli_close($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareerConnect | Job Application Management System</title>
    <link rel="stylesheet" href="Login_Try.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="?page=home" class="logo">
                    <i class="fas fa-briefcase"></i>
                    <span>CareerConnect</span>
                </a>
                <div class="user-actions">
                        <a href="?page=login" class="btn btn-outline">Login</a>
                        <a href="?page=register" class="btn btn-primary">Register</a>
                </div>
            </div>
        </div>
    </header>
    <main>
        <?php if ($page === 'home'): ?>
            <section class="hero">
                <div class="container">
                    <h1>Find Your Dream Job or Ideal Candidate</h1>
                    <p>CareerConnect is the ultimate platform for job seekers and employers to connect, making the hiring process simple and efficient.</p>
                    <div>
                        <a href="?page=register" class="btn btn-primary" style="margin-right: 10px;">Get Started</a>
                        <a href="#" class="btn btn-outline learn-more-btn" style="background: rgba(255,255,255,0.2); border-color: white; color: white;">Learn More</a>
                    </div>
                </div>
            </section>
            <section class="features">
                <div class="container">
                    <h2 class="section-title">Why Choose CareerConnect?</h2>
                    <div class="features-grid">
                        <div class="feature-card">
                            <div class="feature-icon" style="display:flex;align-items:center;justify-content:center;">
                                <img src="smart_job_matching.png" alt="Smart Job Matching" style="width: 60px; height: 60px; border-radius: 50%; background: #f5f7fa; padding: 12px;">
                            </div>
                            <h3>Smart Job Matching</h3>
                            <p>Our advanced algorithm matches your skills and preferences with the perfect job opportunities.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon" style="display:flex;align-items:center;justify-content:center;">
                                <img src="Track_Your_Applications.png" alt="Track Your Applications" style="width: 60px; height: 60px; border-radius: 50%; background: #f5f7fa; padding: 12px;">
                            </div>
                            <h3>Track Your Applications</h3>
                            <p>Easily monitor the status of all your job applications in one convenient dashboard.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon" style="display:flex;align-items:center;justify-content:center;">
                                <img src="Company_Insights.png" alt="Company Insights" style="width:60px;height:60px;border-radius:50%;background:#f5f7fa;padding:12px;">
                            </div>
                            <h3>Company Insights</h3>
                            <p>Get detailed information about companies, including reviews and salary data.</p>
                        </div>
                    </div>
                </div>
            </section>
            <section class="for-companies">
                <div class="container">
                    <h2 class="section-title">For Companies</h2>
                    <div class="company-benefits" style="display:flex;align-items:center;gap:32px;">
                        <div class="company-content" style="flex:1;">
                            <h3>Find the Perfect Candidates</h3>
                            <p>CareerConnect helps employers find qualified candidates quickly and efficiently. Our platform offers:</p>
                            <ul style="margin-top: 15px; margin-left: 20px;">
                                <li>Advanced candidate filtering</li>
                                <li>Automated application tracking</li>
                                <li>Candidate matching algorithms</li>
                                <li>Streamlined communication tools</li>
                            </ul>
                            <a href="?page=register" class="btn btn-primary" style="margin-top: 20px;">Post a Job</a>
                        </div>
            <div class="company-image">
  <img src="home_company.png" alt="For Companies" style="width:100%;height:100%;object-fit:cover;border-radius:10px;">
</div>
                    </div>
                </div>
            </section>
        <?php elseif ($page === 'login'): ?>
            <section class="container">
                <a href="?page=home" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
                <div class="auth-container">
                    <div class="auth-form">
                        <h2>Login to CareerConnect</h2>
                        <p style="text-align: center; margin-bottom: 20px; color: var(--secondary);">
                            Manage your job applications, find the perfect candidates, or administer the platform.
                        </p>
                        <?php if ($loginErr): ?>
                            <div class="error-message" style="text-align:center;"> <?php echo $loginErr; ?> </div>
                        <?php endif; ?>
                        <form method="post" action="?page=login" autocomplete="off" novalidate>
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" value="<?php echo htmlspecialchars($username); ?>" required pattern="[a-zA-Z0-9_]{3,20}">
                                <?php if ($usernameErr): ?>
                                    <span class="error-message"><?php echo $usernameErr; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                                <?php if ($passwordErr): ?>
                                    <span class="error-message"><?php echo $passwordErr; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group" style="display:flex;align-items:center;gap:8px;">
                                <input type="checkbox" id="remember" name="remember" <?php if(isset($_POST['remember'])||isset($_COOKIE['remembered_username'])) echo 'checked'; ?>>
                                <label for="remember" style="margin:0;">Remember me</label>
                            </div>
                            <button class="btn btn-primary" style="width: 100%;" type="submit" name="login">Login</button>
                        </form>
                        <p style="text-align: center; margin-top: 20px; color: var(--secondary);">
                            Don't have an account? <a href="?page=register" style="color: var(--primary);">Register now</a>
                        </p>
                        <p style="text-align: center; margin-top: 10px;">
                            <a href="?page=forgot" style="color: var(--primary);">Forgot your password?</a>
                        </p>
                    </div>
                </div>
            </section>
        <?php elseif ($page === 'register'): ?>
            <section class="container">
                <a href="?page=home" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
                <div class="auth-container">
                    <div class="auth-form">
                        <h2>Create Your Account</h2>
                        <p style="text-align: center; margin-bottom: 20px; color: var(--secondary);">
                            Join CareerConnect to manage your job applications or find the perfect candidates.
                        </p>
                        <?php if ($regSuccess): ?>
                            <div class="success-message"><?php echo $regSuccess; ?></div>
                        <?php endif; ?>
                        <form method="post" action="?page=register" autocomplete="off" novalidate>
                            <div class="form-group">
                                <label for="reg_role">In which role you want to register?</label>
                                <select id="reg_role" name="reg_role" class="form-control" required>
                                    <option value="" disabled selected>Select your role</option>
                                    <option value="job_seeker" <?php if(isset($_POST['reg_role']) && $_POST['reg_role']==='job_seeker') echo 'selected'; ?>>Job Seeker</option>
                                    <option value="company" <?php if(isset($_POST['reg_role']) && $_POST['reg_role']==='company') echo 'selected'; ?>>Company</option>
                                    <option value="admin" <?php if(isset($_POST['reg_role']) && $_POST['reg_role']==='admin') echo 'selected'; ?>>Admin</option>
                                </select>
                                <?php if (isset($regRoleErr) && $regRoleErr): ?>
                                    <span class="error-message"><?php echo $regRoleErr; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="reg_username">Username</label>
                                <input type="text" id="reg_username" name="reg_username" class="form-control" placeholder="Choose a username" value="<?php echo isset($_POST['reg_username']) ? htmlspecialchars($_POST['reg_username']) : ''; ?>" required pattern="[a-zA-Z0-9_]{3,20}">
                                <?php if ($regUsernameErr): ?>
                                    <span class="error-message"><?php echo $regUsernameErr; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="reg_email">Email Address</label>
                                <input type="email" id="reg_email" name="reg_email" class="form-control" placeholder="Enter your email" value="<?php echo isset($_POST['reg_email']) ? htmlspecialchars($_POST['reg_email']) : ''; ?>" required>
                                <?php if ($regEmailErr): ?>
                                    <span class="error-message"><?php echo $regEmailErr; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="reg_password">Password</label>
                                <input type="password" id="reg_password" name="reg_password" class="form-control" placeholder="Create a password" required>
                                <?php if ($regPasswordErr): ?>
                                    <span class="error-message"><?php echo $regPasswordErr; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="reg_confirm_password">Confirm Password</label>
                                <input type="password" id="reg_confirm_password" name="reg_confirm_password" class="form-control" placeholder="Confirm your password" required>
                                <?php if ($regConfirmPasswordErr): ?>
                                    <span class="error-message"><?php echo $regConfirmPasswordErr; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="reg_pet_name">Favorite Pet Name (For Password Recovery)</label>
                                <input type="text" id="reg_pet_name" name="reg_pet_name" class="form-control" placeholder="What's your favorite pet's name?" value="<?php echo isset($_POST['reg_pet_name']) ? htmlspecialchars($_POST['reg_pet_name']) : ''; ?>" required>
                                <?php if ($regPetNameErr): ?>
                                    <span class="error-message"><?php echo $regPetNameErr; ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-primary" style="width: 100%;" type="submit" name="register">Register</button>
                        </form>
                        <p style="text-align: center; margin-top: 20px; color: var(--secondary);">
                            Already have an account? <a href="?page=login" style="color: var(--primary);">Login here</a>
                        </p>
                    </div>
                </div>
            </section>
        <?php elseif ($page === 'forgot'): ?>
            <section class="container">
                <a href="?page=login" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
                <div class="auth-container">
                    <div class="auth-form">
                        <h2>Reset Your Password</h2>
                        <p style="text-align: center; margin-bottom: 20px; color: var(--secondary);">
                            Enter your username and answer the security question to reset your password.
                        </p>
                        <?php if ($forgotSuccess): ?>
                            <div class="success-message"><?php echo $forgotSuccess; ?></div>
                        <?php endif; ?>
                        <form method="post" action="?page=forgot" autocomplete="off" novalidate>
                            <div class="form-group">
                                <label for="forgot_username">Username</label>
                                <input type="text" id="forgot_username" name="forgot_username" class="form-control" placeholder="Enter your username" value="<?php echo isset($_POST['forgot_username']) ? htmlspecialchars($_POST['forgot_username']) : ''; ?>" required>
                                <?php if ($forgotUsernameErr): ?>
                                    <span class="error-message"><?php echo $forgotUsernameErr; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="forgot_pet_name">What is your favorite pet's name?</label>
                                <input type="text" id="forgot_pet_name" name="forgot_pet_name" class="form-control" placeholder="Enter your favorite pet's name" value="<?php echo isset($_POST['forgot_pet_name']) ? htmlspecialchars($_POST['forgot_pet_name']) : ''; ?>" required>
                                <?php if ($forgotPetNameErr): ?>
                                    <span class="error-message"><?php echo $forgotPetNameErr; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter new password" required>
                                <?php if ($forgotPasswordErr): ?>
                                    <span class="error-message"><?php echo $forgotPasswordErr; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="confirm_new_password">Confirm New Password</label>
                                <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-control" placeholder="Confirm new password" required>
                                <?php if ($forgotConfirmPasswordErr): ?>
                                    <span class="error-message"><?php echo $forgotConfirmPasswordErr; ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-primary" style="width: 100%;" type="submit" name="forgot_password">Reset Password</button>
                        </form>
                        <p style="text-align: center; margin-top: 20px; color: var(--secondary);">
                            Remember your password? <a href="?page=login" style="color: var(--primary);">Login here</a>
                        </p>
                    </div>
                </div>
            </section>
        <?php elseif ($page === 'dashboard'): ?>
            <section class="dashboard">
                <div class="container">
                    <div class="dashboard-header">
                        <h1 class="dashboard-title">Dashboard</h1>
                    </div>
                    <div class="stats-container">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div class="stat-card-title">Applications</div>
                                <div class="stat-card-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            </div>
                            <div class="stat-card-value">24</div>
                            <div class="stat-card-change positive">
                                <i class="fas fa-arrow-up"></i> 12% from last month
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div class="stat-card-title">Interviews</div>
                                <div class="stat-card-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                            </div>
                            <div class="stat-card-value">5</div>
                            <div class="stat-card-change positive">
                                <i class="fas fa-arrow-up"></i> 2 scheduled this week
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div class="stat-card-title">Profile Strength</div>
                                <div class="stat-card-icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                            </div>
                            <div class="stat-card-value">85%</div>
                            <div class="progress" style="height: 8px; background-color: #e2e8f0; border-radius: 4px; margin-top: 10px;">
                                <div style="height: 100%; width: 85%; background-color: var(--primary); border-radius: 4px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Recent Applications</h2>
                            <button class="btn btn-primary">New Application</button>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Position</th>
                                    <th>Company</th>
                                    <th>Date Applied</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Frontend Developer</td>
                                    <td>TechCorp Inc.</td>
                                    <td>Jun 12, 2023</td>
                                    <td><span class="status-badge status-pending">Pending</span></td>
                                    <td>
                                        <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                        <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>UX Designer</td>
                                    <td>DesignHub</td>
                                    <td>Jun 10, 2023</td>
                                    <td><span class="status-badge status-approved">Approved</span></td>
                                    <td>
                                        <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                        <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Product Manager</td>
                                    <td>InnovateTech</td>
                                    <td>Jun 5, 2023</td>
                                    <td><span class="status-badge status-rejected">Rejected</span></td>
                                    <td>
                                        <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                        <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Backend Developer</td>
                                    <td>DataSystems</td>
                                    <td>Jun 3, 2023</td>
                                    <td><span class="status-badge status-pending">Pending</span></td>
                                    <td>
                                        <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                        <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="logo">
                    <i class="fas fa-briefcase"></i>
                    <span>CareerConnect</span>
                </div>
                <div class="footer-links">
    <a href="#" class="footer-link" data-target="privacyPolicy">Privacy Policy</a>
    <a href="#" class="footer-link" data-target="termsOfService">Terms of Service</a>
    <a href="#" class="footer-link" data-target="contactUs">Contact Us</a>
    <a href="#" class="footer-link" data-target="helpCenter">Help Center</a>
</div>
                <div class="copyright">
                    &copy; 2023 CareerConnect. All rights reserved.
                </div>
            </div>
        </div>
    </footer>
    <div class="company-info-modal" id="companyInfoModal">
        <div class="company-info-content">
            <button class="company-info-close" id="companyInfoClose">&times;</button>
            <h2>About CareerConnect</h2>
            <p>CareerConnect is a comprehensive job application management system designed to bridge the gap between job seekers and employers. Our platform offers a seamless experience for both parties in the hiring process.</p>
            <h3>Our Mission</h3>
            <p>To create meaningful connections between talented professionals and forward-thinking companies, making the job search and hiring process more efficient, transparent, and successful for everyone involved.</p>
            <h3>What We Offer</h3>
            <ul>
                <li><strong>For Job Seekers:</strong> Advanced job matching, application tracking, resume building, and career resources</li>
                <li><strong>For Employers:</strong> Candidate sourcing, application management, interview scheduling, and analytics</li>
            </ul>
            <h3>Our Values</h3>
            <ul>
                <li><strong>Transparency:</strong> We believe in open communication and clear processes</li>
                <li><strong>Efficiency:</strong> Streamlining the hiring process for better outcomes</li>
                <li><strong>Innovation:</strong> Continuously improving our platform with the latest technology</li>
                <li><strong>Community:</strong> Building a network of professionals and companies that grow together</li>
            </ul>
            <h3>Contact Information</h3>
            <p>Email: info@careerconnect.com<br>
            Phone: (555) 123-4567<br>
            Address: 123 Career Street, Professional District, PC 12345</p>
        </div>
    </div>
<div class="company-info-modal" id="privacyPolicyModal">
    <div class="company-info-content">
        <button class="company-info-close" onclick="closeModal('privacyPolicyModal')">&times;</button>
        <h2>Privacy Policy</h2>
        <h3>1. Introduction</h3>
        <p>Welcome to CareerConnect. We are committed to protecting your personal information and your right to privacy. This Privacy Policy explains how we collect, use, and share your personal information when you use our services.</p>
        <h3>2. Information We Collect</h3>
        <p>We collect personal information that you provide to us, such as name, email address, contact information, and professional information when you create an account, apply for jobs, or use our services.</p>
        <h3>3. How We Use Your Information</h3>
        <p>We use personal information collected via our services for a variety of business purposes described below:</p>
        <ul>
            <li>To facilitate account creation and login process</li>
            <li>To send you marketing and promotional communications</li>
            <li>To post testimonials with your consent</li>
            <li>To request feedback and to contact you about your use of our services</li>
            <li>To protect our services and enforce our terms</li>
        </ul>
        <h3>4. Will Your Information Be Shared With Anyone?</h3>
        <p>We only share information with your consent, to comply with laws, to provide you with services, to protect your rights, or to fulfill business obligations.</p>
    </div>
</div>
<div class="company-info-modal" id="termsOfServiceModal">
    <div class="company-info-content">
        <button class="company-info-close" onclick="closeModal('termsOfServiceModal')">&times;</button>
        <h2>Terms of Service</h2>
        <h3>1. Agreement to Terms</h3>
        <p>By accessing or using our services, you agree to be bound by these Terms of Service. If you disagree with any part of the terms, then you may not access the services.</p>
        <h3>2. User Accounts</h3>
        <p>When you create an account with us, you must provide us with information that is accurate, complete, and current at all times. Failure to do so constitutes a breach of the Terms.</p>
        <h3>3. Intellectual Property</h3>
        <p>The Service and its original content, features, and functionality are and will remain the exclusive property of CareerConnect and its licensors.</p>
        <h3>4. Links To Other Web Sites</h3>
        <p>Our Service may contain links to third-party web sites or services that are not owned or controlled by CareerConnect. We have no control over, and assume no responsibility for, the content, privacy policies, or practices of any third party web sites or services.</p>
    </div>
</div>
<div class="company-info-modal" id="contactUsModal">
    <div class="company-info-content">
        <button class="company-info-close" onclick="closeModal('contactUsModal')">&times;</button>
        <h2>Contact Us</h2>
        <h3>Get in Touch</h3>
        <p>We'd love to hear from you. Here's how you can reach us:</p>
        <div style="display: flex; flex-wrap: wrap; gap: 20px; margin: 20px 0;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-phone"></i>
                <span>+880 19478 53732<span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-envelope"></i>
                <span>support@careerconnect.com</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-map-marker-alt"></i>
                <span>House:225/226, Road:17, Block: C , Bashundhara R/A, Dhaka-1229</span>
            </div>
        </div>
        <h3>Office Hours</h3>
        <p>Monday - Friday: 9:00 AM - 6:00 PM BST</p>
        <p>Saturday: 10:00 AM - 4:00 PM BST</p>
    </div>
</div>
<div class="company-info-modal" id="helpCenterModal">
    <div class="company-info-content">
        <button class="company-info-close" onclick="closeModal('helpCenterModal')">&times;</button>
        <h2>Help Center</h2>
        <h3>Emergency Help</h3>
        <p>If you need immediate assistance, please contact us at:</p>
        <div style="display: flex; flex-wrap: wrap; gap: 20px; margin: 20px 0;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-phone"></i>
                <span>Emergency Support: +880 19478 53732</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-envelope"></i>
                <span>emergency@careerconnect.com</span>
            </div>
        </div>
        <h3>Frequently Asked Questions</h3>
        <div style="margin: 15px 0;">
            <label style="font-weight: bold;">How do I reset my password?</label>
            <p>Go to your profile settings and click on "Change Password". Follow the instructions to reset your password.</p>
        </div>
        <div style="margin: 15px 0;">
            <label style="font-weight: bold;">How can I delete my account?</label>
            <p>Go to your profile settings and click on "Delete Account" at the bottom of the page.</p>
        </div>
        <div style="margin: 15px 0;">
            <label style="font-weight: bold;">How do I apply for a job?</label>
            <p>Navigate to the Jobs section, find a job you're interested in, and click "Apply Now". Fill out the required information and submit your application.</p>
        </div>
    </div>
</div>
    <script>
        const userTypeButtons = document.querySelectorAll('.user-type-btn');
        const dashboardSection = document.getElementById('dashboard');
        const applicationModal = document.getElementById('applicationModal');
        const viewButtons = document.querySelectorAll('.action-btn.view');
        const modalClose = document.querySelector('.modal-close');
        const learnMoreBtn = document.querySelector('.learn-more-btn');
        const companyInfoModal = document.getElementById('companyInfoModal');
        const companyInfoClose = document.getElementById('companyInfoClose');
        const footerLinks = document.querySelectorAll('.footer-link');
        const footerPages = document.querySelectorAll('.footer-page');
        const backButtons = document.querySelectorAll('.back-button');
        if (userTypeButtons.length > 0) {
            userTypeButtons.forEach(button => {
                button.addEventListener('click', () => {
                    userTypeButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                });
            });
        }
        if (viewButtons.length > 0) {
            viewButtons.forEach(button => {
                button.addEventListener('click', () => {
                    applicationModal.style.display = 'flex';
                });
            });
        }
        if (modalClose) {
            modalClose.addEventListener('click', () => {
                applicationModal.style.display = 'none';
            });
        }
        if (applicationModal) {
            applicationModal.addEventListener('click', (e) => {
                if (e.target === applicationModal) {
                    applicationModal.style.display = 'none';
                }
            });
        }
        if (learnMoreBtn) {
            learnMoreBtn.addEventListener('click', (e) => {
                e.preventDefault();
                companyInfoModal.style.display = 'flex';
            });
        }
        if (companyInfoClose) {
            companyInfoClose.addEventListener('click', () => {
                companyInfoModal.style.display = 'none';
            });
        }
        if (companyInfoModal) {
            companyInfoModal.addEventListener('click', (e) => {
                if (e.target === companyInfoModal) {
                    companyInfoModal.style.display = 'none';
                }
            });
        }
footerLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        const target = link.getAttribute('data-target');
        document.getElementById(target + 'Modal').style.display = 'flex';
    });
});
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}
document.querySelectorAll('.company-info-modal').forEach(modal => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});
        backButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                footerPages.forEach(page => {
                    page.classList.remove('active');
                });
                window.location.href = '?page=home';
            });
        });
        const applications = [
            { position: 'Frontend Developer', company: 'TechCorp Inc.', date: 'Jun 12, 2023', status: 'pending' },
            { position: 'UX Designer', company: 'DesignHub', date: 'Jun 10, 2023', status: 'approved' },
            { position: 'Product Manager', company: 'InnovateTech', date: 'Jun 5, 2023', status: 'rejected' },
            { position: 'Backend Developer', company: 'DataSystems', date: 'Jun 3, 2023', status: 'pending' }
        ];
        console.log('Loaded applications:', applications);
    </script>
</body>
</html>