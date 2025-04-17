<?php
// Include database connection
include('admin_auth.php');
include('config.php');  // Adjust this path to your actual connection file

// Check if the student matric number is passed in the URL
if (isset($_GET['id'])) {
    $stu_matric = $_GET['id'];  // Use 'id' instead of 'stu_matric'

    // Query to retrieve student data using stu_matric as the identifier
    $query = "SELECT * FROM students WHERE stu_matric = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }
    $stmt->bind_param("s", $stu_matric);  // "s" for string, as stu_matric is a string
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a student was found
    if ($result->num_rows > 0) {
        // Fetch the student data
        $students = $result->fetch_assoc();
    } else {
        // Handle case if student not found
        echo "Student not found!";
        exit;
    }

    // Get student events
    /*$events_query = "SELECT e.* FROM events e 
                     INNER JOIN event_participants ep ON e.event_id = ep.event_id 
                     WHERE ep.stu_matric = ?";
    $stmt_events = $conn->prepare($events_query);
    if ($stmt_events) {
        $stmt_events->bind_param("s", $stu_matric);
        $stmt_events->execute();
        $events_result = $stmt_events->get_result();
        $student_events = $events_result->fetch_all(MYSQLI_ASSOC);
        $stmt_events->close();
    } else {
        $student_events = [];
    }*/

     // Get student recruitment applications with proper joins
     $recruitment_query = "SELECT a.*, r.recruit_title, r.recruit_time, 
     t.timeslot_date, t.start_time, 
                            CASE 
                            WHEN a.application_status = 'Pending' THEN '#f59e0b'
                            WHEN a.application_status = 'Approved' THEN '#10b981'
                            WHEN a.application_status = 'Rejected' THEN '#ef4444'
                            WHEN a.application_status = 'In Review' THEN '#3b82f6'
                            ELSE '#64748b'
                            END AS status_color
                            FROM recruitment_applications a
                            LEFT JOIN recruitment r ON a.recruit_id = r.recruit_id
                            LEFT JOIN interview_times t ON a.timeslot_id = t.timeslot_id
                            WHERE a.stu_matric = ?";
    $stmt_recruitment = $conn->prepare($recruitment_query);
    if ($stmt_recruitment) {
        $stmt_recruitment->bind_param("s", $stu_matric);
        $stmt_recruitment->execute();
        $recruitment_result = $stmt_recruitment->get_result();
        $student_recruitments = $recruitment_result->fetch_all(MYSQLI_ASSOC);
        $stmt_recruitment->close();
    } else {
        $student_recruitments = [];
    }
    } else {
        echo "Student Matric number not provided!";
        exit;
    }

// Close database connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Profile - Dunbian Club</title>
  <link rel="stylesheet" href="/EVENT_MANAGEMENT/viewstudent.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div id="sidebar">
        <div class="brand">
            <i class="fas fa-calendar-alt"></i> Dunbian Club
        </div>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="recruitment_list.php"><i class="fas fa-user-plus"></i> Recruitments</a>
        <a href="interview.php"><i class="fas fa-clock"></i> Interview</a>
        <a href="events.php"><i class="fas fa-calendar-check"></i> Events</a>
        <a href="#"><i class="fas fa-comment-alt"></i> Feedback</a>
        <a href="profile.php" class="active"><i class="fas fa-user-circle"></i> Profile</a>
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
            <a href="profile.php">Profile</a>
            <span>&gt;</span>
            <span>Student Profile</span>
        </div>

        <div class="profile-container">
            <h2>Student Profile</h2>
            
            <div class="profile-card">
                <div class="profile-header">
                    <div class="avatar-section">
                        <?php
                        // Construct the avatar path
                        $base_path = 'img/';
                        $avatar_path = $base_path . $students['stu_avatar'];

                        // Check if the file exists
                        if (!file_exists($avatar_path) || empty($students['stu_avatar'])) {
                            $avatar_path = 'uploads/default_profile.png'; // Fallback to default avatar
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="Student Avatar" class="avatar">
                        <h3><?php echo htmlspecialchars($students['stu_first_name'] . ' ' . $students['stu_last_name']); ?></h3>
                        <div class="student-badges">
                            <span class="badge badge-faculty">Student</span>
                            </span>
                        </div>
                    </div>
                    
                    <div class="student-info">
                        <div class="info-grid">
                            <div class="info-item">
                                <i class="fas fa-id-card info-icon"></i>
                                <div class="info-content">
                                    <span class="info-label">Matric Number</span>
                                    <span class="info-value"><?php echo htmlspecialchars($students['stu_matric']); ?></span>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-envelope info-icon"></i>
                                <div class="info-content">
                                    <span class="info-label">Email</span>
                                    <span class="info-value"><?php echo htmlspecialchars($students['stu_email']); ?></span>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-phone info-icon"></i>
                                <div class="info-content">
                                    <span class="info-label">Phone</span>
                                    <span class="info-value"><?php echo htmlspecialchars($students['stu_phone_number'] ?: 'N/A'); ?></span>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-venus-mars info-icon"></i>
                                <div class="info-content">
                                    <span class="info-label">Gender</span>
                                    <span class="info-value"><?php echo htmlspecialchars($students['stu_gender']); ?></span>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-user-graduate info-icon"></i>
                                <div class="info-content">
                                    <span class="info-label">Faculty</span>
                                    <span class="info-value"><?php echo htmlspecialchars($students['stu_faculty']); ?></span>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-calendar-alt info-icon"></i>
                                <div class="info-content">
                                    <span class="info-label">Joined On</span>
                                    <span class="info-value"><?php echo htmlspecialchars($students['stu_created_at']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Activities Section -->
                <div class="activity-section">
                    <div class="activity-tabs">
                        <button class="tab-button active" data-tab="events">Events Joined</button>
                        <button class="tab-button" data-tab="recruitments">Recruitment Applications</button>
                    </div>
                    
                    <!-- Events Tab Content -->
                    <div class="tab-content active" id="events-tab">
                        <h3><i class="fas fa-calendar-check"></i> Events Participated</h3>
                        <?php if (empty($student_events)): ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-xmark empty-icon"></i>
                                <p>No events joined yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="event-cards">
                                <?php foreach ($student_events as $event): ?>
                                    <div class="event-card">
                                        <div class="event-date">
                                            <?php 
                                                $date = new DateTime($event['event_date']);
                                                echo $date->format('M d, Y'); 
                                            ?>
                                        </div>
                                        <h4><?php echo htmlspecialchars($event['event_name']); ?></h4>
                                        <div class="event-details">
                                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['event_location']); ?></p>
                                            <p><i class="fas fa-clock"></i> <?php echo htmlspecialchars($event['event_time']); ?></p>
                                        </div>
                                        <div class="event-status">
                                            <span class="status-badge status-<?php echo strtolower($event['event_status']); ?>">
                                                <?php echo htmlspecialchars($event['event_status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Recruitments Tab Content -->
                    <div class="tab-content" id="recruitments-tab">
                        <h3><i class="fas fa-user-plus"></i> Recruitment Applications</h3>
                        <?php if (empty($student_recruitments)): ?>
                            <div class="empty-state">
                                <i class="fas fa-clipboard-list empty-icon"></i>
                                <p>No recruitment applications found.</p>
                            </div>
                        <?php else: ?>
                            <div class="recruitment-table-container">
                                <table class="recruitment-table">
                                    <thead>
                                        <tr>
                                            <th>Recruitment</th>
                                            <th>1st Position Applied</th>
                                            <th>2nd Position Applied</th>
                                            <th>Status</th>
                                            <th>Date & Time</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($student_recruitments as $recruitment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($recruitment['recruit_title'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($recruitment['dept_choice_1']); ?></td>
                                                <td><?php echo htmlspecialchars($recruitment['dept_choice_2']); ?></td>
                                                <td>
                                                    <span class="status-badge" style="background-color: <?php echo htmlspecialchars($recruitment['status_color']); ?>">
                                                        <?php echo htmlspecialchars($recruitment['application_status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($recruitment['timeslot_date'])): ?>
                                                        <?php echo htmlspecialchars(date('M d, Y', strtotime($recruitment['timeslot_date'])) . ' at ' . $recruitment['start_time']); ?>
                                                    <?php else: ?>
                                                        Not scheduled
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="view_applications.php?id=<?php echo $recruitment['application_id']; ?>" class="btn-editedit">View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="profile-actions">
                <a href="profile.php" class="btn-edit"><i class="fas fa-arrow-left"></i>Back to Student List</a>
            </div>
        </div>
    </div>

    <script>
        // Toggle account menu
        document.querySelector('.account').addEventListener('click', function () {
            const menu = document.querySelector('.account-menu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });

        // Tabs functionality
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Show corresponding content
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId + '-tab').classList.add('active');
            });
        });
    </script>
</body>
</html>
