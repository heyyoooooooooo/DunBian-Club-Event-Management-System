<?php
session_start(); // Start the session

// Check if admin is logged in
if (!isset($_SESSION['ad_matric'])) {
    header("Location: login_form.php"); // Redirect to login page if not logged in
    exit;
}

// Include configuration for the database
include('config.php');

// Handle Avatar Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    // Get admin matric from form
    $ad_matric = isset($_POST['ad_matric']) ? $_POST['ad_matric'] : $_SESSION['ad_matric'];

    // Validate the matric belongs to the logged-in admin or admin has permission
    if ($_SESSION['ad_matric'] !== $ad_matric) {
        echo json_encode(['success' => false, 'message' => 'Permission denied']);
        exit;
    }

    // Set upload directory
    $upload_dir = 'uploads/avatars/';

    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Get file information
    $file = $_FILES['avatar'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];

    // Get file extension
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Allowed extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    // Check if file extension is allowed
    if (!in_array($fileExt, $allowedExtensions)) {
        echo json_encode(['success' => false, 'message' => 'File type not allowed. Please upload an image (jpg, jpeg, png, gif).']);
        exit;
    }

    // Maximum file size (2MB)
    $maxFileSize = 2 * 1024 * 1024;

    // Check file size
    if ($fileSize > $maxFileSize) {
        echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 2MB.']);
        exit;
    }

    // Generate a unique file name to prevent overwriting
    $newFileName = $ad_matric . '_' . uniqid() . '.' . $fileExt;
    $uploadPath = $upload_dir . $newFileName;

    // Try to upload the file
    if (move_uploaded_file($fileTmpName, $uploadPath)) {
        // Delete old avatar if it exists
        $sql = "SELECT ad_avatar FROM admin WHERE ad_matric = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $ad_matric);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $oldAvatar = $row['ad_avatar'];
            
            // Delete old file if it exists and is not the default avatar
            if ($oldAvatar && file_exists($oldAvatar) && strpos($oldAvatar, 'default-avatar') === false) {
                unlink($oldAvatar);
            }
        }
        
        // Update the database with new avatar path
        $sql = "UPDATE admin SET ad_avatar = ? WHERE ad_matric = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $uploadPath, $ad_matric);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Avatar updated successfully', 'avatar_path' => $uploadPath]);
        } else {
            // If database update fails, delete uploaded file
            unlink($uploadPath);
            echo json_encode(['success' => false, 'message' => 'Failed to update database']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
    }
    exit; // End processing for AJAX requests
}

// Fetch admin details from the database
$ad_matric = $_SESSION['ad_matric'];
$sql = "SELECT * FROM admin WHERE ad_matric = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $ad_matric);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc(); // Fetch admin data
} else {
    echo "Admin not found!";
    exit;
}

// Set default avatar if no avatar exists
$avatar_path = isset($admin['ad_avatar']) && $admin['ad_avatar'] ? $admin['ad_avatar'] : "path/to/default-avatar.jpg"; // Update with actual default avatar path

// Close the database connection
$stmt->close();
$conn->close();
?>

<!-- HTML Content -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/EVENT_MANAGEMENT/viewprofile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Add avatar click styles */
        /* Additional styles for improved admin profile display */
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
            position: relative;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        
        .avatar:hover {
            opacity: 0.8;
        }
        
        .avatar:hover::after {
            content: 'Change Photo';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px;
            font-size: 12px;
            text-align: center;
        }
        
        .avatar img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #4CAF50;
        }
        
        .avatar i.fas {
            font-size: 150px;
            color: #ccc;
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
            <a href="profile.php">Admin List</a>
        </div>

        <div class="profile-container">
            <h2>Admin Profile</h2>

            <div class="profile-header">
                <div class="avatar-section">
                    <div class="avatar" id="avatarContainer" onclick="changeAvatar()">
                        <?php if (isset($admin['ad_avatar']) && !empty($admin['ad_avatar'])): ?>
                            <img src="<?php echo htmlspecialchars($admin['ad_avatar']); ?>" alt="Admin Avatar" id="avatarImage">
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
                <a href="edit_admin.php?id=<?php echo htmlspecialchars($admin['ad_matric']); ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit Profile</a>
            </div>
        </div>
        
        <!-- Hidden file input for avatar upload -->
        <input type="file" id="avatarUpload" style="display: none;" accept="image/*">
        
        <!-- Loading indicator and alerts -->
        <div id="uploadStatus" style="display: none; position: fixed; top: 20px; right: 20px; padding: 15px; border-radius: 5px; background-color: #333; color: white;"></div>
    </div>

    <script>
        // Function to trigger avatar upload
        function changeAvatar() {
            document.getElementById('avatarUpload').click();
        }
        
        // Listen for file selection
        document.getElementById('avatarUpload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Show loading status
                const statusElement = document.getElementById('uploadStatus');
                statusElement.textContent = "Uploading...";
                statusElement.style.display = "block";
                
                const formData = new FormData();
                formData.append('avatar', file);
                formData.append('ad_matric', '<?php echo $ad_matric; ?>');

                fetch(window.location.href, {  // Now posting to the same page
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update avatar on the page
                        const avatarContainer = document.getElementById('avatarContainer');
                        
                        // Clear the current content
                        avatarContainer.innerHTML = '';
                        
                        // Create and add new image
                        const img = document.createElement('img');
                        img.src = data.avatar_path + '?t=' + new Date().getTime(); // Add timestamp to prevent caching
                        img.alt = "Admin Avatar";
                        img.id = "avatarImage";
                        avatarContainer.appendChild(img);
                        
                        // Show success message
                        statusElement.textContent = "Avatar updated successfully!";
                        statusElement.style.backgroundColor = "#4CAF50";
                        
                        // Hide the message after 3 seconds
                        setTimeout(() => {
                            statusElement.style.display = "none";
                        }, 3000);
                    } else {
                        // Show error message
                        statusElement.textContent = data.message || "Failed to upload avatar";
                        statusElement.style.backgroundColor = "#f44336";
                        
                        // Hide the message after 3 seconds
                        setTimeout(() => {
                            statusElement.style.display = "none";
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Show error message
                    statusElement.textContent = "Error uploading avatar";
                    statusElement.style.backgroundColor = "#f44336";
                    
                    // Hide the message after 3 seconds
                    setTimeout(() => {
                        statusElement.style.display = "none";
                    }, 3000);
                });
            }
        });

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