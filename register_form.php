<?php
include("config.php");
$error_message = '';
$success = false;

// Get the redirect parameter from the URL or set a default value
$redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : 'recruitmentopportunities.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input values
    $stu_matric = mysqli_real_escape_string($conn, $_POST['stu_matric']);
    $stu_first_name = mysqli_real_escape_string($conn, $_POST['stu_first_name']);
    $stu_last_name = mysqli_real_escape_string($conn, $_POST['stu_last_name']);
    $stu_gender = mysqli_real_escape_string($conn, $_POST['stu_gender']);
    $stu_phone_number = mysqli_real_escape_string($conn, $_POST['stu_phone_number']);
    $stu_faculty = mysqli_real_escape_string($conn, $_POST['stu_faculty']);
    $stu_year = intval($_POST['stu_year']);
    $stu_email = mysqli_real_escape_string($conn, $_POST['stu_email']);
    $stu_password = $_POST['stu_password'];
    $stu_confirm_password = $_POST['stu_cpassword'];

    // Validate passwords match
    if ($stu_password !== $stu_confirm_password) {
        $error_message = "Passwords do not match. Please try again!";
    } else {
        // Hash the password
        $stu_password_hash = password_hash($stu_password, PASSWORD_BCRYPT);

        // Check if matric number or email already exists
        $verify_query = mysqli_query($conn, "SELECT stu_matric, stu_email FROM students WHERE stu_matric='$stu_matric' OR stu_email='$stu_email'");
        if (mysqli_num_rows($verify_query) > 0) {
            $error_message = "This matric number or email is already registered, try another one!";
        } else {
            // Insert data into database
            $insert_query = "INSERT INTO students (stu_matric, stu_first_name, stu_last_name, stu_gender, stu_phone_number, stu_faculty, stu_year, stu_email, stu_password) 
                             VALUES ('$stu_matric', '$stu_first_name', '$stu_last_name', '$stu_gender', '$stu_phone_number', '$stu_faculty', '$stu_year', '$stu_email', '$stu_password_hash')";
            if (mysqli_query($conn, $insert_query)) {
                // After successful registration, log the user in by setting session variables
                session_start();
                $_SESSION['stu_matric'] = $stu_matric;
                $_SESSION['stu_first_name'] = $stu_first_name;
                $_SESSION['stu_last_name'] = $stu_last_name;
                $_SESSION['stu_email'] = $stu_email;
                $_SESSION['stu_faculty'] = $stu_faculty;

                // Registration successful, redirect to the desired page
                echo "<script>
                    alert('Registration successful! Redirecting...');
                    window.location.href = '$redirect_url';
                </script>";
                exit;
            } else {
                $error_message = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="registration.css">
    <title>Register</title>
</head>
<body>
    <div class="container">
        <h1>Sign Up</h1>
        <?php if (!empty($error_message)): ?>
            <div class="message error">
                <p><?php echo $error_message; ?></p>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="message success">
                <p>Registration <span>successful</span>! Redirecting...</p>
            </div>
        <?php else: ?>
            <form action="register_form.php?redirect=<?php echo urlencode($redirect_url); ?>" method="POST">

                <!-- Matric Number -->
                <div class="form-group">
                    <label for="stu_matric">Matric Number</label>
                    <input type="text" name="stu_matric" id="stu_matric" autocomplete="off" required>
                </div>

                <!-- First Name and Last Name -->
                <div class="row">
                    <div class="col">
                        <label for="stu_first_name">First Name</label>
                        <input type="text" name="stu_first_name" id="stu_first_name" autocomplete="off" required>
                    </div>
                    <div class="col">
                        <label for="stu_last_name">Last Name</label>
                        <input type="text" name="stu_last_name" id="stu_last_name" autocomplete="off" required>
                    </div>
                </div>

                <!-- Gender and Phone Number -->
                <div class="row">
                    <div class="col">
                        <label for="stu_gender">Gender</label>
                        <select name="stu_gender" id="stu_gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="col">
                        <label for="stu_phone_number">Phone Number</label>
                        <input type="text" name="stu_phone_number" id="stu_phone_number" autocomplete="off" required>
                    </div>
                </div>

                <!-- Faculty and Year -->
                <div class="row">
                    <div class="col">
                        <label for="stu_faculty">Faculty</label>
                        <select name="stu_faculty" id="stu_faculty" required>
                            <option value="">Select Faculty</option>
                            <option value="FKAAB"> (FKAAB) Faculty of Civil Engineering and Built Environment</option>
                            <option value="FAST">(FAST) Faculty of Applied Science and Technology</option>
                            <option value="FSKTM">(FSKTM) Faculty of Computer Science and Information Technology</option>
                            <option value="FPTP">(FPTP) Faculty of Technology Management and Business</option>
                            <option value="FKMP">(FKMP) Faculty of Mechanical and Manufacturing Engineering</option>
                            <option value="FKEE">(FKEE) Faculty of Electrical and Electronic Engineering</option>
                            <option value="FPTV">(FPTV) Faculty of Technical and Vocational Education</option>
                            <option value="FTK">(FTK) Faculty of Engineering Technology</option>
                        </select>
                    </div>
                    <div class="col">
                        <label for="stu_year">Year</label>
                        <input type="number" name="stu_year" id="stu_year" autocomplete="off" required>
                    </div>
                </div>

                <!-- Email and Password -->
                <div class="form-group">
                    <label for="stu_email">Email</label>
                    <input type="email" name="stu_email" id="stu_email" autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label for="stu_password">Password</label>
                    <input type="password" name="stu_password" id="stu_password" autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label for="stu_cpassword">Confirm Password</label>
                    <input type="password" name="stu_cpassword" id="stu_cpassword" autocomplete="off" required>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="register-btn">Register</button>

                <!-- Login Link -->
                <div class="login-link">
                    Already a member? <a href="login_form.php">Sign In</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
