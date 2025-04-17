<?php
// get_events.php - Retrieves events from database
session_start();

include('admin_auth.php'); // Ensure this file also calls session_start() if needed
include('config.php');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Query to get events
$query = "SELECT event_id, event_title, DATE_FORMAT(event_date, '%d %b %Y') as date, event_
          FROM events 
          WHERE event_date >= CURDATE() - INTERVAL 30 DAY 
          ORDER BY event_date DESC";

$result = mysqli_query($conn, $query);

if ($result) {
    $events = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'date' => $row['date']
        ];
    }
    
    echo json_encode(['success' => true, 'events' => $events]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch events']);
}

mysqli_close($conn);
?>

<?php
// get_participants.php - Retrieves participants for a specific event
session_start();
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if event_id is provided
if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    echo json_encode(['success' => false, 'message' => 'Event ID is required']);
    exit;
}

$event_id = mysqli_real_escape_string($conn, $_GET['event_id']);

// Query to get participants and their attendance status
$query = "SELECT p.id, p.student_id, p.name, p.email, p.phone, 
          DATE_FORMAT(r.registration_date, '%d %b %Y') as registration_date,
          IFNULL(a.attended, 0) as attended
          FROM participants p
          INNER JOIN registrations r ON p.id = r.participant_id
          LEFT JOIN attendance a ON p.id = a.participant_id AND a.event_id = ?
          WHERE r.event_id = ?
          ORDER BY p.name";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $event_id, $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $participants = [];
    while ($row = $result->fetch_assoc()) {
        $participants[] = [
            'id' => $row['id'],
            'student_id' => $row['student_id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'registration_date' => $row['registration_date'],
            'attended' => (bool)$row['attended']
        ];
    }
    
    echo json_encode(['success' => true, 'participants' => $participants]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch participants']);
}

$stmt->close();
mysqli_close($conn);
?>

<?php
// save_attendance.php - Saves attendance status
session_start();
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if required parameters are provided
if (!isset($_POST['participant_id']) || !isset($_POST['event_id']) || !isset($_POST['attended'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$participant_id = mysqli_real_escape_string($conn, $_POST['participant_id']);
$event_id = mysqli_real_escape_string($conn, $_POST['event_id']);
$attended = (int)$_POST['attended'];

// Check if attendance record exists
$check_query = "SELECT id FROM attendance WHERE participant_id = ? AND event_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $participant_id, $event_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Update existing record
    $query = "UPDATE attendance SET attended = ?, updated_at = NOW() WHERE participant_id = ? AND event_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $attended, $participant_id, $event_id);
} else {
    // Insert new record
    $query = "INSERT INTO attendance (participant_id, event_id, attended, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $participant_id, $event_id, $attended);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Attendance saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save attendance']);
}

$check_stmt->close();
$stmt->close();
mysqli_close($conn);
?>

<?php
// export_attendance.php - Exports attendance data as CSV
session_start();
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Check if event_id is provided
if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    echo "Event ID is required";
    exit;
}

$event_id = mysqli_real_escape_string($conn, $_GET['event_id']);

// Get event name
$event_query = "SELECT name FROM events WHERE id = ?";
$event_stmt = $conn->prepare($event_query);
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();
$event_row = $event_result->fetch_assoc();
$event_name = $event_row['name'];

// Query to get participants and their attendance status
$query = "SELECT p.student_id, p.name, p.email, p.phone, 
          DATE_FORMAT(r.registration_date, '%d %b %Y') as registration_date,
          IFNULL(a.attended, 0) as attended
          FROM participants p
          INNER JOIN registrations r ON p.id = r.participant_id
          LEFT JOIN attendance a ON p.id = a.participant_id AND a.event_id = ?
          WHERE r.event_id = ?
          ORDER BY p.name";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $event_id, $event_id);
$stmt->execute();
$result = $stmt->get_result();

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $event_name . '_attendance.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, ['Student ID', 'Name', 'Email', 'Phone', 'Registration Date', 'Attendance Status']);

// Add data
while ($row = $result->fetch_assoc()) {
    $status = $row['attended'] ? 'Present' : 'Absent';
    fputcsv($output, [
        $row['student_id'],
        $row['name'],
        $row['email'],
        $row['phone'],
        $row['registration_date'],
        $status
    ]);
}

// Close the output stream
fclose($output);

$stmt->close();
$event_stmt->close();
mysqli_close($conn);
exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Attendance - Admin Dashboard</title>
  <link rel="stylesheet" href="/EVENT_MANAGEMENT/create_recruitment.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Attendance Specific Styles */
    #attendance-container {
      padding: 20px 30px;
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      margin: 20px;
    }

    .section-title {
      font-size: 24px;
      font-weight: 600;
      margin-bottom: 20px;
      color: var(--dark);
    }

    .event-selector {
      display: flex;
      flex-direction: column;
      margin-bottom: 30px;
    }

    .event-selector label {
      font-weight: 500;
      margin-bottom: 8px;
      color: var(--dark);
    }

    .event-selector select {
      padding: 10px;
      border-radius: 6px;
      border: 1px solid var(--border);
      background-color: white;
      font-size: 16px;
      outline: none;
      transition: border 0.3s;
    }

    .event-selector select:focus {
      border-color: var(--primary);
    }

    .search-container {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
      width: 100%;
      max-width: 500px;
      background-color: var(--light);
      border-radius: 8px;
      padding: 10px 15px;
      border: 1px solid var(--border);
    }

    .search-container input {
      flex: 1;
      padding: 8px;
      border: none;
      background: none;
      outline: none;
      font-size: 16px;
    }

    .search-container i {
      color: var(--secondary);
      margin-right: 10px;
    }

    .attendance-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    .attendance-table th {
      background-color: var(--primary);
      color: white;
      padding: 12px 15px;
      text-align: left;
      font-weight: 500;
    }

    .attendance-table tr {
      border-bottom: 1px solid var(--border);
    }

    .attendance-table tr:hover {
      background-color: #f1f5f9;
    }

    .attendance-table td {
      padding: 12px 15px;
      color: var(--dark);
    }

    .attendance-checkbox {
      width: 18px;
      height: 18px;
      cursor: pointer;
    }

    .attendance-actions {
      display: flex;
      justify-content: flex-end;
      margin-top: 20px;
    }

    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s;
    }

    .btn-primary {
      background-color: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background-color: var(--primary-dark);
    }

    .status-badge {
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 500;
      display: inline-block;
    }

    .status-present {
      background-color: #dcfce7;
      color: #166534;
    }

    .status-absent {
      background-color: #fee2e2;
      color: #991b1b;
    }

    #content {
      flex: 1;
      overflow-y: auto;
      background-color: #f8fafc;
    }

    .loading-indicator {
      display: none;
      text-align: center;
      padding: 20px;
      color: var(--primary);
    }

    .no-events-message {
      text-align: center;
      padding: 30px;
      color: var(--secondary);
      font-size: 16px;
    }

    /* Animation for auto-save */
    @keyframes saveFeedback {
      0% { opacity: 0; }
      50% { opacity: 1; }
      100% { opacity: 0; }
    }

    .save-feedback {
      color: var(--success);
      font-size: 14px;
      margin-left: 10px;
      animation: saveFeedback 1.5s ease-in-out;
    }

    .filter-container {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    .filter-option {
      padding: 8px 15px;
      background-color: white;
      border: 1px solid var(--border);
      border-radius: 20px;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .filter-option.active {
      background-color: var(--primary);
      color: white;
      border-color: var(--primary);
    }
  </style>
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
        <a href="attendance.php" class="active"><i class="fas fa-clipboard-check"></i> Attendance</a>
        <a href="#"><i class="fas fa-comment-alt"></i> Feedback</a>
        <a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a>
        <a href="#"><i class="fas fa-chart-bar"></i> Reporting</a>
    </div>

    <div id="content">
        <div id="header">
            <div class="user-controls">
                <div class="notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-count">2</span>
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

        <!-- Breadcrumb Section -->
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > 
            <a href="attendance.php">Attendance</a>
        </div>

        <!-- Attendance Content -->
        <div id="attendance-container">
            <h2 class="section-title">Student Attendance</h2>
            
            <!-- Event Selection -->
            <div class="event-selector">
                <label for="event-select">Select Event:</label>
                <select id="event-select">
                    <option value="">-- Select an event --</option>
                    <!-- These options will be populated from the database -->
                </select>
            </div>
            
            <!-- Loading indicator -->
            <div class="loading-indicator" id="loading">
                <i class="fas fa-spinner fa-spin"></i> Loading participants...
            </div>
            
            <!-- Attendance Management Section -->
            <div id="attendance-management" style="display:none;">
                <!-- Search Bar -->
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search-participant" placeholder="Search by name, ID, or email...">
                </div>
                
                <!-- Filter Options -->
                <div class="filter-container">
                    <div class="filter-option active" data-filter="all">All</div>
                    <div class="filter-option" data-filter="present">Present</div>
                    <div class="filter-option" data-filter="absent">Absent</div>
                </div>
            
                <!-- Attendance Table -->
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Registration Date</th>
                            <th>Status</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody id="participant-list">
                        <!-- Participant rows will be populated here -->
                    </tbody>
                </table>
                
                <!-- Actions -->
                <div class="attendance-actions">
                    <button class="btn btn-primary" id="export-attendance">Export to CSV</button>
                </div>
            </div>
            
            <!-- No Events Message -->
            <div class="no-events-message" id="no-events" style="display:none;">
                <i class="fas fa-calendar-times fa-3x"></i>
                <p>No events found. Please create an event first.</p>
            </div>
        </div>
    </div>

    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Toggle account menu
        document.querySelector('.account').addEventListener('click', function() {
            const menu = document.querySelector('.account-menu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });

        // When document is ready
        $(document).ready(function() {
            // Load events into the dropdown
            loadEvents();
            
            // Event change handler
            $('#event-select').change(function() {
                const eventId = $(this).val();
                if(eventId) {
                    loadParticipants(eventId);
                } else {
                    $('#attendance-management').hide();
                }
            });
            
            // Search functionality
            $('#search-participant').on('input', function() {
                const searchText = $(this).val().toLowerCase();
                $('#participant-list tr').each(function() {
                    const rowText = $(this).text().toLowerCase();
                    $(this).toggle(rowText.includes(searchText));
                });
            });
            
            // Filter functionality
            $('.filter-option').click(function() {
                $('.filter-option').removeClass('active');
                $(this).addClass('active');
                
                const filter = $(this).data('filter');
                
                if (filter === 'all') {
                    $('#participant-list tr').show();
                } else if (filter === 'present') {
                    $('#participant-list tr').each(function() {
                        $(this).toggle($(this).find('input[type="checkbox"]').prop('checked'));
                    });
                } else if (filter === 'absent') {
                    $('#participant-list tr').each(function() {
                        $(this).toggle(!$(this).find('input[type="checkbox"]').prop('checked'));
                    });
                }
            });
            
            // Export to CSV
            $('#export-attendance').click(function() {
                exportAttendance();
            });
        });
        
        // Load events from database
        function loadEvents() {
            // Show loading
            $('#loading').show();
            
            // AJAX call to get events
            $.ajax({
                url: 'get_events.php', // Create this PHP file
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#loading').hide();
                    
                    if (response.success && response.events.length > 0) {
                        const select = $('#event-select');
                        
                        response.events.forEach(function(event) {
                            select.append(`<option value="${event.id}">${event.name} (${event.date})</option>`);
                        });
                    } else {
                        $('#no-events').show();
                    }
                },
                error: function() {
                    $('#loading').hide();
                    $('#no-events').show();
                    $('#no-events p').text('Failed to load events. Please try again later.');
                }
            });
        }
        
        // Load participants for a specific event
        function loadParticipants(eventId) {
            // Show loading
            $('#loading').show();
            $('#attendance-management').hide();
            
            // AJAX call to get participants
            $.ajax({
                url: 'get_participants.php', // Create this PHP file
                method: 'GET',
                data: { event_id: eventId },
                dataType: 'json',
                success: function(response) {
                    $('#loading').hide();
                    
                    if (response.success && response.participants.length > 0) {
                        const participantList = $('#participant-list');
                        participantList.empty();
                        
                        response.participants.forEach(function(participant) {
                            const isChecked = participant.attended ? 'checked' : '';
                            const statusClass = participant.attended ? 'status-present' : 'status-absent';
                            const statusText = participant.attended ? 'Present' : 'Absent';
                            
                            participantList.append(`
                                <tr data-id="${participant.id}">
                                    <td>${participant.student_id}</td>
                                    <td>${participant.name}</td>
                                    <td>${participant.email}</td>
                                    <td>${participant.phone}</td>
                                    <td>${participant.registration_date}</td>
                                    <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                                    <td>
                                        <input type="checkbox" class="attendance-checkbox" data-participant-id="${participant.id}" ${isChecked}>
                                    </td>
                                </tr>
                            `);
                        });
                        
                        // Show the attendance management section
                        $('#attendance-management').show();
                        
                        // Attach checkbox change event
                        $('.attendance-checkbox').change(function() {
                            const participantId = $(this).data('participant-id');
                            const attended = $(this).prop('checked');
                            const row = $(this).closest('tr');
                            
                            // Update status badge
                            const statusBadge = row.find('.status-badge');
                            if (attended) {
                                statusBadge.removeClass('status-absent').addClass('status-present').text('Present');
                            } else {
                                statusBadge.removeClass('status-present').addClass('status-absent').text('Absent');
                            }
                            
                            // Save attendance status
                            saveAttendance(participantId, eventId, attended, $(this));
                        });
                    } else {
                        $('#no-events').show();
                        $('#no-events p').text('No participants found for this event.');
                    }
                },
                error: function() {
                    $('#loading').hide();
                    $('#no-events').show();
                    $('#no-events p').text('Failed to load participants. Please try again later.');
                }
            });
        }
        
        // Save attendance status
        function saveAttendance(participantId, eventId, attended, checkbox) {
            // AJAX call to save attendance
            $.ajax({
                url: 'save_attendance.php', // Create this PHP file
                method: 'POST',
                data: {
                    participant_id: participantId,
                    event_id: eventId,
                    attended: attended ? 1 : 0
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Show save feedback
                        const feedbackSpan = $('<span class="save-feedback">Saved!</span>');
                        checkbox.after(feedbackSpan);
                        
                        // Remove feedback after animation completes
                        setTimeout(function() {
                            feedbackSpan.remove();
                        }, 1500);
                    }
                }
            });
        }
        
        // Export attendance to CSV
        function exportAttendance() {
            const eventId = $('#event-select').val();
            if (eventId) {
                window.location.href = `export_attendance.php?event_id=${eventId}`;
            }
        }
    </script>
</body>
</html>