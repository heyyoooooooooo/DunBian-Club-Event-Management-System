<?php
session_start();
include("config.php");

// Ensure only super admin can access this page
if ($_SESSION['admin_role'] !== 'super_admin') {
    header("Location: add_admin.php");
    exit();
}

$error_message = ''; // For error messages
$success_message = ''; // For success messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Use ad_ prefix for form data to match table columns
    $ad_first_name = mysqli_real_escape_string($conn, $_POST['ad_first_name']);
    $ad_last_name = mysqli_real_escape_string($conn, $_POST['ad_last_name']);
    $ad_email = mysqli_real_escape_string($conn, $_POST['ad_email']);
    $ad_password = mysqli_real_escape_string($conn, $_POST['ad_password']);
    $ad_gender = mysqli_real_escape_string($conn, $_POST['ad_gender']);
    $ad_phone = mysqli_real_escape_string($conn, $_POST['ad_phone']);
    $ad_faculty = mysqli_real_escape_string($conn, $_POST['ad_faculty']);
    $ad_year = mysqli_real_escape_string($conn, $_POST['ad_year']);
    $admin_role = 'admin'; // Default role for new admins
    $ad_matric = mysqli_real_escape_string($conn, $_POST['ad_matric']); // Matric number (if needed)
    
    // Validate matric number format (2 alphabet characters + 6 numeric digits)
    if (!preg_match("/^[A-Za-z]{2}[0-9]{6}$/", $ad_matric)) {
        $error_message = 'Matric number must start with 2 alphabet characters followed by 6 numeric digits.';
    }
    
   // Validate that the phone number contains only numbers and does not exceed 11 digits
    if (!preg_match('/^[0-9]{1,11}$/', $ad_phone)) {
    $error_message = 'Phone number must not exceed 11 digits and contain only numbers.';
    }

    // Handling file upload for avatar
    if (isset($_FILES['ad_avatar']) && $_FILES['ad_avatar']['error'] === UPLOAD_ERR_OK) {
        $avatar_name = $_FILES['ad_avatar']['name'];
        $avatar_tmp_name = $_FILES['ad_avatar']['tmp_name'];
        $avatar_size = $_FILES['ad_avatar']['size'];
        $avatar_ext = pathinfo($avatar_name, PATHINFO_EXTENSION);

        // Validate file type (allow only image files)
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($avatar_ext), $allowed_extensions)) {
            // Generate a unique file name to avoid conflicts
            $avatar_new_name = uniqid('avatar_') . '.' . $avatar_ext;

            // Set the directory where the avatar will be saved
            $upload_dir = 'uploads/avatars/';

            // Move the file to the desired directory
            if (move_uploaded_file($avatar_tmp_name, $upload_dir . $avatar_new_name)) {
                $ad_avatar = $avatar_new_name;
            } else {
                $error_message = 'Error uploading avatar image.';
            }
        } else {
            $error_message = 'Only image files (jpg, jpeg, png, gif) are allowed for the avatar.';
        }
    } else {
        // If no avatar uploaded, set default value (optional)
        $ad_avatar = 'default-avatar.png';
    }

    // Check if the email or matric is already taken
    $email_check_query = "SELECT * FROM admin WHERE ad_email = '$ad_email' LIMIT 1";
    $result_email = mysqli_query($conn, $email_check_query);
    
    $matric_check_query = "SELECT * FROM admin WHERE ad_matric = '$ad_matric' LIMIT 1";
    $result_matric = mysqli_query($conn, $matric_check_query);

    if (mysqli_num_rows($result_email) > 0) {
        $error_message = 'The email address is already in use.';
    } elseif (mysqli_num_rows($result_matric) > 0) {
        $error_message = 'The matric number is already registered.';
    }

    // If no errors, proceed to insert data into the database
    if (!$error_message) {
        // Hash password before storing it
        $hashed_password = password_hash($ad_password, PASSWORD_DEFAULT);

        // Get the current date and time for ad_created_at
        $created_at = date("Y-m-d H:i:s");

        // Insert new admin into database
        $query = "INSERT INTO admin (ad_matric, ad_first_name, ad_last_name, ad_gender, ad_phone, ad_faculty, ad_year, ad_email, ad_password, ad_created_at, admin_role, ad_avatar) 
                  VALUES ('$ad_matric', '$ad_first_name', '$ad_last_name', '$ad_gender', '$ad_phone', '$ad_faculty', '$ad_year', '$ad_email', '$hashed_password', '$created_at', '$admin_role', '$ad_avatar')";

        if (mysqli_query($conn, $query)) {
            // Set success message
            $success_message = 'New admin added successfully!';
        } else {
            $error_message = 'Error adding admin: ' . mysqli_error($conn);
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/EVENT_MANAGEMENT/add_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div id="sidebar">
        <div class="brand">
            <i class="fas fa-calendar-alt"></i> Dunbian Club
        </div>
        <a href="dashboard.php" ><i class="fas fa-home"></i> Dashboard</a>
        <a href="recruitment_list.php"><i class="fas fa-user-plus"></i> Recruitments</a>
        <a href="interview.php"><i class="fas fa-clock"></i> Interview</a>
        <a href="events.php"><i class="fas fa-calendar-check"></i> Events</a>
        <a href="#"><i class="fas fa-comment-alt"></i> Feedback</a>
        <a href="profile.php" class="active"><i class="fas fa-user-circle"></i> Profile</a>
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
      <a href="dashboard.php">Home</a> > 
      <a href="profile.php">Profile</a> > 
      <a href="add_admin.php">Create New Admin</a>
    </div>

        <div class="container">
            <h2>Add New Admin</h2>

            <!-- Displaying error message if set -->
            <?php if ($error_message): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Displaying success message if set -->
            <?php if ($success_message): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>


            <form action="add_admin.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="ad_first_name">First Name</label>
                    <input type="text" name="ad_first_name" id="ad_first_name" required>
                </div>

                <div class="form-group">
                    <label for="ad_last_name">Last Name</label>
                    <input type="text" name="ad_last_name" id="ad_last_name" required>
                </div>

                <div class="form-group">
                    <label for="ad_matric">Matric Number</label>
                    <input type="text" name="ad_matric" id="ad_matric" required>
                </div>

                <div class="form-group">
                    <label for="ad_gender">Gender</label>
                    <select name="ad_gender" id="ad_gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ad_phone">Phone Number</label>
                    <input type="text" name="ad_phone" id="ad_phone" required>
                </div>

                <div class="form-group">
                    <label for="ad_faculty">Faculty</label>
                    <select name="ad_faculty" id="ad_faculty" required>
                    <option value="FKAAB">FKAAB - Faculty of Civil Engineering and Built Environment</option>
                        <option value="FAST">FAST - Faculty of Applied Science and Technology</option>
                        <option value="FSKTM">FSKTM - Faculty of Computer Science and Information Technology</option>
                        <option value="FPTP">FPTP - Faculty of Technology Management and Business</option>
                        <option value="FKMP">FKMP - Faculty of Mechanical and Manufacturing Engineering</option>
                        <option value="FKEE">FKEE - Faculty of Electrical and Electronic Engineering</option>
                        <option value="FPTV">FPTV - Faculty of Technical and Vocational Education</option>
                        <option value="FTK">FTK - Faculty of Engineering Technology</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ad_year">Year</label>
                    <select name="ad_year" id="ad_year" required>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ad_email">Email Address</label>
                    <input type="email" name="ad_email" id="ad_email" required>
                </div>

                <div class="form-group">
                    <label for="ad_password">Password</label>
                    <input type="password" name="ad_password" id="ad_password" required>
                </div>

                

                <div class="form-group">
                    <button type="submit">Add Admin</button>
                </div>
            </form>
        </div>
    </div>
    <script>
  // Toggle account menu
  document.querySelector('.account').addEventListener('click', function () {
    const menu = document.querySelector('.account-menu');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
  });


</script>
</body>
</html>
