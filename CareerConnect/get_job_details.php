<?php
// get_job_details.php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "careerconnect";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Get job ID from request
$job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($job_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid job ID']);
    exit;
}

// Get company name from session for security
$companyName = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Fetch job details
$sql = "SELECT * FROM postjob WHERE id = ? AND companyName = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "is", $job_id, $companyName);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode([
        'success' => true,
        'job' => [
            'jobTitle' => $row['jobTitle'],
            'jobDescription' => $row['jobDescription'],
            'jobLocation' => $row['jobLocation'],
            'jobType' => $row['jobType'],
            'jobCategory' => $row['jobCategory'],
            'applicationDeadline' => $row['applicationDeadline']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Job not found or access denied']);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>