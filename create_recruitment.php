<?php 
include('admin_auth.php');
include('config.php');
$message = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the form data
    $recruit_title = $_POST['recruit_title'];
    $recruit_description = $_POST['recruit_description'];
    $recruit_date = $_POST['recruit_date']; // This will be hidden and auto-filled based on calendar selection
    $recruit_time = $_POST['recruit_time'];
    $recruit_deadline = $_POST['recruit_deadline'];

    // Handle file upload (poster)
    $poster = $_FILES['recruit_poster']['name'];
    $target_dir = "img/"; // Specify your upload directory
    $target_file = $target_dir . basename($poster);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is an image
    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES["recruit_poster"]["tmp_name"]);
        if ($check !== false) {
            $message = "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            $message = "File is not an image.";
            $uploadOk = 0;
        }
    }

    // Check file size (Limit to 5MB)
    if ($_FILES["recruit_poster"]["size"] > 5000000) {
        $message = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats (PNG, JPG, JPEG, GIF)
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // If everything is ok, upload the file
    if ($uploadOk == 0) {
        $message = "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES['recruit_poster']['tmp_name'], $target_file)) {
            $message = "The file " . basename($poster) . " has been uploaded successfully.";
        } else {
            $message = "Sorry, there was an error uploading your file.";
        }
    }

    // Insert the data into the database using prepared statements to prevent SQL injection
    if ($uploadOk == 1) {
        $stmt = $conn->prepare("INSERT INTO recruitment (recruit_title, recruit_description, recruit_date, recruit_time, recruit_deadline, recruit_poster) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $recruit_title, $recruit_description, $recruit_date, $recruit_time, $recruit_deadline, $poster);

        if ($stmt->execute()) {
            $message .= " New recruitment record created successfully!";
            header("Location: create_recruitment.php?success=1");
            exit();
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Display success message if redirected after successful creation
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = "New recruitment record created successfully!";
}

// Fetch existing recruitment dates to mark on the calendar
$booked_dates = array();
$sql = "SELECT recruit_id, recruit_title, recruit_description, recruit_date, recruit_time, recruit_deadline, recruit_poster FROM recruitment";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $booked_dates[] = array(
            'id' => $row["recruit_id"],
            'title' => $row["recruit_title"],
            'date' => $row["recruit_date"],
            'description' => $row["recruit_description"],
            'time' => $row["recruit_time"],
            'deadline' => $row["recruit_deadline"],
            'poster' => $row["recruit_poster"]
        );
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create New Recruitment - Admin Dashboard</title>
  <link rel="stylesheet" href="/EVENT_MANAGEMENT/create_recruitment.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  
  <!-- Include FullCalendar CSS -->
  <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
  <!-- Include Timepicker CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
  <!-- Include jQuery UI CSS -->
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
  <!-- Include custom calendar CSS -->
  <link rel="stylesheet" href="calendar.css">
</head>
<body>
    <div id="sidebar">
        <div class="brand">
            <i class="fas fa-calendar-alt"></i> Dunbian Club
        </div>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="recruitment_list.php" class="active"><i class="fas fa-user-plus"></i> Recruitments</a>
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

    <!-- Breadcrumb Section -->
    <div class="breadcrumb">
      <a href="dashboard.php">Home</a> > 
      <a href="recruitment_list.php">Recruitment</a> > 
      <a href="create_recruitment.php">Create New Recruitment</a>
    </div>

    <!-- Message Display -->
    <?php if ($message): ?>
        <div class="message <?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>">
            <i class="fas <?php echo (strpos($message, 'successfully') !== false) ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Calendar View -->
    <div class="calendar-container">
        <h2>Select Date for New Recruitment</h2>
        <div id="calendar"></div>
        <div class="calendar-legend">
            <div class="legend-item"><span class="available"></span> Available</div>
            <div class="legend-item"><span class="booked"></span> Booked</div>
            <div class="legend-item"><span class="selected"></span> Selected</div>
        </div>
    </div>

    <!-- Create Recruitment Form Modal -->
    <div id="recruitment-form-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Create New Recruitment</h2>
            <form action="create_recruitment.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="recruit_date" name="recruit_date">
                
                <div class="form-group">
                    <label for="recruit_title">Recruitment Title</label>
                    <input type="text" id="recruit_title" name="recruit_title" required>
                </div>

                <div class="form-group">
                    <label for="recruit_description">Description</label>
                    <textarea id="recruit_description" name="recruit_description" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label for="recruit_time">Time</label>
                    <div class="time-picker-container">
                        <input type="text" id="recruit_time" name="recruit_time" class="time-picker" required readonly>
                        <i class="fas fa-clock input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="recruit_deadline">Application Deadline</label>
                    <div class="date-picker-container">
                        <input type="text" id="recruit_deadline" name="recruit_deadline" class="date-picker" required readonly>
                        <i class="fas fa-calendar-alt input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="recruit_poster">Upload Poster</label>
                    <input type="file" id="recruit_poster" name="recruit_poster" accept="image/*">
                </div>

                <div class="form-actions">
                    <button type="button" id="cancel-btn" class="cancel-button">Cancel</button>
                    <button type="submit" class="submit-button">Create Recruitment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Recruitment Details Modal -->
    <div id="view-recruitment-modal" class="modal">
        <div class="modal-content">
            <span class="close-view">&times;</span>
            <h2>Recruitment Details</h2>
            <div class="recruitment-details">
                <div class="detail-item">
                    <h3>Title:</h3>
                    <p id="view-title"></p>
                </div>
                <div class="detail-item">
                    <h3>Date:</h3>
                    <p id="view-date"></p>
                </div>
                <div class="detail-item">
                    <h3>Time:</h3>
                    <p id="view-time"></p>
                </div>
                <div class="detail-item">
                    <h3>Application Deadline:</h3>
                    <p id="view-deadline"></p>
                </div>
                <div class="detail-item">
                    <h3>Description:</h3>
                    <p id="view-description"></p>
                </div>
                <div class="detail-item">
                    <h3>Poster:</h3>
                    <div class="poster-container">
                        <img id="view-poster" src="" alt="Recruitment Poster">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" id="close-view-btn" class="cancel-button">Close</button>
                    <button type="button" id="edit-btn" class="submit-button">Edit</button>
                </div>
            </div>
        </div>
    </div>
  </div>

  <!-- Include jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Include jQuery UI -->
  <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
  <!-- Include jQuery Timepicker -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
  <!-- Include FullCalendar JS -->
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
  <script>
    // Pass PHP array of booked dates to JavaScript
    const bookedDates = <?php echo json_encode($booked_dates); ?>;
    
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        let selectedDate = null;
        
        const calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          selectable: true,
          headerToolbar: {
              left: 'prev,next today',
              center: 'title',
              right: 'dayGridMonth,dayGridWeek' // Add dayGridWeek for additional view
          },
          buttonText: {
              today: 'Today',
              month: 'Month',
              week: 'Week'
          },
          eventClassNames: 'booked-event',
          events: bookedDates.map(function(event) {
              return {
                  id: event.id,
                  title: event.title,
                  start: event.date,
                  allDay: true,
                  extendedProps: {
                      description: event.description,
                      time: event.time,
                      deadline: event.deadline,
                      poster: event.poster
                  }
              };
          }),
          dateClick: function(info) {
              const clickedDate = info.dateStr;
              const today = new Date();
              today.setHours(0, 0, 0, 0);
              const clickedDateObj = new Date(clickedDate);
              
              // Check if date is in the past
              if (clickedDateObj < today) {
                  alert('Cannot schedule recruitment on past dates.');
                  return;
              }
              
              // Check if date is already booked
              const isBooked = bookedDates.some(event => event.date === clickedDate);
              if (isBooked) {
                  alert('This date already has a scheduled recruitment!');
                  return;
              }
              
              // Remove previously selected date styling
              if (selectedDate) {
                  const cells = document.querySelectorAll('.fc-day');
                  cells.forEach(cell => {
                      cell.classList.remove('selected-date');
                  });
              }
              
              // Set new selected date
              selectedDate = clickedDate;
              
              // Add selected styling
              info.dayEl.classList.add('selected-date');
              
              // Show the modal form and populate the hidden date field
              document.getElementById('recruit_date').value = clickedDate;
              document.getElementById('recruitment-form-modal').style.display = 'block';
          },
          eventClick: function(info) {
              // Get the event data
              const event = info.event;
              const eventId = event.id;
              const eventTitle = event.title;
              const eventDate = event.startStr;
              const eventDescription = event.extendedProps.description;
              const eventTime = event.extendedProps.time;
              const eventDeadline = event.extendedProps.deadline;
              const eventPoster = event.extendedProps.poster;
              
              // Populate the view modal with event data
              document.getElementById('view-title').textContent = eventTitle;
              document.getElementById('view-date').textContent = eventDate;
              document.getElementById('view-time').textContent = eventTime;
              document.getElementById('view-deadline').textContent = eventDeadline;
              document.getElementById('view-description').textContent = eventDescription;
              
              // Set the poster image source
              if (eventPoster) {
                  document.getElementById('view-poster').src = "img/" + eventPoster;
                  document.getElementById('view-poster').style.display = 'block';
              } else {
                  document.getElementById('view-poster').style.display = 'none';
              }
              
              // Display the view modal
              document.getElementById('view-recruitment-modal').style.display = 'block';
              
              // Store the event ID for potential editing
              document.getElementById('edit-btn').setAttribute('data-id', eventId);
          }
        });
        
        calendar.render();
        
        // Modal close buttons
        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('recruitment-form-modal').style.display = 'none';
            document.getElementById('recruit_date').value = '';
        });
        
        document.querySelector('.close-view').addEventListener('click', function() {
            document.getElementById('view-recruitment-modal').style.display = 'none';
        });
        
        // Close view modal button
        document.getElementById('close-view-btn').addEventListener('click', function() {
            document.getElementById('view-recruitment-modal').style.display = 'none';
        });
        
        // Edit button functionality - this would redirect to an edit page
        document.getElementById('edit-btn').addEventListener('click', function() {
            const eventId = this.getAttribute('data-id');
            window.location.href = 'edit_recruitment.php?id=' + eventId;
        });
        
        // Cancel button functionality
        document.getElementById('cancel-btn').addEventListener('click', function() {
            document.getElementById('recruitment-form-modal').style.display = 'none';
            document.getElementById('recruit_date').value = '';
        });
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            const createModal = document.getElementById('recruitment-form-modal');
            const viewModal = document.getElementById('view-recruitment-modal');
            
            if (event.target == createModal) {
                createModal.style.display = 'none';
                document.getElementById('recruit_date').value = '';
            }
            
            if (event.target == viewModal) {
                viewModal.style.display = 'none';
            }
        });
        
        // Initialize time picker with separate hour/minute controls
            $('.time-picker').timepicker({
                timeFormat: 'h:mm',       // 12-hour format with AM/PM
                controlType: 'select',      // Use dropdown selectors instead of sliders
                minTime: '8:00am',
                maxTime: '10:00pm',
                defaultTime: '10:00am',
                dropdown: true,
                scrollbar: true,
                step: 1                     // 1-minute increments
            });
        
        // Initialize date picker for deadline
        $('.date-picker').datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: 0,
            changeMonth: true,
            changeYear: true
        });
    });

    // Toggle account menu
    document.querySelector('.account').addEventListener('click', function () {
        const menu = document.querySelector('.account-menu');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });
  </script>
</body>
</html>