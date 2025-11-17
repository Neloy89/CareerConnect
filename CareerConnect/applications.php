<?php
// Handle AJAX request to update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reviewed') {
  $appid = isset($_POST['appid']) ? intval($_POST['appid']) : 0;
  $jobid = isset($_POST['jobid']) ? intval($_POST['jobid']) : 0;
  $applicant = isset($_POST['applicant']) ? $_POST['applicant'] : '';
  $position = isset($_POST['position']) ? $_POST['position'] : '';
  $conn = mysqli_connect('localhost', 'root', '', 'careerconnect');
  if ($conn && $appid && $jobid && $applicant && $position) {
    $applicant = mysqli_real_escape_string($conn, $applicant);
    $position = mysqli_real_escape_string($conn, $position);
    $updateStatus = mysqli_query($conn, "UPDATE status SET status='Reviewed' WHERE jobId='$jobid' AND applicant='$applicant' AND position='$position'");
    if ($updateStatus) {
      echo json_encode(['success' => true]);
    } else {
      echo json_encode(['success' => false, 'error' => 'DB update failed']);
    }
    mysqli_close($conn);
  } else {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
  }
  exit;
}
// Handle resume download
if (isset($_GET['download_resume'])) {
  $id = intval($_GET['download_resume']);
  $conn = mysqli_connect('localhost', 'root', '', 'careerconnect');
  if ($conn) {
    $sql = "SELECT resume FROM applications WHERE id=$id LIMIT 1";
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
      $file = $row['resume']; // Use full path from DB
      if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
      } else {
        echo "File not found: " . htmlspecialchars($file);
        exit;
      }
    }
    mysqli_close($conn);
  }
}
?>
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Applications - CareerConnect</title>
    <link rel="stylesheet" href="applications.css" />
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
                <a href="postjob.php" class="nav-link" data-target="post-job"
                  >Post Job</a
                >
              </li>
              <li>
                <a
                  href="applications.php"
                  class="nav-link active"
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
                  <a href="information.php" class="nav-link" data-target="information">Informations</a>
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
        
    </header>
    <main class="dashboard">
      <div class="container">
        <div id="applications" class="tabContent active">
          <h2 class="sectionTitle">Job Applications</h2>
          <div class="card">
            <div class="filterSection">
              <div class="filterItem">
                <label for="jobFilter">Filter by Job</label>
                <form method="get" id="filterForm">
                  <select name="jobFilter" id="jobFilter" class="formControl" onchange="document.getElementById('filterForm').submit();">
                    <option value="">All Jobs</option>
                    <?php
                    $filterConn = mysqli_connect('localhost', 'root', '', 'careerconnect');
                    $sessionUsername = $_SESSION['username'] ?? '';
                    if ($filterConn && $sessionUsername) {
                      $filterQuery = "SELECT DISTINCT position FROM status WHERE company_username = '" . mysqli_real_escape_string($filterConn, $sessionUsername) . "'";
                      $filterResult = mysqli_query($filterConn, $filterQuery);
                      $selectedJob = isset($_GET['jobFilter']) ? $_GET['jobFilter'] : '';
                      if ($filterResult && mysqli_num_rows($filterResult) > 0) {
                        while ($filterRow = mysqli_fetch_assoc($filterResult)) {
                          $pos = $filterRow['position'];
                          $selected = ($selectedJob === $pos) ? 'selected' : '';
                          echo '<option value="' . htmlspecialchars($pos) . '" ' . $selected . '>' . htmlspecialchars($pos) . '</option>';
                        }
                      }
                    }
                    if ($filterConn) mysqli_close($filterConn);
                    ?>
                  </select>
                </form>
              </div>
              
              <div class="filterItem">
                <label for="search">Search</label>
                <form method="get" id="searchForm">
                  <input type="text" name="search" id="search" class="formControl" placeholder="Search by applicator name..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" oninput="document.getElementById('searchForm').submit();" />
                  <?php
                  // Preserve job filter in search form
                  if (isset($_GET['jobFilter'])) {
                    echo '<input type="hidden" name="jobFilter" value="' . htmlspecialchars($_GET['jobFilter']) . '" />';
                  }
                  ?>
                </form>
              </div>
            </div>
            <div class="tableResponsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Applicant</th>
                    <th>Position</th>
                    <th>Applied On</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  // DB connection
                  $servername = "localhost";
                  $username = "root";
                  $password = "";
                  $dbname = "careerconnect";
                  $conn = mysqli_connect($servername, $username, $password, $dbname);
                  if (!$conn) {
                    die("Connection failed: " . mysqli_connect_error());
                  }
                  // Create status table if not exists
                  $sqlStatusTable = "CREATE TABLE IF NOT EXISTS status (
                    jobId INT(11) NOT NULL,
                    applicant VARCHAR(100) NOT NULL,
                    position VARCHAR(100) NOT NULL,
                    status VARCHAR(20) NOT NULL DEFAULT 'New',
                    username VARCHAR(100) NOT NULL ,
                    company_username VARCHAR(100) NOT NULL,
                    applied_at DATETIME NOT NULL
                  )";
                  mysqli_query($conn, $sqlStatusTable);

                  $company_username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
                  $sql = "SELECT a.*, p.jobTitle FROM applications a JOIN postjob p ON a.jobId = p.id WHERE a.company_username = '" . mysqli_real_escape_string($conn, $company_username) . "'";
                  $selectedJob = isset($_GET['jobFilter']) ? $_GET['jobFilter'] : '';
                  if ($selectedJob) {
                    $sql .= " AND p.jobTitle = '" . mysqli_real_escape_string($conn, $selectedJob) . "'";
                  }
                  $searchApplicant = isset($_GET['search']) ? trim($_GET['search']) : '';
                  if ($searchApplicant !== '') {
                    // Get matching applicants from status table
                    $searchSql = "SELECT DISTINCT applicant FROM status WHERE company_username = '" . mysqli_real_escape_string($conn, $company_username) . "' AND applicant LIKE '%" . mysqli_real_escape_string($conn, $searchApplicant) . "%'";
                    $searchResult = mysqli_query($conn, $searchSql);
                    $applicantsArr = [];
                    if ($searchResult && mysqli_num_rows($searchResult) > 0) {
                      while ($searchRow = mysqli_fetch_assoc($searchResult)) {
                        $applicantsArr[] = mysqli_real_escape_string($conn, $searchRow['applicant']);
                      }
                    }
                    if (count($applicantsArr) > 0) {
                      $sql .= " AND a.fullName IN ('" . implode("','", $applicantsArr) . "')";
                    } else {
                      $sql .= " AND 0";
                    }
                  }
                  $sql .= " ORDER BY a.applied_at DESC";
                  $result = mysqli_query($conn, $sql);
                  if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Check if interview is scheduled for this applicant/position/company
                        $checkInterview = mysqli_query($conn, "SELECT * FROM interviews WHERE applicant = '" . mysqli_real_escape_string($conn, $row['fullName']) . "' AND position = '" . mysqli_real_escape_string($conn, $row['jobTitle']) . "' AND username = '" . mysqli_real_escape_string($conn, $row['username']) . "' AND company_username = '" . mysqli_real_escape_string($conn, $company_username) . "'");
                        if (mysqli_num_rows($checkInterview) > 0) {
                          // Interview scheduled, update status table if needed
                          $checkStatus = mysqli_query($conn, "SELECT * FROM status WHERE jobId = '" . $row['jobId'] . "' AND applicant = '" . mysqli_real_escape_string($conn, $row['fullName']) . "' AND position = '" . mysqli_real_escape_string($conn, $row['jobTitle']) . "'");
                          if (mysqli_num_rows($checkStatus) > 0) {
                            $statusRow = mysqli_fetch_assoc($checkStatus);
                            if ($statusRow['status'] !== 'Scheduled') {
                              mysqli_query($conn, "UPDATE status SET status='Scheduled' WHERE jobId = '" . $row['jobId'] . "' AND applicant = '" . mysqli_real_escape_string($conn, $row['fullName']) . "' AND position = '" . mysqli_real_escape_string($conn, $row['jobTitle']) . "'");
                              $statusValue = 'Scheduled';
                            } else {
                              $statusValue = 'Scheduled';
                            }
                          } else {
                            // Insert new status row as Scheduled
                            mysqli_query($conn, "INSERT INTO status (jobId, applicant, position, status, username, company_username, applied_at) VALUES ('" . $row['jobId'] . "', '" . mysqli_real_escape_string($conn, $row['fullName']) . "', '" . mysqli_real_escape_string($conn, $row['jobTitle']) . "', 'Scheduled', '" . mysqli_real_escape_string($conn, $row['username']) . "', '" . mysqli_real_escape_string($conn, $company_username) . "', '" . mysqli_real_escape_string($conn, $row['applied_at']) . "')");
                            $statusValue = 'Scheduled';
                          }
                        } else {
                          // No interview scheduled, use status table as before
                          $checkStatus = mysqli_query($conn, "SELECT * FROM status WHERE jobId = '" . $row['jobId'] . "' AND applicant = '" . mysqli_real_escape_string($conn, $row['fullName']) . "' AND position = '" . mysqli_real_escape_string($conn, $row['jobTitle']) . "'");
                          if (mysqli_num_rows($checkStatus) == 0) {
                            mysqli_query($conn, "INSERT INTO status (jobId, applicant, position, status, username, company_username, applied_at) VALUES ('" . $row['jobId'] . "', '" . mysqli_real_escape_string($conn, $row['fullName']) . "', '" . mysqli_real_escape_string($conn, $row['jobTitle']) . "', 'New', '" . mysqli_real_escape_string($conn, $row['username']) . "', '" . mysqli_real_escape_string($conn, $company_username) . "', '" . mysqli_real_escape_string($conn, $row['applied_at']) . "')");
                            $statusValue = 'New';
                          } else {
                            $statusRow = mysqli_fetch_assoc($checkStatus);
                            $statusValue = $statusRow['status'];
                          }
                        }
                        $statusClass = 'statusNew';
                        if ($statusValue === 'Reviewed') $statusClass = 'statusReviewed';
                        if ($statusValue === 'Scheduled') $statusClass = 'statusScheduled';
                      echo '<tr>';
                      echo '<td>' . htmlspecialchars($row['fullName']) . '</td>';
                      echo '<td>' . htmlspecialchars($row['jobTitle']) . '</td>';
                      echo '<td>' . htmlspecialchars(date('M d, Y', strtotime($row['applied_at']))) . '</td>';
                      echo '<td><span class="status ' . $statusClass . '">' . htmlspecialchars($statusValue) . '</span></td>';
                      echo '<td>';
                      echo '<button class="btn btnOutline view-btn" 
                        data-appid="' . $row['id'] . '" 
                        data-jobid="' . $row['jobId'] . '" 
                        data-applicant="' . htmlspecialchars($row['fullName']) . '" 
                        data-position="' . htmlspecialchars($row['jobTitle']) . '" 
                        data-email="' . (isset($row['email']) ? htmlspecialchars($row['email']) : '') . '" 
                        data-phone="' . (isset($row['phone']) ? htmlspecialchars($row['phone']) : '') . '" 
                        data-location="' . (isset($row['location']) ? htmlspecialchars($row['location']) : '') . '" 
                        data-interest="' . (isset($row['interest']) ? htmlspecialchars($row['interest']) : '') . '" 
                        data-resume="' . (isset($row['resume']) ? htmlspecialchars($row['resume']) : '') . '" 
                        data-appliedat="' . htmlspecialchars(date('M d, Y', strtotime($row['applied_at']))) . '"
                        >View</button> ';
                        echo '<a class="btn btnPrimary" href="interviews.php?applicant=' . urlencode($row['fullName']) . '&position=' . urlencode($row['jobTitle']) . '&username=' . urlencode($row['username']) . '">Schedule</a> ';
                        //download
                      echo '</td>';
                      echo '</tr>';
                    }
                  } else {
                    echo '<tr><td colspan="5">No applications found.</td></tr>';
                  }
                  mysqli_close($conn);
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
    
  <!-- Applicant Info Modal -->
  <div id="applicantModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;">
    <div style="background:#fff; margin:5% auto; padding:20px; border-radius:8px; max-width:400px; position:relative;">
      <span id="closeModal" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:22px;">&times;</span>
      <h3>Applicant Information</h3>
      <div id="modalContent">
        <!-- Info will be injected here -->
      </div>
      <div style="margin-top:20px; text-align:right;">
        <button id="okModalBtn" style="margin-right:10px; padding:6px 18px; background:#007bff; color:#fff; border:none; border-radius:4px; cursor:pointer;">OK</button>
        <button id="cancelModalBtn" style="padding:6px 18px; background:#6c757d; color:#fff; border:none; border-radius:4px; cursor:pointer;">Cancel</button>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.view-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
          window.lastViewBtn = btn;
          var info = '<ul style="list-style:none; padding:0;">';
          info += '<li><strong>Name:</strong> ' + btn.getAttribute('data-applicant') + '</li>';
          info += '<li><strong>Position:</strong> ' + btn.getAttribute('data-position') + '</li>';
          info += '<li><strong>Email:</strong> ' + (btn.getAttribute('data-email') || 'N/A') + '</li>';
          info += '<li><strong>Phone:</strong> ' + (btn.getAttribute('data-phone') || 'N/A') + '</li>';
          info += '<li><strong>Location:</strong> ' + (btn.getAttribute('data-location') && btn.getAttribute('data-location').trim() !== '' ? btn.getAttribute('data-location') : 'N/A') + '</li>';
          info += '<li><strong>Interest:</strong> ' + (btn.getAttribute('data-interest') && btn.getAttribute('data-interest').trim() !== '' ? btn.getAttribute('data-interest') : 'N/A') + '</li>';
          info += '<li><strong>Applied On:</strong> ' + btn.getAttribute('data-appliedat') + '</li>';
          info += '<li><strong>Resume:</strong> ' + (btn.getAttribute('data-resume') ? '<a href="uploads/jobseeker/' + btn.getAttribute('data-resume') + '" target="_blank">Download</a>' : 'N/A') + '</li>';
            // Change resume link to use PHP download logic
            info = info.replace(/<a href=\"uploads\/jobseeker\/([^\"]+)\"/g, '<a href="applications.php?download_resume=' + btn.getAttribute('data-appid') + '"');
          info += '</ul>';
          document.getElementById('modalContent').innerHTML = info;
          document.getElementById('applicantModal').style.display = 'block';
        });
      });
      document.getElementById('closeModal').onclick = function() {
        document.getElementById('applicantModal').style.display = 'none';
      };
      document.getElementById('okModalBtn').onclick = function() {
        // Get current applicant info from last clicked view-btn
        var lastBtn = window.lastViewBtn;
        if (!lastBtn) {
          document.getElementById('applicantModal').style.display = 'none';
          return;
        }
        var appid = lastBtn.getAttribute('data-appid');
        var jobid = lastBtn.getAttribute('data-jobid');
        var applicant = lastBtn.getAttribute('data-applicant');
        var position = lastBtn.getAttribute('data-position');
        // AJAX request to update status
        var xhr = new XMLHttpRequest();
        xhr.open('POST', window.location.href, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
          if (xhr.readyState === 4 && xhr.status === 200) {
            try {
              var resp = JSON.parse(xhr.responseText);
              if (resp.success) {
                // Update status in table
                var statusSpan = lastBtn.closest('tr').querySelector('.status');
                if (statusSpan) {
                  statusSpan.textContent = 'Reviewed';
                  statusSpan.className = 'status statusReviewed';
                }
              }
            } catch(e) {}
            document.getElementById('applicantModal').style.display = 'none';
          }
        };
        xhr.send('action=reviewed&appid=' + encodeURIComponent(appid) + '&jobid=' + encodeURIComponent(jobid) + '&applicant=' + encodeURIComponent(applicant) + '&position=' + encodeURIComponent(position));
      };
      document.getElementById('cancelModalBtn').onclick = function() {
        document.getElementById('applicantModal').style.display = 'none';
      };
      window.onclick = function(event) {
        var modal = document.getElementById('applicantModal');
        if (event.target == modal) {
          modal.style.display = 'none';
        }
      };
    });
  </script>
</body>
</html>