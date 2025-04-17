<?php 
session_start();
include("config.php");

$error_message = ''; // Initialize error message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = 'Both email and password are required.';
    } else {
        $query_admin = "SELECT * FROM admin WHERE ad_email = ?";
        $query_student = "SELECT * FROM students WHERE stu_email = ?";

        $stmt_admin = $conn->prepare($query_admin);
        $stmt_admin->bind_param("s", $email);
        $stmt_admin->execute();
        $result_admin = $stmt_admin->get_result();

        $stmt_student = $conn->prepare($query_student);
        $stmt_student->bind_param("s", $email);
        $stmt_student->execute();
        $result_student = $stmt_student->get_result();

        // Check admin login
        if ($result_admin->num_rows > 0) {
            $row = $result_admin->fetch_assoc();
            if (password_verify($password, $row['ad_password'])) {
                $_SESSION['ad_matric'] = $row['ad_matric'];
                $_SESSION['admin_name'] = $row['ad_first_name'];
                $_SESSION['admin_role'] = $row['admin_role'];

                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = 'Incorrect password for admin.';
            }
        } 
        // Check student login
        elseif ($result_student->num_rows > 0) {
            $row = $result_student->fetch_assoc();
            if (password_verify($password, $row['stu_password'])) {
                $_SESSION['stu_matric'] = $row['stu_matric'];
                $_SESSION['stu_first_name'] = $row['stu_first_name'];

                // Redirect to the previous page or default to recruitment opportunities
                $redirect_url = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'recruitmentopportunities.php';
                unset($_SESSION['redirect_url']); // Clear the redirect URL
                header("Location: $redirect_url");
                exit();
            } else {
                $error_message = 'Incorrect password for student.';
            }
        } 
        // No account found
        else {
            $error_message = 'No account found with this email address.';
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
    <link rel="stylesheet" href="/EVENT_MANAGEMENT/login.css">
    <title>Login</title>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Login</header>

            <?php if (!empty($error_message)): ?>
                <div class="message error">
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="field input">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="field input">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div class="field">
                <button type="submit" name="submit" class="btn">Login</button>
            </div>
            <div class="links">
                    Don't have an account? <a href="register_form.php?redirect=recruitmentopportunities.php">Sign Up Now</a><br>
                    Forgot Password? <a href="forgot_password.php">Click Here</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
