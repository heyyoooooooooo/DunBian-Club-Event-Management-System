<?php
// Database connection
include('config.php');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $ad_matric = 'AI220274';
    $ad_first_name = 'Ong';
    $ad_last_name = 'Yi Fung';
    $ad_gender = 'Female';
    $ad_phone = '0123456789';
    $ad_faculty = 'FSKTM';
    $ad_year = 3;
    $ad_email = 'yifung0808@gmail.com';
    $ad_password = 'Yifung0808';  // Plain password (will be hashed later)

    // Hash the password
    $hashed_password = password_hash($ad_password, PASSWORD_BCRYPT);

    // Create SQL query with the variables directly in the query string
    $sql = "INSERT INTO admin (ad_matric, ad_first_name, ad_last_name, ad_gender, ad_phone, ad_faculty, ad_year, ad_email, ad_password, ad_created_at) 
            VALUES ('$ad_matric', '$ad_first_name', '$ad_last_name', '$ad_gender', '$ad_phone', '$ad_faculty', '$ad_year', '$ad_email', '$hashed_password', NOW())";

    // Execute the SQL query
    if ($conn->query($sql) === TRUE) {
        echo "New admin account created successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close the database connection
    $conn->close();
}
?>
