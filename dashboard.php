<?php
include('admin_auth.php');

// Include database configuration
include('config.php');

// Fetch the last 5 recruitment applications
$sql_applications = "
    SELECT 
        ra.application_id, ra.stu_matric, ra.application_status, 
        ra.dept_choice_1, ra.dept_choice_2, it.timeslot_date, 
        it.start_time, r.recruit_title, s.stu_first_name, s.stu_last_name
    FROM recruitment_applications ra
    JOIN students s ON ra.stu_matric = s.stu_matric
    JOIN recruitment r ON ra.recruit_id = r.recruit_id
    JOIN interview_times it ON ra.timeslot_id = it.timeslot_id
    ORDER BY ra.application_id DESC LIMIT 5";
$result_applications = $conn->query($sql_applications);

// Fetch total recruitments
$sql_recruitments = "SELECT COUNT(*) AS total_recruitments FROM recruitment WHERE recruit_id IS NOT NULL";
$result_recruitments = $conn->query($sql_recruitments);
$total_recruitments = $result_recruitments->fetch_assoc()['total_recruitments'];

// Fetch total applications
$sql_applications_count = "SELECT COUNT(*) AS total_applications FROM recruitment_applications";
$result_applications_count = $conn->query($sql_applications_count);
$total_applications = $result_applications_count->fetch_assoc()['total_applications'];

// Fetch total events
$sql_events = "SELECT COUNT(*) AS total_events FROM events";
$result_events = $conn->query($sql_events);
$total_events = $result_events->fetch_assoc()['total_events'];

// Fetch total registered students
$sql_students = "SELECT COUNT(*) AS total_students FROM students";
$result_students = $conn->query($sql_students);
$total_students = $result_students->fetch_assoc()['total_students'];

// Fetch upcoming events (next 30 days)
$sql_upcoming_events = "SELECT COUNT(*) AS upcoming_events FROM events 
                        WHERE event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
$result_upcoming_events = $conn->query($sql_upcoming_events);
$upcoming_events = $result_upcoming_events->fetch_assoc()['upcoming_events'];

// Fetch upcoming recruitments (next 30 days)
$sql_upcoming_recruitments = "SELECT COUNT(*) AS upcoming_recruitments FROM recruitment 
                              WHERE recruit_date >= CURDATE() AND recruit_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
$result_upcoming_recruitments = $conn->query($sql_upcoming_recruitments);
$upcoming_recruitments = $result_upcoming_recruitments->fetch_assoc()['upcoming_recruitments'];

// Fetch the last 5 event registrations
$sql_event_registrations = "
    SELECT 
        er.registration_id, er.stu_matric,
        e.event_title, e.event_date, e.event_location,
        s.stu_first_name, s.stu_last_name
    FROM event_registrations er
    JOIN students s ON er.stu_matric = s.stu_matric
    JOIN events e ON er.event_id = e.event_id
    ORDER BY er.registration_id DESC LIMIT 5
";
$result_event_registrations = $conn->query($sql_event_registrations);

// Fetch this month's statistics
$current_month = date('Y-m');
$sql_month_events = "SELECT COUNT(*) AS month_events FROM events WHERE DATE_FORMAT(event_date, '%Y-%m') = '$current_month'";
$result_month_events = $conn->query($sql_month_events);
$month_events = $result_month_events->fetch_assoc()['month_events'];

$sql_month_applications = "SELECT COUNT(*) AS month_applications FROM recruitment WHERE DATE_FORMAT(recruit_date, '%Y-%m') = '$current_month'";
$result_month_applications = $conn->query($sql_month_applications);
$month_applications = $result_month_applications->fetch_assoc()['month_applications'];

// Fetch events and recruitments for calendar display
$sql_calendar_events = "
    SELECT event_id AS id, event_title AS title, event_date, 'event' AS type 
    FROM events 
    UNION
    SELECT recruit_id AS id, recruit_title AS title, recruit_date AS event_date, 'recruitment' AS type 
    FROM recruitment 
    ORDER BY event_date ASC";
$result_calendar_events = $conn->query($sql_calendar_events);

$calendar_events = [];
if ($result_calendar_events && $result_calendar_events->num_rows > 0) {
    while ($row = $result_calendar_events->fetch_assoc()) {
        $event_date = date('Y-m-d', strtotime($row['event_date']));
        if (!isset($calendar_events[$event_date])) {
            $calendar_events[$event_date] = [];
        }
        $calendar_events[$event_date][] = [
            'id' => $row['id'],          // Unified column name
            'title' => $row['title'],    // Unified column name
            'type' => $row['type']
        ];
    }
}
// Convert to JSON for JavaScript usage
$calendar_events_json = json_encode($calendar_events);

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/EVENT_MANAGEMENT/dashboardd.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Custom styles for the enhanced dashboard */
        .dashboard-metrics {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px; /* Space between metrics */
            padding: 10px 30px; /* Reduced top padding to 10px */
            margin-bottom: 10px; /* Reduced margin below metrics */
        }

        /* Reduce space between metrics and calendar row */
        .dashboard-overview {
            display: flex;
            gap: 20px; /* Space between elements in the row */
            padding: 0 30px 10px; /* Reduced bottom padding to 10px */
            margin-bottom: 10px; /* Reduced margin below the row */
        }

        /* Ensure consistent styles for metrics */
        .metric {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            border-left: 5px solid transparent;
            transition: transform 0.3s ease;
        }

        .metric:hover {
            transform: translateY(-5px);
        }
        
        .metric h3 {
            margin-bottom: 10px;
            color: var(--secondary);
            font-size: 1rem;
        }
        
        .metric .value {
            font-size: 28px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .metric .value i {
            font-size: 22px;
        }
        
        .metric .trend {
            font-size: 0.85rem;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .metric .trend.up {
            color: var(--success);
        }
        
        .metric .trend.down {
            color: var(--danger);
        }
        /*
        .metric.primary {
            border-left-color: var(--primary);
        }
        
        .metric.success {
            border-left-color: var(--success);
        }
        
        .metric.warning {
            border-left-color: var(--warning);
        }
        
        .metric.danger {
            border-left-color: var(--danger);
        }
        
        .metric.purple {
            border-left-color: #9c27b0;
        }
        
        .metric.teal {
            border-left-color: #009688;
        }
        */
        .overview-card {
            flex: 1;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        
        .overview-card h3 {
            margin-bottom: 15px;
            font-size: 1.1rem;
            color: var(--dark);
            border-bottom: 2px solid var(--light);
            padding-bottom: 10px;
        }
        
        .tables-container {
            display: flex;              /* Use a flex layout for easier alignment */
            gap: 10px;                  /* Reduce the gap between the two tables */
            margin: 10px 0 0 30px;      /* Align with the calendar (left margin) */
            padding: 0;                 /* Remove unnecessary padding */
            justify-content: flex-start; /* Align tables to the left */
        }

        .table-container {
            flex: 1;                    /* Allow the tables to adjust width evenly */
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            height: auto;               /* Adjust height dynamically */
            border-top: 4px solid var(--primary);
        }

        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .table-header h2 {
            font-size: 1.2rem;
            color: var(--dark);
            margin: 0;
        }
        
        .table-header .view-all {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .applicants-table, .registrations-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .applicants-table th, .registrations-table th,
        .applicants-table td, .registrations-table td {
            padding: 0.8rem; 
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .applicants-table th, .registrations-table th {
            font-weight: 600;
            color: #666;
            background: #f8f9fa;
            position: sticky;
            top: 0;
        }
        
        .applicants-table tr:last-child td, .registrations-table tr:last-child td {
            border-bottom: none;
        }
        
        .status-pill {
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
            display: inline-block;
        }
        
        .status-pending {
            background-color: #fff8e1;
            color: #ff9800;
        }
        
        .status-approved {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-rejected {
            background-color: #ffebee;
            color: #c62828;
        }
        
        /* Calendar widget styles remain unchanged */
        .calendar-widget {
            padding: 15px;
            background: white;
            border-radius: 12px;
            font-size: 0.9rem;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .calendar-nav {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .calendar-nav button {
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
            font-size: 16px;
            padding: 5px;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .calendar-nav button:hover {
            background: rgba(79, 70, 229, 0.1);
        }

        
        .month-selection {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .month-year-select {
            padding: 5px 10px;
            border-radius: 5px;
            border: 1px solid #e2e8f0;
            font-size: 0.9rem;
            background: white;
            cursor: pointer;
        }

        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--secondary);
        }

        .calendar-body {
            display: grid;
            grid-template-rows: repeat(6, auto);
        }

        .calendar-row {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            height: 40px;
        }

        .calendar-day {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            cursor: pointer;
            position: relative;
            height: 40px;
            width: 40px;
            margin: auto;
            transition: all 0.2s ease;
        }

        .calendar-day:hover {
            background: rgba(79, 70, 229, 0.1);
        }

        .calendar-day.today {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
        }

        .calendar-day.selected {
            box-shadow: 0 0 0 2px var(--primary);
        }

        .calendar-day.has-event {
            border: 2px solid var(--primary);
            font-weight: 500;
        }

        .calendar-day.has-recruitment {
            border: 2px solid var(--success);
            font-weight: 500;
        }

        .calendar-day.has-both {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.2) 50%, rgba(34, 197, 94, 0.2) 50%);
            border: 2px solid #9c27b0;
            font-weight: 500;
        }

        .calendar-day.empty {
            background: none;
            cursor: default;
        }

        
        .event-indicators {
            position: absolute;
            bottom: -4px;
            display: flex;
            gap: 3px;
        }

        .event-indicator {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .event-indicator.event {
            background-color: var(--primary);
        }

        .event-indicator.recruitment {
            background-color: var(--success);
        }

        /* Timeline Styles */
        .timeline {
            margin-top: 15px;
            max-height: 200px;
            overflow-y: auto;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }

        .timeline-item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #e2e8f0;
        }

        .timeline-date {
            min-width: 80px;
            font-size: 0.8rem;
            color: var(--secondary);
        }

        .timeline-content {
            flex: 1;
        }

        .timeline-title {
            font-weight: 500;
            color: var(--dark);
            font-size: 0.9rem;
            margin-bottom: 2px;
        }

        .timeline-type {
            display: inline-block;
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 20px;
            margin-bottom: 5px;
        }

        .timeline-type.event {
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--primary);
        }

        .timeline-type.recruitment {
            background-color: rgba(34, 197, 94, 0.1);
            color: var(--success);
        }

        
        .event-details-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1050;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
            padding: 20px;
            position: relative;
            animation: modalFadeIn 0.3s;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        
        .modal-header h3 {
            margin: 0;
            color: var(--dark);
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: var(--secondary);
        }
        
        .modal-body {
            font-size: 0.9rem;
            color: var(--secondary);
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }
        
        .event-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .event-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-left: 3px solid var(--primary);
            transition: transform 0.2s ease;
        }
        
        .event-card:hover {
            transform: translateY(-3px);
        }
        
        .event-card.recruitment {
            border-left-color: var(--success);
        }
        
        .event-card h4 {
            margin: 0 0 10px;
            font-size: 1rem;
            color: var(--dark);
        }
        
        .event-card p {
            margin: 5px 0;
            font-size: 0.85rem;
            color: var(--secondary);
        }
        
        .event-tag {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            margin-top: 10px;
        }
        
        .event-tag.event {
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--primary);
        }
        
        .event-tag.recruitment {
            background-color: rgba(34, 197, 94, 0.1);
            color: var(--success);
        }
        
        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .dashboard-metrics {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .dashboard-overview {
                flex-direction: column;
            }
            
            .tables-container {
                flex-direction: column; /* Stack tables vertically on smaller screens */
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-metrics {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div id="sidebar">
        <div class="brand">
            <i class="fas fa-calendar-alt"></i> Dunbian Club
        </div>
        <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
        <a href="recruitment_list.php"><i class="fas fa-user-plus"></i> Recruitments</a>
        <a href="interview.php"><i class="fas fa-clock"></i> Interview</a>
        <a href="events.php"><i class="fas fa-calendar-check"></i> Events</a>
        <a href="#"><i class="fas fa-comment-alt"></i> Feedback</a>
        <a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a>
        <a href="#"><i class="fas fa-chart-bar"></i> Reporting</a>
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
        </div>

        <!-- Dashboard Metrics -->
        <div class="dashboard-metrics">
            <div class="metric primary">
                <h3>Upcoming Events</h3>
                <div class="value"><i class="fas fa-calendar-day"></i> <?php echo $upcoming_events; ?></div>
                <div class="trend up">
                    <i class="fas fa-arrow-up"></i> +<?php echo $month_events; ?> this month
                </div>
            </div>
            <div class="metric success">
                <h3>Upcoming Recruitments</h3>
                <div class="value"><i class="fas fa-user-plus"></i> <?php echo $upcoming_recruitments; ?></div>
                <div class="trend up">
                    <i class="fas fa-arrow-up"></i> Active campaigns
                </div>
            </div>
            <div class="metric warning">
                <h3>Total Recruitments</h3>
                <div class="value"><i class="fas fa-users"></i> <?php echo $total_recruitments; ?></div>
                <div class="trend up">
                    <i class="fas fa-trophy"></i> All time
                </div>
            </div>
            <div class="metric danger">
                <h3>Total Events</h3>
                <div class="value"><i class="fas fa-calendar-check"></i> <?php echo $total_events; ?></div>
                <div class="trend up">
                    <i class="fas fa-star"></i> Successfully hosted
                </div>
            </div>
            <div class="metric purple">
                <h3>Total Students</h3>
                <div class="value"><i class="fas fa-user-graduate"></i> <?php echo $total_students; ?></div>
                <div class="trend up">
                    <i class="fas fa-users"></i> Registered members
                </div>
            </div>
            <div class="metric teal">
                <h3>Total Applications</h3>
                <div class="value"><i class="fas fa-file-alt"></i> <?php echo $total_applications; ?></div>
                <div class="trend up">
                    <i class="fas fa-arrow-up"></i> +<?php echo $month_applications; ?> this month
                </div>
            </div>
        </div>

        <!-- Event Analytics Overview -->
        <div class="dashboard-overview">
            <div class="overview-card">
                <h3>Calendar & Upcoming Activities</h3>
                <div id="calendar-widget" class="calendar-widget">
                    <div class="calendar-header">
                        <div class="month-selection">
                            <select id="month-select" class="month-year-select">
                                <option value="0">January</option>
                                <option value="1">February</option>
                                <option value="2">March</option>
                                <option value="3">April</option>
                                <option value="4">May</option>
                                <option value="5">June</option>
                                <option value="6">July</option>
                                <option value="7">August</option>
                                <option value="8">September</option>
                                <option value="9">October</option>
                                <option value="10">November</option>
                                <option value="11">December</option>
                            </select>
                            <select id="year-select" class="month-year-select"></select>
                        </div>
                        <div class="calendar-nav">
                            <button id="prev-month"><i class="fas fa-chevron-left"></i></button>
                            <button id="next-month"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                    <div class="calendar-weekdays">
                        <div>Sun</div>
                        <div>Mon</div>
                        <div>Tue</div>
                        <div>Wed</div>
                        <div>Thu</div>
                        <div>Fri</div>
                        <div>Sat</div>
                    </div>
                    <div class="calendar-body">
                        <!-- Calendar days will be generated by JS -->
                    </div>
                    
                    <!-- Event/Recruitment Timeline -->
                    <div class="timeline" id="calendar-timeline">
                        <h4 style="margin-bottom: 10px;">Upcoming Activities</h4>
                        <!-- Timeline items will be populated by JS -->
                    </div>
                </div>
            </div>
            <div class="overview-card">
                <h3>Engagement Overview</h3>
                <canvas id="engagement-chart" height="250"></canvas>
            </div>
        </div>

        <!-- Lists Container -->
        <div class="tables-container">
            <!-- Recruitment Applications List -->
            <div class="table-container">
                <div class="table-header">
                    <h2>Recent Recruitment Applications</h2>
                    <a href="recruitment_list.php?tab=applicants" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <table class="applicants-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Recruitment</th>
                            <th>Interview Date</th>
                            <th>First Choice</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_applications->num_rows > 0): ?>
                            <?php while ($row = $result_applications->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['stu_first_name'] . ' ' . $row['stu_last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['recruit_title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['timeslot_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['dept_choice_1']); ?></td>
                                    <td>
                                        <button class="btn-view" onclick="location.href='view_applications.php?id=<?php echo $row['application_id']; ?>'">
                                            View
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5">No applications found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Event Registrations List -->
            <div class="table-container">
                <div class="table-header">
                    <h2>Recent Event Registrations</h2>
                    <a href="events.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <table class="registrations-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Event</th>
                            <th>Event Date</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_event_registrations && $result_event_registrations->num_rows > 0): ?>
                            <?php while ($row = $result_event_registrations->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['stu_first_name'] . ' ' . $row['stu_last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['event_title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['event_location']); ?></td>
                                    <td>
                                        <button class="btn-view
                                        <button class="btn-view" onclick="location.href='view_registration.php?id=<?php echo $row['registration_id']; ?>'">
                                            View
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5">No registrations found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <script>
        // Toggle account menu
        document.querySelector('.account').addEventListener('click', function () {
            const menu = document.querySelector('.account-menu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });

        // Initialize Chart.js for engagement overview
        /*const ctx = document.getElementById('engagement-chart').getContext('2d');
        const engagementChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Event Registrations',
                    data: [65, 59, 80, 81, 56, 40],
                    backgroundColor: '#4f46e5',
                    borderWidth: 0,
                    borderRadius: 5
                }, {
                    label: 'Recruitment Applications',
                    data: [28, 48, 40, 19, 36, 27],
                    backgroundColor: '#22c55e',
                    borderWidth: 0,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });*/

        // Calendar functionality implementation
document.addEventListener('DOMContentLoaded', function() {
    // Calendar event data is loaded from PHP
    const calendarEvents = <?php echo $calendar_events_json; ?>;
    
    // Initialize calendar variables
    const today = new Date();
    let currentMonth = today.getMonth();
    let currentYear = today.getFullYear();
    
    // DOM elements
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');
    const monthSelect = document.getElementById('month-select');
    const yearSelect = document.getElementById('year-select');
    const calendarBody = document.querySelector('.calendar-body');
    const calendarTimeline = document.getElementById('calendar-timeline');
    
    // Set up year select options (5 years back, 5 years forward)
    const currentYearIndex = 5;
    for (let i = currentYear - 5; i <= currentYear + 5; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = i;
        yearSelect.appendChild(option);
    }
    
    // Set initial values for month and year selects
    monthSelect.value = currentMonth;
    yearSelect.value = currentYear;
    
    // Add event listeners for calendar navigation
    prevMonthBtn.addEventListener('click', function() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        updateSelectValues();
        renderCalendar();
    });
    
    nextMonthBtn.addEventListener('click', function() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        updateSelectValues();
        renderCalendar();
    });
    
    monthSelect.addEventListener('change', function() {
        currentMonth = parseInt(this.value);
        renderCalendar();
    });
    
    yearSelect.addEventListener('change', function() {
        currentYear = parseInt(this.value);
        renderCalendar();
    });
    
    // Update select inputs when navigation buttons are clicked
    function updateSelectValues() {
        monthSelect.value = currentMonth;
        yearSelect.value = currentYear;
    }
    
    // Main calendar rendering function
    function renderCalendar() {
        calendarBody.innerHTML = '';
        
        // Get days in month and first day of month
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        
        // Create calendar rows
        for (let i = 0; i < 6; i++) {
            const row = document.createElement('div');
            row.className = 'calendar-row';
            calendarBody.appendChild(row);
        }
        
        const rows = document.querySelectorAll('.calendar-row');
        
        // Add empty cells for days before the first day of month
        let dayCount = 1;
        let currentRow = 0;
        
        // First row with empty cells
        for (let i = 0; i < 7; i++) {
            if (i < firstDay) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'calendar-day empty';
                rows[currentRow].appendChild(emptyDay);
            } else {
                // Add the first days of the month
                const dayElement = createDayElement(dayCount, currentMonth, currentYear);
                rows[currentRow].appendChild(dayElement);
                dayCount++;
            }
        }
        
        // Fill in the rest of the days
        currentRow++;
        while (dayCount <= daysInMonth) {
            for (let i = 0; i < 7 && dayCount <= daysInMonth; i++) {
                const dayElement = createDayElement(dayCount, currentMonth, currentYear);
                rows[currentRow].appendChild(dayElement);
                dayCount++;
            }
            
            if (dayCount <= daysInMonth) {
                currentRow++;
            }
        }
        
        // Add empty cells for the last row if needed
        if (rows[currentRow].children.length < 7) {
            const remainingCells = 7 - rows[currentRow].children.length;
            for (let i = 0; i < remainingCells; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'calendar-day empty';
                rows[currentRow].appendChild(emptyDay);
            }
        }
        
        // Update timeline with events for the current month
        updateEventTimeline();
    }
    
    // Create a single day element for the calendar
    function createDayElement(day, month, year) {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        dayElement.textContent = day;
        
        // Format date to check for events
        const dateStr = formatDateForComparison(year, month, day);
        
        // Check if the date is today
        const nowDate = new Date();
        if (day === nowDate.getDate() && month === nowDate.getMonth() && year === nowDate.getFullYear()) {
            dayElement.classList.add('today');
        }
        
        // Check if the date has events
        if (calendarEvents[dateStr]) {
            const events = calendarEvents[dateStr];
            let hasEvent = false;
            let hasRecruitment = false;
            
            // Check for event types
            events.forEach(event => {
                if (event.type === 'event') hasEvent = true;
                if (event.type === 'recruitment') hasRecruitment = true;
            });
            
            // Add appropriate classes
            if (hasEvent && hasRecruitment) {
                dayElement.classList.add('has-both');
            } else if (hasEvent) {
                dayElement.classList.add('has-event');
            } else if (hasRecruitment) {
                dayElement.classList.add('has-recruitment');
            }
            
            // Add event indicators
            const indicatorsDiv = document.createElement('div');
            indicatorsDiv.className = 'event-indicators';
            
            if (hasEvent) {
                const eventIndicator = document.createElement('div');
                eventIndicator.className = 'event-indicator event';
                indicatorsDiv.appendChild(eventIndicator);
            }
            
            if (hasRecruitment) {
                const recruitmentIndicator = document.createElement('div');
                recruitmentIndicator.className = 'event-indicator recruitment';
                indicatorsDiv.appendChild(recruitmentIndicator);
            }
            
            dayElement.appendChild(indicatorsDiv);
            
            // Add click event to show event details
            dayElement.addEventListener('click', function() {
                showEventDetails(dateStr, events);
            });
        }
        
        return dayElement;
    }
    
    // Helper function to format date for comparison with calendarEvents
    function formatDateForComparison(year, month, day) {
        month++; // JavaScript months are 0-indexed
        return `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
    }
    
    // Function to show event details for a specific date
    function showEventDetails(dateStr, events) {
        // Highlight the selected day
        const selectedDay = document.querySelector('.calendar-day.selected');
        if (selectedDay) {
            selectedDay.classList.remove('selected');
        }
        
        // Update the event timeline section
        updateEventTimeline(dateStr);
    }
    
    // Update the timeline section with events
    function updateEventTimeline(selectedDate = null) {
        calendarTimeline.innerHTML = '<h4 style="margin-bottom: 10px;">Upcoming Activities</h4>';
        
        // Get all events for current month
        const eventsToShow = [];
        
        // If a specific date is selected, show only those events
        if (selectedDate && calendarEvents[selectedDate]) {
            calendarEvents[selectedDate].forEach(event => {
                eventsToShow.push({
                    date: selectedDate,
                    ...event
                });
            });
        } else {
            // Otherwise show all events for the current month
            Object.keys(calendarEvents).forEach(date => {
                // Check if the event is in the current month/year
                const eventDate = new Date(date);
                if (eventDate.getMonth() === currentMonth && eventDate.getFullYear() === currentYear) {
                    calendarEvents[date].forEach(event => {
                        eventsToShow.push({
                            date: date,
                            ...event
                        });
                    });
                }
            });
            
            // Sort by date
            eventsToShow.sort((a, b) => new Date(a.date) - new Date(b.date));
        }
        
        // Show message if no events
        if (eventsToShow.length === 0) {
            const noEvents = document.createElement('p');
            noEvents.textContent = 'No activities scheduled for this period.';
            noEvents.style.color = '#666';
            noEvents.style.fontStyle = 'italic';
            calendarTimeline.appendChild(noEvents);
            return;
        }
        
        // Create timeline items for each event
        eventsToShow.forEach(event => {
            const timelineItem = document.createElement('div');
            timelineItem.className = 'timeline-item';
            
            const dateObj = new Date(event.date);
            const formattedDate = dateObj.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric'
            });
            
            timelineItem.innerHTML = `
                <div class="timeline-date">${formattedDate}</div>
                <div class="timeline-content">
                    <div class="timeline-title">${event.title}</div>
                    <span class="timeline-type ${event.type}">${event.type}</span>
                </div>
            `;
            
            calendarTimeline.appendChild(timelineItem);
        });
    }
    
    // Initialize the calendar
    renderCalendar();
    
    // Initialize Chart.js for engagement overview
    /*const ctx = document.getElementById('engagement-chart').getContext('2d');
    const engagementChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Event Registrations',
                data: [65, 59, 80, 81, 56, 40],
                backgroundColor: '#4f46e5',
                borderWidth: 0,
                borderRadius: 5
            }, {
                label: 'Recruitment Applications',
                data: [28, 48, 40, 19, 36, 27],
                backgroundColor: '#22c55e',
                borderWidth: 0,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    }); */
});
    </script>
</body>
</html>