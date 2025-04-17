<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
include 'config.php';

// Set up the date and time for reports
$current_date = date('Y-m-d');
$current_time = date('H:i:s');

// Function to sanitize input
function sanitize($conn, $input) {
    return mysqli_real_escape_string($conn, $input);
}

// Check report type and generate appropriate report
if (isset($_POST['type'])) {
    $report_type = sanitize($conn, $_POST['type']);
    
    // For debugging
    error_log("Generating report of type: " . $report_type);
    error_log("POST data: " . print_r($_POST, true));
    
    switch ($report_type) {
        case 'event':
            generateEventReport($conn);
            break;
        case 'recruitment':
            generateRecruitmentReport($conn);
            break;
        case 'attendance':
            generateAttendanceReport($conn);
            break;
        case 'student':
            generateStudentReport($conn);
            break;
        case 'admin':
            generateAdminReport($conn);
            break;
        default:
            echo "<p>Invalid report type selected.</p>";
    }
} else {
    echo "<p>No report type specified.</p>";
}

// Event Registration Report
function generateEventReport($conn) {
    if (!isset($_POST['event_id']) || empty($_POST['event_id'])) {
        echo "<p>No event selected.</p>";
        return;
    }
    
    $event_id = sanitize($conn, $_POST['event_id']);
    
    // Get event details
    $event_query = "SELECT * FROM events WHERE event_id = '$event_id'";
    $event_result = mysqli_query($conn, $event_query);
    
    if (!$event_result) {
        echo "<p>Database error: " . mysqli_error($conn) . "</p>";
        return;
    }
    
    if (mysqli_num_rows($event_result) == 0) {
        echo "<p>Event not found.</p>";
        return;
    }
    
    $event_data = mysqli_fetch_assoc($event_result);
    
    // Get registrations for this event
    $registrations_query = "SELECT er.*, s.stu_first_name, s.stu_last_name, s.stu_email, s.stu_phone_number, s.stu_faculty, s.stu_year 
                           FROM events_registrations er
                           LEFT JOIN students s ON er.stu_matric = s.stu_matric
                           WHERE er.event_id = '$event_id'
                           ORDER BY er.registration_date ASC";
    $registrations_result = mysqli_query($conn, $registrations_query);
    
    if (!$registrations_result) {
        echo "<p>Database error: " . mysqli_error($conn) . "</p>";
        return;
    }
    
    $total_registrations = mysqli_num_rows($registrations_result);
    
    // Start report output
    $output = "
    <div id='event-report-content'>
        <div class='report-summary' style='display:block;'>
            <h3>Event Registration Report</h3>
            <div class='summary-item'>
                <span>Event Name:</span>
                <span>{$event_data['event_title']}</span>
            </div>
            <div class='summary-item'>
                <span>Event Date:</span>
                <span>{$event_data['event_date']}</span>
            </div>
            <div class='summary-item'>
                <span>Event Time:</span>
                <span>{$event_data['event_time']}</span>
            </div>
            <div class='summary-item'>
                <span>Total Registrations:</span>
                <span>{$total_registrations}</span>
            </div>
            <div class='summary-item'>
                <span>Report Generated:</span>
                <span>" . date('Y-m-d H:i:s') . "</span>
            </div>
        </div>
        
        <button class='btn btn-success print-button' style='display:inline-block;' onclick='printReport(\"event-report-content\")'>
            <i class='fas fa-print'></i> Print Report
        </button>";
    
    if ($total_registrations > 0) {
        $output .= "
        <table class='report-table'>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Matric Number</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Faculty</th>
                    <th>Year</th>
                    <th>Registration Date</th>
                </tr>
            </thead>
            <tbody>";
        
        $count = 1;
        while ($row = mysqli_fetch_assoc($registrations_result)) {
            $student_name = $row['stu_first_name'] . ' ' . $row['stu_last_name'];
            $output .= "
            <tr>
                <td>{$count}</td>
                <td>{$student_name}</td>
                <td>{$row['stu_matric']}</td>
                <td>{$row['stu_email']}</td>
                <td>{$row['stu_phone_number']}</td>
                <td>{$row['stu_faculty']}</td>
                <td>{$row['stu_year']}</td>
                <td>{$row['registration_date']}</td>
            </tr>";
            $count++;
        }
        
        $output .= "
            </tbody>
        </table>";
    } else {
        $output .= "<p class='no-data'>No registrations found for this event.</p>";
    }
    
    $output .= "</div>";
    echo $output;
}

// Recruitment Applications Report
function generateRecruitmentReport($conn) {
    if (!isset($_POST['recruitment_id']) || empty($_POST['recruitment_id'])) {
        echo "<p>No recruitment selected.</p>";
        return;
    }
    
    $recruitment_id = sanitize($conn, $_POST['recruitment_id']);
    
    // Get recruitment details
    $recruitment_query = "SELECT * FROM recruitment WHERE recruit_id = '$recruitment_id'";
    $recruitment_result = mysqli_query($conn, $recruitment_query);
    
    if (!$recruitment_result) {
        echo "<p>Database error: " . mysqli_error($conn) . "</p>";
        return;
    }
    
    if (mysqli_num_rows($recruitment_result) == 0) {
        echo "<p>Recruitment not found.</p>";
        return;
    }
    
    $recruitment_data = mysqli_fetch_assoc($recruitment_result);
    
    // Get applications for this recruitment
    $applications_query = "SELECT ra.*, s.stu_first_name, s.stu_last_name, s.stu_email, s.stu_phone_number, 
                           s.stu_faculty, s.stu_year, it.timeslot_date 
                           FROM recruitment_applications ra
                           LEFT JOIN students s ON ra.stu_matric = s.stu_matric
                           LEFT JOIN interview_times it ON ra.timeslot_id = it.timeslot_id
                           WHERE ra.recruit_id = '$recruitment_id'
                           ORDER BY ra.application_date ASC";
    $applications_result = mysqli_query($conn, $applications_query);
    
    if (!$applications_result) {
        echo "<p>Database error: " . mysqli_error($conn) . "</p>";
        return;
    }
    
    $total_applications = mysqli_num_rows($applications_result);
    
    // Start report output
    $output = "
    <div id='recruitment-report-content'>
        <div class='report-summary' style='display:block;'>
            <h3>Recruitment Applications Report</h3>
            <div class='summary-item'>
                <span>Recruitment Title:</span>
                <span>{$recruitment_data['recruit_title']}</span>
            </div>
            <div class='summary-item'>
                <span>Recruitment Date:</span>
                <span>{$recruitment_data['recruit_date']}</span>
            </div>
            <div class='summary-item'>
                <span>Recruitment Time:</span>
                <span>{$recruitment_data['recruit_time']}</span>
            </div>
            <div class='summary-item'>
                <span>Total Applications:</span>
                <span>{$total_applications}</span>
            </div>
            <div class='summary-item'>
                <span>Report Generated:</span>
                <span>" . date('Y-m-d H:i:s') . "</span>
            </div>
        </div>
        
        <button class='btn btn-success print-button' style='display:inline-block;' onclick='printReport(\"recruitment-report-content\")'>
            <i class='fas fa-print'></i> Print Report
        </button>";
    
    if ($total_applications > 0) {
        $output .= "
        <table class='report-table'>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Matric Number</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Faculty</th>
                    <th>Year</th>
                    <th>Preferred Time</th>
                    <th>Department Choice 1</th>
                    <th>Department Choice 2</th>
                    <th>Interview Date</th>
                </tr>
            </thead>
            <tbody>";
        
        $count = 1;
        while ($row = mysqli_fetch_assoc($applications_result)) {
            $student_name = $row['stu_first_name'] . ' ' . $row['stu_last_name'];
            $output .= "
            <tr>
                <td>{$count}</td>
                <td>{$student_name}</td>
                <td>{$row['stu_matric']}</td>
                <td>{$row['stu_email']}</td>
                <td>{$row['stu_phone_number']}</td>
                <td>{$row['stu_faculty']}</td>
                <td>{$row['stu_year']}</td>
                <td>{$row['preferred_time']}</td>
                <td>{$row['dept_choice_1']}</td>
                <td>{$row['dept_choice_2']}</td>
                <td>{$row['timeslot_date']}</td>
            </tr>";
            $count++;
        }
        
        $output .= "
            </tbody>
        </table>";
    } else {
        $output .= "<p class='no-data'>No applications found for this recruitment.</p>";
    }
    
    $output .= "</div>";
    echo $output;
}

// Attendance Report
function generateAttendanceReport($conn) {
    if (!isset($_POST['event_id']) || empty($_POST['event_id'])) {
        echo "<p>No event selected.</p>";
        return;
    }
    
    $event_id = sanitize($conn, $_POST['event_id']);
    
    // Get event details
    $event_query = "SELECT * FROM events WHERE event_id = '$event_id'";
    $event_result = mysqli_query($conn, $event_query);
    
    if (!$event_result) {
        echo "<p>Database error: " . mysqli_error($conn) . "</p>";
        return;
    }
    
    if (mysqli_num_rows($event_result) == 0) {
        echo "<p>Event not found.</p>";
        return;
    }
    
    $event_data = mysqli_fetch_assoc($event_result);
    
    // Get attendance for this event
    $attendance_query = "SELECT a.*, s.stu_first_name, s.stu_last_name, s.stu_email, s.stu_phone_number, 
                         s.stu_faculty, s.stu_year 
                         FROM attendance a
                         LEFT JOIN students s ON a.stu_matric = s.stu_matric
                         WHERE a.event_id = '$event_id'
                         ORDER BY a.check_in_time ASC";
    $attendance_result = mysqli_query($conn, $attendance_query);
    
    if (!$attendance_result) {
        echo "<p>Database error: " . mysqli_error($conn) . "</p>";
        return;
    }
    
    $total_attendance = mysqli_num_rows($attendance_result);
    
    // Start report output
    $output = "
    <div id='attendance-report-content'>
        <div class='report-summary' style='display:block;'>
            <h3>Event Attendance Report</h3>
            <div class='summary-item'>
                <span>Event Name:</span>
                <span>{$event_data['event_title']}</span>
            </div>
            <div class='summary-item'>
                <span>Event Date:</span>
                <span>{$event_data['event_date']}</span>
            </div>
            <div class='summary-item'>
                <span>Event Time:</span>
                <span>{$event_data['event_time']}</span>
            </div>
            <div class='summary-item'>
                <span>Total Attendees:</span>
                <span>{$total_attendance}</span>
            </div>
            <div class='summary-item'>
                <span>Report Generated:</span>
                <span>" . date('Y-m-d H:i:s') . "</span>
            </div>
        </div>
        
        <button class='btn btn-success print-button' style='display:inline-block;' onclick='printReport(\"attendance-report-content\")'>
            <i class='fas fa-print'></i> Print Report
        </button>";
    
    if ($total_attendance > 0) {
        $output .= "
        <table class='report-table'>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Matric Number</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Faculty</th>
                    <th>Year</th>
                    <th>Check-in Time</th>
                </tr>
            </thead>
            <tbody>";
        
        $count = 1;
        while ($row = mysqli_fetch_assoc($attendance_result)) {
            $student_name = $row['stu_first_name'] . ' ' . $row['stu_last_name'];
            $output .= "
            <tr>
                <td>{$count}</td>
                <td>{$student_name}</td>
                <td>{$row['stu_matric']}</td>
                <td>{$row['stu_email']}</td>
                <td>{$row['stu_phone_number']}</td>
                <td>{$row['stu_faculty']}</td>
                <td>{$row['stu_year']}</td>
                <td>{$row['check_in_time']}</td>
            </tr>";
            $count++;
        }
        
        $output .= "
            </tbody>
        </table>";
    } else {
        $output .= "<p class='no-data'>No attendance records found for this event.</p>";
    }
    
    $output .= "</div>";
    echo $output;
}

// Student Report
function generateStudentReport($conn) {
    $filter_type = isset($_POST['filter_type']) ? sanitize($conn, $_POST['filter_type']) : 'all';
    $filter_value = isset($_POST['filter_value']) ? sanitize($conn, $_POST['filter_value']) : '';
    
    // Build query based on filter
    $where_clause = "";
    $filter_description = "All Students";
    
    if ($filter_type != 'all' && !empty($filter_value)) {
        switch ($filter_type) {
            case 'faculty':
                $where_clause = "WHERE stu_faculty = '$filter_value'";
                $filter_description = "Students in $filter_value Faculty";
                break;
            case 'year':
                $where_clause = "WHERE stu_year = '$filter_value'";
                $filter_description = "Year $filter_value Students";
                break;
            case 'gender':
                $where_clause = "WHERE stu_gender = '$filter_value'";
                $filter_description = "$filter_value Students";
                break;
        }
    }
    
    $students_query = "SELECT * FROM students $where_clause ORDER BY stu_faculty, stu_year, stu_first_name";
    $students_result = mysqli_query($conn, $students_query);
    
    if (!$students_result) {
        echo "<p>Database error: " . mysqli_error($conn) . "</p>";
        return;
    }
    
    $total_students = mysqli_num_rows($students_result);
    
    // Start report output
    $output = "
    <div id='student-report-content'>
        <div class='report-summary' style='display:block;'>
            <h3>Student Report - {$filter_description}</h3>
            <div class='summary-item'>
                <span>Total Students:</span>
                <span>{$total_students}</span>
            </div>
            <div class='summary-item'>
                <span>Report Generated:</span>
                <span>" . date('Y-m-d H:i:s') . "</span>
            </div>
        </div>
        
        <button class='btn btn-success print-button' style='display:inline-block;' onclick='printReport(\"student-report-content\")'>
            <i class='fas fa-print'></i> Print Report
        </button>";
    
    if ($total_students > 0) {
        $output .= "
        <table class='report-table'>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Matric Number</th>
                    <th>Gender</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Faculty</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody>";
        
        $count = 1;
        while ($row = mysqli_fetch_assoc($students_result)) {
            $student_name = $row['stu_first_name'] . ' ' . $row['stu_last_name'];
            $output .= "
            <tr>
                <td>{$count}</td>
                <td>{$student_name}</td>
                <td>{$row['stu_matric']}</td>
                <td>{$row['stu_gender']}</td>
                <td>{$row['stu_email']}</td>
                <td>{$row['stu_phone_number']}</td>
                <td>{$row['stu_faculty']}</td>
                <td>{$row['stu_year']}</td>
            </tr>";
            $count++;
        }
        
        $output .= "
            </tbody>
        </table>";
    } else {
        $output .= "<p class='no-data'>No students found matching the selected criteria.</p>";
    }
    
    $output .= "</div>";
    echo $output;
}

// Admin Report
function generateAdminReport($conn) {
    $filter_type = isset($_POST['filter_type']) ? sanitize($conn, $_POST['filter_type']) : 'all';
    $filter_value = isset($_POST['filter_value']) ? sanitize($conn, $_POST['filter_value']) : '';
    
    // Build query based on filter
    $where_clause = "";
    $filter_description = "All Admins";
    
    if ($filter_type != 'all' && !empty($filter_value)) {
        switch ($filter_type) {
            case 'faculty':
                $where_clause = "WHERE ad_faculty = '$filter_value'";
                $filter_description = "Admins in $filter_value Faculty";
                break;
            case 'year':
                $where_clause = "WHERE ad_year = '$filter_value'";
                $filter_description = "Year $filter_value Admins";
                break;
        }
    }
    
    $admins_query = "SELECT * FROM admin $where_clause ORDER BY ad_faculty, ad_year, ad_first_name";
    $admins_result = mysqli_query($conn, $admins_query);
    
    if (!$admins_result) {
        echo "<p>Database error: " . mysqli_error($conn) . "</p>";
        return;
    }
    
    $total_admins = mysqli_num_rows($admins_result);
    
    // Start report output
    $output = "
    <div id='admin-report-content'>
        <div class='report-summary' style='display:block;'>
            <h3>Admin Report - {$filter_description}</h3>
            <div class='summary-item'>
                <span>Total Admins:</span>
                <span>{$total_admins}</span>
            </div>
            <div class='summary-item'>
                <span>Report Generated:</span>
                <span>" . date('Y-m-d H:i:s') . "</span>
            </div>
        </div>
        
        <button class='btn btn-success print-button' style='display:inline-block;' onclick='printReport(\"admin-report-content\")'>
            <i class='fas fa-print'></i> Print Report
        </button>";
    
    if ($total_admins > 0) {
        $output .= "
        <table class='report-table'>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Admin Name</th>
                    <th>Matric Number</th>
                    <th>Gender</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Faculty</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody>";
        
        $count = 1;
        while ($row = mysqli_fetch_assoc($admins_result)) {
            $admin_name = $row['ad_first_name'] . ' ' . $row['ad_last_name'];
            $output .= "
            <tr>
                <td>{$count}</td>
                <td>{$admin_name}</td>
                <td>{$row['ad_matric']}</td>
                <td>{$row['ad_gender']}</td>
                <td>{$row['ad_email']}</td>
                <td>{$row['ad_phone']}</td>
                <td>{$row['ad_faculty']}</td>
                <td>{$row['ad_year']}</td>
            </tr>";
            $count++;
        }
        
        $output .= "
            </tbody>
        </table>";
    } else {
        $output .= "<p class='no-data'>No admins found matching the selected criteria.</p>";
    }
    
    $output .= "</div>";
    echo $output;
}
?>