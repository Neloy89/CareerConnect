<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Schedule Interviews - CareerConnect</title>
    <link rel="stylesheet" href="interviews.css" />
  </head>
  <body>
    <?php
    $applicantErr = $dateErr = $timeErr = $typeErr = $locationErr = $interviewersErr = $positionErr = "";
    $applicant = isset($_GET['applicant']) ? htmlspecialchars($_GET['applicant']) : "";
    $position = isset($_GET['position']) ? htmlspecialchars($_GET['position']) : "";
    $applicant_username = isset($_GET['username']) ? htmlspecialchars($_GET['username']) : ""; // applicant's username
    $date = $time = $type = $location = $interviewers = $notes = "";
    $edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;

    // If editing, fetch interview data
    if ($edit_id > 0) {
      $conn = mysqli_connect('localhost', 'root', '', 'careerconnect');
      if ($conn) {
        $sessionUsername = $_SESSION['username'] ?? '';
        $result = mysqli_query($conn, "SELECT * FROM interviews WHERE id = $edit_id AND company_username = '" . mysqli_real_escape_string($conn, $sessionUsername) . "' LIMIT 1");
        if ($result && $row = mysqli_fetch_assoc($result)) {
          $applicant = $row['applicant'];
          $position = $row['position'];
          $date = date('d/m/Y', strtotime($row['interview_date']));
          $time = date('H:i', strtotime($row['interview_time']));
          $type = $row['interview_type'];
          $location = $row['location'];
          $interviewers = $row['interviewers'];
          $notes = $row['notes'];
          $applicant_username = $row['username']; // applicant's username from DB
        }
        mysqli_close($conn);
      }
    }
    // Form submission handling

     function test_input($data) {
          $data = trim($data);
          $data = stripslashes($data);
          $data = htmlspecialchars($data);
          return $data;
        }
    

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $edit_id_post = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;

        if (empty($_POST["applicantSelect"])) {
          $applicantErr = "Applicant is required";
        } else {
          $applicant = test_input($_POST["applicantSelect"]);
        }
        // Position validation (letters, spaces, dash only, 2-50 chars)
        if (empty($_POST["position"])) {
          $positionErr = "Position is required";
        } else {
          $position = test_input($_POST["position"]);
          if (!preg_match("/^[a-zA-Z \-]{2,50}$/", $position)) {
            $positionErr = "Position must be 2-50 characters, letters, spaces, dash only.";
          }
        }

        if (empty($_POST["interviewDate"])) {
            $dateErr = "Date is required";
        } else {
            $date = test_input($_POST["interviewDate"]);
            if (!preg_match("/^\d{2}\/\d{2}\/\d{4}$/", $date)) {
                $dateErr = "Invalid date format. Use dd/mm/yyyy.";
            } else {
                list($day, $month, $year) = explode('/', $date);
                if (!checkdate((int)$month, (int)$day, (int)$year)) {
                    $dateErr = "Invalid date value.";
                } else {
                    // Check if date is not in the past
                    $inputDate = DateTime::createFromFormat('d/m/Y', $date);
                    $today = new DateTime();
                    $today->setTime(0, 0, 0);
                    if ($inputDate) {
                        $inputDate->setTime(0, 0, 0);
                        if ($inputDate < $today) {
                            $dateErr = "Interview date cannot be in the past";
                        }
                    } else {
                        $dateErr = "Invalid date format";
                    }
                  
                }
            }
        }

        if (empty($_POST["interviewTime"])) {
            $timeErr = "Time is required";
        } else {
            $time = test_input($_POST["interviewTime"]);
            if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
                $timeErr = "Invalid time format (HH:MM)";
            }
        }

        if (empty($_POST["interviewType"])) {
            $typeErr = "Type is required";
        } else {
            $type = test_input($_POST["interviewType"]);
        }

        if (empty($_POST["interviewLocation"])) {
            $locationErr = "Location/Details is required";
        } else {
            $location = test_input($_POST["interviewLocation"]);
            if (strlen($location) < 3) {
                $locationErr = "Location must be at least 3 characters";
            }
        }

        if (empty($_POST["interviewers"])) {
            $interviewersErr = "Interviewers are required";
        } else {
            $interviewers = test_input($_POST["interviewers"]);
            if (!preg_match('/^[a-zA-Z ,.-]{3,}$/', $interviewers)) {
                $interviewersErr = "Enter valid names (letters, comma, dot, dash)";
            }
        }

        $notes = test_input($_POST["interviewNotes"] ?? "");


        ///////////////database connection//////////
        // Database connection and creation logic (procedural mysqli)
        $servername = "localhost";
        $db_username = "root";
        $password = "";
        $dbname = "careerconnect";

        // 3. Now connect to the database
        $conn = mysqli_connect($servername, $db_username, $password, $dbname);
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        $sql = "CREATE TABLE IF NOT EXISTS interviews (
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

          if (mysqli_query($conn, $sql)) {
              // Table created successfully or already exists
          } else {
              echo "Error creating table: " . mysqli_error($conn);
          }


          // 4. Insert interview data if no validation errors
          if (empty($applicantErr) && empty($dateErr) && empty($timeErr) && empty($typeErr) && empty($locationErr) && empty($interviewersErr) && empty($positionErr)) {
            $company_username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
            $applicant_username = isset($_POST['username']) ? test_input($_POST['username']) : '';
            // Convert date to YYYY-MM-DD format for MySQL
            $dateParts = explode('/', $date);
            if (count($dateParts) === 3) {
              $formattedDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
            } else {
              $formattedDate = null; // Invalid date format
          }

          if ($edit_id_post > 0) {
            // Update existing interview
            $stmt = mysqli_prepare($conn, "UPDATE interviews SET applicant=?, position=?, interview_date=?, interview_time=?, interview_type=?, location=?, interviewers=?, notes=?, username=?, company_username=? WHERE id=?");
            if ($stmt === false) {
              die("Prepare failed: " . htmlspecialchars(mysqli_error($conn)));
            }
            mysqli_stmt_bind_param($stmt, "ssssssssssi", $applicant, $position, $formattedDate, $time, $type, $location, $interviewers, $notes, $applicant_username, $company_username, $edit_id_post);
            if (mysqli_stmt_execute($stmt)) {
              echo "<script>alert('Interview updated successfully!'); window.location.href='interviews.php';</script>";
              $applicant = $date = $time = $type = $location = $interviewers = $notes = "";
            } else {
              echo "Execute failed: " . htmlspecialchars(mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
            } else {
            // Insert new interview
            $stmt = mysqli_prepare($conn, "INSERT INTO interviews (applicant, position, interview_date, interview_time, interview_type, location, interviewers, notes, username, company_username) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt === false) {
              die("Prepare failed: " . htmlspecialchars(mysqli_error($conn)));
            }
            mysqli_stmt_bind_param($stmt, "ssssssssss", $applicant, $position, $formattedDate, $time, $type, $location, $interviewers, $notes, $applicant_username, $company_username);
            if (mysqli_stmt_execute($stmt)) {
              echo "<script>alert('Interview scheduled successfully!'); window.location.href='interviews.php';</script>";
              $applicant = $date = $time = $type = $location = $interviewers = $notes = "";
            } else {
              echo "Execute failed: " . htmlspecialchars(mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
          }



          mysqli_close($conn);
          ///////////database connection end//////////

    }
    } 
?>

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
              <li>
                <a
                  href="dashboard.php" class="nav-link" data-target="dashboard">Dashboard</a
                >
              </li>
              <li>
                <a href="postjob.php" class="nav-link" data-target="post-job"
                  >Post Job</a
                >
              </li>
              <li>
                <a
                  href="applications.php"
                  class="nav-link"
                  data-target="applications"
                  >Applications</a
                >
              </li>
              <li>
                <a
                  href="interviews.php"
                  class="nav-link active"
                  data-target="schedule"
                  >Interviews</a
                >
              </li>
               <li>
                <a href="information.php" class="nav-link" data-target="information"
                  >Informations</a
                >
              </li>
            </ul>
          </nav>
          <div class="userActions">
            <span>Welcome, <strong><?php
            $displayName = 'Company Guest Admin';
            if (isset($_SESSION['username'])) {
              $conn = mysqli_connect('localhost', 'root', '', 'careerconnect');
              if ($conn) {
                $username_company = mysqli_real_escape_string($conn, $_SESSION['username']);
                $result = mysqli_query($conn, "SELECT companyName FROM companyUsers WHERE username = '$username_company' LIMIT 1");
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
    <main class="dashboard">
      <div class="container">
        <div id="schedule" class="tabContent active">
          <h2 class="sectionTitle">Schedule Interviews</h2>
          <div class="card">
      <form
        id="interviewForm"
        class="interviewForm"
        method="post"
        action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?><?php if($edit_id) echo '?edit_id=' . $edit_id; ?>"
        novalidate
      >
  <input type="hidden" name="username" value="<?php echo htmlspecialchars($applicant_username); ?>" />
  <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>" />
        <div class="formGroup">
          <label for="applicantSelect">Applicant</label>
          <input
            type="text"
            id="applicantSelect"
            name="applicantSelect"
            class="formControl"
            placeholder="Enter applicant name"
            required
            value="<?php echo $applicant; ?>"
            readonly
          />
          <span class="error" style="color:red; font-size:14px;">* <?php echo $applicantErr;?></span>
        </div>
        <div class="formGroup">
          <label for="position">Position</label>
          <input
            type="text"
            id="position"
            name="position"
            class="formControl"
            placeholder="Enter position (e.g. Frontend Developer)"
            required
            pattern="[a-zA-Z \-]{2,50}"
            minlength="2"
            maxlength="50"
            value="<?php echo htmlspecialchars($position); ?>"
            readonly
          />
          <span class="error" style="color:red; font-size:14px;">* <?php echo $positionErr;?></span>
        </div>
                <div class="formGroup">
                    <label for="interviewDate">Interview Date</label>
                    <input
                        type="text"
                        id="interviewDate"
                        name="interviewDate"
                        class="formControl"
                        placeholder="dd/mm/yyyy"
                        pattern="\d{2}/\d{2}/\d{4}"
                        required
                        value="<?php echo htmlspecialchars($date); ?>"
                    />
                    <span class="error" style="color:red; font-size:14px;">* <?php echo $dateErr;?></span>
                </div>
                <div class="formGroup">
                    <label for="interviewTime">Interview Time</label>
                    <input
                        type="time"
                        id="interviewTime"
                        name="interviewTime"
                        class="formControl"
                        value="<?php echo $time; ?>"
                        pattern="^([01]?[0-9]|2[0-3]):[0-5][0-9]$"
                    />
                    <span class="error" style="color:red; font-size:14px;">* <?php echo $timeErr;?></span>
                </div>
                <div class="formGroup">
                    <label for="interviewType">Interview Type</label>
                    <select
                        id="interviewType"
                        name="interviewType"
                        class="formControl"
                       
                    >
                        <option value="">Select Type</option>
                        <option value="in-person" <?php if($type=="in-person") echo "selected"; ?>>In-Person</option>
                        <option value="video" <?php if($type=="video") echo "selected"; ?>>Video Call</option>
                        <option value="phone" <?php if($type=="phone") echo "selected"; ?>>Phone Call</option>
                    </select>
                    <span class="error" style="color:red; font-size:14px;">* <?php echo $typeErr;?></span>
                </div>
                <div class="formGroup">
                    <label for="interviewLocation">Location/Details</label>
                    <input
                        type="text"
                        id="interviewLocation"
                        name="interviewLocation"
                        class="formControl"
                        placeholder="Office address or meeting link"
                        value="<?php echo $location; ?>"
                        pattern=".{3,}"
                    />
                    <span class="error" style="color:red; font-size:14px;">* <?php echo $locationErr;?></span>
                </div>
                <div class="formGroup">
                    <label for="interviewers">Interviewers</label>
                    <input
                        type="text"
                        id="interviewers"
                        name="interviewers"
                        class="formControl"
                        placeholder="Names of interviewers"
                        value="<?php echo $interviewers; ?>"
                        pattern="[a-zA-Z ,.-]{3,}"
                    />
                    <span class="error" style="color:red; font-size:14px;">* <?php echo $interviewersErr;?></span>
                </div>
                <div class="formGroup" style="grid-column: 1 / -1">
                    <label for="interviewNotes">Additional Notes</label>
                    <textarea
                        id="interviewNotes"
                        name="interviewNotes"
                        class="formControl"
                        placeholder="Any special instructions for the candidate..."
                    ><?php echo $notes; ?></textarea>
                </div>
                <div class="formGroup" style="grid-column: 1 / -1">
                    <button type="submit" class="btn btnPrimary">
                        Schedule Interview
                    </button>
                </div>
            </form>
          </div>
          <h3 class="sectionTitle">Upcoming Interviews</h3>
          <div class="card">
            <div class="tableResponsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Applicant</th>
                    <th>Position</th>
                    <th>Date & Time</th>
                    <th>Type</th>
                    <th>Interviewer</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $conn = mysqli_connect("localhost", "root", "", "careerconnect");
                  $sessionUsername = $_SESSION['username'] ?? '';

                  // Handle delete action
                  if (isset($_POST['delete_interview_id'])) {
                    $deleteId = intval($_POST['delete_interview_id']);
                    $deleteQuery = "DELETE FROM interviews WHERE id = $deleteId AND company_username = '" . mysqli_real_escape_string($conn, $sessionUsername) . "'";
                    mysqli_query($conn, $deleteQuery);
                  }

                  // Fetch interviews for this company
                  if ($conn && $sessionUsername) {
                    $query = "SELECT id, applicant, position, interview_date, interview_time, interview_type, interviewers FROM interviews WHERE company_username = '" . mysqli_real_escape_string($conn, $sessionUsername) . "' ORDER BY interview_date, interview_time";
                    $result = mysqli_query($conn, $query);
                    if ($result && mysqli_num_rows($result) > 0) {
                      while ($row = mysqli_fetch_assoc($result)) {
                        // Format date & time
                        $date = date('d/m/Y', strtotime($row['interview_date']));
                        $time = date('H:i', strtotime($row['interview_time']));
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['applicant']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['position']) . '</td>';
                        echo '<td>' . $date . ' - ' . $time . '</td>';
                        echo '<td>' . htmlspecialchars($row['interview_type']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['interviewers']) . '</td>';
                        echo '<td>';
                        echo '<form method="post" style="display:inline;">';
                        echo '<input type="hidden" name="delete_interview_id" value="' . intval($row['id']) . '">';
                        echo '<button type="submit" class="btn btnDanger" onclick="return confirm(\'Are you sure you want to delete this interview?\');">Delete</button>';
                        echo '</form> ';
                        echo '<button class="btn btnPrimary" onclick="window.location.href=\'interviews.php?edit_id=' . intval($row['id']) . '\'" style="margin-left:6px;">Edit</button>';
                        echo '</td>';
                        echo '</tr>';
                      }
                    } else {
                      echo '<tr><td colspan="6">No upcoming interviews found.</td></tr>';
                    }
                  } else {
                    echo '<tr><td colspan="6">Database connection error or session missing.</td></tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>
    <!-- Footer -->
    <!-- Footer -->
                <link rel="stylesheet" href="jsdash.css">
    <?php include 'footer.php'; ?>
    <script src="interviews.js"></script>
  </body>
</html>