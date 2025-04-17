<?php
include('admin_auth.php');
// Include the database connection file
include('config.php');
$message = ''; // Initialize message variable

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the form data
    $recruit_title = $_POST['recruit_title'];
    $recruit_description = $_POST['recruit_description'];
    $recruit_date = $_POST['recruit_date'];
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

    // If everything is ok, try to upload the file
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
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create New Recruitment - Admin Dashboard</title>
  <link rel="stylesheet" href="/EVENT_MANAGEMENT/create_recruitment.css">
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
        <a href="recruitment_list.php" class="active"><i class="fas fa-user-plus"></i> Recruitments</a>
        <a href="interview.php"><i class="fas fa-clock"></i> Interview</a>
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

    <!-- Breadcrumb Section -->
    <div class="breadcrumb">
      <a href="dashboard.php">Home</a> > 
      <a href="recruitment_list.php">Recruitment</a> > 
      <a href="create_recruitment.php">Create New Recruitment</a>
    </div>

     <!-- Create Recruitment Form -->
     <div class="new-recruitment">
      <h2>Create New Recruitment</h2>
      
      <!-- Message Display -->
      <?php if ($message): ?>
        <div class="message <?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>">
          <i class="fas <?php echo (strpos($message, 'successfully') !== false) ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
          <?php echo $message; ?>
        </div>
      <?php endif; ?>

      <form action="create_recruitment.php" method="POST" enctype="multipart/form-data">
        <label for="recruit_title">Recruitment Title</label>
        <input type="text" id="recruit_title" name="recruit_title" required>

        <label for="recruit_description">Description</label>
        <textarea id="recruit_description" name="recruit_description" rows="4" required></textarea>

        <label for="recruit_date">Date</label>
        <div class="input-container">
          <input type="text" id="recruit_date" name="recruit_date" required>
          <i class="fas fa-calendar-alt input-icon"></i>
        </div>

        <label for="recruit_time">Time</label>
        <div class="input-container">
          <input type="text" id="recruit_time" name="recruit_time" required>
          <i class="fas fa-clock input-icon"></i>
        </div>

        <label for="recruit_deadline">Deadline</label>
        <div class="input-container">
          <input type="text" id="recruit_deadline" name="recruit_deadline" required>
          <i class="fas fa-calendar-alt input-icon"></i>
        </div>

        <label for="recruit_poster">Upload Poster</label>
        <input type="file" id="recruit_poster" name="recruit_poster" accept="image/*">

        <button type="submit">Create Recruitment</button>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    flatpickr("#recruit_date", {
      enableTime: false,
      dateFormat: "Y-m-d"
    });

    flatpickr("#recruit_time", {
      enableTime: true,
      noCalendar: true,
      dateFormat: "H:i",
      time_24hr: true
    });

    flatpickr("#recruit_deadline", {
      enableTime: false,
      dateFormat: "Y-m-d"
    });

    // Check if the current date and time have passed the recruitment deadline
    function checkRecruitmentDeadline() {
        const recruitDate = document.getElementById("recruit_date").value;
        const recruitTime = document.getElementById("recruit_time").value;

        const recruitDateTime = new Date(recruitDate + " " + recruitTime); // Combine date and time
        const currentDateTime = new Date(); // Get the current date and time

        const applyButton = document.getElementById("apply_button");

        // If the recruitment deadline has passed, hide the "Apply Now" button
        if (currentDateTime > recruitDateTime) {
            applyButton.style.display = "none"; // Hide the button
        }
    }

    // Trigger the function to check the deadline when the page loads
    window.onload = checkRecruitmentDeadline;

    // Toggle account menu
    document.querySelector('.account').addEventListener('click', function () {
      const menu = document.querySelector('.account-menu');
      menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });
  </script>

</body>
</html>
