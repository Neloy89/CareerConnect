<?php
// Updates status for an application in the status table
if (!isset($_POST['id']) || !isset($_POST['status'])) die('Missing data');
$id = intval($_POST['id']);
$status = $_POST['status'];
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "careerconnect";
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) die("Connection failed: " . mysqli_connect_error());
$sql = "SELECT a.*, p.jobTitle FROM applications a JOIN postjob p ON a.jobId = p.id WHERE a.id = $id";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    $jobId = $row['jobId'];
    $applicant = $row['fullName'];
    $position = $row['jobTitle'];
    $update = mysqli_query($conn, "UPDATE status SET status='" . mysqli_real_escape_string($conn, $status) . "' WHERE jobId='$jobId' AND applicant='" . mysqli_real_escape_string($conn, $applicant) . "' AND position='" . mysqli_real_escape_string($conn, $position) . "'");
    if ($update) echo 'Success';
    else echo 'Failed';
} else {
    echo 'Application not found.';
}
mysqli_close($conn);
