<?php
include('admin_auth.php');
// Include the database connection file
include('config.php');

// Check if the application ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $application_id = intval($_GET['id']);

    // Fetch the details of the application
    $sql = "SELECT a.application_id, CONCAT(s.stu_first_name, ' ', s.stu_last_name) AS stu_name, s.stu_matric, 
                   s.stu_gender, s.stu_phone_number, s.stu_faculty, s.stu_year, s.stu_email, s.stu_avatar,
                   a.dept_choice_1, a.dept_choice_2, a.join_reason, a.past_experience, a.head_role, a.ig_link, 
                   a.recommendations, a.application_status, t.timeslot_date, t.start_time, r.recruit_title
            FROM recruitment_applications a
            LEFT JOIN students s ON a.stu_matric = s.stu_matric
            LEFT JOIN recruitment r ON a.recruit_id = r.recruit_id
            LEFT JOIN interview_times t ON a.timeslot_id = t.timeslot_id
            WHERE a.application_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $application_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if an application is found
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        } else {
            echo "Application not found.";
            exit;
        }
    } else {
        echo "Error preparing statement: " . $conn->error;
        exit;
    }
} else {
    echo "Invalid or missing application ID.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Recruitment - Admin Dashboard</title>
  <link rel="stylesheet" href="/EVENT_MANAGEMENT/view_applications.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div id="sidebar">
        <div class="brand">
            <i class="fas fa-calendar-alt"></i> Dunbian Club
        </div>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="recruitment_list.php" class="active"><i class="fas fa-user-plus"></i> Recruitments</a>
        <a href="interview.php"><i class="fas fa-clock"></i> Interview</a>
        <a href="events.php"><i class="fas fa-calendar-check"></i> Events</a>
        <a href="#"><i class="fas fa-comment-alt"></i> Feedback</a>
        <a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a>
        <a href="#"><i class="fas fa-chart-bar"></i> Reporting</a>
    </div>

    <div id="content">
        <div id="header">
            <!--<div class="search-bar">-->
                <!--<i class="fas fa-search"></i>-->
                <!--<input type="text" placeholder="Search...">-->
            
            <div class="user-controls">
                <div class="notifications">
                    <i class="fas fa-bell"></i>
                
                </div>
                <div class="account">
                    <i class="fas fa-user"></i>
                    <div class="account-menu">
                        <a href="admin_page.php">Profile</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> 
            <span>&gt;</span> 
            <a href="recruitment_list.php">Recruitment</a> 
            <span>&gt;</span> 
            <a href="view_applications.php?id=<?php echo $application_id; ?>">View Recruitment Applications</a>
        </div>

        <div class="container">
        <!-- Recruitment Application Profile Section -->
        <div class="profile-section">
            <h2>Student Profile</h2>
            <div class="profile-header">
                <div class="avatar-section">
                    <img src="<?php echo $row['stu_avatar'] ?>" alt="Student Avatar" class="avatar">
                </div>
                <div class="personal-details">
                    <div class="detail-row">
                        <strong>Name:</strong> <span><?php echo htmlspecialchars($row['stu_name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <strong>Matric No:</strong> <span><?php echo htmlspecialchars($row['stu_matric']); ?></span>
                    </div>
                    <div class="detail-row">
                        <strong>Gender:</strong> <span><?php echo htmlspecialchars($row['stu_gender']); ?></span>
                    </div>
                    <div class="detail-row">
                        <strong>Email:</strong> <span><?php echo htmlspecialchars($row['stu_email']); ?></span>
                    </div>
                    <div class="detail-row">
                        <strong>Phone:</strong> <span><?php echo htmlspecialchars($row['stu_phone_number']); ?></span>
                    </div>
                    <div class="detail-row">
                        <strong>Faculty:</strong> <span><?php echo htmlspecialchars($row['stu_faculty']); ?></span>
                    </div>
                    <div class="detail-row">
                        <strong>Year:</strong> <span><?php echo htmlspecialchars($row['stu_year']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Application Details Section -->
        <div class="application-section">
            <h2>Application Details</h2>
            <div class="detail-row">
                <strong>First Choice:</strong> <span><?php echo htmlspecialchars($row['dept_choice_1']); ?></span>
            </div>
            <div class="detail-row">
                <strong>Second Choice:</strong> <span><?php echo htmlspecialchars($row['dept_choice_2']); ?></span>
            </div>
            <div class="detail-row">
                <strong>Reason for Joining:</strong> <span><?php echo nl2br(htmlspecialchars($row['join_reason'])); ?></span>
            </div>
            <div class="detail-row">
                <strong>Past Experience:</strong> <span><?php echo nl2br(htmlspecialchars($row['past_experience'])); ?></span>
            </div>
            <div class="detail-row">
                <strong>Role Head:</strong> <span><?php echo htmlspecialchars($row['head_role']); ?></span>
            </div>
            <div class="detail-row">
                <strong>Instagram:</strong> 
                <?php if (!empty($row['ig_link'])): ?>
                    <a href="<?php echo htmlspecialchars($row['ig_link']); ?>" target="_blank">Instagram Profile</a>
                <?php else: ?>
                    Not Provided
                <?php endif; ?>
            </div>
            <div class="detail-row">
                <strong>Recommendations:</strong> <span><?php echo nl2br(htmlspecialchars($row['recommendations'])); ?></span>
            </div>
            <div class="detail-row">
                <strong>Interview Time:</strong> <span><?php echo htmlspecialchars($row['timeslot_date']) . " " . htmlspecialchars($row['start_time']); ?></span>
            </div>
        </div>

        <!-- Back Button -->
        <div class="applications-actions">
            <a href="recruitment_list.php" class="btn-back">Back to Applications List</a>
        </div>
    </div>
</body>
</html>
