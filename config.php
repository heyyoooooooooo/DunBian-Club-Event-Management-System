<?php
// Database connection details
$servername = "localhost"; // database server's IP address
$username = "root"; // MySQL username
$password = ""; // MySQL password
$dbname = "event_management"; // database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
mysqli_query($conn, "SET time_zone = '+08:00'"); //Set timezone for not using UTC or Berlin time

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
