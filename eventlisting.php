<?php
// Start the session to use session variables
session_start();

// Connect to the database
include('config.php');

// Modify the query section near the top of your file
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $query = "SELECT * FROM events 
              WHERE event_title LIKE '%$search%' 
              OR event_description LIKE '%$search%' 
              ORDER BY event_created_at DESC";
} else {
    $query = "SELECT * FROM events ORDER BY event_created_at DESC";
}
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Listings - DunBian Club</title>
    <link rel="stylesheet" href="eventlisting.css">
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
                <a href="eventlisting.php" class="active">Events</a>
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

    <!-- Main Content -->
    <main class="main-content">
        <h1 class="page-title">Event Listings</h1>

         <!-- Search bar in its own row -->
         <div class="search-section">
                <div class="search-container">
                    <form action="eventlisting.php" method="GET" class="search-form">
                        <div class="search-wrapper">
                            <input type="text" name="search" placeholder="Search..." 
                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                class="search-input">
                            <button type="submit" class="search-button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        <div class="event-grid">
            <?php
            // Check if there are any records to display
            if ($result->num_rows > 0) {
                // Loop through all event records and display them
                while ($row = $result->fetch_assoc()) {
                    $event_id = $row['event_id'];
                    $event_title = $row['event_title'];
                    $event_description = $row['event_description'];
                    $event_date = $row['event_date'];
                    $event_time = $row['event_time'];
                    $event_location = $row['event_location'] ?? 'TBA'; // Added location with fallback
                    $event_deadline = $row['event_deadline'];
                    $poster = $row['event_poster']; 
                    
                    // Format the date and time
                    $formatted_date = date("F j, Y", strtotime($event_date));
                    $formatted_time = date("g:i A", strtotime($event_time));
                    $formatted_deadline = date("F j, Y", strtotime($event_deadline));
            ?>
            <!-- Event Card -->
            <div class="event-card">
                <a href="eventpage.php?id=<?php echo $event_id; ?>" class="event-card-link">
                    <div class="event-card-header">
                     <img src="img/<?php echo $poster; ?>" alt="Event Poster" class="event-poster">

                    </div>
                    <div class="event-card-body">
                        <h2 class="event-title"><?php echo htmlspecialchars($event_title); ?></h2>
                        <p class="event-description">
                            <?php 
                            // Truncate description to 3 lines
                            $description = htmlspecialchars($event_description);
                            $sentences = preg_split('/(?<=[.!?])\s+/', $description, -1, PREG_SPLIT_NO_EMPTY);
                            $truncated_description = implode(' ', array_slice($sentences, 0, 3));
                            if (count($sentences) > 3) {
                                echo $truncated_description . '...';
                            } else {
                                echo $truncated_description;
                            }
                            ?>
                        </p>
                        <div class="event-details">
                            <div class="detail-item">
                                <!--<i class="fas fa-calendar"></i>-->
                                <span>Date: <?php echo $formatted_date; ?></span>
                            </div>
                            <div class="detail-item">
                                <!--<i class="fas fa-clock"></i>-->
                                <span>Time: <?php echo $formatted_time; ?></span>
                            </div>
                            <div class="detail-item">
                                <!--<i class="fas fa-map-marker-alt"></i>-->
                                <span>Location: <?php echo htmlspecialchars($event_location); ?></span>
                            </div>
                            <div class="detail-item">
                                <!--<i class="fas fa-hourglass-end"></i>-->
                                <span>Register by: <?php echo $formatted_deadline; ?></span>
                            </div>
                        </div>
                    </div>
                </a>
                <div class="card-apply-btn">
                    <?php if(isset($_SESSION['stu_matric'])): ?>
                        <a href="eventpage.php?id=<?php echo $event_id; ?>" class="apply-btn">Register</a>
                    <?php else: ?>
                        <a href="login_form.php" class="apply-btn"><b>Register Now</b></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php
                }
            } else {
                echo "<div class='no-events'><p>No events available at the moment. Please check back later!</p></div>";
            }

            // Close the database connection
            $conn->close();
            ?>
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
                    <li><a href="eventlisting.php">Events</a></li>
                    <li><a href="recruitmentopportunities.php">Recruitments</a></li>
                    <li><a href="contactus.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3 class="footer-title">Contact Us</h3>
                <ul class="footer-links">
                    <li><i class="fas fa-envelope"></i> dunbianclub@gmail.com</li>
                    <li><i class="fas fa-phone"></i> 0123456789</li>
                    <li><i class="fas fa-university"></i> University Tun Hussein Onn Malaysia</li>
                    <li><i class="fas fa-map-marker-alt"></i> Persiaran Tun Dr. Ismail, 86400 Parit Raja, Johor Darul Ta'zim</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 DunBian Club. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Get all navigation links
        const navLinks = document.querySelectorAll('.nav-items a');

        // Add click event to each link
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Remove active class from all links
                navLinks.forEach(link => link.classList.remove('active'));
                // Add active class to the clicked link
                this.classList.add('active');
            });
        });

        // Toggle account menu
        document.querySelector('.account')?.addEventListener('click', function() {
            const menu = document.querySelector('.account-menu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const account = document.querySelector('.account');
            const menu = document.querySelector('.account-menu');
            if (account && !account.contains(event.target)) {
                menu.style.display = 'none';
            }
        });
    </script>
</body>
</html>