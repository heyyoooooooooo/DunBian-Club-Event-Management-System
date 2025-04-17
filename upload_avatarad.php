<?php
// Include database connection
include('config.php'); 

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['avatar']) && isset($_POST['ad_matric'])) {
        $ad_matric = $_POST['ad_matric'];
        $file = $_FILES['avatar'];

        // Validate the file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
            exit;
        }

        // Set file path
        $target_dir = 'img/';
        $file_name = $ad_matric . "_" . basename($file['name']);
        $target_file = $target_dir . $file_name;

        // Check if the avatar already exists and delete it
        $query = "SELECT ad_avatar FROM admin WHERE ad_matric = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $ad_matric);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingAvatar = $result->fetch_assoc()['ad_avatar'];
        if (!empty($existingAvatar) && file_exists($target_dir . $existingAvatar)) {
            unlink($target_dir . $existingAvatar); // Remove the old avatar
        }
        $stmt->close();

        // Move the uploaded file to the target directory
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            // Update the avatar path in the database
            $query = "UPDATE admin SET ad_avatar = ? WHERE ad_matric = ?";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                echo json_encode(['success' => false, 'message' => 'Failed to prepare the query: ' . $conn->error]);
                exit;
            }
            $stmt->bind_param('ss', $file_name, $ad_matric);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'avatar_path' => $target_file]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update avatar in the database.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload file.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing data.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>