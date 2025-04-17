<?php
// Ensure session is started early on
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('admin_auth.php'); 
include('config.php');

// Initialize variables
$event_id = $event_title = $event_description = $event_date = $event_time = $event_location = '';
$event_participant_limit = 50; // Default prefill value
$event_points_awarded = 10;    // Default prefill value
$event_status = $event_deadline = $event_poster = '';
$edit_mode = false;
$alert_message = '';
$alert_type = '';

// Get current admin's matric number from session
if (!isset($_SESSION['ad_matric']) || empty($_SESSION['ad_matric'])) {
    // If not logged in, redirect to login page or show an appropriate error message
    header("Location: login.php");
    exit();
}
$ad_matric = $_SESSION['ad_matric'];

// Check if edit mode is active
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_mode = true;
    $event_id = $_GET['edit'];
    
    // Fetch event details
    $sql = "SELECT * FROM events WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $event_data = $result->fetch_assoc();
        $event_title = $event_data['event_title'];
        $event_description = $event_data['event_description'];
        $event_date = $event_data['event_date'];
        $event_time = $event_data['event_time'];
        $event_location = $event_data['event_location'];
        $event_participant_limit = $event_data['event_participant_limit'];
        $event_points_awarded = $event_data['event_points_awarded'];
        $event_status = $event_data['event_status'];
        $event_deadline = $event_data['event_deadline'];
        $event_poster = $event_data['event_poster'];
    } else {
        $alert_message = "Event not found.";
        $alert_type = "danger";
        $edit_mode = false;
    }
}

// Handle event deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    
    // First, check if there's a poster to delete
    $sql = "SELECT event_poster FROM events WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $poster_data = $result->fetch_assoc();
        if (!empty($poster_data['event_poster']) && file_exists($poster_data['event_poster'])) {
            unlink($poster_data['event_poster']);
        }
    }
    
    // Delete the event
    $sql = "DELETE FROM events WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $alert_message = "Event deleted successfully!";
        $alert_type = "success";
    } else {
        $alert_message = "Error deleting event: " . $conn->error;
        $alert_type = "danger";
    }
}

// Process form submission for adding or updating an event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_event'])) {
    // Retrieve and trim form values
    $event_title = trim($_POST['event_title']);
    $event_description = trim($_POST['event_description']);
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $event_location = trim($_POST['event_location']);
    $event_participant_limit = isset($_POST['event_participant_limit']) ? intval($_POST['event_participant_limit']) : 50;
    $event_points_awarded = isset($_POST['event_points_awarded']) ? intval($_POST['event_points_awarded']) : 10;
    $event_status = $_POST['event_status'];
    $event_deadline = $_POST['event_deadline'];
    
    // Handle file upload for event poster
    if (isset($_FILES['event_poster']) && $_FILES['event_poster']['error'] == 0) {
        // Check if file is an image
        $check = getimagesize($_FILES['event_poster']['tmp_name']);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $alert_message = "File is not an image.";
            $alert_type = "danger";
            $uploadOk = 0;
        }
    
        // Get file extension
        $imageFileType = strtolower(pathinfo($_FILES['event_poster']['name'], PATHINFO_EXTENSION));
        
        // Check file size (5MB limit)
        if ($_FILES['event_poster']['size'] > 5000000) {
            $alert_message = "Sorry, your file is too large.";
            $alert_type = "danger";
            $uploadOk = 0;
        }
    
        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $alert_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $alert_type = "danger";
            $uploadOk = 0;
        }
    
        if ($uploadOk == 1) {
            $target_dir = "img/"; 
            
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
    
            $event_poster = time() . '_' . basename($_FILES['event_poster']['name']);
            $target_file = $target_dir . $event_poster;
    
            if (move_uploaded_file($_FILES['event_poster']['tmp_name'], $target_file)) {
                // If updating and there's an old poster, delete it
                if (!empty($_POST['current_poster']) && $edit_mode) {
                    $old_poster = $_POST['current_poster'];
                    if (file_exists($old_poster)) {
                        unlink($old_poster);
                    }
                }
                // Only store the filename in the database
                $event_poster = basename($event_poster);
            } else {
                $alert_message = "Sorry, there was an error uploading your file.";
                $alert_type = "danger";
                $uploadOk = 0;
            }
        }
    } else {
        $event_poster = isset($_POST['current_poster']) ? $_POST['current_poster'] : '';
    }

    if (isset($_POST['event_id']) && !empty($_POST['event_id'])) {
        $event_id = $_POST['event_id'];
        $sql = "UPDATE events SET 
                    event_title = ?, 
                    event_description = ?,
                    event_date = ?, 
                    event_time = ?,
                    event_location = ?,
                    event_participant_limit = ?,
                    event_points_awarded = ?,
                    event_status = ?, 
                    event_deadline = ?,
                    event_poster = ?
                WHERE event_id = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $alert_message = "Prepare failed: " . $conn->error;
            $alert_type = "danger";
        } else {
            $stmt->bind_param("sssssiiissi", $event_title, $event_description, $event_date, $event_time, 
                               $event_location, $event_participant_limit, $event_points_awarded, 
                               $event_status, $event_deadline, $event_poster, $event_id);
            
            if ($stmt->execute()) {
                $alert_message = "Event updated successfully!";
                $alert_type = "success";
            } else {
                $alert_message = "Error updating event: " . $conn->error;
                $alert_type = "danger";
            }
        }
    } else {
        
        $check_admin_sql = "SELECT ad_matric FROM admin WHERE ad_matric = ?";
        $check_admin_stmt = $conn->prepare($check_admin_sql);
        if (!$check_admin_stmt) {
            $alert_message = "Prepare failed: " . $conn->error;
            $alert_type = "danger";
        } else {
            $check_admin_stmt->bind_param("s", $ad_matric);
            $check_admin_stmt->execute();
            $check_admin_result = $check_admin_stmt->get_result();
            
            if ($check_admin_result->num_rows == 0) {
                $alert_message = "Error: Admin not found in the system.";
                $alert_type = "danger";
            } else {
                $sql = "INSERT INTO events (event_title, event_description, event_date, event_time, 
                                             event_location, event_participant_limit, event_points_awarded, 
                                             event_status, event_deadline, ad_matric, event_poster) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    $alert_message = "Prepare failed: " . $conn->error;
                    $alert_type = "danger";
                } else {
                    $stmt->bind_param("sssssiissss", $event_title, $event_description, $event_date, $event_time, 
                                      $event_location, $event_participant_limit, $event_points_awarded, 
                                      $event_status, $event_deadline, $ad_matric, $event_poster);
                    
                    if ($stmt->execute()) {
                        $alert_message = "Event created successfully!";
                        $alert_type = "success";
                        // Optionally clear form fields
                        $event_title = $event_description = $event_date = $event_time = $event_location = '';
                        $event_participant_limit = 50;
                        $event_points_awarded = 10;
                        $event_status = $event_deadline = $event_poster = '';
                    } else {
                        $alert_message = "Error creating event: " . $conn->error;
                        $alert_type = "danger";
                    }
                }
            }
        }
    }
}

$update_status_sql = "UPDATE events SET event_status = 'open' WHERE event_deadline < CURDATE() AND event_status = 'open'";
$conn->query($update_status_sql);

$view_registrations = false;
$registrations = [];
$current_event_title = '';

if (isset($_GET['view_registrations']) && !empty($_GET['view_registrations'])) {
    $view_registrations = true;
    $event_id = $_GET['view_registrations'];
    
    // Get event title
    $event_sql = "SELECT event_title FROM events WHERE event_id = ?";
    $event_stmt = $conn->prepare($event_sql);
    $event_stmt->bind_param("i", $event_id);
    $event_stmt->execute();
    $event_result = $event_stmt->get_result();
    
    if ($event_result->num_rows > 0) {
        $event_data = $event_result->fetch_assoc();
        $current_event_title = $event_data['event_title'];
    }
    
    // Get registrations for this event
    $sql = "SELECT r.*, s.stu_name, s.stu_matric as student_number 
            FROM registrations r 
            JOIN students s ON r.student_id = s.id 
            WHERE r.event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $registrations[] = $row;
        }
    }
}

// Fetch all events for listing
$events = [];
$sql = "SELECT * FROM events ORDER BY event_date DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

// Get current date for min attribute in date inputs
$current_date = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Event Management</title>
  <link rel="stylesheet" href="/EVENT_MANAGEMENT/events.css">
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
        <a href="events.php" class="active"><i class="fas fa-calendar-check"></i> Events</a>
        <a href="#"><i class="fas fa-comment-alt"></i> Feedback</a>
        <a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a>
        <a href="reports.php"><i class="fas fa-chart-bar"></i> Reporting</a>
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
            <a href="dashboard.php">Home</a>
            <span>&gt;</span>
            <a href="events.php">Events</a>
            <?php if ($view_registrations): ?>
                <span>&gt;</span>
                <span>Registrations for <?php echo htmlspecialchars($current_event_title); ?></span>
            <?php endif; ?>
        </div>

        <div class="container">
            <?php if (!empty($alert_message)): ?>
                <div class="alert alert-<?php echo $alert_type; ?>">
                    <?php echo $alert_message; ?>
                </div>
            <?php endif; ?>

            <div class="page-title">
                <div class="container">
                    <h2>Events Management</h2>
                </div>
            </div>

            <?php if ($view_registrations): ?>
                <!-- Registrations View -->
                <a href="events.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Events
                </a>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            Registrations for "<?php echo htmlspecialchars($current_event_title); ?>"
                            <span class="badge-count"><?php echo count($registrations); ?></span>
                        </div>
                    </div>
                    
                    <?php if (count($registrations) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Student Name</th>
                                        <th>Student ID</th>
                                        <th>Registration Date</th>
                                        <th>Payment Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; foreach ($registrations as $reg): ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><?php echo htmlspecialchars($reg['stu_name']); ?></td>
                                            <td><?php echo htmlspecialchars($reg['student_number']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($reg['registration_date'])); ?></td>
                                            <td>
                                                <?php if ($reg['payment_status'] == 'paid'): ?>
                                                    <span class="status-badge status-open">Paid</span>
                                                <?php else: ?>
                                                    <span class="status-badge status-closed">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No registrations found for this event.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Events Management -->
                <div class="tabs">
                    <div class="tab active" id="tab-events" onclick="switchTab('events')">Events List</div>
                    <div class="tab" id="tab-create" onclick="switchTab('create')">Create/Edit Event</div>
                </div>
                
                <!-- Event List Section -->
                <div id="events-section" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">All Events</div>
                            <button class="btn btn-primary" onclick="switchTab('create')">
                                <i class="fas fa-plus"></i> Add New Event
                            </button>
                        </div>
                        
                        <?php if (count($events) > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Location</th>
                                            <th>Participants</th>
                                            <!--<th>Points</th>-->
                                            <th>Status</th>
                                            <th>Deadline</th>
                                            <th>Poster</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($events as $event): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($event['event_title']); ?></td>
                                                <td><?php echo date('d M Y', strtotime($event['event_date'])); ?></td>
                                                <td><?php echo date('h:i A', strtotime($event['event_time'])); ?></td>
                                                <td><?php echo htmlspecialchars($event['event_location']); ?></td>
                                                <td><?php echo htmlspecialchars($event['event_participant_limit']); ?></td>
                                                <td><?php echo htmlspecialchars($event['event_points_awarded']); ?></td>
                                                <td>
                                                    <?php if ($event['event_status'] == 'open'): ?>
                                                        <span class="status-badge status-open">Open</span>
                                                    <?php else: ?>
                                                        <span class="status-badge status-closed">Closed</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('d M Y', strtotime($event['event_deadline'])); ?></td>
                                                <!-- In the events list table -->
                                                <td>
                                                    <?php if (!empty($event['event_poster'])): ?>
                                                        <img src="img/<?php echo htmlspecialchars($event['event_poster']); ?>" 
                                                            alt="Event Poster" width="50" height="50" 
                                                            onclick="openModal('img/<?php echo htmlspecialchars($event['event_poster']); ?>')" 
                                                            style="cursor: pointer;">
                                                    <?php else: ?>
                                                        <span>No poster</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="actions-cell">
                                                    <a href="events.php?view_registrations=<?php echo $event['event_id']; ?>" class="btn btn-info" title="View Registrations">
                                                        <!--<i class="fas fa-eye"></i>--> View 
                                                    </a>
                                                    <a href="events.php?edit=<?php echo $event['event_id']; ?>" class="btn btn-warning" title="Edit Event">
                                                        <!--<i class="fas fa-edit"></i>--> Edit
                                                    </a>
                                                    <a href="#" onclick="confirmDelete(<?php echo $event['event_id']; ?>); return false;" class="btn btn-danger" title="Delete Event">
                                                        <!--<i class="fas fa-trash"></i>--> Delete
                                                    </a>
                                                </td>

                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No events found. Create your first event!</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Create/Edit Event Section -->
                <div id="create-section" class="tab-content" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title"><?php echo $edit_mode ? 'Edit Event' : 'Create New Event'; ?></div>
                        </div>
                        
                        <form method="POST" action="events.php" enctype="multipart/form-data">
                            <?php if ($edit_mode): ?>
                                <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                                <?php if (!empty($event_poster)): ?>
                                    <input type="hidden" name="current_poster" value="<?php echo $event_poster; ?>">
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label class="form-label">Event Title</label>
                                <input type="text" name="event_title" class="form-control" value="<?php echo htmlspecialchars($event_title); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Event Description</label>
                                <textarea name="event_description" class="form-control" rows="5"><?php echo htmlspecialchars($event_description); ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Event Date</label>
                                    <input type="date" name="event_date" class="form-control" value="<?php echo $event_date; ?>" min="<?php echo $current_date; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Event Time</label>
                                    <input type="time" name="event_time" class="form-control" value="<?php echo $event_time; ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Event Location</label>
                                <input type="text" name="event_location" class="form-control" value="<?php echo htmlspecialchars($event_location); ?>" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Participation Limit</label>
                                    <input type="number" name="event_participant_limit" class="form-control prefilled" 
                                           value="<?php echo !empty($event_participant_limit) ? htmlspecialchars($event_participant_limit) : '50'; ?>" 
                                           required min="1" onclick="this.select()">
                                </div>
                                
                                <!--<div class="form-group">
                                    <label class="form-label">Points Awarded</label>
                                    <input type="number" name="event_points_awarded" class="form-control prefilled" 
                                           value="<?php echo !empty($event_points_awarded) ? htmlspecialchars($event_points_awarded) : '10'; ?>" 
                                           required min="0" onclick="this.select()">
                                </div>-->
                            </div>
                            
                            <!-- In the create/edit form -->
                            <div class="form-group">
                                <label class="form-label">Event Poster</label>
                                <input type="file" name="event_poster" class="form-control" accept="image/*">
                                <?php if (!empty($event_poster)): ?>
                                    <p>Current poster:</p>
                                    <img src="img/<?php echo htmlspecialchars($event_poster); ?>" 
                                        alt="Event Poster" class="poster-preview" 
                                        onclick="openModal('img/<?php echo htmlspecialchars($event_poster); ?>')">
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Status</label>
                                    <select name="event_status" class="form-control" required>
                                        <option value="open" <?php echo ($event_status == 'open') ? 'selected' : ''; ?>>Open</option>
                                        <option value="closed" <?php echo ($event_status == 'closed') ? 'selected' : ''; ?>>Closed</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Registration Deadline</label>
                                    <input type="date" name="event_deadline" class="form-control" 
                                           value="<?php echo $event_deadline; ?>" min="<?php echo $current_date; ?>" required>
                                    <small class="form-text text-muted">Registration closes at 00:00 AM on this date.</small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" name="submit_event" class="btn btn-primary">
                                    <?php echo $edit_mode ? 'Update Event' : 'Create Event'; ?>
                                </button>
                                <a href="events.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="posterModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="posterImage">
    </div>

    <script>
        // Toggle account menu
        document.querySelector('.account').addEventListener('click', function () {
            const menu = document.querySelector('.account-menu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });

        // Auto-hide alert messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alertMessages = document.querySelectorAll('.alert');
            
            if (alertMessages.length > 0) {
                setTimeout(function() {
                    alertMessages.forEach(function(alert) {
                        alert.style.display = 'none';
                    });
                }, 5000);
                
                // Also hide when clicking anywhere on the document
                document.addEventListener('click', function() {
                    alertMessages.forEach(function(alert) {
                        alert.style.display = 'none';
                    });
                });
            }
        });
        
        // Switch between tabs
        function switchTab(tab) {
            console.log("Switching to tab:", tab); 
            if (tab === 'events') {
                document.getElementById('events-section').style.display = 'block';
                document.getElementById('create-section').style.display = 'none';
                document.getElementById('tab-events').classList.add('active');
                document.getElementById('tab-create').classList.remove('active');
            } else if (tab === 'create') {
                document.getElementById('events-section').style.display = 'none';
                document.getElementById('create-section').style.display = 'block';
                document.getElementById('tab-events').classList.remove('active');
                document.getElementById('tab-create').classList.add('active');
            }
        }
        
        <?php if ($edit_mode): ?>
            switchTab('create');
        <?php endif; ?>
        
        // Confirm delete
        function confirmDelete(eventId) {
        if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = 'events.php';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete';
        input.value = eventId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
        
        function openModal(imageSrc) {
            const modal = document.getElementById('posterModal');
            const modalImg = document.getElementById('posterImage');
            modal.style.display = "block";
            modalImg.src = imageSrc;
        }
        
        function closeModal() {
            document.getElementById('posterModal').style.display = "none";
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('posterModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>