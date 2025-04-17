<?php
// Start the session to manage login state
session_start();

// Include the database connection file
include('config.php');

// Retrieve recruitment ID from the URL
$recruitment_id = isset($_GET['id']) ? $_GET['id'] : '';
$student_id = isset($_SESSION['stu_matric']) ? $_SESSION['stu_matric'] : ''; // Assuming the student matric number is stored in the session

// Initialize message variable
$message = '';

// Check if recruitment ID is provided, if not, show an error message
if (!$recruitment_id) {
    $message = "Recruitment ID is missing.";
}

// Fetch recruitment details from the database
if ($recruitment_id) {
    $stmt = $conn->prepare("SELECT * FROM recruitment WHERE recruit_id = ?");
    $stmt->bind_param("i", $recruitment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $recruitment = $result->fetch_assoc();

    if (!$recruitment) {
        $message = "Recruitment not found.";
    }

    $stmt->close();
}

// Check if the student has already applied for this recruitment
if ($student_id && $recruitment_id) {
    $stmt = $conn->prepare("SELECT * FROM recruitment_applications WHERE stu_matric = ? AND recruit_id = ?");
    $stmt->bind_param("si", $student_id, $recruitment_id);
    $stmt->execute();
    $application_result = $stmt->get_result();
    
    // If a record is found, it means the student has already applied
    if ($application_result->num_rows > 0) {
        $message = "You have already applied for this recruitment.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment Detail - Event Website</title>
    <link rel="stylesheet" href="recruitmentpage.css">
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
                <a href="recruitmentopportunities.php" class="active">Recruitments</a>
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

    <!-- Main Content -->
    <main class="main-content">
        <div class="recruitment-container">
            <?php if ($message): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php else: ?>
                <!-- Detail Section -->
                <div class="detail-section">
                    <!-- Left Side - Poster -->
                    <div class="poster-section">
                        <!-- Display Recruitment Poster -->
                        <?php if (isset($recruitment['recruit_poster'])): ?>
                            <img src="img/<?php echo htmlspecialchars($recruitment['recruit_poster']); ?>" alt="Recruitment Poster" class="poster-image">
                        <?php endif; ?>
                    </div>

                    <!-- Right Side - Details -->
                    <div class="info-section">
                        <h1 class="job-title">
                            <?php echo htmlspecialchars($recruitment['recruit_title'] ?? 'Title not available'); ?>
                        </h1>

                        <div class="info-item">
                            <span class="info-icon">📅</span>
                            <span>Recruitment Date: <?php echo htmlspecialchars($recruitment['recruit_date'] ?? 'Not available'); ?></span>
                        </div>

                        <div class="info-item">
                            <span class="info-icon">⏰</span>
                            <span>Time: <?php echo htmlspecialchars($recruitment['recruit_time'] ?? 'Not available'); ?></span>
                        </div>

                        <div class="info-item">
                            <span class="info-icon">📍</span>
                            <span>Interview Method: Google Meet</span>
                        </div>

                        <div class="info-item">
                            <span class="info-icon">📍</span>
                            <span>Deadline: <?php echo htmlspecialchars($recruitment['recruit_deadline'] ?? 'Not available'); ?></span>
                        </div>

                        <!-- Only show Apply button if the student hasn't applied yet -->
                        <?php if ($message === ''): ?>
                            <a href="recruitment_applications.php?recruit_title=<?php echo urlencode($recruitment['recruit_title']); ?>" class="apply-button">Apply Now</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Description Section -->
                <div class="description-section">
                    <h2 class="description-title">Description</h2>
                    <div class="description-content">
                        <p>
                            <?php echo nl2br(htmlspecialchars($recruitment['recruit_description'] ?? 'Description not available')); ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3 class="footer-title">About Us</h3>
                <p>We strive to promote the art of debate, create an environment where students can learn, practice and understand the different aspects of debating through different engaging activities.</p>
            </div>
            <div class="footer-section">
                <h3 class="footer-title">Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="homepage.php">Home</a></li>
                    <li><a href="aboutpage.php">About</a></li>
                    <li><a href="events.php">Events</a></li>
                    <li><a href="recruitmentopportunities.php">Recruitments</a></li>
                    <li><a href="contactus.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3 class="footer-title">Contact Us</h3>
                <ul class="footer-links">
                    <li>Email: dunbianclub@gmail.com</li>
                    <li>Phone: 0123456789</li>
                    <li>University Tun Hussein Onn Malaysia</li>
                    <li>Address: Persiaran Tun Dr. Ismail, 86400 Parit Raja, Johor Darul Ta'zim</li>
                </ul>
            </div>
        </div>
    </footer>

    <script>
// Get all navigation links
const navLinks = document.querySelectorAll('.nav-items a');

// Add click event to each link
navLinks.forEach(link => {
    link.addEventListener('click', function () {
        // Remove active class from all links
        navLinks.forEach(link => link.classList.remove('active'));
        // Add active class to the clicked link
        this.classList.add('active');
    });
});

        // Toggle account menu
        document.querySelector('.account').addEventListener('click', function () {
            const menu = document.querySelector('.account-menu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });

        // Close menu when clicking outside
        document.addEventListener('click', function (event) {
            const account = document.querySelector('.account');
            const menu = document.querySelector('.account-menu');
            if (!account.contains(event.target)) {
                menu.style.display = 'none';
            }
        });
    </script>

</body>
</html>