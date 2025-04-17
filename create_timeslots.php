<?php
include('admin_auth.php');
include('config.php');

// Fetch recruitment events
$sql_recruitment = "SELECT recruit_id, recruit_title FROM Recruitment";
$result_recruitment = $conn->query($sql_recruitment);

if (!$result_recruitment) {
    die("Error fetching recruitment: " . $conn->error);
}

$errors = [];
$success_message = ""; // Variable to hold success message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate POST data
    $recruit_id = filter_input(INPUT_POST, 'recruit_id', FILTER_SANITIZE_NUMBER_INT);
    $timeslot_date = filter_input(INPUT_POST, 'timeslot_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $start_time = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $end_time = filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $interval = filter_input(INPUT_POST, 'interval', FILTER_VALIDATE_INT);
    $max_participants = filter_input(INPUT_POST, 'max_participants', FILTER_VALIDATE_INT);

    // Validate inputs
    if (!$recruit_id || !$timeslot_date || !$start_time || !$end_time || !$interval || !$max_participants) {
        $errors[] = "All fields are required and must be valid.";
    }

    if ($interval <= 0 || $max_participants <= 0) {
        $errors[] = "Interval and max participants must be positive numbers.";
    }

    // Convert start and end times to timestamps
    $start_timestamp = strtotime("$timeslot_date $start_time");
    $end_timestamp = strtotime("$timeslot_date $end_time");

    if ($start_timestamp >= $end_timestamp) {
        $errors[] = "Start time must be earlier than end time.";
    }

    // Check for overlapping timeslots (simplified query without bind_param)
    $sql_check_overlap = "SELECT * FROM Interview_Times 
    WHERE recruit_id = $recruit_id 
    AND timeslot_date = '$timeslot_date' 
    AND (
        (start_time < '$end_time' AND end_time > '$start_time') OR
        (start_time >= '$start_time' AND start_time < '$end_time') OR
        (start_time < '$end_time' AND end_time > '$start_time')
    )";
    
    $result_check_overlap = $conn->query($sql_check_overlap);

    if ($result_check_overlap && $result_check_overlap->num_rows > 0) {
        $errors[] = "The selected timeslot overlaps with an existing timeslot.";
    }

    // Prevent creating new timeslot inside existing timeslot
    $sql_check_time_conflict = "SELECT * FROM Interview_Times 
                                WHERE recruit_id = $recruit_id 
                                AND timeslot_date = '$timeslot_date' 
                                AND (
                                    ('$start_time' >= start_time AND '$start_time' < end_time) OR
                                    ('$end_time' > start_time AND '$end_time' <= end_time)
                                )";
    $result_check_time_conflict = $conn->query($sql_check_time_conflict);

    if ($result_check_time_conflict && $result_check_time_conflict->num_rows > 0) {
        $errors[] = "The start or end time falls within an existing timeslot. Please choose a different time.";
    }

    // If no errors, proceed with creating the timeslots
    if (empty($errors)) {
        $created_timeslots = 0;

        // Create multiple timeslots based on the interval
        while ($start_timestamp < $end_timestamp) {
            $next_timestamp = $start_timestamp + ($interval * 60); // Add interval in seconds

            // Ensure the slot does not exceed the end time
            if ($next_timestamp > $end_timestamp) {
                break;
            }

            // Format the times for the current slot
            $slot_start_time = date('H:i:s', $start_timestamp);
            $slot_end_time = date('H:i:s', $next_timestamp);

            // Insert the timeslot into the database
            $sql_insert = "INSERT INTO Interview_Times (recruit_id, timeslot_date, start_time, end_time, max_participants, booked_count, timeslot_status)
                           VALUES ($recruit_id, '$timeslot_date', '$slot_start_time', '$slot_end_time', $max_participants, 0, 'Available')";
            if ($conn->query($sql_insert)) {
                $created_timeslots++;
            } else {
                $errors[] = "Error creating timeslot $slot_start_time - $slot_end_time: " . $conn->error;
            }

            // Update the start timestamp for the next loop
            $start_timestamp = $next_timestamp;
        }

        // Success message
        if (empty($errors)) {
            $success_message = "Total timeslots created successfully: $created_timeslots.";
        }
    }
}
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
      <a href="create_timeslots.php">Create Interview Timeslot</a>
    </div>

    <div class="new-interview">
    <h2>Create Interview Timeslot</h2>
    <?php if (!empty($errors)): ?>
    <div class="message-container error-messages">
        <i class="fas fa-times-circle"></i> <!-- Error icon -->
        <?php foreach ($errors as $error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($success_message)): ?>
    <div class="message-container success-message">
        <i class="fas fa-check-circle"></i> <!-- Success icon -->
        <p><?php echo htmlspecialchars($success_message); ?></p>
    </div>
<?php endif; ?>


      
<form action="create_timeslots.php" method="POST">
        <div class="form-group">
            <label for="recruit_id">Recruitment Event:</label>
            <select name="recruit_id" id="recruit_id" required>
                <?php while ($row = $result_recruitment->fetch_assoc()): ?>
                    <option value="<?php echo $row['recruit_id']; ?>"><?php echo $row['recruit_title']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="timeslot_date">Date:</label>
            <div class="input-container">
                <input type="date" name="timeslot_date" id="timeslot_date" required>
                <i class="fa fa-calendar input-icon"></i> <!-- Calendar Icon -->
            </div>
        </div>

        <div class="form-group">
            <label for="start_time">Start Time:</label>
            <div class="input-container">
                <input type="time" name="start_time" id="start_time" required>
                <i class="fa fa-clock input-icon"></i> <!-- Clock Icon -->
            </div>
        </div>

        <div class="form-group">
            <label for="end_time">End Time:</label>
            <div class="input-container">
                <input type="time" name="end_time" id="end_time" required>
                <i class="fa fa-clock input-icon"></i> <!-- Clock Icon -->
            </div>
        </div>

        <div class="form-group">
            <label for="max_participants">Max Participants:</label>
            <input type="number" name="max_participants" id="max_participants" min="1" value="3" required>
        </div>

        <div class="form-group">
            <label for="interval">Interval (minutes):</label>
            <select name="interval" id="interval" required>
                <option value="15">15 minutes</option>
                <option value="30">30 minutes</option>
                <option value="45">45 minutes</option>
                <option value="60">1 hour</option>
            </select>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Create Timeslot</button>
        </div>
    </form>
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
