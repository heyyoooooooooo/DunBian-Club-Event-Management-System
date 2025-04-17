<?php
include('admin_auth.php');
// Include the database connection file
include('config.php');
$message = ''; // Initialize message variable
$recruitment = null; // Initialize recruitment variable to prevent warnings

// Check if the recruitment ID is provided
if (isset($_GET['id'])) {
    $recruit_id = $_GET['id'];

    // Fetch the existing data from the database
    $stmt = $conn->prepare("SELECT * FROM recruitment WHERE recruit_id = ?");
    $stmt->bind_param("i", $recruit_id); // Bind the correct parameter
    $stmt->execute(); // Execute the prepared statement
    $result = $stmt->get_result(); // Get the result from the query

    if ($result->num_rows == 1) {
        $recruitment = $result->fetch_assoc(); // Fetch the data
    } else {
        $message = "Recruitment not found!";
    }
    $stmt->close(); // Close the statement
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $recruit_title = $_POST['recruit_title'];
    $recruit_description = $_POST['recruit_description'];
    $recruit_date = $_POST['recruit_date'];
    $recruit_time = $_POST['recruit_time'];
    $recruit_deadline = $_POST['recruit_deadline'];
    $recruit_id = $_POST['id']; // Hidden field for ID

    // Handle file upload (poster)
    $poster = $_FILES['recruit_poster']['name'];
    $uploadOk = 1;

    if (!empty($poster)) {
        $target_dir = "img/"; // Specify your upload directory
        $target_file = $target_dir . basename($poster);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate the image
        if ($_FILES["recruit_poster"]["size"] > 5000000) {
            $message = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
            $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES['recruit_poster']['tmp_name'], $target_file)) {
                $message = "The file " . basename($poster) . " has been uploaded successfully.";
            } else {
                $message = "Sorry, there was an error uploading your file.";
                $uploadOk = 0;
            }
        }
    } else {
        // If no new file is uploaded, keep the existing poster filename
  $poster = isset($recruitment['recruit_poster']) ? $recruitment['recruit_poster'] : '';

    }

    // Update the record in the database if everything is okay
    if ($uploadOk == 1) {
        if (!empty($poster)) {
            $stmt = $conn->prepare("UPDATE recruitment SET recruit_title = ?, recruit_description = ?, recruit_date = ?, recruit_time = ?, recruit_deadline = ?, recruit_poster = ? WHERE recruit_id = ?");
            $stmt->bind_param("ssssssi", $recruit_title, $recruit_description, $recruit_date, $recruit_time, $recruit_deadline, $poster, $recruit_id);
        } else {
            $stmt = $conn->prepare("UPDATE recruitment SET recruit_title = ?, recruit_description = ?, recruit_date = ?, recruit_time = ?, recruit_deadline = ? WHERE recruit_id = ?");
            $stmt->bind_param("sssssi", $recruit_title, $recruit_description, $recruit_date, $recruit_time, $recruit_deadline, $recruit_id);
        }

        if ($stmt->execute()) {
            $message .= " Recruitment record updated successfully!";
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
  <title>Edit Recruitment - Admin Dashboard</title>
  <link rel="stylesheet" href="/EVENT_MANAGEMENT/recruitment.css">
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
        <!-- Recruitments Link with Dropdown -->
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
      <a href="dashboard.php">Home</a> 
      <span>&gt;</span> 
      <a href="recruitment_list.php">Recruitment</a> 
      <span>&gt;</span> 
      <a href="edit_recruitment.php?id=<?php echo $recruit_id; ?>">Edit Recruitment</a>
    </div>

    <!-- Edit Recruitment Form -->
    <div class="new-recruitment">
      <h2>Edit Recruitment</h2>
      
      <!-- Message Display -->
      <?php if ($message): ?>
        <div class="message <?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>">
          <i class="fas <?php echo (strpos($message, 'successfully') !== false) ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
          <?php echo $message; ?>
        </div>
      <?php endif; ?>

      <!-- Only show the form if $recruitment is populated -->
      <form action="edit_recruitment.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $recruit_id; ?>"> <!-- Hidden field for ID -->

        <label for="recruit_title">Recruitment Title</label>
        <input type="text" id="recruit_title" name="recruit_title" value="<?php echo isset($_POST['recruit_title']) ? $_POST['recruit_title'] : (isset($recruitment['recruit_title']) ? $recruitment['recruit_title'] : ''); ?>" required>

        <label for="recruit_description">Description</label>
        <textarea id="recruit_description" name="recruit_description" rows="4" required><?php echo isset($_POST['recruit_description']) ? $_POST['recruit_description'] : (isset($recruitment['recruit_description']) ? $recruitment['recruit_description'] : ''); ?></textarea>

        <label for="recruit_date">Date</label>
        <div class="input-container">
          <input type="text" id="recruit_date" name="recruit_date" value="<?php echo isset($_POST['recruit_date']) ? $_POST['recruit_date'] : (isset($recruitment['recruit_date']) ? $recruitment['recruit_date'] : ''); ?>" required>
          <i class="fas fa-calendar-alt input-icon"></i>
        </div>

        <label for="recruit_time">Time</label>
        <div class="input-container">
          <input type="text" id="recruit_time" name="recruit_time" value="<?php echo isset($_POST['recruit_time']) ? $_POST['recruit_time'] : (isset($recruitment['recruit_time']) ? $recruitment['recruit_time'] : ''); ?>" required>
          <i class="fas fa-clock input-icon"></i>
        </div>

        <label for="recruit_deadline">Deadline</label>
        <div class="input-container">
          <input type="text" id="recruit_deadline" name="recruit_deadline" value="<?php echo isset($_POST['recruit_deadline']) ? $_POST['recruit_deadline'] : (isset($recruitment['recruit_deadline']) ? $recruitment['recruit_deadline'] : ''); ?>" required>
          <i class="fas fa-calendar-alt input-icon"></i>
        </div>

        <label for="recruit_poster">Recruitment Poster</label>
        <input type="file" id="recruit_poster" name="recruit_poster">
        <p>Current poster:</p>
        <a href="img/<?php echo isset($recruitment['recruit_poster']) ? $recruitment['recruit_poster'] : ''; ?>" target="_blank">
    <img src="img/<?php echo isset($recruitment['recruit_poster']) ? $recruitment['recruit_poster'] : ''; ?>" alt="Recruitment Poster" style="max-width: 100px; height: auto;">
</a>
        <button type="submit">Save Changes</button>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    flatpickr("#recruit_date", {
      enableTime: false,
      dateFormat: "Y-m-d",
    });

    flatpickr("#recruit_time", {
      enableTime: true,
      noCalendar: true,
      dateFormat: "H:i",
    });

    flatpickr("#recruit_deadline", {
      enableTime: false,
      dateFormat: "Y-m-d",
    });
  </script>
</body>
</html>
