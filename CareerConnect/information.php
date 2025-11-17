<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Information - CareerConnect</title>
    <link rel="stylesheet" href="information.css" />
  </head>
  <body>
    <!-- Header -->
    <header>
      <div class="container">
        <div class="headerContent">
          <div class="logo">
            <i class="fas fa-briefcase"></i>
            <span>CareerConnect</span>
          </div>
          <nav>
            <ul>
              <li><a href="dashboard.php" class="nav-link" data-target="dashboard">Dashboard</a></li>
              <li><a href="postjob.php" class="nav-link" data-target="post-job">Post Job</a></li>
              <li><a href="applications.php" class="nav-link" data-target="applications">Applications</a></li>
              <li><a href="interviews.php" class="nav-link" data-target="schedule">Schedule Interviews</a></li>
              <li><a href="information.php" class="nav-link active" data-target="information">Information</a></li>
            </ul>
          </nav>
          <div class="userActions">
            <span>Welcome, <strong><?php
            $displayName = 'Company Guest Admin';
            if (isset($_SESSION['username'])) {
              $conn = mysqli_connect('localhost', 'root', '', 'careerconnect');
              if ($conn) {
                $username = mysqli_real_escape_string($conn, $_SESSION['username']);
                $result = mysqli_query($conn, "SELECT companyName FROM companyUsers WHERE username = '$username' LIMIT 1");
                if ($result && $row = mysqli_fetch_assoc($result)) {
                  $displayName = $row['companyName'];
                } else {
                  $displayName = $_SESSION['username'];
                }
                mysqli_close($conn);
              } else {
                $displayName = $_SESSION['username'];
              }
            }
            echo htmlspecialchars($displayName);
            ?></strong></span>
            <a href="Login_Try.php" class="btn btnOutline" id="logoutBtn">Logout</a>
          </div>
        </div>
      </div>
    </header>
    <main class="information">
      <div class="container">
        <div id="information" class="tabContent active">
          <h2 class="sectionTitle">Company Information</h2>
          <?php
          if (!empty($successMsg)) {
            echo '<div style="color: green; font-weight: bold; margin-bottom: 10px;">' . htmlspecialchars($successMsg) . '</div>';
          }

          $loggedInUser = isset($_SESSION['username']) ? $_SESSION['username'] : '';

          // MySQLi connection
          $servername = "localhost";
          $dbusername = "root";
          $dbpassword = "";
          $dbname = "careerconnect";
          $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
          if ($conn->connect_error) {
              die("Connection failed: " . $conn->connect_error);
          }

          // Create table if not exists (add username column)
          $createTableSQL = "CREATE TABLE IF NOT EXISTS companyUsers (
              id INT AUTO_INCREMENT PRIMARY KEY,
              companyName VARCHAR(255) NOT NULL,
              type VARCHAR(100) NOT NULL,
              employee INT NOT NULL,
              location VARCHAR(255) NOT NULL,
              website VARCHAR(255) NOT NULL,
              openPosition INT NOT NULL,
              description TEXT NOT NULL
          )";
          $conn->query($createTableSQL);

          // Add username column safely if missing
          $checkColumn = $conn->query("SHOW COLUMNS FROM companyUsers LIKE 'username'");
          if ($checkColumn->num_rows == 0) {
            // Step 1: Add column as NULL and not unique
            $conn->query("ALTER TABLE companyUsers ADD COLUMN username VARCHAR(100) NULL AFTER id");
            // Step 2: Update all existing rows to a default username
            $conn->query("UPDATE companyUsers SET username = 'unknown' WHERE username IS NULL");
            // Remove duplicate 'unknown' rows, keep only one
            $result = $conn->query("SELECT id FROM companyUsers WHERE username = 'unknown'");
            if ($result && $result->num_rows > 1) {
              $first = true;
              while ($row = $result->fetch_assoc()) {
                if ($first) { $first = false; continue; }
                $conn->query("DELETE FROM companyUsers WHERE id = " . intval($row['id']));
              }
            }
            // Step 3: Alter column to NOT NULL UNIQUE
            $conn->query("ALTER TABLE companyUsers MODIFY COLUMN username VARCHAR(100) NOT NULL, ADD UNIQUE KEY unique_user (username)");
          }

          $companyName = $type = $employee = $location = $website = $openPosition = $description = "";
          $companyNameErr = $typeErr = $employeeErr = $locationErr = $websiteErr = $openPositionErr = $descriptionErr = "";
          $successMsg = "";

          // Pre-fill form if user data exists
          if ($loggedInUser) {
            $stmt = $conn->prepare("SELECT companyName, type, employee, location, website, openPosition, description FROM companyUsers WHERE username = ? LIMIT 1");
            $stmt->bind_param("s", $loggedInUser);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
              $stmt->bind_result($companyName, $type, $employee, $location, $website, $openPosition, $description);
              $stmt->fetch();
            }
            $stmt->close();
          }

          if ($_SERVER["REQUEST_METHOD"] == "POST") {
            function test_input($data) {
              $data = trim($data);
              $data = stripslashes($data);
              $data = htmlspecialchars($data);
              return $data;
            }
            if (empty($_POST["companyName"])) {
                $companyNameErr = "Company Name is required";
              } else {
                $companyName = test_input($_POST["companyName"]);
                if (!preg_match("/^[a-zA-Z0-9 .'-]+$/", $companyName) || strlen(trim($companyName)) == 0 || preg_match('/^\d+$/', $companyName) || preg_match('/^\s+$/', $companyName)) {
                  $companyNameErr = "Company Name must contain letters. Only numbers or only spaces are not allowed.";
                }
              }
            if (empty($_POST["type"])) {
              $typeErr = "Type is required";
            } else {
              $type = test_input($_POST["type"]);
              if (!preg_match("/^[a-zA-Z ,'-]+$/", $type)) {
                $typeErr = "Only letters and commas allowed";
              }
            }
            if (empty($_POST["employee"])) {
              $employeeErr = "Employee count is required";
            } else {
              $employee = test_input($_POST["employee"]);
              if (!filter_var($employee, FILTER_VALIDATE_INT)) {
                $employeeErr = "Only integer numbers allowed";
              }
            }
            if (empty($_POST["location"])) {
              $locationErr = "Location is required";
            } else {
              $location = test_input($_POST["location"]);
            }
            if (empty($_POST["website"])) {
              $websiteErr = "Website is required";
            } else {
              $website = test_input($_POST["website"]);
              if (!filter_var($website, FILTER_VALIDATE_URL)) {
                $websiteErr = "Invalid URL format";
              }
            }
            if (empty($_POST["openPosition"])) {
              $openPositionErr = "Open position is required";
            } else {
              $openPosition = test_input($_POST["openPosition"]);
              if (!filter_var($openPosition, FILTER_VALIDATE_INT)) {
                $openPositionErr = "Only integer numbers allowed";
              }
            }
            if (empty($_POST["description"])) {
              $descriptionErr = "Description is required";
            } else {
              $description = test_input($_POST["description"]);
            }

            // If no errors, update or insert into DB
            if (empty($companyNameErr) && empty($typeErr) && empty($employeeErr) && empty($locationErr) && empty($websiteErr) && empty($openPositionErr) && empty($descriptionErr)) {
              // Check if user already has info
              $stmt = $conn->prepare("SELECT id FROM companyUsers WHERE username = ? LIMIT 1");
              $stmt->bind_param("s", $loggedInUser);
              $stmt->execute();
              $stmt->store_result();
              if ($stmt->num_rows > 0) {
                // Update
                $stmt->close();
                $updateStmt = $conn->prepare("UPDATE companyUsers SET companyName=?, type=?, employee=?, location=?, website=?, openPosition=?, description=? WHERE username=?");
                $updateStmt->bind_param("ssississ", $companyName, $type, $employee, $location, $website, $openPosition, $description, $loggedInUser);
                if ($updateStmt->execute()) {
                  $successMsg = "Information updated successfully!";
                } else {
                  $successMsg = "Error updating information: " . $updateStmt->error;
                }
                $updateStmt->close();
              } else {
                // Insert
                $stmt->close();
                $insertStmt = $conn->prepare("INSERT INTO companyUsers (username, companyName, type, employee, location, website, openPosition, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $insertStmt->bind_param("sssissis", $loggedInUser, $companyName, $type, $employee, $location, $website, $openPosition, $description);
                if ($insertStmt->execute()) {
                  $successMsg = "Information saved successfully!";
                } else {
                  $successMsg = "Error saving information: " . $insertStmt->error;
                }
                $insertStmt->close();
              }
            }
          }
          ?>
          <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="formGroup">
              <label>Company Name:</label>
              <input type="text" name="companyName" value="<?php echo $companyName; ?>">
              <span class="error">* <?php echo $companyNameErr;?></span>
            </div>
            <div class="formGroup">
              <label>Type:</label>
              <input type="text" name="type" value="<?php echo $type; ?>">
              <span class="error">* <?php echo $typeErr;?></span>
            </div>
            <div class="formGroup">
              <label>Employee:</label>
              <input type="number" name="employee" value="<?php echo $employee; ?>">
              <span class="error">* <?php echo $employeeErr;?></span>
            </div>
            <div class="formGroup">
              <label>Company Location:</label>
              <input type="text" name="location" value="<?php echo $location; ?>">
              <span class="error">* <?php echo $locationErr;?></span>
            </div>
            <div class="formGroup">
              <label>Website:</label>
              <input type="url" name="website" value="<?php echo $website; ?>">
              <span class="error">* <?php echo $websiteErr;?></span>
            </div>
            <div class="formGroup">
              <label>Open Position:</label>
              <input type="number" name="openPosition" value="<?php echo $openPosition; ?>">
              <span class="error">* <?php echo $openPositionErr;?></span>
            </div>
            <div class="formGroup">
              <label>Description:</label>
              <textarea name="description" rows="3"><?php echo $description; ?></textarea>
              <span class="error">* <?php echo $descriptionErr;?></span>
            </div>
            <button type="submit" class="btn">Save Information</button>
          </form>
        </div>
      </div>
    </main>
    <!-- Footer -->
   <!-- Footer -->
                <link rel="stylesheet" href="jsdash.css">
    <?php include 'footer.php'; ?>
    <script src="../js/information.js"></script>
  </body>
</html>
