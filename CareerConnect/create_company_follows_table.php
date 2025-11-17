<?php
// create_company_follows_table.php
$conn = mysqli_connect('localhost', 'root', '', 'careerconnect');
if (!$conn) {
    die('Database connection failed.');
}
$sql = "CREATE TABLE IF NOT EXISTS company_follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    company_id INT NOT NULL,
    followed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (mysqli_query($conn, $sql)) {
    echo "Table 'company_follows' created or already exists.";
} else {
    echo "Error creating table: " . mysqli_error($conn);
}
mysqli_close($conn);
?>
