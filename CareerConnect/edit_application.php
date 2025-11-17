<?php
// PHP validation and update for application edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $fullName = trim($_POST['fullName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $interest = trim($_POST['interest'] ?? '');
    $errors = [];

    // Validation
    if ($id <= 0) $errors[] = 'Invalid application.';
    if ($fullName === '' || !preg_match('/^[a-zA-Z\s\-\']+$/', $fullName)) $errors[] = 'Full Name is required and must be valid.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid Email is required.';
    if ($phone === '' || !preg_match('/^01[0-9]{9}$/', $phone)) $errors[] = 'Valid Bangladeshi phone required.';
    if ($location === '') $errors[] = 'Location is required.';
    if ($interest === '') $errors[] = 'Interest is required.';

    if ($errors) {
        echo json_encode(['status'=>'fail','errors'=>$errors]);
        exit;
    }

    $conn = mysqli_connect('localhost', 'root', '', 'careerconnect');
    if (!$conn) {
        echo json_encode(['status'=>'fail','errors'=>['DB connection failed.']]);
        exit;
    }
    $sql = "UPDATE applications SET fullName=?, email=?, phone=?, location=?, interest=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sssssi', $fullName, $email, $phone, $location, $interest, $id);
    $result = mysqli_stmt_execute($stmt);
    if ($result) {
        echo json_encode(['status'=>'success']);
    } else {
        echo json_encode(['status'=>'fail','errors'=>['Update failed.']]);
    }
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit;
}
echo json_encode(['status'=>'fail','errors'=>['Invalid request.']]);
