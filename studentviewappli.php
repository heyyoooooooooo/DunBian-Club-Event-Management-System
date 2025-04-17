<?php
// Include the database connection file 
include('config.php');

// Check if recruit_id is passed in the URL
if (isset($_GET['recruit_id'])) {
    $recruit_id = $_GET['recruit_id'];

    // Query to get recruitment details based on recruit_id
    $query = "SELECT 
        ra.application_id,
        ra.stu_matric,
        ra.preferred_time,
        ra.dept_choice_1,
        ra.dept_choice_2,
        ra.join_reason,
        ra.past_experience,
        ra.head_role,
        ra.ig_link,
        ra.recommendations,
        ra.application_status,
        it.timeslot_id,
        it.timeslot_date,
        it.start_time,
        it.end_time,
        it.max_participants,
        it.booked_count,
        it.timeslot_status,
        it.recruit_id
    FROM 
        recruitment_applications AS ra
    LEFT JOIN 
        interview_times AS it ON ra.timeslot_id = it.timeslot_id
    WHERE 
        ra.stu_matric = ?
    ORDER BY 
        it.timeslot_date ASC, it.start_time ASC";

    // Prepare the query and bind parameters
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $recruit_id); // "i" for integer (recruit_id is an integer)
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the recruitment record is found
    if ($result->num_rows > 0) {
        $recruitment = $result->fetch_assoc();
    } else {
        echo "No recruitment details found.";
        exit;
    }
} else {
    echo "No recruitment ID provided.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Recruitment Details</title>
    <link rel="stylesheet" href="studentprofile.css">
</head>
<body>
    <!-- Header -->
    <header id="header">
        <div class="header-container">
            <!-- Logo -->
            <img src="logo.png" alt="Logo" class="logo"> <!-- Add your logo -->

            <!-- Navigation Menu -->
            <nav class="nav-items">
                <a href="homepage.php">Home</a>
                <a href="aboutpage.php">About</a>
                <a href="eventlisting.php">Events</a>
                <a href="recruitmentopportunities.php">Recruitments</a>
                <a href="contactus.php">Contact Us</a>
            </nav>

            <!-- User Controls (Notification and Account icons) -->
            <?php if (isset($_SESSION['ad_matric']) || isset($_SESSION['username'])): ?>
                <div class="user-controls">
                    <!-- Notification Icon -->
                    <a href="notifications.php" class="notifications">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count">3</span> <!-- Example notification count -->
                    </a>

                    <!-- Account Icon -->
                    <div class="account">
                        <i class="fas fa-user"></i>
                        <!-- Account Menu (hidden by default) -->
                        <div class="account-menu">
                            <a href="studentprofile.php">Profile</a>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Show Login Now button if not logged in -->
                <a href="login_form.php" class="login-btn">Login Now</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="container">
        <h2>Recruitment Details</h2>

        <!-- Recruitment Details Table -->
        <div class="section">
            <h3><?php echo htmlspecialchars($recruitment_applications['recruit_title']); ?></h3>
            <table class="details-table">
                <tr>
                    <th>Description</th>
                    <td><?php echo nl2br(htmlspecialchars($recruitment_applications['recruit_description'])); ?></td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td><?php echo htmlspecialchars($recruitment_applications['recruit_date']); ?></td>
                </tr>
                <tr>
                    <th>Department Choice 1</th>
                    <td><?php echo htmlspecialchars($recruitment_applications['dept_choice_1']); ?></td>
                </tr>
                <tr>
                    <th>Department Choice 2</th>
                    <td><?php echo htmlspecialchars($recruitment_applications['dept_choice_2']); ?></td>
                </tr>
                <tr>
                    <th>Reason for Joining</th>
                    <td><?php echo nl2br(htmlspecialchars($recruitment_applications['join_reason'])); ?></td>
                </tr>
                <tr>
                    <th>Past Experience</th>
                    <td><?php echo nl2br(htmlspecialchars($recruitment_applications['past_experience'])); ?></td>
                </tr>
                <tr>
                    <th>Head Role</th>
                    <td><?php echo htmlspecialchars($recruitment_applications['head_role']); ?></td>
                </tr>
                <tr>
                    <th>Instagram Link</th>
                    <td><a href="<?php echo htmlspecialchars($recruitment_applications['ig_link']); ?>" target="_blank"><?php echo htmlspecialchars($recruitment['ig_link']); ?></a></td>
                </tr>
                <tr>
                    <th>Recommendations</th>
                    <td><?php echo nl2br(htmlspecialchars($recruitment_applications['recommendations'])); ?></td>
                </tr>
            </table>
        </div>

        <!-- Interview Timeslot Table -->
        <div class="section">
            <h3>Interview Timeslot Details</h3>
            <table class="details-table">
                <tr>
                    <th>Interview Date</th>
                    <td><?php echo htmlspecialchars($$recruitment_applications['timeslot_date']); ?></td>
                </tr>
                <tr>
                    <th>Start Time</th>
                    <td><?php echo htmlspecialchars($$recruitment_applications['start_time']); ?></td>
                </tr>
                <tr>
                    <th>End Time</th>
                    <td><?php echo htmlspecialchars($$recruitment_applications['end_time']); ?></td>
                </tr>
            </table>
        </div>

        <!-- Back Button -->
        <div class="back-button">
            <a href="studentprofile.php" class="btn">Back to Profile</a>
        </div>
    </div>
</body>
</html>
