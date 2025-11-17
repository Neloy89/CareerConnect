<?php
// delete_application.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $conn = mysqli_connect('localhost', 'root', '', 'careerconnect');
    if ($conn && $id > 0) {
        // Delete from applications table
        $sql = "DELETE FROM applications WHERE id = $id";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            echo 'success';
            exit;
        }
    }
    echo 'fail';
    exit;
}
echo 'invalid';
