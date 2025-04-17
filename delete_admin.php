<?php
include('admin_auth.php');
// Include database connection
include('config.php'); // Replace with your actual database connection file

// Start session to use session variables for message passing
session_start();

// Get the referrer (previous page) URL
$previous_page = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'profile.php';  // Default to 'view_admin.php' if no referrer

// Check if admin ID is provided
if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $ad_matric = trim($_GET['id']);

    // Fetch the admin details from the database
    $query = "SELECT * FROM admin WHERE ad_matric = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $ad_matric);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the admin exists
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
    } else {
        $_SESSION['message'] = ["type" => "error", "text" => "Admin not found!"];
        header("Location: $previous_page"); // Redirect to the previous page
        exit;
    }

    // Only allow super admins or the admin themselves to delete their account
    if ($_SESSION['admin_role'] !== 'super_admin' && $_SESSION['ad_matric'] !== $ad_matric) {
        $_SESSION['message'] = ["type" => "error", "text" => "You do not have permission to delete this admin!"];
        header("Location: profile.php"); // Redirect to the profile page if permission is not granted
        exit;
    }

    // Proceed with deleting the admin record
    $delete_query = "DELETE FROM admin WHERE ad_matric = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("s", $ad_matric);

    if ($delete_stmt->execute()) {
        $_SESSION['message'] = ["type" => "success", "text" => "Admin deleted successfully!"];
        header("Location: $previous_page"); // Redirect back to the previous page after successful deletion
        exit;
    } else {
        // Log the error message from MySQL for debugging purposes
        $_SESSION['message'] = ["type" => "error", "text" => "Error deleting admin: " . $conn->error];
        header("Location: $previous_page"); // Redirect back to the previous page
        exit;
    }

} else {
    $_SESSION['message'] = ["type" => "error", "text" => "Admin ID not provided!"];
    header("Location: $previous_page"); // Redirect if no ID is provided
    exit;
}

// Close database connection
$stmt->close();
$conn->close();
?>
