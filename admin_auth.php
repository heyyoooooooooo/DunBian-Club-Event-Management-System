<?php
include ('config.php');

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['register'])) {
    $ad_matric = 'AI220274';
    $ad_first_name = 'Ong';
    $ad_last_name = 'Yi Fung';
    $ad_gender = 'Female';
    $ad_phone = '0123456789';
    $ad_faculty = 'FSKTM';
    $ad_year = 3;
    $ad_email = 'yifung0808@gmail.com';
    $ad_password = 'Yifung0808';
    $hashed_password = password_hash($ad_password, PASSWORD_BCRYPT);
    $ad_created_at = date("Y-m-d H:i:s");

    $check_query = "SELECT * FROM admin WHERE ad_email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $ad_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $insert_query = "INSERT INTO admin (
            ad_matric, ad_first_name, ad_last_name, ad_gender, ad_phone, ad_faculty, ad_year, ad_email, ad_password, ad_created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssssssisss", $ad_matric, $ad_first_name, $ad_last_name, $ad_gender, $ad_phone, $ad_faculty, $ad_year, $ad_email, $hashed_password, $ad_created_at);
        if ($stmt->execute()) {
            echo "Admin account created successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Super admin already exists.";
    }
    $stmt->close();
}

if (isset($_POST['login'])) {
    $input_email = $_POST['email'];
    $input_password = $_POST['password'];

    $query = "SELECT ad_password FROM admin WHERE ad_email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $input_email);
    $stmt->execute();
    $stmt->bind_result($stored_hashed_password);
    $stmt->fetch();
    $stmt->close();

    if ($stored_hashed_password && password_verify($input_password, $stored_hashed_password)) {
        echo "Login successful!";
    } else {
        echo "Incorrect email or password.";
    }
}

$conn->close();
?>
