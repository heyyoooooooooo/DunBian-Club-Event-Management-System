<?php
include('admin_auth.php');
include('config.php'); 

if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $ad_matric = trim($_GET['id']);

    $query = "SELECT * FROM admin WHERE ad_matric = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("s", $ad_matric);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
    } else {
        echo "Admin not found!";
        exit;
    }

    $stmt->close();
} else {
    echo "Admin ID not provided or is empty!";
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Admin Profile - Admin Dashboard</title>
  <link rel="stylesheet" href="/EVENT_MANAGEMENT/viewprofile.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <style>
    .profile-container {
      width: 80%;
      max-width: 1000px;
      margin: 30px auto;
    }

    .profile-header {
      display: grid;
      grid-template-columns: 250px 1fr;
      gap: 2rem;
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      margin-bottom: 1.5rem;
    }

    .avatar-section {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .avatar {
      width: 200px;
      height: 200px;
      border-radius: 50%;
      object-fit: cover;
      border: 5px solid #f0f0f0;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      background-color: #e5e7eb;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .avatar i {
      font-size: 80px;
      color: #9ca3af;
    }
    
    .avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    .role-badge {
      background-color: var(--primary);
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 500;
      margin-top: 10px;
      box-shadow: 0 2px 4px rgba(79, 70, 229, 0.3);
    }
    
    .admin-info {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    
    .admin-info p {
      margin-bottom: 12px;
      padding: 8px 0;
      border-bottom: 1px solid #f0f0f0;
      font-size: 16px;
      color: var(--secondary);
    }
    
    .admin-info strong {
      color: var(--dark);
      font-weight: 600;
      width: 100px;
      display: inline-block;
    }
    
    .profile-actions {
      display: flex;
      justify-content: space-between;
      margin-top: 20px;
    }
    
    .btn-back {
      display: inline-flex;
      align-items: center;
      background-color: var(--primary);
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    
    .btn-back i {
      margin-right: 10px;
    }
    
    .btn-back:hover {
      background-color: #4338ca;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(79, 70, 229, 0.3);
    }
    
    .btn-edit {
      background-color: #f0ad4e;
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
    }
    
    .btn-edit i {
      margin-right: 10px;
    }
    
    .btn-edit:hover {
      background-color: #ec971f;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(240, 173, 78, 0.3);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .profile-header {
        grid-template-columns: 1fr;
      }
      
      .avatar-section {
        margin-bottom: 20px;
      }
      
      .admin-info {
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
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="recruitment_list.php"><i class="fas fa-user-plus"></i> Recruitments</a>
        <a href="interview.php"><i class="fas fa-clock"></i> Interview</a>
        <a href="events.php"><i class="fas fa-calendar-check"></i> Events</a>
        <a href="#"><i class="fas fa-comment-alt"></i> Feedback</a>
        <a href="profile.php" class="active"><i class="fas fa-user-circle"></i> Profile</a>
        <a href="#"><i class="fas fa-chart-bar"></i> Reporting</a>
    </div>

    <div id="content">
        <div id="header">
            <div class="user-controls">
                <div class="notifications">
                    <i class="fas fa-bell"></i>
                    <!--<span class="notification-count">3</span>-->
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
            <a href="profile.php">Admin List</a>
            <span>&gt;</span>
            <span>Admin Profile</span>
        </div>

        <div class="profile-container">
            <h2>Admin Profile</h2>

            <div class="profile-header">
                <div class="avatar-section">
                    <div class="avatar">
                        <?php if (isset($admin['ad_avatar']) && !empty($admin['ad_avatar'])): ?>
                            <img src="<?php echo htmlspecialchars($admin['ad_avatar']); ?>" alt="Admin Avatar">
                        <?php else: ?>
                            <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                    </div>
                    <span class="role-badge">Administrator</span>
                </div>

                <div class="admin-info">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($admin['ad_first_name'] . ' ' . $admin['ad_last_name']); ?></p>
                    <p><strong>Matric:</strong> <?php echo htmlspecialchars($admin['ad_matric']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($admin['ad_email']); ?></p>
                    <p><strong>Gender:</strong> <?php echo htmlspecialchars($admin['ad_gender']); ?></p>
                    <p><strong>Year:</strong> <?php echo htmlspecialchars($admin['ad_year']); ?></p>
                    <p><strong>Faculty:</strong> <?php echo htmlspecialchars($admin['ad_faculty']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($admin['ad_phone'] ?: 'N/A'); ?></p>
                    <p><strong>Created at:</strong> <?php echo htmlspecialchars($admin['ad_created_at']); ?></p>
                </div>
            </div>

            <div class="profile-actions">
                <a href="profile.php?tab=admin" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Admin List</a>
                <a href="edit_admin.php?id=<?php echo htmlspecialchars($admin['ad_matric']); ?>" class="btn-edit"> Edit Profile</a>
            </div>
        </div>
    </div>

<script>
  // Toggle account menu
  document.querySelector('.account').addEventListener('click', function () {
    const menu = document.querySelector('.account-menu');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
  });
  
  // Close account menu when clicking outside
  document.addEventListener('click', function(event) {
    if (!event.target.closest('.account')) {
      document.querySelector('.account-menu').style.display = 'none';
    }
  });
</script>
</body>
</html>