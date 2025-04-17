<?php
include('admin_auth.php');
// Include database connection
include('config.php');

// Start session to pass messages
session_start();

// Check if the admin ID is provided
if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $ad_matric = trim($_GET['id']);

    // Fetch admin details
    $query = "SELECT * FROM admin WHERE ad_matric = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $ad_matric);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if admin exists
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
    } else {
        $_SESSION['message'] = ["type" => "error", "text" => "Admin not found!"];
        header("Location: login_form.php"); // Redirect if admin is not found
        exit;
    }

    // Check if the logged-in admin is a super admin
    if ($_SESSION['admin_role'] !== 'super_admin' && $_SESSION['ad_matric'] !== $ad_matric) {
        $_SESSION['message'] = ["type" => "error", "text" => "You do not have permission to edit this admin's details!"];
        header("Location: login_form.php"); // Redirect if not authorized
        exit;
    }

} else {
    $_SESSION['message'] = ["type" => "error", "text" => "Admin ID not provided!"];
    header("Location: login_form.php"); // Redirect if no ID is provided
    exit;
}

// Handle form submission for updating admin details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $first_name = htmlspecialchars(trim($_POST['ad_first_name']));
    $last_name = htmlspecialchars(trim($_POST['ad_last_name']));
    $email = filter_var(trim($_POST['ad_email']), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['ad_phone']));
    $faculty = htmlspecialchars(trim($_POST['ad_faculty']));
    $year = intval($_POST['ad_year']);
    $gender = htmlspecialchars(trim($_POST['ad_gender']));

    // Check if the avatar file is uploaded
    if (isset($_FILES['ad_avatar']) && $_FILES['ad_avatar']['error'] == 0) {
        // Define the allowed file types and size
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $max_file_size = 5 * 1024 * 1024; // 5MB

        // Get file details
        $file_name = $_FILES['ad_avatar']['name'];
        $file_tmp_name = $_FILES['ad_avatar']['tmp_name'];
        $file_size = $_FILES['ad_avatar']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate file extension and size
        if (!in_array($file_ext, $allowed_extensions)) {
            $_SESSION['message'] = ["type" => "error", "text" => "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed."];
            header("Location: edit_admin.php?id=$ad_matric");
            exit;
        }
        if ($file_size > $max_file_size) {
            $_SESSION['message'] = ["type" => "error", "text" => "File size exceeds the maximum allowed size of 5MB."];
            header("Location: edit_admin.php?id=$ad_matric");
            exit;
        }

        // Generate a unique name for the avatar
        $avatar_new_name = 'avatar_' . time() . '.' . $file_ext;
        $avatar_path = 'uploads/avatars/' . $avatar_new_name;

        // Move the file to the desired directory
        if (!move_uploaded_file($file_tmp_name, $avatar_path)) {
            $_SESSION['message'] = ["type" => "error", "text" => "Failed to upload avatar. Please try again."];
            header("Location: edit_admin.php?id=$ad_matric");
            exit;
        }
    } else {
        // If no new avatar is uploaded, keep the old one
        $avatar_new_name = $admin['ad_avatar']; // Existing avatar from the database
    }

    // Validate matric number format
    if (!preg_match('/^[A-Za-z]{2}\d{6}$/', $ad_matric)) {
        $_SESSION['message'] = ["type" => "error", "text" => "Invalid matric number format! Must be 2 alphabets followed by 6 digits."];
        header("Location: edit_admin.php?id=$ad_matric");
        exit;
    }

    // Validate phone number format
    if (!preg_match('/^\d{1,11}$/', $phone)) {
        $_SESSION['message'] = ["type" => "error", "text" => "Invalid phone number! It must contain only digits and not exceed 11 digits."];
        header("Location: edit_admin.php?id=$ad_matric");
        exit;
    }

    // Check for duplicate matric number or email
    $duplicate_query = "SELECT * FROM admin WHERE (ad_email = ? OR ad_matric = ?) AND ad_matric != ?";
    $duplicate_stmt = $conn->prepare($duplicate_query);
    $duplicate_stmt->bind_param("sss", $email, $ad_matric, $ad_matric);
    $duplicate_stmt->execute();
    $duplicate_result = $duplicate_stmt->get_result();

    if ($duplicate_result->num_rows > 0) {
        $_SESSION['message'] = ["type" => "error", "text" => "Matric number or email already exists!"];
        header("Location: edit_admin.php?id=$ad_matric");
        exit;
    }

    // Update admin details in the database including avatar
    $update_query = "UPDATE admin SET ad_first_name = ?, ad_last_name = ?, ad_email = ?, ad_phone = ?, ad_faculty = ?, ad_year = ?, ad_gender = ?, ad_avatar = ? WHERE ad_matric = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssssssss", $first_name, $last_name, $email, $phone, $faculty, $year, $gender, $avatar_new_name, $ad_matric);

    if ($update_stmt->execute()) {
        $_SESSION['message'] = ["type" => "success", "text" => "Admin details updated successfully!"];
        header("Location: edit_admin.php?id=$ad_matric");
        exit;
    } else {
        $_SESSION['message'] = ["type" => "error", "text" => "Error updating admin details: " . $conn->error];
        header("Location: edit_admin.php?id=$ad_matric");
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
    <title>Edit Admin Details</title>
    <link rel="stylesheet" href="/EVENT_MANAGEMENT/edit_admin.css">
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
            <a href="edit_admin.php">Edit Profile</a>
        </div>

        <div class="container">
            <h2>Edit Admin Details</h2>

            <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo $_SESSION['message']['type']; ?>">
                <?php if ($_SESSION['message']['type'] === 'success'): ?>
                    <i class="fas fa-check-circle" style="color: green;"></i>
                <?php else: ?>
                    <i class="fas fa-times-circle" style="color: red;"></i>
                <?php endif; ?>
                <?php echo $_SESSION['message']['text']; ?>
            </div>
            <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="ad_first_name" value="<?php echo htmlspecialchars($admin['ad_first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="ad_last_name" value="<?php echo htmlspecialchars($admin['ad_last_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="ad_email" value="<?php echo htmlspecialchars($admin['ad_email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="ad_phone" value="<?php echo htmlspecialchars($admin['ad_phone']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="faculty">Faculty</label>
                    <select name="ad_faculty" id="ad_faculty" required>
                        <option value="FKAAB" <?php echo $admin['ad_faculty'] == 'FKAAB' ? 'selected' : ''; ?>>FKAAB - Faculty of Civil Engineering and Built Environment</option>
                        <option value="FAST" <?php echo $admin['ad_faculty'] == 'FAST' ? 'selected' : ''; ?>>FAST - Faculty of Applied Science and Technology</option>
                        <option value="FSKTM" <?php echo $admin['ad_faculty'] == 'FSKTM' ? 'selected' : ''; ?>>FSKTM - Faculty of Computer Science and Information Technology</option>
                        <option value="FPTP" <?php echo $admin['ad_faculty'] == 'FPTP' ? 'selected' : ''; ?>>FPTP - Faculty of Technology Management and Business</option>
                        <option value="FKMP" <?php echo $admin['ad_faculty'] == 'FKMP' ? 'selected' : ''; ?>>FKMP - Faculty of Mechanical and Manufacturing Engineering</option>
                        <option value="FKEE" <?php echo $admin['ad_faculty'] == 'FKEE' ? 'selected' : ''; ?>>FKEE - Faculty of Electrical and Electronic Engineering</option>
                        <option value="FPTV" <?php echo $admin['ad_faculty'] == 'FPTV' ? 'selected' : ''; ?>>FPTV - Faculty of Technical and Vocational Education</option>
                        <option value="FTK" <?php echo $admin['ad_faculty'] == 'FTK' ? 'selected' : ''; ?>>FTK - Faculty of Engineering Technology</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="year">Year</label>
                    <select name="ad_year" id="ad_year" required>
                        <option value="1" <?php echo $admin['ad_year'] == 1 ? 'selected' : ''; ?>>1</option>
                        <option value="2" <?php echo $admin['ad_year'] == 2 ? 'selected' : ''; ?>>2</option>
                        <option value="3" <?php echo $admin['ad_year'] == 3 ? 'selected' : ''; ?>>3</option>
                        <option value="4" <?php echo $admin['ad_year'] == 4 ? 'selected' : ''; ?>>4</option>
                    </select>                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select name="ad_gender" id="ad_gender" required>
                        <option value="Male" <?php echo $admin['ad_gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $admin['ad_gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                
                <button type="submit" class="btn">Update</button>
            </form>
        </div>
    </div>
</body>
</html>
