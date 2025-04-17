<?php
// Database connection
include 'config.php';

// Function to sanitize input
function sanitize($conn, $input) {
    return mysqli_real_escape_string($conn, $input);
}

// Check what type of filters we need to populate
if (isset($_POST['type']) && isset($_POST['filter'])) {
    $type = sanitize($conn, $_POST['type']);
    $filter = sanitize($conn, $_POST['filter']);
    
    $output = '';
    
    // Handle student filters
    if ($type == 'student') {
        switch ($filter) {
            case 'faculty':
                $query = "SELECT DISTINCT stu_faculty FROM students ORDER BY stu_faculty";
                $result = mysqli_query($conn, $query);
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $output .= "<option value='" . $row['stu_faculty'] . "'>" . $row['stu_faculty'] . "</option>";
                }
                break;
                
            case 'year':
                $query = "SELECT DISTINCT stu_year FROM students ORDER BY stu_year";
                $result = mysqli_query($conn, $query);
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $output .= "<option value='" . $row['stu_year'] . "'>" . $row['stu_year'] . "</option>";
                }
                break;
                
            case 'gender':
                $output .= "<option value='Male'>Male</option>";
                $output .= "<option value='Female'>Female</option>";
                break;
        }
    }
    // Handle admin filters
    else if ($type == 'admin') {
        switch ($filter) {
            case 'faculty':
                $query = "SELECT DISTINCT ad_faculty FROM admin ORDER BY ad_faculty";
                $result = mysqli_query($conn, $query);
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $output .= "<option value='" . $row['ad_faculty'] . "'>" . $row['ad_faculty'] . "</option>";
                }
                break;
                
            case 'year':
                $query = "SELECT DISTINCT ad_year FROM admin ORDER BY ad_year";
                $result = mysqli_query($conn, $query);
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $output .= "<option value='" . $row['ad_year'] . "'>" . $row['ad_year'] . "</option>";
                }
                break;
        }
    }
    
    if (empty($output)) {
        echo "<option value=''>No options available</option>";
    } else {
        echo $output;
    }
} else {
    echo "<option value=''>Error: Missing parameters</option>";
}
?>