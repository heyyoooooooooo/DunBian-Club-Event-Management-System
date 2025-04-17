<?php
// Add at the top of your PHP section
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Only process report generation if this is an AJAX request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
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
    
    // Exit to prevent the rest of the page from being processed in AJAX requests
    exit;
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
    // Check if recruitment_id is set in POST data
    if (!isset($_POST['recruitment_id']) || empty($_POST['recruitment_id'])) {
        echo "<p>No recruitment selected.</p>";
        return;
    }
    
    $recruitment_id = sanitize($conn, $_POST['recruitment_id']);
    // Get recruitment details
    $recruitment_query = "SELECT * FROM recruitment WHERE recruit_id = '$recruitment_id'";
    $recruitment_result = mysqli_query($conn, $recruitment_query);
    
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
            
        </div>
        
        <button class='btn btn-success print-button' style='display:inline-block;' onclick='printReport(\"attendance-report-content\")'>
            <i class='fas fa-print'></i> Print Report";
    
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard</title>
    <link rel="stylesheet" href="/EVENT_MANAGEMENT/reports.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
</head>
<body>
    <div id="sidebar">
        <div class="brand">
            <i class="fas fa-calendar-alt"></i> Dunbian Club
        </div>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="recruitment_list.php"><i class="fas fa-user-plus"></i> Recruitments</a>
        <a href="interview.php"><i class="fas fa-clock"></i> Interview</a>
        <a href="events.php"><i class="fas fa-calendar-check"></i> Events</a>
        <a href="#"><i class="fas fa-comment-alt"></i> Feedback</a>
        <a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a>
        <a href="reports.php" class="active"><i class="fas fa-chart-bar"></i> Reporting</a>
    </div>

    <div id="content">
        <div id="header">
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
            <a href="dashboard.php">Dashboard</a>
            <span>></span>
            <span>Reports</span>
        </div>

        <div class="container">
            <h1>Reports Generation</h1>
            <p>Generate various reports for events, recruitments, attendance, students, and admins.</p>
            
            <!-- Event Reports Section -->
            <div class="report-section">
                <h2><i class="fas fa-calendar-check"></i> Event Registration Reports</h2>
                <div class="report-options">
                    <select id="event-select">
                        <option value="">Select Event</option>
                        <?php
                        // Database connection
                        include 'config.php';
                        
                        // Fetch events
                        $sql = "SELECT * FROM events ORDER BY event_date DESC";
                        $result = mysqli_query($conn, $sql);
                        
                        if (mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='" . $row['event_id'] . "'>" . $row['event_title'] . " (" . $row['event_date'] . ")</option>";
                            }
                        }
                        ?>
                    </select>
                    <button class="btn btn-primary" id="generate-event-report">Generate Report</button>
                </div>
                
                <div id="event-report-results">
                    <!-- Results will be displayed here via AJAX -->
                </div>
            </div>
            
            <!-- Recruitment Reports Section -->
            <div class="report-section">
                <h2><i class="fas fa-user-plus"></i> Recruitment Applications Reports</h2>
                <div class="report-options">
                    <select id="recruitment-select">
                        <option value="">Select Recruitment</option>
                        <?php
                        // Fetch recruitments
                        $sql = "SELECT * FROM recruitment ORDER BY recruit_date DESC";
                        $result = mysqli_query($conn, $sql);
                        
                        if (mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='" . $row['recruit_id'] . "'>" . $row['recruit_title'] . " (" . $row['recruit_date'] . ")</option>";
                            }
                        }
                        ?>
                    </select>
                    <button class="btn btn-primary" id="generate-recruitment-report">Generate Report</button>
                </div>
                
                <div id="recruitment-report-results">
                    <!-- Results will be displayed here via AJAX -->
                </div>
            </div>
            
            <!-- Attendance Reports Section -->
            <div class="report-section">
                <h2><i class="fas fa-clipboard-check"></i> Student Attendance Reports</h2>
                <div class="report-options">
                    <select id="attendance-event-select">
                        <option value="">Select Event</option>
                        <?php
                        // Fetch events for attendance
                        $sql = "SELECT * FROM events ORDER BY event_date DESC";
                        $result = mysqli_query($conn, $sql);
                        
                        if (mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='" . $row['event_id'] . "'>" . $row['event_title'] . " (" . $row['event_date'] . ")</option>";
                            }
                        }
                        ?>
                    </select>
                    <button class="btn btn-primary" id="generate-attendance-report">Generate Report</button>
                </div>
                
                <div id="attendance-report-results">
                    <!-- Results will be displayed here via AJAX -->
                </div>
            </div>
            
            <!-- Student Reports Section -->
            <div class="report-section">
                <h2><i class="fas fa-user-graduate"></i> Student Reports</h2>
                <div class="report-options">
                    <select id="student-filter">
                        <option value="all">All Students</option>
                        <option value="faculty">Filter by Faculty</option>
                        <option value="year">Filter by Year</option>
                        <option value="gender">Filter by Gender</option>
                    </select>
                    <select id="student-filter-value" style="display: none;">
                        <!-- Values will be populated via JavaScript based on filter selection -->
                    </select>
                    <button class="btn btn-primary" id="generate-student-report">Generate Report</button>
                </div>
                
                <div id="student-report-results">
                    <!-- Results will be displayed here via AJAX -->
                </div>
            </div>
            
            <!-- Admin Reports Section -->
            <div class="report-section">
                <h2><i class="fas fa-user-shield"></i> Admin Reports</h2>
                <div class="report-options">
                    <select id="admin-filter">
                        <option value="all">All Admins</option>
                        <option value="faculty">Filter by Faculty</option>
                        <option value="year">Filter by Year</option>
                    </select>
                    <select id="admin-filter-value" style="display: none;">
                        <!-- Values will be populated via JavaScript based on filter selection -->
                    </select>
                    <button class="btn btn-primary" id="generate-admin-report">Generate Report</button>
                </div>
                
                <div id="admin-report-results">
                    <!-- Results will be displayed here via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        // Toggle account menu
        document.querySelector('.account').addEventListener('click', function() {
            document.querySelector('.account-menu').style.display = 
                document.querySelector('.account-menu').style.display === 'block' ? 'none' : 'block';
        });

        // Student filter change handler
        document.getElementById('student-filter').addEventListener('change', function() {
            const filterType = this.value;
            const filterValueSelect = document.getElementById('student-filter-value');
            
            if (filterType === 'all') {
                filterValueSelect.style.display = 'none';
                return;
            }
            
            filterValueSelect.style.display = 'inline-block';
            filterValueSelect.innerHTML = '<option value="">Loading...</option>';
            
            // Fetch appropriate filter values based on selection
            $.ajax({
                url: 'get_filter_values.php',
                type: 'POST',
                data: { type: 'student', filter: filterType },
                success: function(response) {
                    filterValueSelect.innerHTML = response;
                },
                error: function() {
                    filterValueSelect.innerHTML = '<option value="">Error loading options</option>';
                }
            });
        });

        // Admin filter change handler
        document.getElementById('admin-filter').addEventListener('change', function() {
            const filterType = this.value;
            const filterValueSelect = document.getElementById('admin-filter-value');
            
            if (filterType === 'all') {
                filterValueSelect.style.display = 'none';
                return;
            }
            
            filterValueSelect.style.display = 'inline-block';
            filterValueSelect.innerHTML = '<option value="">Loading...</option>';
            
            // Fetch appropriate filter values based on selection
            $.ajax({
                url: 'get_filter_values.php',
                type: 'POST',
                data: { type: 'admin', filter: filterType },
                success: function(response) {
                    filterValueSelect.innerHTML = response;
                },
                error: function() {
                    filterValueSelect.innerHTML = '<option value="">Error loading options</option>';
                }
            });
        });

        // Generate Event Report
        document.getElementById('generate-event-report').addEventListener('click', function() {
            const eventId = document.getElementById('event-select').value;
            if (!eventId) {
                alert('Please select an event');
                return;
            }
            
            document.getElementById('event-report-results').innerHTML = '<p>Loading report...</p>';
            
            $.ajax({
                url: window.location.href, // Use current file URL instead of generate_report.php
                type: 'POST',
                data: { type: 'event', event_id: eventId },
                success: function(response) {
                    document.getElementById('event-report-results').innerHTML = response;
                },
                error: function() {
                    document.getElementById('event-report-results').innerHTML = '<p>Error generating report</p>';
                }
            });
        });

        // Generate Recruitment Report
        document.getElementById('generate-recruitment-report').addEventListener('click', function() {
            const recruitmentId = document.getElementById('recruitment-select').value;
            if (!recruitmentId) {
                alert('Please select a recruitment');
                return;
            }
            
            document.getElementById('recruitment-report-results').innerHTML = '<p>Loading report...</p>';
            
            $.ajax({
                url: window.location.href, // Use current file URL
                type: 'POST',
                data: { type: 'recruitment', recruitment_id: recruitmentId },
                success: function(response) {
                    document.getElementById('recruitment-report-results').innerHTML = response;
                },
                error: function() {
                    document.getElementById('recruitment-report-results').innerHTML = '<p>Error generating report</p>';
                }
            });
        });

        // Generate Attendance Report
        document.getElementById('generate-attendance-report').addEventListener('click', function() {
            const eventId = document.getElementById('attendance-event-select').value;
            if (!eventId) {
                alert('Please select an event');
                return;
            }
            
            document.getElementById('attendance-report-results').innerHTML = '<p>Loading report...</p>';
            
            $.ajax({
                url: window.location.href, // Use current file URL
                type: 'POST',
                data: { type: 'attendance', event_id: eventId },
                success: function(response) {
                    document.getElementById('attendance-report-results').innerHTML = response;
                },
                error: function() {
                    document.getElementById('attendance-report-results').innerHTML = '<p>Error generating report</p>';
                }
            });
        });

        // Generate Student Report
        document.getElementById('generate-student-report').addEventListener('click', function() {
            const filterType = document.getElementById('student-filter').value;
            let filterValue = '';
            
            if (filterType !== 'all') {
                filterValue = document.getElementById('student-filter-value').value;
                if (!filterValue) {
                    alert('Please select a filter value');
                    return;
                }
            }
            
            document.getElementById('student-report-results').innerHTML = '<p>Loading report...</p>';
            
            $.ajax({
                url: window.location.href, // Use current file URL
                type: 'POST',
                data: { 
                    type: 'student', 
                    filter_type: filterType,
                    filter_value: filterValue
                },
                success: function(response) {
                    document.getElementById('student-report-results').innerHTML = response;
                },
                error: function() {
                    document.getElementById('student-report-results').innerHTML = '<p>Error generating report</p>';
                }
            });
        });

        // Generate Admin Report
        document.getElementById('generate-admin-report').addEventListener('click', function() {
            const filterType = document.getElementById('admin-filter').value;
            let filterValue = '';
            
            if (filterType !== 'all') {
                filterValue = document.getElementById('admin-filter-value').value;
                if (!filterValue) {
                    alert('Please select a filter value');
                    return;
                }
            }
            
            document.getElementById('admin-report-results').innerHTML = '<p>Loading report...</p>';
            
            $.ajax({
                url: window.location.href, // Use current file URL
                type: 'POST',
                data: { 
                    type: 'admin', 
                    filter_type: filterType,
                    filter_value: filterValue
                },
                success: function(response) {
                    document.getElementById('admin-report-results').innerHTML = response;
                },
                error: function() {
                    document.getElementById('admin-report-results').innerHTML = '<p>Error generating report</p>';
                }
            });
        });

        // Print report function
        function printReport(reportId) {
            const printContents = document.getElementById(reportId).innerHTML;
            const originalContents = document.body.innerHTML;
            
            document.body.innerHTML = `
                <div style="padding: 20px;">
                    <h1 style="text-align: center; margin-bottom: 20px;">Dunbian Club Report</h1>
                    <p style="text-align: center; margin-bottom: 30px;">Generated on ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}</p>
                    ${printContents}
                </div>`;
            
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }
    </script>
</body>
</html>