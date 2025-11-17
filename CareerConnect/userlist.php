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

// Initialize variables for form validation
$usernameErr = $emailErr = $pet_nameErr = $roleErr = "";
$username = $email = $pet_name = $role = "";
$edit_mode = false;
$current_email = "";

// Handle Delete Operation
if (isset($_GET['delete'])) {
    $email = mysqli_real_escape_string($conn, $_GET['delete']);
    $sql = "DELETE FROM registeredusers WHERE email=?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('User deleted successfully!');</script>";
        } else {
            echo "<script>alert('Error deleting user!');</script>";
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle Edit Mode - Load user data for editing
if (isset($_GET['edit'])) {
    $edit_email = mysqli_real_escape_string($conn, $_GET['edit']);
    $sql = "SELECT username, email, pet_name, role FROM registeredusers WHERE email=?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $edit_email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user_data = mysqli_fetch_assoc($result);
        
        if ($user_data) {
            $edit_mode = true;
            $current_email = $user_data['email'];
            $username = $user_data['username'];
            $email = $user_data['email'];
            $pet_name = $user_data['pet_name'];
            $role = $user_data['role'];
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle Update Operation with Form Validation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    // Get form data
    $current_email = $_POST['current_email'];
    $username = test_input($_POST['username']);
    $email = test_input($_POST['email']);
    $pet_name = test_input($_POST['pet_name']);
    $role = test_input($_POST['role']);
    
    // Form validation
    $valid = true;
    
    // Validate username
    if (empty($username)) {
        $usernameErr = "Username is required";
        $valid = false;
    } elseif (!preg_match("/^[a-zA-Z0-9_ ]{3,50}$/", $username)) {
        $usernameErr = "Username must be 3-50 characters and contain only letters, numbers, spaces, and underscores";
        $valid = false;
    }
    
    // Validate email
    if (empty($email)) {
        $emailErr = "Email is required";
        $valid = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Invalid email format";
        $valid = false;
    } else {
        // Check if email already exists (excluding current user)
        $check_sql = "SELECT email FROM registeredusers WHERE email=? AND email !=?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "ss", $email, $current_email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $emailErr = "Email already exists";
            $valid = false;
        }
        mysqli_stmt_close($check_stmt);
    }
    
    // Validate pet name
    if (empty($pet_name)) {
        $pet_nameErr = "Pet name is required";
        $valid = false;
    } elseif (!preg_match("/^[a-zA-Z ]{2,30}$/", $pet_name)) {
        $pet_nameErr = "Pet name must be 2-30 characters and contain only letters and spaces";
        $valid = false;
    }
    
    // Validate role
    if (empty($role)) {
        $roleErr = "Role is required";
        $valid = false;
    } elseif (!in_array($role, ['seeker', 'company', 'admin'])) {
        $roleErr = "Invalid role selected";
        $valid = false;
    }
    
    // If validation passes, update the database
    if ($valid) {
        $sql = "UPDATE registeredusers SET username=?, email=?, pet_name=?, role=? WHERE email=?";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssss", $username, $email, $pet_name, $role, $current_email);
            if (mysqli_stmt_execute($stmt)) {
                echo "<script>alert('User updated successfully!');</script>";
                // Clear form and exit edit mode
                $edit_mode = false;
                $username = $email = $pet_name = $role = $current_email = "";
            } else {
                echo "<script>alert('Error updating user!');</script>";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Function to sanitize input data
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fetch all users from registeredusers table
$sql = "SELECT username, email, pet_name, role, reg_date FROM registeredusers";
$result = mysqli_query($conn, $sql);
$users = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}

// Get filter values from URL or set defaults
$filterType = isset($_GET['type']) ? $_GET['type'] : 'all';
$filterStatus = isset($_GET['status']) ? $_GET['status'] : 'all';
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// Filter users based on criteria (using role as type)
$filteredUsers = array_filter($users, function($user) use ($filterType, $searchQuery) {
    $typeMatch = ($filterType === 'all' || $user['role'] === $filterType);
    $searchMatch = empty($searchQuery) || 
                  stripos($user['username'], $searchQuery) !== false || 
                  stripos($user['email'], $searchQuery) !== false;
    
    return $typeMatch && $searchMatch;
});

// Get view preference
$viewMode = isset($_GET['view']) ? $_GET['view'] : (isset($_SESSION['user_view_mode']) ? $_SESSION['user_view_mode'] : 'card');
$_SESSION['user_view_mode'] = $viewMode;

// Function to get user type badge class
function getUserTypeBadge($type) {
    switch($type) {
        case 'seeker':
            return 'type-seeker';
        case 'company':
            return 'type-company';
        case 'admin':
            return 'type-admin';
        default:
            return 'type-seeker';
    }
}

// Function to get user type display text
function getUserTypeText($type) {
    switch($type) {
        case 'seeker':
            return 'Job Seeker';
        case 'company':
            return 'Company';
        case 'admin':
            return 'Admin';
        default:
            return 'Job Seeker';
    }
}

// Function to get user avatar class
function getUserAvatarClass($type) {
    switch($type) {
        case 'seeker':
            return 'job-seeker';
        case 'company':
            return 'company';
        case 'admin':
            return 'admin';
        default:
            return 'job-seeker';
    }
}

// Function to get status color (using registration date as status indicator)
function getStatusColor($reg_date) {
    $reg_timestamp = strtotime($reg_date);
    $current_timestamp = time();
    $days_diff = ($current_timestamp - $reg_timestamp) / (60 * 60 * 24);
    
    if ($days_diff < 30) {
        return 'var(--success)'; // Active - registered within 30 days
    } else {
        return 'var(--warning)'; // Pending - registered more than 30 days ago
    }
}

// Function to get status text
function getStatusText($reg_date) {
    $reg_timestamp = strtotime($reg_date);
    $current_timestamp = time();
    $days_diff = ($current_timestamp - $reg_timestamp) / (60 * 60 * 24);
    
    if ($days_diff < 30) {
        return 'Active';
    } else {
        return 'Long-term';
    }
}

// Function to get user icon
function getUserIcon($type) {
    switch($type) {
        case 'seeker':
            return 'fas fa-user';
        case 'company':
            return 'fas fa-building';
        case 'admin':
            return 'fas fa-cog';
        default:
            return 'fas fa-user';
    }
}

// Close database connection at the end
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - CareerConnect Admin</title>
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
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-label {
            font-weight: 500;
            color: var(--secondary);
        }
        
        .filter-select {
            padding: 8px 12px;
            border: 1px solid var(--gray);
            border-radius: 6px;
            background-color: white;
        }
        
        .search-box {
            position: relative;
            flex-grow: 1;
            max-width: 300px;
        }
        
        .search-input {
            width: 100%;
            padding: 8px 15px 8px 40px;
            border: 1px solid var(--gray);
            border-radius: 6px;
            font-size: 14px;
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary);
        }
        
        .user-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .user-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            transition: transform 0.3s;
        }
        
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .user-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }
        
        .job-seeker { background-color: var(--primary); }
        .company { background-color: var(--success); }
        .admin { background-color: var(--warning); }
        
        .user-info h3 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .user-info p {
            color: var(--secondary);
            font-size: 14px;
        }
        
        .user-type {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 5px;
        }
        
        .type-seeker {
            background-color: #dbeafe;
            color: var(--primary);
        }
        
        .type-company {
            background-color: #d1fae5;
            color: var(--success);
        }
        
        .type-admin {
            background-color: #fef3c7;
            color: var(--warning);
        }
        
        .user-details {
            margin: 15px 0;
            padding: 15px 0;
            border-top: 1px solid var(--gray);
            border-bottom: 1px solid var(--gray);
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .detail-label {
            color: var(--secondary);
        }
        
        .detail-value {
            font-weight: 500;
        }
        
        .user-actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }
        
        .action-btn.view {
            background-color: #dbeafe;
            color: var(--primary);
        }
        
        .action-btn.edit {
            background-color: #fef3c7;
            color: #d97706;
        }
        
        .action-btn.delete {
            background-color: #fee2e2;
            color: #dc2626;
        }
        
        .view-toggle {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 15px;
        }
        
        .toggle-btn {
            background: white;
            border: 1px solid var(--gray);
            padding: 8px 15px;
            cursor: pointer;
        }
        
        .toggle-btn:first-child {
            border-radius: 6px 0 0 6px;
        }
        
        .toggle-btn:last-child {
            border-radius: 0 6px 6px 0;
        }
        
        .toggle-btn.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 30px;
            display: none;
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
        
        /* Edit Form Styles */
        .edit-form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
            border-left: 4px solid var(--primary);
        }
        
        .edit-form-title {
            color: var(--primary);
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--gray);
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .error {
            color: var(--danger);
            font-size: 14px;
            margin-top: 5px;
            display: block;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
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
            
            .filters {
                flex-direction: column;
            }
            
            .search-box {
                max-width: 100%;
            }
            
            .user-cards {
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
            
            .form-actions {
                flex-direction: column;
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
                        <li><a href="admindashboard.php">Dashboard</a></li>
                        <li><a href="userlist.php" class="active">Users</a></li>
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
                    <h1 class="dashboard-title">User Management</h1>
                </div>

                <!-- Edit User Form -->
                <?php if ($edit_mode): ?>
                <div class="edit-form-container">
                    <h2 class="edit-form-title">Edit User: <?php echo htmlspecialchars($username); ?></h2>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" novalidate>
                        <input type="hidden" name="current_email" value="<?php echo htmlspecialchars($current_email); ?>">
                        
                        <div class="form-group">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-control" 
                                   value="<?php echo htmlspecialchars($username); ?>" 
                                   placeholder="Enter username" required>
                            <span class="error"><?php echo $usernameErr; ?></span>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($email); ?>" 
                                   placeholder="Enter email address" required>
                            <span class="error"><?php echo $emailErr; ?></span>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Pet Name *</label>
                            <input type="text" name="pet_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($pet_name); ?>" 
                                   placeholder="Enter pet name" required>
                            <span class="error"><?php echo $pet_nameErr; ?></span>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Role *</label>
                            <select name="role" class="form-control" required>
                                <option value="">Select Role</option>
                                <option value="seeker" <?php echo $role == 'seeker' ? 'selected' : ''; ?>>Job Seeker</option>
                                <option value="company" <?php echo $role == 'company' ? 'selected' : ''; ?>>Company</option>
                                <option value="admin" <?php echo $role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <span class="error"><?php echo $roleErr; ?></span>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_user" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update User
                            </button>
                            <a href="userlist.php" class="btn btn-outline">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <form method="GET" action="" class="filters">
                    <div class="filter-group">
                        <span class="filter-label">Filter by Role:</span>
                        <select class="filter-select" name="type" onchange="this.form.submit()">
                            <option value="all" <?php echo $filterType === 'all' ? 'selected' : ''; ?>>All Users</option>
                            <option value="seeker" <?php echo $filterType === 'seeker' ? 'selected' : ''; ?>>Job Seekers</option>
                            <option value="company" <?php echo $filterType === 'company' ? 'selected' : ''; ?>>Companies</option>
                            <option value="admin" <?php echo $filterType === 'admin' ? 'selected' : ''; ?>>Admins</option>
                        </select>
                    </div>
                    
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" name="search" placeholder="Search by username or email..." 
                               value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <button type="submit" style="display: none;">Search</button>
                    </div>
                </form>

                <div class="view-toggle">
                    <a href="?type=<?php echo $filterType; ?>&search=<?php echo urlencode($searchQuery); ?>&view=card" 
                       class="toggle-btn <?php echo $viewMode === 'card' ? 'active' : ''; ?>" id="cardViewBtn">
                        <i class="fas fa-th-large"></i> Card View
                    </a>
                    <a href="?type=<?php echo $filterType; ?>&search=<?php echo urlencode($searchQuery); ?>&view=table" 
                       class="toggle-btn <?php echo $viewMode === 'table' ? 'active' : ''; ?>" id="tableViewBtn">
                        <i class="fas fa-table"></i> Table View
                    </a>
                </div>

                <?php if ($viewMode === 'card'): ?>
                <div class="user-cards" id="cardView">
                    <?php foreach($filteredUsers as $user): ?>
                    <div class="user-card" data-type="<?php echo $user['role']; ?>">
                        <div class="user-header">
                            <div class="user-avatar <?php echo getUserAvatarClass($user['role']); ?>">
                                <i class="<?php echo getUserIcon($user['role']); ?>"></i>
                            </div>
                            <div class="user-info">
                                <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                                <p><?php echo htmlspecialchars($user['email']); ?></p>
                                <span class="user-type <?php echo getUserTypeBadge($user['role']); ?>">
                                    <?php echo getUserTypeText($user['role']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="user-details">
                            <div class="detail-item">
                                <span class="detail-label">Status:</span>
                                <span class="detail-value" style="color: <?php echo getStatusColor($user['reg_date']); ?>;">
                                    <?php echo getStatusText($user['reg_date']); ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Joined:</span>
                                <span class="detail-value"><?php echo date('M j, Y', strtotime($user['reg_date'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Pet Name:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($user['pet_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                        </div>
                        <div class="user-actions">
                            <a href="?edit=<?php echo urlencode($user['email']); ?>" class="action-btn edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="?delete=<?php echo urlencode($user['email']); ?>" class="action-btn delete" onclick="return confirm('Are you sure you want to delete this user?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($filteredUsers)): ?>
                    <div class="user-card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                        <h3>No users found</h3>
                        <p>Try adjusting your filters or search query</p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="table-container" id="tableView" style="display: block;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Pet Name</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($filteredUsers as $user): ?>
                            <tr data-type="<?php echo $user['role']; ?>">
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="user-type <?php echo getUserTypeBadge($user['role']); ?>">
                                        <?php echo getUserTypeText($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['pet_name']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($user['reg_date'])); ?></td>
                                <td>
                                    <a href="?edit=<?php echo urlencode($user['email']); ?>" class="action-btn edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo urlencode($user['email']); ?>" class="action-btn delete" onclick="return confirm('Are you sure you want to delete this user?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($filteredUsers)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px;">
                                    <h3>No users found</h3>
                                    <p>Try adjusting your filters or search query</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Footer -->
                <link rel="stylesheet" href="jsdash.css">
    <?php include 'footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle search form submission on enter key
            const searchInput = document.querySelector('.search-input');
            searchInput.addEventListener('keyup', function(event) {
                if (event.key === 'Enter') {
                    this.form.submit();
                }
            });
        });
    </script>
</body>
</html>