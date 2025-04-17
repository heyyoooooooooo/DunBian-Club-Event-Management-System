<?php
// Include PHPMailer files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-6.9.2/src/Exception.php';
require 'PHPMailer-6.9.2/src/PHPMailer.php';
require 'PHPMailer-6.9.2/src/SMTP.php';

include("config.php");
date_default_timezone_set('Asia/Kuala_Lumpur'); 

$message = "";
$message_class = ""; // Class to style the message (success/failed)

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $user_type = ''; 

    // Check if email exists in admin or student table
    $admin_query = "SELECT * FROM admin WHERE ad_email='$email'";
    $student_query = "SELECT * FROM students WHERE stu_email='$email'";

    $admin_result = mysqli_query($conn, $admin_query);
    $student_result = mysqli_query($conn, $student_query);

    if (mysqli_num_rows($admin_result) > 0) {
        $user_type = 'admin';
        $row = mysqli_fetch_assoc($admin_result);
    } elseif (mysqli_num_rows($student_result) > 0) {
        $user_type = 'student';
        $row = mysqli_fetch_assoc($student_result);
    }

    if ($user_type) {
        // Generate a unique token for password reset
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Update the token and expiry_date in the respective table
        if ($user_type == 'admin') {
            $update_token_query = "UPDATE admin SET token='$token', expiry_date='$expiry' WHERE ad_email='$email'";
        } else {
            $update_token_query = "UPDATE students SET token='$token', expiry_date='$expiry' WHERE stu_email='$email'";
        }

        if (mysqli_query($conn, $update_token_query)) {
            // Send reset link to user's email 
            $reset_link = "http://localhost/Event_Management/reset_password.php?token=$token";
            $subject = "Password Reset Request";

            $email_message = "
                <p>Click the following link to reset your password:</p>
                <p><a href='$reset_link'>$reset_link</a></p>
                <p>This link will expire in 1 hour, so please reset your password as soon as possible.</p>
                <p>If you did not request a password reset, please ignore this email.</p>
            ";

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'yifung0808@gmail.com';
                $mail->Password = 'cfso thaw kybd pdxr';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                $mail->setFrom('yifung0808@gmail.com', 'No Reply');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $email_message;

                $mail->send();
                $message = "An email has been sent with password reset instructions.";
                $message_class = "success";
            } catch (Exception $e) {
                $message = "Failed to send email. Mailer Error: {$mail->ErrorInfo}";
                $message_class = "failed";
            }
        } else {
            $message = "Failed to generate reset token.";
            $message_class = "failed";
        }
    } else {
        $message = "No account found with that email address.";
        $message_class = "failed";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="/EVENT_MANAGEMENT/forgotpassword.css">
    <style>
        .message-container {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-size: 14px;
            max-width: 400px;
        }
        .message-container.success {
            background-color: #e6ffe6;
            color: #2e7d32;
            border: 1px solid #c1e1c1;
        }
        .message-container.failed {
            background-color: #ffe6e6;
            color: #d32f2f;
            border: 1px solid #e1c1c1;
        }
        .message-container .icon {
            font-size: 20px;
            margin-right: 10px;
        }
        .message-container.success .icon {
            color: #2e7d32; 
        }
        .message-container.failed .icon {
            color: #d32f2f; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Forgot Password</header>
            
            <?php if (!empty($message)) : ?>
                <div class="message-container <?php echo $message_class; ?>">
                    <span class="icon">
                        <?php echo $message_class === 'success' ? '&#10004;' : '&#10006;'; ?>
                    </span>
                    <span><?php echo $message; ?></span>
                </div>
            <?php endif; ?>
            
            <form action="" method="post">
                <div class="field input">
                    <label for="email">Enter Your Email</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="field">
                    <input type="submit" class="btn" value="Send Reset Link">
                </div>
            </form>
        </div>
    </div>
</body>
</html>