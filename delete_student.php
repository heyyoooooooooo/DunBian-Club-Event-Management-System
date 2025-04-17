<?php
include('admin_auth.php'); // Authentication check
// Include database connection
include('config.php'); // Your actual database connection file

// Start session to use session variables for message passing
session_start();

// Check if ID is passed for deletion
if (isset($_GET['id'])) {
    $student_id = $_GET['id']; // Get student ID from URL

    // Step 1: Fetch the timeslot_id(s) related to the student's recruitment applications
    $sql_applications = "SELECT ra.timeslot_id
                         FROM recruitment_applications ra
                         WHERE ra.stu_matric = ?";

    $stmt_applications = $conn->prepare($sql_applications);
    $stmt_applications->bind_param('s', $student_id); // Assuming stu_matric is a string (matric number)
    $stmt_applications->execute();
    $result_applications = $stmt_applications->get_result();

    // Step 2: If the student is linked to an interview (recruitment), decrement the booked_count for the timeslot
    if ($result_applications->num_rows > 0) {
        while ($row_application = $result_applications->fetch_assoc()) {
            $timeslot_id = $row_application['timeslot_id'];

            // Update booked count for the timeslot
            $update_timeslot = "UPDATE interview_times 
                                SET booked_count = booked_count - 1, 
                                    status = IF(booked_count - 1 < max_participants, 'Available', status) 
                                WHERE timeslot_id = ?";

            $stmt_update = $conn->prepare($update_timeslot);
            $stmt_update->bind_param('i', $timeslot_id);
            $stmt_update->execute();
            $stmt_update->close();
        }
    }

    // Step 3: Now delete the student's application from the recruitment_applications table
    $sql_delete_application = "DELETE FROM recruitment_applications WHERE stu_matric = ?";
    $stmt_delete_application = $conn->prepare($sql_delete_application);
    $stmt_delete_application->bind_param('s', $student_id);
    $stmt_delete_application->execute();
    $stmt_delete_application->close();

    // Step 4: Now delete the student profile from the students table
    $sql_delete_student = "DELETE FROM students WHERE stu_matric = ?";
    $stmt_delete_student = $conn->prepare($sql_delete_student);
    $stmt_delete_student->bind_param('s', $student_id);
    $stmt_delete_student->execute();
    $stmt_delete_student->close();

    // Step 5: Set success message in session
    $_SESSION['message'] = "Student profile and associated interview application deleted successfully!";

    // Redirect back to the student list page or any other page
    header("Location: profile.php");
    exit();
}

// Fetch and display student list if no ID is provided
$sql = "SELECT stu_matric, stu_first_name, stu_last_name FROM students";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Student</title>
    <link rel="stylesheet" href="profile.css"> <!-- Include your CSS -->
</head>
<body>
    <h1>Delete Student Profile</h1>
    
    <?php
    // Show the success/error message if set
    if (isset($_SESSION['message'])):
        echo "<script>alert('" . $_SESSION['message'] . "');</script>";
        unset($_SESSION['message']); // Clear the message after showing it
    endif;
    ?>
    
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['stu_first_name'] . ' ' . $row['stu_last_name']); ?></td>
                        <td>
                            <!-- Updated Delete Button -->
                            <a href="delete_student.php?id=<?php echo $row['stu_matric']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No students found!</p>
    <?php endif; ?>

</body>
</html>
