<?php
session_start();
include('admin_auth.php');
include('config.php');

// Check if the user role is 'super_admin'
$is_admin = isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin';

// Get active tab (default to 'students')
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'students';

// Initialize search terms
$search_term_student = isset($_POST['search_student']) ? $_POST['search_student'] : '';
$search_term_admin = isset($_POST['search_admin']) ? $_POST['search_admin'] : '';

// Base queries
$student_query = "SELECT * FROM students";
$admin_query = "SELECT * FROM admin";

// Apply search filters if terms exist
if (!empty($search_term_student)) {
    $search_term_student = $conn->real_escape_string($search_term_student);
    $student_query .= " WHERE stu_first_name LIKE '%$search_term_student%' 
                       OR stu_last_name LIKE '%$search_term_student%' 
                       OR stu_matric LIKE '%$search_term_student%' 
                       OR stu_email LIKE '%$search_term_student%' 
                       OR stu_phone_number LIKE '%$search_term_student%'";
}

if (!empty($search_term_admin)) {
    $search_term_admin = $conn->real_escape_string($search_term_admin);
    $admin_query .= " WHERE ad_first_name LIKE '%$search_term_admin%' 
                     OR ad_last_name LIKE '%$search_term_admin%' 
                     OR ad_matric LIKE '%$search_term_admin%' 
                     OR ad_email LIKE '%$search_term_admin%' 
                     OR ad_phone LIKE '%$search_term_admin%'";
}

// Execute queries
$student_result = $conn->query($student_query);
$admin_result = $conn->query($admin_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recruitment Management</title>
  <link rel="stylesheet" href="/EVENT_MANAGEMENT/profile.css">
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
            <a href="profile.php">Profile</a>
        </div>

        <div class="main-content">
            <!-- Page Title -->
            <div class="page-header">
                <h2>Users Management</h2>
            </div>

            <!-- Tab Navigation -->
            <div class="tab-navigation">
                <button class="tab-button <?php echo $active_tab == 'students' ? 'active' : ''; ?>" 
                        onclick="window.location.href='profile.php?tab=students'">
                    <i class="fas fa-users"></i> Students
                </button>
                <button class="tab-button <?php echo $active_tab == 'admin' ? 'active' : ''; ?>" 
                        onclick="window.location.href='profile.php?tab=admin'">
                    <i class="fas fa-user-shield"></i> Admin
                </button>
            </div>

        
            <!-- Tab Content -->
            <div class="tab-content">
                <?php if ($active_tab == 'students'): ?>
                    <!-- Students Tab -->
                    <div class="tab-pane active">
                        <h2>Student List</h2>
                        <div class="search-bar-container">
                            <form method="POST" action="profile.php?tab=students" class="search-bar">
                              
                                <input type="text" name="search_student" 
                                       placeholder="Search students..." 
                                       value="<?php echo htmlspecialchars($search_term_student); ?>">
                                <button type="submit">Search</button>
                            </form>
                        </div>

                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Matric</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $student_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['stu_matric']); ?></td>
                                            <td><?php echo htmlspecialchars($row['stu_first_name'] . ' ' . $row['stu_last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['stu_email']); ?></td>
                                            <td><?php echo htmlspecialchars($row['stu_phone_number']); ?></td>
                                            <td class="actions">
                                                <button class="btn-view" onclick="location.href='view_student.php?id=<?php echo htmlspecialchars($row['stu_matric']); ?>'">
                                                    <!--<i class="fas fa-eye"></i>--> View
                                                </button>
                                                <button class="btn-delete" onclick="confirmDelete('student', '<?php echo htmlspecialchars($row['stu_matric']); ?>')">
                                                    <!--<i class="fas fa-trash"></i>--> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Admin Tab -->
                    <div class="tab-pane active">
                        <div class="admin-section-header">
                            <div class="title-action-row">
                                <h2>Admin List</h2>
                                <?php if ($is_admin): ?>
                                    <button class="btn-create" onclick="location.href='add_admin.php'">
                                        <i class="fas fa-plus"></i> Create New Admin
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="search-bar-container">
                                <form method="POST" action="profile.php?tab=admin" class="search-bar">
                                    <input type="text" name="search_admin" 
                                        placeholder="Search admins..." 
                                        value="<?php echo htmlspecialchars($search_term_admin); ?>">
                                    <button type="submit">Search</button>
                                </form>
                            </div>
                        </div>

                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Matric</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($admin = $admin_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($admin['ad_matric']); ?></td>
                                            <td><?php echo htmlspecialchars($admin['ad_first_name'] . ' ' . $admin['ad_last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($admin['ad_email']); ?></td>
                                            <td><?php echo htmlspecialchars($admin['ad_phone']); ?></td>
                                            <td class="actions">
                                                <button class="btn-view" onclick="location.href='view_profile.php?id=<?php echo htmlspecialchars($admin['ad_matric']); ?>'">
                                                    <!--<i class="fas fa-eye"></i>--> View
                                                </button>
                                                <?php if ($is_admin): ?>
                                                    <button class="btn-edit" onclick="location.href='edit_admin.php?id=<?php echo htmlspecialchars($admin['ad_matric']); ?>'">
                                                        <!--<i class="fas fa-edit"></i>--> Edit
                                                    </button>
                                                    <button class="btn-delete" onclick="confirmDelete('admin', '<?php echo htmlspecialchars($admin['ad_matric']); ?>')">
                                                        <!--<i class="fas fa-trash"></i>--> Delete
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Account menu toggle
        document.querySelector('.account').addEventListener('click', function() {
            const menu = document.querySelector('.account-menu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });

        // Delete confirmation
        function confirmDelete(type, id) {
            if (confirm(`Are you sure you want to delete this ${type}?`)) {
                location.href = `delete_${type}.php?id=${id}`;
            }
        }
    </script>
</body>
</html>