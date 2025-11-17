<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Post Job - CareerConnect</title>
    <link rel="stylesheet" href="postjob.css" />
    <script>
      function toggleJobStatus(jobId, currentStatus, buttonElement) {
        // Prevent form submission
        event.preventDefault();
        
        // Show loading state
        const originalText = buttonElement.textContent;
        buttonElement.textContent = 'Updating...';
        buttonElement.disabled = true;
        
        // Create form data
        const formData = new FormData();
        formData.append('job_id', jobId);
        formData.append('current_status', currentStatus);
        formData.append('toggle_status', 'true');
        
        // Send AJAX request
        fetch('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', {
          method: 'POST',
          body: formData
        })
        .then(response => response.text())
        .then(data => {
          // Update button text and status
          const newStatus = currentStatus === 'active' ? 'deactive' : 'active';
          buttonElement.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
          buttonElement.setAttribute('onclick', `toggleJobStatus(${jobId}, '${newStatus}', this)`);
          buttonElement.disabled = false;
          
          // Update button color based on status
          if (newStatus === 'active') {
            buttonElement.style.background = '#e0f7ea';
            buttonElement.style.color = '#059669';
          } else {
            buttonElement.style.background = '#ffe0e0';
            buttonElement.style.color = '#dc2626';
          }
        })
        .catch(error => {
          console.error('Error:', error);
          buttonElement.textContent = originalText;
          buttonElement.disabled = false;
          alert('Error updating status. Please try again.');
        });
      }

      function loadJobForEdit(jobId) {
          // Show loading state in form
          const submitBtn = document.querySelector('#jobForm button[type="submit"]');
          const originalText = submitBtn.textContent;
          submitBtn.textContent = 'Loading...';
          submitBtn.disabled = true;

          // Fetch job data
          fetch('get_job_details.php?id=' + jobId)
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // Fill form with job data
                document.getElementById('jobTitle').value = data.job.jobTitle;
                document.getElementById('jobDescription').value = data.job.jobDescription;
                document.getElementById('jobLocation').value = data.job.jobLocation;
                document.getElementById('jobType').value = data.job.jobType;
                document.getElementById('jobCategory').value = data.job.jobCategory;
                document.getElementById('applicationDeadline').value = data.job.applicationDeadline;

                // Remove any previous edit_job_id hidden input
                const oldEditInput = document.querySelector('#jobForm input[name="edit_job_id"]');
                if (oldEditInput) oldEditInput.remove();
                // Add hidden input for edit_job_id
                const editInput = document.createElement('input');
                editInput.type = 'hidden';
                editInput.name = 'edit_job_id';
                editInput.value = jobId;
                document.getElementById('jobForm').appendChild(editInput);

                // Change form to update mode
                submitBtn.textContent = 'Update Job';
                document.querySelector('h2.sectionTitle').textContent = 'Edit Job';

                // Add cancel button if not present
                if (!document.querySelector('#jobForm button.cancel-edit')) {
                  const cancelBtn = document.createElement('button');
                  cancelBtn.type = 'button';
                  cancelBtn.className = 'btn btnOutline cancel-edit';
                  cancelBtn.textContent = 'Cancel Edit';
                  cancelBtn.onclick = function() {
                    document.getElementById('jobForm').reset();
                    if (editInput) editInput.remove();
                    submitBtn.textContent = 'Post Job';
                    document.querySelector('h2.sectionTitle').textContent = 'Post a New Job';
                    cancelBtn.remove();
                  };
                  document.getElementById('jobForm').appendChild(cancelBtn);
                }

                // Scroll to form
                document.getElementById('jobForm').scrollIntoView({ behavior: 'smooth' });
              } else {
                alert('Error loading job details: ' + data.message);
              }
              submitBtn.textContent = originalText;
              submitBtn.disabled = false;
            })
            .catch(error => {
              console.error('Error:', error);
              alert('Error loading job details. Please try again.');
              submitBtn.textContent = originalText;
              submitBtn.disabled = false;
            });
        }
    </script>
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
              <li>
                <a
                  href="dashboard.php"
                  class="nav-link"
                  data-target="dashboard"
                  >Dashboard</a
                >
              </li>
              <li>
                <a
                  href="postjob.php"
                  class="nav-link active"
                  data-target="post-job"
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
                <a href="interviews.php" class="nav-link" data-target="schedule"
                  >Schedule Interviews</a
                >
              </li>
              <li>
                <a
                  href="information.php"
                  class="nav-link"
                  data-target="dashboard"
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
    <main class="dashboard">
      <div class="container">
        <div id="post-job" class="tabContent active">
          <h2 class="sectionTitle"><?php echo isset($_POST['edit_job_id']) ? 'Edit Job' : 'Post a New Job'; ?></h2>
          <div class="card">
            <?php
            // Database connection (W3Schools procedural style)
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "careerconnect";
 
            // Create connection
            $conn = mysqli_connect($servername, $username, $password, $dbname);
            // Check connection
            if (!$conn) {
              die("Connection failed: " . mysqli_connect_error());
            }
 
            // Create table if not exists
            $sql = "CREATE TABLE IF NOT EXISTS postjob (
              id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              companyName VARCHAR(100) NOT NULL,
              jobTitle VARCHAR(100) NOT NULL,
              jobDescription TEXT NOT NULL,
              jobLocation VARCHAR(100) NOT NULL,
              jobType VARCHAR(50) NOT NULL,
              jobCategory VARCHAR(50) NOT NULL,
              applicationDeadline VARCHAR(20) NOT NULL,
              jobstatus VARCHAR(10) NOT NULL DEFAULT 'active',
              reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            mysqli_query($conn, $sql);

            // Add jobstatus column if missing
            $checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM postjob LIKE 'jobstatus'");
            if (mysqli_num_rows($checkColumn) == 0) {
              mysqli_query($conn, "ALTER TABLE postjob ADD COLUMN jobstatus VARCHAR(10) NOT NULL DEFAULT 'active'");
            }
 
            $jobTitleErr = $jobDescriptionErr = $jobLocationErr = $jobTypeErr = $jobCategoryErr = $applicationDeadlineErr = "";
            $jobTitle = $jobDescription = $jobLocation = $jobType = $jobCategory = $applicationDeadline = "";
            $edit_job_id = "";
 
            
            // Handle AJAX status toggle request
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_status']) && isset($_POST['job_id'])) {
              $job_id = intval($_POST['job_id']);
              $current_status = $_POST['current_status'] === 'active' ? 'deactive' : 'active';
              
              $sql = "UPDATE postjob SET jobstatus='" . mysqli_real_escape_string($conn, $current_status) . "' WHERE id=$job_id";
              if (mysqli_query($conn, $sql)) {
                // If this is an AJAX request, just exit without outputting HTML
                if (isset($_POST['toggle_status'])) {
                  exit; // Stop further execution for AJAX requests
                }
              } else {
                if (isset($_POST['toggle_status'])) {
                  http_response_code(500);
                  exit;
                }
              }
            }
            
            // Handle main form submission - both create and update
            if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['toggle_status'])) {
              // Check if this is an edit operation
              $is_edit = isset($_POST['edit_job_id']) && !empty($_POST['edit_job_id']);
              $edit_job_id = $is_edit ? $_POST['edit_job_id'] : '';
              
              // Validation
              if (empty($_POST["jobTitle"])) {
                $jobTitleErr = "Job Title is required";
              } else {
                $jobTitle = htmlspecialchars($_POST["jobTitle"]);
              }
              if (empty($_POST["jobDescription"])) {
                $jobDescriptionErr = "Job Description is required";
              } else {
                $jobDescription = htmlspecialchars($_POST["jobDescription"]);
              }
              if (empty($_POST["jobLocation"])) {
                $jobLocationErr = "Location is required";
              } else {
                $jobLocation = htmlspecialchars($_POST["jobLocation"]);
              }
              if (empty($_POST["jobType"])) {
                $jobTypeErr = "Job Type is required";
              } else {
                $jobType = htmlspecialchars($_POST["jobType"]);
              }
              if (empty($_POST["jobCategory"])) {
                $jobCategoryErr = "Category is required";
              } else {
                $jobCategory = htmlspecialchars($_POST["jobCategory"]);
              }
              if (empty($_POST["applicationDeadline"])) {
                $applicationDeadlineErr = "Application Deadline is required";
              } else {
                $applicationDeadline = test_input($_POST["applicationDeadline"]);
                if (!preg_match("/^\d{2}\/\d{2}\/\d{4}$/", $applicationDeadline)) {
                  $applicationDeadlineErr = "Invalid date format. Use dd/mm/yyyy.";
                } else {
                  list($day, $month, $year) = explode('/', $applicationDeadline);
                  if (!checkdate((int)$month, (int)$day, (int)$year)) {
                    $applicationDeadlineErr = "Invalid date value.";
                  } else {
                    // Check if date is not in the past
                    $inputDate = DateTime::createFromFormat('d/m/Y', $applicationDeadline);
                    $today = new DateTime();
                    $today->setTime(0, 0, 0);
                    if ($inputDate) {
                      $inputDate->setTime(0, 0, 0);
                      if ($inputDate < $today) {
                        $applicationDeadlineErr = "Application deadline cannot be in the past";
                      }
                    } else {
                      $applicationDeadlineErr = "Invalid date format";
                    }
                  }
                }
              }
 
              // If no errors, insert or update into database
              if (
                empty($jobTitleErr) && empty($jobDescriptionErr) && empty($jobLocationErr) &&
                empty($jobTypeErr) && empty($jobCategoryErr) && empty($applicationDeadlineErr)
              ) {
                $companyName = isset($_SESSION['username']) ? $_SESSION['username'] : '';
                
                if ($is_edit) {
                  // Update existing job
                  $sql = "UPDATE postjob SET jobTitle=?, jobDescription=?, jobLocation=?, jobType=?, jobCategory=?, applicationDeadline=? WHERE id=? AND companyName=?";
                  $stmt = mysqli_prepare($conn, $sql);
                  mysqli_stmt_bind_param($stmt, "ssssssis", $jobTitle, $jobDescription, $jobLocation, $jobType, $jobCategory, $applicationDeadline, $edit_job_id, $companyName);
                  if (mysqli_stmt_execute($stmt)) {
                    echo "<span style='color:green;'>Job updated successfully!</span>";
                    // Clear edit mode
                    $edit_job_id = "";
                    // Optionally clear form fields
                    $jobTitle = $jobDescription = $jobLocation = $jobType = $jobCategory = $applicationDeadline = "";
                  } else {
                    echo "<span style='color:red;'>Error updating job: " . mysqli_error($conn) . "</span>";
                  }
                } else {
                  // Insert new job
                  $sql = "INSERT INTO postjob (companyName, jobTitle, jobDescription, jobLocation, jobType, jobCategory, applicationDeadline)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
                  $stmt = mysqli_prepare($conn, $sql);
                  mysqli_stmt_bind_param($stmt, "sssssss", $companyName, $jobTitle, $jobDescription, $jobLocation, $jobType, $jobCategory, $applicationDeadline);
                  if (mysqli_stmt_execute($stmt)) {
                    echo "<span style='color:green;'>Job posted successfully!</span>";
                    // Optionally clear form fields
                    $jobTitle = $jobDescription = $jobLocation = $jobType = $jobCategory = $applicationDeadline = "";
                  } else {
                    echo "<span style='color:red;'>Error: " . mysqli_error($conn) . "</span>";
                  }
                }
                mysqli_stmt_close($stmt);
              }
            }
            
            mysqli_close($conn);
 
            function test_input($data) {
              $data = trim($data);
              $data = stripslashes($data);
              $data = htmlspecialchars($data);
              return $data;
            }
            ?>
            <form id="jobForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" novalidate>
              <?php if (!empty($edit_job_id)): ?>
                <input type="hidden" name="edit_job_id" value="<?php echo htmlspecialchars($edit_job_id); ?>">
              <?php endif; ?>
              
              <div class="formGroup">
                <label for="jobTitle">Job Title</label>
                <input
                  type="text"
                  id="jobTitle"
                  name="jobTitle"
                  class="formControl"
                  placeholder="e.g. Senior Frontend Developer"
                  required
                  pattern=".{3,}"
                  value="<?php echo htmlspecialchars($jobTitle); ?>"
                />
                <span class="error" style="color:red;"><?php echo $jobTitleErr;?></span>
              </div>
              <div class="formGroup">
                <label for="jobDescription">Job Description</label>
                <textarea
                  id="jobDescription"
                  name="jobDescription"
                  class="formControl"
                  placeholder="Describe the role, responsibilities, and requirements..."
                  required
                ><?php echo htmlspecialchars($jobDescription); ?></textarea>
                <span class="error" style="color:red;"><?php echo $jobDescriptionErr;?></span>
              </div>
              <div class="formGroup">
                <label for="jobLocation">Location</label>
                <input
                  type="text"
                  id="jobLocation"
                  name="jobLocation"
                  class="formControl"
                  placeholder="e.g. New York, NY or Remote"
                  required
                  pattern=".{2,}"
                  value="<?php echo htmlspecialchars($jobLocation); ?>"
                />
                <span class="error" style="color:red;"><?php echo $jobLocationErr;?></span>
              </div>
              <div class="formGroup">
                <label for="jobType">Job Type</label>
                <select id="jobType" name="jobType" class="formControl" required>
                  <option value="">Select Job Type</option>
                  <option value="full-time" <?php if($jobType=="full-time") echo "selected"; ?>>Full Time</option>
                  <option value="part-time" <?php if($jobType=="part-time") echo "selected"; ?>>Part Time</option>
                  <option value="contract" <?php if($jobType=="contract") echo "selected"; ?>>Contract</option>
                  <option value="internship" <?php if($jobType=="internship") echo "selected"; ?>>Internship</option>
                </select>
                <span class="error" style="color:red;"><?php echo $jobTypeErr;?></span>
              </div>
              <div class="formGroup">
                <label for="jobCategory">Category</label>
                <select id="jobCategory" name="jobCategory" class="formControl" required>
                  <option value="">Select Category</option>
                  <option value="design" <?php if($jobCategory=="design") echo "selected"; ?>>Design</option>
                  <option value="development" <?php if($jobCategory=="development") echo "selected"; ?>>Development</option>
                  <option value="marketing" <?php if($jobCategory=="marketing") echo "selected"; ?>>Marketing</option>
                  <option value="sales" <?php if($jobCategory=="sales") echo "selected"; ?>>Sales</option>
                  <option value="support" <?php if($jobCategory=="support") echo "selected"; ?>>Customer Support</option>
                </select>
                <span class="error" style="color:red;"><?php echo $jobCategoryErr;?></span>
              </div>
              <div class="formGroup">
                <label for="applicationDeadline">Application Deadline</label>
                <input
                  type="text"
                  id="applicationDeadline"
                  name="applicationDeadline"
                  class="formControl"
                  placeholder="dd/mm/yyyy"
                  pattern="\d{2}/\d{2}/\d{4}"
                  required
                  value="<?php echo htmlspecialchars($applicationDeadline); ?>"
                />
                <span class="error" style="color:red;"><?php echo $applicationDeadlineErr;?></span>
              </div>
              <button type="submit" class="btn btnPrimary">
                <?php echo !empty($edit_job_id) ? 'Update Job' : 'Post Job'; ?>
              </button>
              
              <?php if (!empty($edit_job_id)): ?>
                <button type="button" class="btn btnOutline" onclick="cancelEdit()">Cancel Edit</button>
              <?php endif; ?>
            </form>
            
            <script>
              function cancelEdit() {
                // Clear form and reset to create mode
                document.getElementById('jobForm').reset();
                document.querySelector('input[name="edit_job_id"]').remove();
                document.querySelector('button[type="submit"]').textContent = 'Post Job';
                document.querySelector('h2.sectionTitle').textContent = 'Post a New Job';
                // Remove cancel button if exists
                const cancelBtn = document.querySelector('button[onclick="cancelEdit()"]');
                if (cancelBtn) cancelBtn.remove();
              }
            </script>
          </div>
          <h3 class="sectionTitle">Active Job Postings</h3>
          <div class="card">
            <div class="tableResponsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Job Title</th>
                    <th>Posted Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  // Show only jobs for logged-in user
                  $companyName = isset($_SESSION['username']) ? $_SESSION['username'] : '';
                  $conn = mysqli_connect($servername, $username, $password, $dbname);
                  if ($conn) {
                    $sql = "SELECT id, jobTitle, reg_date, jobstatus FROM postjob WHERE companyName = '" . mysqli_real_escape_string($conn, $companyName) . "' ORDER BY reg_date DESC";
                    $result = mysqli_query($conn, $sql);
                    if ($result && mysqli_num_rows($result) > 0) {
                      while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['jobTitle']) . '</td>';
                        $date = date('M j, Y', strtotime($row['reg_date']));
                        echo '<td>' . $date . '</td>';
                        echo '<td>';
                        
                        // Set button colors based on status
                        $buttonStyle = '';
                        if ($row['jobstatus'] === 'active') {
                          $buttonStyle = 'background:#e0f7ea;color:#059669;';
                        } else {
                          $buttonStyle = 'background:#ffe0e0;color:#dc2626;';
                        }
                        
                        // Use button with onclick instead of form
                        echo '<button type="button" class="status-btn" onclick="toggleJobStatus(' . $row['id'] . ', \'' . $row['jobstatus'] . '\', this)" style="' . $buttonStyle . 'padding:4px 12px;border:none;border-radius:12px;font-weight:500;min-width:80px;cursor:pointer;">' . ucfirst($row['jobstatus']) . '</button>';
                        echo '</td>';
                        echo '<td><button class="btn btnOutline" onclick="loadJobForEdit(' . $row['id'] . ')">Edit</button></td>';
                        echo '</tr>';
                      }
                    } else {
                      echo '<tr><td colspan="5">No jobs posted yet.</td></tr>';
                    }
                    mysqli_close($conn);
                  } else {
                    echo '<tr><td colspan="5">Could not connect to database.</td></tr>';
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
                <link rel="stylesheet" href="jsdash.css">
    <?php include 'footer.php'; ?>
  </body>
</html>