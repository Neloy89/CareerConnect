<?php
// follow_company.php
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}
$username = $_SESSION['username'];
$companyId = isset($_POST['companyId']) ? intval($_POST['companyId']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

$conn = mysqli_connect('localhost', 'root', '', 'careerconnect');
// Ensure company_follows table exists
$createCompanyFollowsTable = "CREATE TABLE IF NOT EXISTS company_follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    company_id INT NOT NULL,
    followed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $createCompanyFollowsTable);
if (!$conn || !$companyId || ($action !== 'follow' && $action !== 'unfollow')) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

if ($action === 'follow') {
    // Insert if not exists
    $check = mysqli_query($conn, "SELECT * FROM company_follows WHERE username='$username' AND company_id=$companyId");
    if (mysqli_num_rows($check) === 0) {
        mysqli_query($conn, "INSERT INTO company_follows (username, company_id) VALUES ('$username', $companyId)");
    }
    echo json_encode(['status' => 'success', 'action' => 'follow']);
} else {
    // Unfollow
    mysqli_query($conn, "DELETE FROM company_follows WHERE username='$username' AND company_id=$companyId");
    echo json_encode(['status' => 'success', 'action' => 'unfollow']);
}
mysqli_close($conn);
?>
