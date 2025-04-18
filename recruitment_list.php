
<?php
include('admin_auth.php');
// Include the database connection file
include('config.php');

// Check for status change action
if (isset($_GET['action']) && isset($_GET['application_id'])) {
    $application_id = $_GET['application_id'];
    $action = $_GET['action'];

    // Validate the action
    if ($action == 'approve' || $action == 'reject') {
        // Determine the status
        $status = ($action == 'approve') ? 'Approve' : 'Reject';  // Set status to Approve or Reject

        // Prepare the update SQL query
        $update_sql = "UPDATE recruitment_applications SET application_status = ? WHERE application_id = ?";

        // Prepare the statement
        if ($stmt = $conn->prepare($update_sql)) {
            // Bind parameters
            $stmt->bind_param('si', $status, $application_id);

            // Execute the query
            if ($stmt->execute()) {
                // Check if any rows were affected
                if ($stmt->affected_rows > 0) {
                    // Redirect back to the recruitment list with a success message
                    header("Location: recruitment_list.php?status=success&action=$action");
                    exit();  // Make sure no further code is executed
                } else {
                    // No rows were affected (could be due to the same status being set)
                    header("Location: recruitment_list.php?status=nochange&action=$action");
                    exit();
                }
            } else {
                // Error executing the query
                echo "Error executing the query: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        } else {
            // Error preparing the statement
            echo "Error preparing the statement: " . $conn->error;
        }
    }
}

// Set default active tab
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'recruitments';

// Fetch all recruitment titles for the dropdown
$recruitments_sql = "SELECT recruit_id, recruit_title FROM recruitment";  // Correct table name here
$recruitments_result = $conn->query($recruitments_sql);

// Check if a recruitment title is selected
$recruit_id = isset($_POST['recruit_id']) ? $_POST['recruit_id'] : '';

// Fetch applications related to the selected recruitment title
if ($recruit_id) {
    $sql = "SELECT a.application_id, CONCAT(s.stu_first_name, ' ', s.stu_last_name) AS stu_name, s.stu_matric, 
                   a.dept_choice_1, a.dept_choice_2, a.join_reason, a.past_experience, a.head_role, a.ig_link, 
                   a.recommendations, a.application_status, t.timeslot_date, t.start_time, r.recruit_title, r.recruit_time
            FROM recruitment_applications a
            LEFT JOIN students s ON a.stu_matric = s.stu_matric
            LEFT JOIN recruitment r ON a.recruit_id = r.recruit_id
            LEFT JOIN interview_times t ON a.timeslot_id = t.timeslot_id
            WHERE a.recruit_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param('i', $recruit_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing the query: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recruitment Management</title>
  <link rel="stylesheet" href="/EVENT_MANAGEMENT/recruitment.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        <div class="breadcrumb">
            <a href="dashboard.php">Home</a>
            <span>&gt;</span>
            <a href="recruitment_list.php">Recruitments</a>
        </div>

        <!-- Display status messages if any -->
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="message success">
            <i class="fas fa-check-circle"></i>
            Application has been <?php echo $_GET['action'] == 'approve' ? 'approved' : 'rejected'; ?> successfully.
        </div>
        <?php endif; ?>

        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <button class="tab-button <?php echo $activeTab == 'recruitments' ? 'active' : ''; ?>" 
                    onclick="switchTab('recruitments')">
                <i class="fas fa-clipboard-list"></i> Manage Recruitments
            </button>
            <button class="tab-button <?php echo $activeTab == 'applicants' ? 'active' : ''; ?>" 
                    onclick="switchTab('applicants')">
                <i class="fas fa-users"></i> View Applicants
            </button>
        </div>

        <!-- Applicants Tab Content -->
        <div id="applicantsTab" class="tab-content <?php echo $activeTab == 'applicants' ? 'active' : ''; ?>">
            <div class="applications-list">
                <form method="POST" action="recruitment_list.php?tab=applicants">
                    <label for="recruitment">Select Recruitment:</label>
                    <select name="recruit_id" id="recruitment" onchange="this.form.submit()">
                        <option value="">-- Select Recruitment --</option>
                        <?php
                        if ($recruitments_result && $recruitments_result->num_rows > 0) {
                            while ($row = $recruitments_result->fetch_assoc()) {
                                $selected = ($recruit_id == $row['recruit_id']) ? 'selected' : '';
                                echo "<option value='" . $row['recruit_id'] . "' $selected>" . htmlspecialchars($row['recruit_title']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </form>
            </div>

            <?php if ($recruit_id && $activeTab == 'applicants'): ?>
            <div class="applications-list-table-container">
                <div class="interview-table-header">
                    <h2>Recruitment Application List</h2>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Matric No</th>
                                <th>First Choice</th>
                                <th>Second Choice</th>
                                <th>Interview Date</th>
                                <th>Interview Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($result) && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    // Determine status class
                                    $statusClass = '';
                                    $status = $row['application_status'] ?? 'Pending';
                                    if ($status == 'Approve') {
                                        $statusClass = 'status-approved';
                                    } elseif ($status == 'Reject') {
                                        $statusClass = 'status-rejected';
                                    } else {
                                        $statusClass = 'status-pending';
                                    }
                                    
                                    echo "<tr>
                                            <td>" . htmlspecialchars($row['stu_name']) . "</td>
                                            <td>" . htmlspecialchars($row['stu_matric']) . "</td>
                                            <td>" . htmlspecialchars($row['dept_choice_1']) . "</td>
                                            <td>" . htmlspecialchars($row['dept_choice_2']) . "</td>
                                            <td>" . htmlspecialchars($row['timeslot_date'] ?? 'Not Set') . "</td>
                                            <td>" . htmlspecialchars($row['start_time'] ?? 'Not Set') . "</td>
                                            <td><span class='status $statusClass'>" . htmlspecialchars($status) . "</span></td>
                                            <td>
                                                <div class='action-buttons'>
                                                    <button class='btn btn-sm btn-primary' onclick=\"location.href='view_applications.php?id=" . $row['application_id'] . "'\">
                                                        <i class='fas fa-eye'></i> View
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8'>No applications found for this recruitment.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php elseif ($activeTab == 'applicants' && !$recruit_id): ?>
            <div class="applications-list-table-container">
                <div class="interview-table-header">
                    <h2>Recruitment Application List</h2>
                </div>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>No Recruitment Selected</h3>
                    <p>Please select a recruitment from the dropdown above to view applications.</p>
                </div>
            </div>
            <?php endif; ?>
    </div>

<!-- Updated Recruitments Tab Content -->
<div id="recruitmentsTab" class="tab-content <?php echo $activeTab == 'recruitments' ? 'active' : ''; ?>">
    <div class="created-recruitments-table-container">
        <div class="created-recruitments-header">
            <h2>Event Recruitments</h2>
            <button class="btn-create-new" onclick="location.href='create_recruitment.php';"><i class="fas fa-plus"></i> Create Recruitment</button>
        </div>
        <div>
            <table>
                <thead>
                    <tr>
                        <th>Recruitment Title</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Deadline</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT recruit_id, recruit_title, recruit_description, recruit_date, recruit_time, recruit_deadline FROM recruitment ORDER BY recruit_deadline ASC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($row['recruit_title']) . "</td>
                                    <td>" . htmlspecialchars($row['recruit_description']) . "</td>
                                    <td>" . htmlspecialchars($row['recruit_date']) . "</td>
                                    <td>" . htmlspecialchars($row['recruit_time']) . "</td>
                                    <td>" . htmlspecialchars($row['recruit_deadline']) . "</td>
                                    <td>
                                        <div class='action-buttons'>
                                            <button class='btn-view' onclick=\"location.href='view_recruitment.php?id=" . htmlspecialchars($row['recruit_id']) . "'\"><i class='fas fa-eye'></i> View</button>
                                            <button class='btn-edit' onclick=\"location.href='edit_recruitment.php?id=" . htmlspecialchars($row['recruit_id']) . "'\"><i class='fas fa-edit'></i> Edit</button>
                                            <button class='btn-delete' onclick=\"if(confirm('Are you sure you want to delete this recruitment?')) location.href='delete_recruitment.php?id=" . htmlspecialchars($row['recruit_id']) . "'\"><i class='fas fa-trash'></i> Delete</button>
                                        </div>
                                    </td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No recruitments found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
  // Toggle account menu
  document.querySelector('.account').addEventListener('click', function () {
    const menu = document.querySelector('.account-menu');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
  });

  // Tab switching function
  function switchTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(tab => {
      tab.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
      button.classList.remove('active');
    });
    
    // Show the selected tab content and activate the button
    document.getElementById(tabName + 'Tab').classList.add('active');
    event.currentTarget.classList.add('active');
    
    // Update URL with the active tab
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tabName);
    window.history.pushState({}, '', url);
  }

  function confirmAction(action, applicationId) {
    const confirmation = confirm(`Are you sure you want to ${action} this application?`);
    if (confirmation) {
        // Redirect to a PHP handler to process the action
        window.location.href = `process_action.php?action=${action}&application_id=${applicationId}&recruit_id=${document.getElementById('recruitment').value}`;
    }
  }
</script>
</body>
</html>