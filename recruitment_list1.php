<?php
include('admin_auth.php');
// Include the database connection file
include('config.php');

// Check for status change action
if (isset($_GET['action']) && isset($_GET['application_id'])) {
    $application_id = $_GET['application_id'];
    $action = $_GET['action'];

    // Validate the action
    if ($action == 'approve' || $action == 'reject') {
        // Determine the status
        $status = ($action == 'approve') ? 'Approve' : 'Reject';  // Set status to Approve or Reject

        // Prepare the update SQL query
        $update_sql = "UPDATE recruitment_applications SET application_status = ? WHERE application_id = ?";

        // Prepare the statement
        if ($stmt = $conn->prepare($update_sql)) {
            // Bind parameters
            $stmt->bind_param('si', $status, $application_id);

            // Execute the query
            if ($stmt->execute()) {
                // Check if any rows were affected
                if ($stmt->affected_rows > 0) {
                    // Redirect back to the recruitment list with a success message
                    header("Location: recruitment_list.php?status=success&action=$action");
                    exit();  // Make sure no further code is executed
                } else {
                    // No rows were affected (could be due to the same status being set)
                    header("Location: recruitment_list.php?status=nochange&action=$action");
                    exit();
                }
            } else {
                // Error executing the query
                echo "Error executing the query: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        } else {
            // Error preparing the statement
            echo "Error preparing the statement: " . $conn->error;
        }
    }
}

// Set default active tab
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'recruitments';

// Fetch all recruitment titles for the dropdown
$recruitments_sql = "SELECT recruit_id, recruit_title FROM recruitment";  // Correct table name here
$recruitments_result = $conn->query($recruitments_sql);

// Check if a recruitment title is selected
$recruit_id = isset($_POST['recruit_id']) ? $_POST['recruit_id'] : '';

// Fetch applications related to the selected recruitment title
if ($recruit_id) {
    $sql = "SELECT a.application_id, CONCAT(s.stu_first_name, ' ', s.stu_last_name) AS stu_name, s.stu_matric, 
                   a.dept_choice_1, a.dept_choice_2, a.join_reason, a.past_experience, a.head_role, a.ig_link, 
                   a.recommendations, a.application_status, t.timeslot_date, t.start_time, r.recruit_title, r.recruit_time
            FROM recruitment_applications a
            LEFT JOIN students s ON a.stu_matric = s.stu_matric
            LEFT JOIN recruitment r ON a.recruit_id = r.recruit_id
            LEFT JOIN interview_times t ON a.timeslot_id = t.timeslot_id
            WHERE a.recruit_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param('i', $recruit_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing the query: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recruitment Management</title>
  <link rel="stylesheet" href="/EVENT_MANAGEMENT/recruitment.css">
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
    <a href="recruitment_list.php">Recruitments</a>
  </div>

  <!-- Tab Navigation -->
  <div class="tab-navigation">
    <button class="tab-button <?php echo $activeTab == 'recruitments' ? 'active' : ''; ?>" 
            onclick="switchTab('recruitments')">
      <i class="fas fa-clipboard-list"></i> Manage Recruitments
    </button>
    <button class="tab-button <?php echo $activeTab == 'applicants' ? 'active' : ''; ?>" 
            onclick="switchTab('applicants')">
      <i class="fas fa-users"></i> View Applicants
    </button>
  </div>