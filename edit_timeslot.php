<?php
include('admin_auth.php');
// Database connection
include('config.php');

// Get parameters from the URL
    
$timeslot_id = filter_input(INPUT_GET, 'timeslot_id', FILTER_SANITIZE_NUMBER_INT);
$timeslot_date = filter_input(INPUT_GET, 'timeslot_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$start_time = filter_input(INPUT_GET, 'start_time', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$recruit_id = filter_input(INPUT_GET, 'recruit_id', FILTER_SANITIZE_NUMBER_INT);

if (!$timeslot_id || !$timeslot_date || !$start_time || !$recruit_id) {
    die("Invalid timeslot data.");
}

// Query to fetch the timeslot data
$sql = "SELECT * FROM Interview_Times WHERE timeslot_id = ? AND timeslot_date = ? AND start_time = ? AND recruit_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isss", $timeslot_id, $timeslot_date, $start_time, $recruit_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Timeslot data not available for editing.");
}

$timeslot = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $max_participants = filter_input(INPUT_POST, 'max_participants', FILTER_SANITIZE_NUMBER_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    $update_sql = "UPDATE Interview_Times SET max_participants = ?, timeslot_status = ? WHERE timeslot_id = ? AND timeslot_date = ? AND start_time = ? AND recruit_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("isisss", $max_participants, $status, $timeslot_id, $timeslot_date, $start_time, $recruit_id);

    if ($update_stmt->execute()) {
        echo "<p style='color:green;'>Timeslot updated successfully.</p>";
    } else {
        echo "<p style='color:red;'>Failed to update timeslot.</p>";
    }

    $update_stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Interview Timeslot</title>
  <link rel="stylesheet" href="/EVENT_MANAGEMENT/create_timeslot.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<!-- Include Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
    <div id="sidebar">
        <div class="brand">
            <i class="fas fa-calendar-alt"></i> Dunbian Club
        </div>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="recruitment_list.php"><i class="fas fa-user-plus"></i> Recruitments</a>
        <a href="interview.php" class="active"><i class="fas fa-clock"></i> Interview</a>
        <a href="events.php"><i class="fas fa-calendar-check"></i> Events</a>
        <a href="#"><i class="fas fa-comment-alt"></i> Feedback</a>
        <a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a>
        <a href="#"><i class="fas fa-chart-bar"></i> Reporting</a>
    </div>

    <div id="content">
        <div id="header">
            <!--<div class="search-bar">-->
                <!--<i class="fas fa-search"></i>-->
                <!--<input type="text" placeholder="Search...">-->
            
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
      <a href="interview.php">Manage Interviews</a>
      <span>&gt;</span>
      <a href="edit_timeslots.php">Create Interview Timeslot</a>
    </div>

    <div class="new-interview">
      <h2>Edit Interview Timeslot</h2>
      
       
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="success">
            <p><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($timeslot_data): ?>
    <form action="edit_timeslot.php?timeslot_id=<?php echo $timeslot_id; ?>" method="POST">
        <label for="timeslot_date">Date:</label>
        <input type="date" id="timeslot_date" name="timeslot_date" 
               value="<?php echo htmlspecialchars($timeslot_data['timeslot_date']); ?>" required>

        <label for="start_time">Start Time:</label>
        <input type="time" id="start_time" name="start_time" 
               value="<?php echo htmlspecialchars($timeslot_data['start_time']); ?>" required>

        <label for="end_time">End Time:</label>
        <input type="time" id="end_time" name="end_time" 
               value="<?php echo htmlspecialchars($timeslot_data['end_time']); ?>" required>

        <label for="interval">Interval (minutes):</label>
        <input type="number" id="interval" name="interval" 
               value="<?php echo htmlspecialchars($timeslot_data['interval_minutes']); ?>" required>

        <label for="max_participants">Max Participants:</label>
        <input type="number" id="max_participants" name="max_participants" 
               value="<?php echo htmlspecialchars($timeslot_data['max_participants']); ?>" required>

        <button type="submit">Save Changes</button>
    </form>
    <?php else: ?>
        <p>Timeslot data not available for editing.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
  // Initialize flatpickr for the timeslot date
  flatpickr("#timeslot_date", {
    dateFormat: "Y-m-d",
  });

  // Initialize flatpickr for the start time (with time picker)
  flatpickr("#start_time", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
  });

  // Initialize flatpickr for the end time (with time picker)
  flatpickr("#end_time", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
  });

    // Toggle account menu
    document.querySelector('.account').addEventListener('click', function () {
      const menu = document.querySelector('.account-menu');
      menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });
  </script>
</body>
</html>