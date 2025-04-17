<?php
include("config.php");

date_default_timezone_set('Asia/Kuala_Lumpur'); // Set the timezone for PHP

if (isset($_GET['token']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = mysqli_real_escape_string($conn, $_GET['token']); // Secure token input
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    if ($password === $cpassword) {
        // Check if token exists in admin or students table
        $admin_query = "SELECT * FROM admin WHERE token='$token'";
        $student_query = "SELECT * FROM students WHERE token='$token'";

        $admin_result = mysqli_query($conn, $admin_query);
        $student_result = mysqli_query($conn, $student_query);

        if (mysqli_num_rows($admin_result) > 0) {
            $user_type = 'admin';
            $row = mysqli_fetch_assoc($admin_result);
            $email = $row['ad_email']; // Use the 'ad_email' field for admin
        } elseif (mysqli_num_rows($student_result) > 0) {
            $user_type = 'student';
            $row = mysqli_fetch_assoc($student_result);
            $email = $row['stu_email']; // Use the 'stu_email' field for student
        }

        if (isset($user_type)) {
            $expiry = $row['expiry_date']; // Expiry time from the database

            // Convert expiry time to Asia/Kuala_Lumpur timezone for comparison
            $expiry_time = new DateTime($expiry, new DateTimeZone('UTC')); // Assuming expiry is stored in UTC
            $expiry_time->setTimezone(new DateTimeZone('Asia/Kuala_Lumpur')); // Convert to the correct timezone

            $current_time = new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur')); // Current time in Asia/Kuala_Lumpur timezone

            // Compare expiry with the current time
            if ($expiry_time > $current_time) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Update password based on user type
                if ($user_type == 'admin') {
                    $update_stmt = $conn->prepare("UPDATE admin SET ad_password = ? WHERE ad_email = ?");
                } else {
                    $update_stmt = $conn->prepare("UPDATE students SET stu_password = ? WHERE stu_email = ?");
                }

                $update_stmt->bind_param("ss", $hashed_password, $email);

                if ($update_stmt->execute()) {
                    // Clear the token and expiry after password reset
                    if ($user_type == 'admin') {
                        $clear_token_stmt = $conn->prepare("UPDATE admin SET token = NULL, expiry_date = NULL WHERE ad_email = ?");
                    } else {
                        $clear_token_stmt = $conn->prepare("UPDATE students SET token = NULL, expiry_date = NULL WHERE stu_email = ?");
                    }
                    $clear_token_stmt->bind_param("s", $email);
                    $clear_token_stmt->execute();

                    echo "<p class='success-message'>Password reset successful. <a href='login_form.php'>Login here</a>.</p>";
                } else {
                    echo "<p class='error-message'>Error updating password. Please try again later.</p>";
                }
            } else {
                echo "<p class='error-message'>Invalid or expired token. Please request a new password reset.</p>";
            }
        } else {
            echo "<p class='error-message'>Invalid token. Please request a new password reset.</p>";
        }
    } else {
        echo "<p class='error-message'>Passwords do not match. Please try again.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="/EVENT_MANAGEMENT/reset_password.css">
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Reset Password</header>
            <form action="" method="post">
                <div class="field input">
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div class="field input">
                    <label for="cpassword">Confirm Password</label>
                    <input type="password" name="cpassword" id="cpassword" required>
                </div>
                <div class="field">
                    <input type="submit" class="btn" value="Reset Password">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
