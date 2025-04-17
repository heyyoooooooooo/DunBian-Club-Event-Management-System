<?php
// Start the session to use session variables
session_start();

// Connect to the database
include('config.php');

// Modify the query section near the top of your file
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $query = "SELECT * FROM recruitment 
              WHERE recruit_title LIKE '%$search%' 
              OR recruit_description LIKE '%$search%' 
              ORDER BY recruit_created_at DESC";
} else {
    $query = "SELECT * FROM recruitment ORDER BY recruit_created_at DESC";
}
$result = $conn->query($query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment Opportunities</title>
    <link rel="stylesheet" href="recruitmentoppo.css">
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
            <!-- Top section with title and info -->
            <div class="top-section">
                <h1 class="page-title">Recruitment Opportunities</h1>
            </div>

            <!-- Search bar in its own row -->
            <div class="search-section">
                <div class="search-container">
                    <form action="recruitmentopportunities.php" method="GET" class="search-form">
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

            <!-- Grid of recruitment cards -->
            <div class="recruitment-grid">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $recruit_title = $row['recruit_title'];
                        $recruit_description = $row['recruit_description'];
                        $recruit_date = $row['recruit_date'];
                        $recruit_time = $row['recruit_time'];
                        $recruit_deadline = $row['recruit_deadline'];
                        $poster = $row['recruit_poster'];
                        
                        // Format the date and time
                        $formatted_date = date("F j, Y", strtotime($recruit_date));
                        $formatted_time = date("g:i A", strtotime($recruit_time));

                        // Truncate description to 2 sentences
                        $sentences = preg_split('/(?<=[.!?])\s+/', $recruit_description, -1, PREG_SPLIT_NO_EMPTY);
                        $truncated_description = implode(' ', array_slice($sentences, 0, 2));
                        if (count($sentences) > 2) {
                            $truncated_description .= '...';
                        }
                ?>
                <!-- Recruitment Card -->
                <div class="recruitment-card">
                    <a href="recruitmentpage.php?id=<?php echo $row['recruit_id']; ?>" class="recruitment-card-link">
                        <div class="recruitment-card-header">
                            <img src="img/<?php echo $poster; ?>" alt="Recruitment Poster" class="recruitment-poster">
                        </div>
                        <div class="recruitment-card-body">
                            <h2 class="recruitment-title"><?php echo htmlspecialchars($recruit_title); ?></h2>
                            <p class="recruitment-description"><?php echo htmlspecialchars($recruit_description); ?></p>
                            <div class="recruitment-details">
                                <div class="detail-item">
                                    <!--<i class="fas fa-calendar"></i>-->
                                    <span>Date: <?php echo $formatted_date; ?></span>
                                </div>
                                <div class="detail-item">
                                    <!--<i class="fas fa-clock"></i>-->
                                    <span>Time: <?php echo $formatted_time; ?></span>
                                </div>
                                <div class="detail-item">
                                    <!--<i class="fas fa-hourglass-end"></i>-->
                                    <span>Deadline: <?php echo htmlspecialchars($recruit_deadline); ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                    <div class="card-apply-btn">
                        <a href="recruitment_applications.php?recruit_title=<?php echo $row['recruit_title']; ?>" class="apply-btn">
                            Apply Now
                        </a>
                    </div>
                </div>
                <?php
                    }
                } else {
                    echo "<p class='no-opportunities'>No recruitment opportunities available at the moment.</p>";
                }
                $conn->close();
                ?>
            </div>
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
                    <li>Email: dunbianclub@gmail.com</li>
                    <li>Phone: 0123456789</li>
                    <li>University Tun Hussein Onn Malaysia</li>
                    <li>Address: Persiaran Tun Dr. Ismail, 86400 Parit Raja, Johor Darul Ta'zim</li>
                </ul>
            </div>
        </div>
    </footer>

    <script>
        // Navigation active state
        const navLinks = document.querySelectorAll('.nav-items a');
        navLinks.forEach(link => {
            link.addEventListener('click', function () {
                navLinks.forEach(link => link.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Toggle account menu
        const account = document.querySelector('.account');
        const menu = document.querySelector('.account-menu');
        account.addEventListener('click', function (e) {
            e.stopPropagation();
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });

        // Close menu when clicking outside
        document.addEventListener('click', function (event) {
            if (!account.contains(event.target)) {
                menu.style.display = 'none';
            }
        });

    </script>
</body>
</html>