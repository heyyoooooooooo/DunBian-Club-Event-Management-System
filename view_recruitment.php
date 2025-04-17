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

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Recruitment - Admin Dashboard</title>
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
      <a href="view_recruitment.php?id=<?php echo $recruit_id; ?>">View Recruitment</a>
    </div>

    <!-- View Recruitment Details -->
    <div class="new-recruitment">
      <h2>View Recruitment</h2>
      
      <!-- Message Display -->
      <?php if ($message): ?>
        <div class="message <?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>">
          <i class="fas <?php echo (strpos($message, 'successfully') !== false) ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
          <?php echo $message; ?>
        </div>
      <?php endif; ?>

      <!-- Show the recruitment details in a form -->
      <?php if ($recruitment): ?>
        <form action="#" method="POST">
          <div class="details">
            <div class="form-group">
              <label for="recruit_title">Recruitment Title</label>
              <input type="text" id="recruit_title" name="recruit_title" value="<?php echo $recruitment['recruit_title']; ?>" disabled>
            </div>
            <div class="form-group">
              <label for="recruit_description">Description</label>
              <textarea id="recruit_description" name="recruit_description" rows="5" disabled><?php echo $recruitment['recruit_description']; ?></textarea>
            </div>
            <div class="form-group">
              <label for="recruit_date">Date</label>
              <input type="text" id="recruit_date" name="recruit_date" value="<?php echo $recruitment['recruit_date']; ?>" disabled>
            </div>
            <div class="form-group">
              <label for="recruit_time">Time</label>
              <input type="text" id="recruit_time" name="recruit_time" value="<?php echo $recruitment['recruit_time']; ?>" disabled>
            </div>
            <div class="form-group">
              <label for="recruit_deadline">Deadline</label>
              <input type="text" id="recruit_deadline" name="recruit_deadline" value="<?php echo $recruitment['recruit_deadline']; ?>" disabled>
            </div>
            <div class="form-group">
              <label for="recruit_poster">Recruitment Poster</label>
              <div>
                <a href="img/<?php echo $recruitment['recruit_poster']; ?>" target="_blank">
                  <img src="img/<?php echo $recruitment['recruit_poster']; ?>" alt="Recruitment Poster" style="width: 200px;">
                </a>
              </div>
            </div>
          </div>
        </form>
      <?php else: ?>
        <p>Recruitment not found.</p>
      <?php endif; ?>
    </div>
  </div>

    <script>
  // Toggle account menu
  document.querySelector('.account').addEventListener('click', function () {
    const menu = document.querySelector('.account-menu');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
  });

  </script>
</body>
</html>
