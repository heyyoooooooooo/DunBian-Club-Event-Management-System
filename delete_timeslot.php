<?php
include('admin_auth.php');
// Include the database connection file
include('config.php');

// Check if the timeslot_id is set in the URL
if (isset($_GET['timeslot_id'])) {
    $timeslot_id = filter_input(INPUT_GET, 'timeslot_id', FILTER_SANITIZE_NUMBER_INT);

    if (empty($timeslot_id)) {
        die("Invalid timeslot ID.");
    }

    // Prepare the SQL query to delete the timeslot
    $sql = "DELETE FROM interview_times WHERE timeslot_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $timeslot_id);
    $stmt->execute();

    // Check if the deletion was successful
    if ($stmt->affected_rows > 0) {
        // Redirect to the interview page after deletion
        header("Location: interview.php");
        exit();
    } else {
        echo "Error deleting timeslot.";
    }

    $stmt->close();
} else {
    echo "Timeslot ID is required.";
}
?>
