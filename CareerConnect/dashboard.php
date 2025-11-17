<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard - CareerConnect</title>
    <link rel="stylesheet" href="dashboard.css" />
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
                  href="dashboard.html"
                  class="nav-link active"
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
        </div>
      </div>
    </header>
    <main class="dashboard">
      <div class="container">
        <div id="dashboard" class="tabContent active">
          <h2 class="sectionTitle">Company Dashboard</h2>
          <div class="statsContainer">
            <div class="statCard">
              <span class="statLabel">Active Job Postings</span>
              <span class="statValue">
                <?php
                $conn = mysqli_connect("localhost", "root", "", "careerconnect");
                $activeJobCount = 0;
                $sessionUsername = $_SESSION['username'] ?? '';
                if ($conn && $sessionUsername) {
                  $query = "SELECT COUNT(*) AS cnt FROM postjob WHERE companyName = '" . mysqli_real_escape_string($conn, $sessionUsername) . "' AND jobstatus = 'active'";
                  $result = mysqli_query($conn, $query);
                  if ($result && $row = mysqli_fetch_assoc($result)) {
                    $activeJobCount = intval($row['cnt']);
                  }
                }
                echo $activeJobCount;
                ?>
              </span>
            </div>
            <div class="statCard">
              <span class="statLabel">New Applications</span>
              <span class="statValue">
                <?php
                $newAppCount = 0;
                if ($conn && $sessionUsername) {
                  $query = "SELECT COUNT(*) AS cnt FROM applications WHERE company_username = '" . mysqli_real_escape_string($conn, $sessionUsername) . "'";
                  $result = mysqli_query($conn, $query);
                  if ($result && $row = mysqli_fetch_assoc($result)) {
                    $newAppCount = intval($row['cnt']);
                  }
                }
                echo $newAppCount;
                ?>
              </span>
            </div>
            <div class="statCard">
              <span class="statLabel">Interviews Scheduled</span>
              <span class="statValue">
                <?php
                $interviewCount = 0;
                if ($conn && $sessionUsername) {
                  $query = "SELECT COUNT(*) AS cnt FROM interviews WHERE company_username = '" . mysqli_real_escape_string($conn, $sessionUsername) . "'";
                  $result = mysqli_query($conn, $query);
                  if ($result && $row = mysqli_fetch_assoc($result)) {
                    $interviewCount = intval($row['cnt']);
                  }
                }
                echo $interviewCount;
                ?>
              </span>
            </div>
          </div>
          <div class="card">
            <h3 class="sectionTitle">Recent Applications</h3>
            <div class="tableResponsive">
              <table class="table">
                <thead>
          <tr>
            <th>Applicant</th>
            <th>Position</th>
            <th>Applied On</th>
            <th>Status</th>
          </tr>
                </thead>
                <tbody>
                    <?php
                    // Show only rows where session username matches company_username
                    if ($conn && $sessionUsername) {
                      $statusQuery = "SELECT applicant, position, status, applied_at FROM status WHERE company_username = '" . mysqli_real_escape_string($conn, $sessionUsername) . "' ORDER BY applied_at DESC LIMIT 7";
                      $statusResult = mysqli_query($conn, $statusQuery);
                      if ($statusResult && mysqli_num_rows($statusResult) > 0) {
                        while ($row = mysqli_fetch_assoc($statusResult)) {
                          echo '<tr>';
                          echo '<td>' . htmlspecialchars($row['applicant']) . '</td>';
                          echo '<td>' . htmlspecialchars($row['position']) . '</td>';
                          // Applied On column
                          $appliedOn = $row['applied_at'] ? date('M d, Y', strtotime($row['applied_at'])) : 'N/A';
                          echo '<td>' . htmlspecialchars($appliedOn) . '</td>';
                          // Status styling
                          $statusClass = 'status';
                          if (strtolower($row['status']) === 'new') {
                            $statusClass .= ' statusNew';
                          } elseif (strtolower($row['status']) === 'reviewed') {
                            $statusClass .= ' statusReviewed';
                          } elseif (strtolower($row['status']) === 'scheduled') {
                            $statusClass .= ' statusScheduled';
                          }
                          echo '<td><span class="' . $statusClass . '">' . htmlspecialchars($row['status']) . '</span></td>';
                          echo '</tr>';
                        }
                      } else {
                        echo '<tr><td colspan="4">No applications found for your company.</td></tr>';
                      }
                    } else {
                      echo '<tr><td colspan="4">Database connection error or session missing.</td></tr>';
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
