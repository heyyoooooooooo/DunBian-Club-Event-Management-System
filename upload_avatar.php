<?php
session_start();

if (!isset($_SESSION['stu_matric'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$stu_matric = $_SESSION['stu_matric'];

// Ensure a file was uploaded
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] != UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit();
}

// Validate file type (e.g., JPEG, PNG)
$allowed_types = ['image/jpeg', 'image/png'];
if (!in_array($_FILES['avatar']['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit();
}

// Define upload path
$upload_dir = 'img/';
$avatar_name = $stu_matric . '_' . basename($_FILES['avatar']['name']);
$upload_path = $upload_dir . $avatar_name;

// Move uploaded file
if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
    // Update database with avatar path
    include('config.php');
    $query = "UPDATE students SET stu_avatar = ? WHERE stu_matric = ?";
    $stmt = $conn->prepare($query);
    $avatar_db_path = $upload_dir . $avatar_name;
    $stmt->bind_param('ss', $avatar_db_path, $stu_matric);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'avatar_path' => $avatar_db_path]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update database']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
}
?>
