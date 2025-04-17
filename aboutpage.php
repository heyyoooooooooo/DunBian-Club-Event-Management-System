<?php
// Start the session to use session variables
session_start();

// Connect to the database
include('config.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment Opportunities</title>
    <link rel="stylesheet" href="aboutpage.css">
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
                <a href="aboutpage.php" class="active">About</a>
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
    <div class="page-wrapper">
    <!-- Content -->
            <div class="about-container">
                <!-- Hero Section -->
                <div class="intro-section">
                    <h1>About DunBian Club</h1>
                    <p class="main-description">
                        Welcome to DunBian Club, we bring together students passionate about public speaking and critical thinking. Learn more about our mission, vision and values.
                    </p>
                </div>

                <!-- Mission & Vision -->
                <div class="mission-vision-section">
                    <div class="mission-box">
                        <h2>Our Mission</h2>
                        <p>We strive to promote the art of debate, create an environment where students can learn, practice and understand the different aspects of debating through different engaging activities.</p>
                    </div>
                    <div class="vision-box">
                        <h2>Our Vision</h2>
                        <p>To be the leading debate club that shapes confident, articulate and thoughtful leaders of tomorrow.</p>
                    </div>
                </div>

                <!-- History Section -->
                <div class="history-section">
                    <h2>Our Journey</h2>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="year">2017</div>
                            <div class="content">
                                <h3>Club Foundation</h3>
                                <p>DunBian Club was established with a vision to promote debate culture at UTHM.</p>
                            </div>
                        </div>
                </div>

                <!-- What We Do Section -->
                <div class="activities-section">
                    <h2>What We Do</h2>
                    <div class="activities-grid">
                        <div class="activity-card">
                            <i class="fas fa-trophy"></i>
                            <h3>Competitions</h3>
                            <p>Participate in national and international debate tournaments.</p>
                        </div>
                        <div class="activity-card">
                            <i class="fas fa-users"></i>
                            <h3>Activity Sessions</h3>
                            <p>Sessions introduce debate, boosting critical thinking, public speaking, and argument skills.</p>
                        </div>
                        <div class="activity-card">
                            <i class="fas fa-graduation-cap"></i>
                            <h3>Mentorship</h3>
                            <p>Experienced debaters guiding newcomers.</p>
                        </div>
                    </div>
                </div>

                <!-- Team Section -->
                <div class="team-section">
                    <h2>Our Leadership Team</h2>
                    <div class="team-grid">
                        <div class="team-member">
                            <img src="img/martin.jpg" alt="President" />
                            <h3>Club President</h3>-<br>
                            <h4>Lim Teck Sheng</h4>
                            <p>Responsible for leading the club with vision and dedication, ensuring the club’s activities align with its goals. The President represents the club externally and makes key decisions to drive the club forward.</p>
                        </div>
                        <div class="team-member">
                            <img src="img/chuakaizen.jpg" alt="Vice President" />
                            <h3>Vice President</h3>-
                            <h4>Chua Kai Zen</h4>
                            <p>Assists the President in managing club activities and operations. The Vice President supports the growth and development of the club and steps in for the President when needed to ensure smooth leadership.</p>
                        </div>
                        <div class="team-member">
                            <img src="img/tianxian.jpg" alt="Vice President" />
                            <h3>Vice President</h3>- <br>
                            <h4>Tan Tian Xian</h4>
                            <p>Assists the President in managing club activities and operations. The Vice President supports the growth and development of the club and steps in for the President when needed to ensure smooth leadership.</p>
                        </div>
                        <div class="team-member">
                            <img src="img/yewen.jpg" alt="Secretary" />
                            <h3>Secretary</h3>- <br>
                            <h4>Tan Ye Wen</h4>
                            <p>Manages the administrative duties of the club, including maintaining records, taking meeting minutes, and organizing communication among members. The Secretary ensures that all documents and processes are well-managed and accessible.</p>
                        </div>
                        <div class="team-member">
                            <img src="img/yingyan.jpg" alt="Treasurer" />
                            <h3>Treasurer</h3>- <br>
                            <h4>Ling Ying Yan</h4>
                            <p>Oversees the club’s financial activities, including managing the budget, tracking expenses, and ensuring transparency. The Treasurer organizes fundraising efforts to support the club’s initiatives and events.</p>
                        </div>
                        <div class="team-member">
                            <img src="img/jocelyn.jpg" alt="Multimedia" />
                            <h3>Multimedia Leader</h3>- <br>
                            <h4>Jocelyn Song Zhi Wei</h4>
                            <p>Responsible for creating and managing all multimedia content for the club, including videos, graphics, and promotional materials. The Multimedia Leader ensures that the club's digital presence is engaging and professionally maintained across social media platforms and event promotions.</p>
                        </div>
                        <div class="team-member">
                            <img src="img/yiqian.jpg" alt="Event" />
                            <h3>Event Coordinator Leader</h3>- <br>
                            <h4>Choi Yi Qian</h4>
                            <p>Organizes and manages all club events, ensuring smooth logistics and execution. The Event Coordinator Leader works to ensure that events are well-planned, promoted, and executed, providing a memorable experience for attendees.</p>
                        </div>
                        <div class="team-member">
                            <img src="img/yikwon.jpg" alt="GeneralAffairs" />
                            <h3>General Affairs Leader</h3>- <br>
                            <h4>Chow Yik Wonn</h4>
                            <p>Handles the internal operations of the club, including membership management and internal communication. The General Affairs Leader ensures that all administrative tasks are well-organized and that the club’s activities run efficiently.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
