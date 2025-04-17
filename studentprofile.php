<?php
session_start(); // Ensure the session is started

// Include database connection
include('config.php');

// Check if the user is logged in by verifying the session variable
if (!isset($_SESSION['stu_matric'])) {
    // Redirect to the login page if not logged in
    header("Location: login_form.php");
    exit();
}

// Assuming the user is logged in and the username is stored in session
$stu_matric = $_SESSION['stu_matric'];

// Fetch student profile information
$query = "SELECT * FROM students WHERE stu_matric = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $stu_matric);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("Student not found.");
}

// Set default avatar if no avatar exists
$avatar_path = isset($student['stu_avatar']) && $student['stu_avatar'] ? $student['stu_avatar'] : "path/to/default-avatar.jpg";

// Fetch recruitment history
$recruitment_query = "
    SELECT 
        ra.*, r.*, it.*
    FROM 
        recruitment_applications AS ra
    LEFT JOIN 
        interview_times AS it ON ra.timeslot_id = it.timeslot_id
    LEFT JOIN
        recruitment AS r ON ra.recruit_id = r.recruit_id
    WHERE 
        ra.stu_matric = ?
    ORDER BY 
        r.recruit_date ASC, it.start_time ASC";
$recruitment_stmt = $conn->prepare($recruitment_query);
$recruitment_stmt->bind_param("s", $stu_matric);
$recruitment_stmt->execute();
$recruitment_result = $recruitment_stmt->get_result();
$recruitment_history = $recruitment_result->fetch_all(MYSQLI_ASSOC);

// Fetch events history
// In a real application, replace this with actual database query
$eventsData = []; // This will be filled with data from database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile | DunBian Club</title>
    <link rel="stylesheet" href="studentprofile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <div class="logo-section">
                <img src="img/logo.jpg" alt="Club Logo" class="logo">
                <span class="club-name">DunBian Club</span>
            </div>
            <nav class="nav-items">
                <a href="homepage.php">Home</a>
                <a href="aboutpage.php">About</a>
                <a href="eventlisting.php">Events</a>
                <a href="recruitmentopportunities.php">Recruitments</a>
                <a href="contactus.php">Contact Us</a>
            </nav>
            <?php if (isset($_SESSION['stu_matric'])): ?>
            <!-- Logged-in State -->
            <div class="user-controls">
                <div class="notifications">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="account">
                    <i class="fas fa-user"></i>
                    <div class="account-menu" style="display: none;">
                        <a href="studentprofile.php">Profile</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Logged-out State -->
            <div class="auth-controls">
                <a href="login_form.php" class="login-btn">Login Now</a>
            </div>
            <?php endif; ?>
        </div>
    </header>

    <div class="container">
        <!-- Two-card layout for profile -->
        <div class="profile-layout">
            <!-- Left Card - Avatar -->
            <div class="avatar-card">
                <div class="avatar-container">
                    <div class="avatar-wrapper">
                        <img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="Student Avatar" class="avatar">
                    </div>
                    <h2 class="student-name"><?php echo htmlspecialchars($student['stu_first_name'] . ' ' . $student['stu_last_name']); ?></h2>
                    <p class="student-matric"><?php echo htmlspecialchars($student['stu_matric']); ?></p>
                    <button onclick="changeAvatar()" class="avatar-btn">
                        <i class="fas fa-camera"></i> Change Avatar
                    </button>
                </div>
            </div>
            
            <!-- Right Card - Personal Information -->
            <div class="info-card">
                <div class="card-header">
                    <h2>Personal Information</h2>
                    <button class="edit-btn" onclick="window.location.href='editstudentprofile.php?id=<?php echo htmlspecialchars($student['stu_matric']); ?>'">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Full Name</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['stu_first_name'] . ' ' . $student['stu_last_name']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Matric Number</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['stu_matric']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Gender</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['stu_gender']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['stu_email']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Phone Number</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['stu_phone_number']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Faculty</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['stu_faculty']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Year of Study</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['stu_year']); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- History Cards -->
        <div class="history-cards">
            <!-- Events History Card -->
            <div class="history-card">
                <div class="card-header">
                    <h2><i class="fas fa-calendar-alt"></i> Events History</h2>
                </div>
                
                <div class="card-content">
                    <?php if (!empty($eventsData)): ?>
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date & Time</th>
                                <th>Location</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($eventsData as $event): ?>
                            <tr>
                                <td>
                                    <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                                </td>
                                <td>
                                    <div class="event-date">
                                        <i class="far fa-calendar-alt"></i> 
                                        <?php echo htmlspecialchars($event['date']); ?>
                                    </div>
                                    <div class="event-time">
                                        <i class="far fa-clock"></i> 
                                        <?php echo htmlspecialchars($event['time']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="event-location">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <?php echo htmlspecialchars($event['location']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="event-status <?php echo ($event['status'] == 'completed') ? 'status-completed' : 'status-upcoming'; ?>">
                                        <?php echo ucfirst(htmlspecialchars($event['status'])); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <img src="/api/placeholder/150/150" alt="No Events">
                        <h3>No Events History Found</h3>
                        <p>You haven't registered for any events yet.</p>
                        <a href="eventlisting.php" class="action-btn">Browse Events</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recruitment History Card -->
            <div class="history-card">
                <div class="card-header">
                    <h2><i class="fas fa-users"></i> Recruitment History</h2>
                </div>
                
                <div class="card-content">
                    <?php if (count($recruitment_history) > 0): ?>
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Recruitment</th>
                                <th>Department Choices</th>
                                <th>Interview Schedule</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recruitment_history as $application): ?>
                            <tr>
                                <td>
                                    <div class="recruitment-title"><?php echo htmlspecialchars($application['recruit_title'] ?? 'No Title'); ?></div>
                                    <div class="recruitment-date">
                                        <?php echo htmlspecialchars(date('M d, Y', strtotime($application['recruit_date'] ?? 'now'))); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="department-choice primary">
                                        <i class="fas fa-star"></i> <?php echo htmlspecialchars($application['dept_choice_1']); ?>
                                    </div>
                                    <div class="department-choice secondary">
                                        <i class="far fa-star"></i> <?php echo htmlspecialchars($application['dept_choice_2']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="timeslot">
                                        <i class="far fa-calendar-alt"></i>
                                        <?php echo htmlspecialchars($application['timeslot_date'] ?? 'No Date'); ?>
                                    </div>
                                    <div class="timeslot">
                                        <i class="far fa-clock"></i>
                                        <?php echo htmlspecialchars($application['start_time'] ?? '') . ' - ' . htmlspecialchars($application['end_time'] ?? ''); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $status = isset($application['status']) ? $application['status'] : 'pending';
                                    $statusClass = '';
                                    $statusText = '';
                                    
                                    switch($status) {
                                        case 'accepted':
                                            $statusClass = 'status-completed';
                                            $statusText = 'Accepted';
                                            break;
                                        case 'rejected':
                                            $statusClass = 'status-rejected';
                                            $statusText = 'Not Selected';
                                            break;
                                        default:
                                            $statusClass = 'status-upcoming';
                                            $statusText = 'Pending';
                                            break;
                                    }
                                    ?>
                                    <span class="event-status <?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <img src="/api/placeholder/150/150" alt="No Recruitment History">
                        <h3>No Recruitment Applications Found</h3>
                        <p>You haven't applied for any recruitments yet.</p>
                        <a href="recruitmentopportunities.php" class="action-btn">Browse Recruitments</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Set the active navigation tab
        document.querySelector('.nav-items a[href="studentprofile.php"]').classList.add('active');

        // Handle avatar change
        function changeAvatar() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            
            input.onchange = function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Show preview before upload
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.querySelector('.avatar').src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                    
                    // Continue with upload
                    const formData = new FormData();
                    formData.append('avatar', file);
                    formData.append('stu_matric', '<?php echo $stu_matric; ?>');

                    fetch('upload_avatar.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            alert('Failed to upload avatar');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error uploading avatar');
                    });
                }
            };
            input.click();
        }

        // Toggle account menu
        document.querySelector('.account').addEventListener('click', function (e) {
            e.stopPropagation();
            const menu = document.querySelector('.account-menu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });

        // Close menu when clicking outside
        document.addEventListener('click', function (event) {
            const menu = document.querySelector('.account-menu');
            const account = document.querySelector('.account');
            if (!account.contains(event.target)) {
                menu.style.display = 'none';
            }
        });
    </script>
</body>
</html>