<?php
// Start session to pass messages
session_start();

include('config.php');

// Check if the student ID is provided
if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $stu_matric = trim($_GET['id']);

    // Fetch student details
    $query = "SELECT * FROM students WHERE stu_matric = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $stu_matric);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if student exists
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
    } else {
        $_SESSION['message'] = ["type" => "error", "text" => "Student not found!"];
        header("Location: studentprofile.php");
        exit;
    }
} else {
    $_SESSION['message'] = ["type" => "error", "text" => "Student ID not provided!"];
    header("Location: studentprofile.php");
    exit;
}

// Handle form submission for updating student details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $first_name = htmlspecialchars(trim($_POST['stu_first_name'] ?? ''));
    $last_name = htmlspecialchars(trim($_POST['stu_last_name'] ?? ''));
    $email = filter_var(trim($_POST['stu_email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['stu_phone_number'] ?? ''));
    $faculty = htmlspecialchars(trim($_POST['stu_faculty'] ?? ''));
    $year = intval($_POST['stu_year'] ?? 0);
    $gender = htmlspecialchars(trim($_POST['stu_gender'] ?? ''));

    // Check if email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = ["type" => "error", "text" => "Invalid email format!"];
        header("Location: editstudentprofile.php?id=$stu_matric");
        exit;
    }

    // Update student details in the database
    $update_query = "UPDATE students SET stu_first_name = ?, stu_last_name = ?, stu_email = ?, stu_phone_number = ?, stu_faculty = ?, stu_year = ?, stu_gender = ? WHERE stu_matric = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssssssss", $first_name, $last_name, $email, $phone, $faculty, $year, $gender, $stu_matric);

    if ($update_stmt->execute()) {
        $_SESSION['message'] = ["type" => "success", "text" => "Student details updated successfully!"];
        header("Location: editstudentprofile.php?id=$stu_matric");
        exit;
    } else {
        $_SESSION['message'] = ["type" => "error", "text" => "Error updating student details: " . $conn->error];
        header("Location: editstudentprofile.php?id=$stu_matric");
        exit;
    }
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
  <title>Edit Student Details</title>
  <link rel="stylesheet" href="/EVENT_MANAGEMENT/editprofile.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
    <h2>Edit Personal Details</h2>

    <!-- Display Session Message as Alert -->
    <?php
        if (isset($_SESSION['message'])):
            $message = $_SESSION['message'];  // Store the session message
            unset($_SESSION['message']);  // Clear the message after displaying it
        ?>
        <script>
            // Display alert based on session message type
            alert("<?php echo $message['text']; ?>");
        </script>
        <?php endif; ?>

        <form action="" method="POST">
            <!-- Form fields for updating student details -->
            <div class="form-group">
                <label for="stu_first_name">First Name</label>
                <input type="text" id="stu_first_name" name="stu_first_name" value="<?php echo htmlspecialchars($student['stu_first_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="stu_last_name">Last Name</label>
                <input type="text" id="stu_last_name" name="stu_last_name" value="<?php echo htmlspecialchars($student['stu_last_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="stu_email">Email</label>
                <input type="email" id="stu_email" name="stu_email" value="<?php echo htmlspecialchars($student['stu_email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="stu_phone_number">Phone</label>
                <input type="text" id="stu_phone_number" name="stu_phone_number" value="<?php echo htmlspecialchars($student['stu_phone_number'] ?? ''); ?>" required>
            </div>

        <div class="form-group">
    <label for="stu_faculty">Faculty</label>
    <select name="stu_faculty" id="stu_faculty" required>
        <option value="">Select Faculty</option>
        <option value="FKAAB" <?php echo isset($student['stu_faculty']) && $student['stu_faculty'] == 'FKAAB' ? 'selected' : ''; ?>> (FKAAB) Faculty of Civil Engineering and Built Environment</option>
        <option value="FAST" <?php echo isset($student['stu_faculty']) && $student['stu_faculty'] == 'FAST' ? 'selected' : ''; ?>>(FAST) Faculty of Applied Science and Technology</option>
        <option value="FSKTM" <?php echo isset($student['stu_faculty']) && $student['stu_faculty'] == 'FSKTM' ? 'selected' : ''; ?>> (FSKTM) Faculty of Computer Science and Information Technology</option>
        <option value="FPTP" <?php echo isset($student['stu_faculty']) && $student['stu_faculty'] == 'FPTP' ? 'selected' : ''; ?>> (FPTP) Faculty of Technology Management and Business</option>
        <option value="FKMP" <?php echo isset($student['stu_faculty']) && $student['stu_faculty'] == 'FKMP' ? 'selected' : ''; ?>> (FKMP) Faculty of Mechanical and Manufacturing Engineering</option>
        <option value="FKEE" <?php echo isset($student['stu_faculty']) && $student['stu_faculty'] == 'FKEE' ? 'selected' : ''; ?>> (FKEE) Faculty of Electrical and Electronic Engineering</option>
        <option value="FPTV" <?php echo isset($student['stu_faculty']) && $student['stu_faculty'] == 'FPTV' ? 'selected' : ''; ?>> (FPTV) Faculty of Technical and Vocational Education</option>
        <option value="FTK" <?php echo isset($student['stu_faculty']) && $student['stu_faculty'] == 'FTK' ? 'selected' : ''; ?>> (FTK) Faculty of Engineering Technology</option>
    </select>
</div>

       <div class="form-group">
                <label for="stu_year">Year</label>
                <input type="number" id="stu_year" name="stu_year" value="<?php echo htmlspecialchars($student['stu_year'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="stu_gender">Gender</label>
                <select id="stu_gender" name="stu_gender" required>
                    <option value="Male" <?php echo isset($student['stu_gender']) && $student['stu_gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo isset($student['stu_gender']) && $student['stu_gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo isset($student['stu_gender']) && $student['stu_gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <button type="submit" class="btn-save-changes">Save Changes</button>
        </form>
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
                    <li><a href="home.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
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