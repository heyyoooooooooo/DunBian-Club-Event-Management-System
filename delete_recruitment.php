<?php
include('admin_auth.php');
include('config.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize input
    $sql = "DELETE FROM recruitment WHERE recruit_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        header("Location: recruitment_list.php");
        exit;
    } else {
        echo "Error deleting record.";
    }
} else {
    echo "Invalid request.";
}
?>
