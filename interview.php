<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Management | Admin Dashboard</title>
    <link rel="stylesheet" href="/EVENT_MANAGEMENT/interview.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Additional CSS for improved design */
        .tab-navigation {
            display: flex;
            margin: 0 20px 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .tab-button {
            padding: 14px 28px;
            background: none;
            color: var(--secondary);
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-weight: 500;
            margin-right: 15px;
            transition: all 0.3s ease;
        }
        
        .tab-button.active {
            color: var(--primary);
            border-bottom: 3px solid var(--primary);
        }
        
        .tab-button i {
            margin-right: 10px;
        }
        
        .recruitment-filter {
            margin: 0 20px 25px;
            width: calc(100% - 40px);
        }
        
        .recruitment-filter select {
            padding: 12px 18px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            width: 100%;
            max-width: 350px;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .recruitment-filter select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }
        
        /* Status indicators */
        .status-full-row {
            background-color: #fff5f5;
        }
        
        .status-full {
            color: #dc2626;
            font-weight: 600;
            padding: 6px 12px;
            background-color: rgba(220, 38, 38, 0.1);
            border-radius: 20px;
            display: inline-block;
            font-size: 0.85rem;
        }
        
        .status-available {
            color: #16a34a;
            font-weight: 600;
            padding: 6px 12px;
            background-color: rgba(22, 163, 74, 0.1);
            border-radius: 20px;
            display: inline-block;
            font-size: 0.85rem;
        }
        
        /* Page title styling */
        .page-title {
            margin: 30px 30px 20px;
            font-size: 1.8rem;
            color: var(--dark);
            font-weight: 600;
        }
        
        /* Container styling */
        .interview-table-container {
            background: white;
            border-radius: 12px;
            padding: 25px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin: 0 30px 30px;
        }
        
        /* Table header */
        .interview-table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0 20px 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .interview-table-header h3 {
            font-size: 1.3rem;
            color: var(--dark);
            font-weight: 600;
            margin: 0;
        }
        
        /* Create button */
        .btn-create-new {
            background-color: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-create-new:hover {
            background-color: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
        }
        
        /* Search bar */
        .search-bar-container {
            margin: 0 20px 25px;
            display: flex;
            justify-content: flex-end;
        }
        
        .search-bar {
            display: flex;
            align-items: center;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 4px 6px 4px 16px;
            width: 100%;
            max-width: 350px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .search-bar input {
            flex: 1;
            border: none;
            outline: none;
            padding: 10px 0;
            font-size: 14px;
        }
        
        .search-bar button {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        
        .search-bar button:hover {
            background-color: #4338ca;
        }
        
        /* Table styling */
        .interview-applicants-table {
            width: calc(100% - 40px);
            margin: 0 20px;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .interview-applicants-table th {
            background-color: #f8fafc;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: var(--secondary);
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }
        
        .interview-applicants-table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        
        .interview-applicants-table tr:last-child td {
            border-bottom: none;
        }
        
        .interview-applicants-table tr:hover {
            background-color: #f8fafc;
        }
        
        /* Action buttons */
        .btn-view, .btn-delete {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.3s ease;
            min-width: 80px;
        }
        
        .btn-view {
            background-color: var(--primary);
        }
        
        .btn-view:hover {
            background-color: #4338ca;
            transform: translateY(-1px);
        }
        
        .btn-delete {
            background-color: #f44336;
        }
        
        .btn-delete:hover {
            background-color: #e53935;
            transform: translateY(-1px);
        }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--secondary);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .interview-table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .btn-create-new {
                align-self: flex-start;
            }
            
            .recruitment-filter select {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar - Keeping original -->
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

    <!-- Main Content -->
    <div id="content">
        <!-- Header - Keeping original -->
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

        <!-- Breadcrumb Navigation -->
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a>
            <span>></span>
            <a href="interview.php">Interview</a>
            <?php if ($active_tab == 'applicants'): ?>
                <span>></span>
                <a href="interview.php?tab=applicants">Applicants</a>
            <?php endif; ?>
        </div>

        <h2 class="page-title">Interview Timeslots Management</h2>

        <!-- Interview Management -->
        <div class="interview-table-container">
            <!-- Tab Navigation -->
            <div class="tab-navigation">
                <a href="interview.php?tab=timeslots<?php echo $selected_recruitment ? '&recruitment=' . $selected_recruitment : ''; ?>">
                    <button class="tab-button <?php echo $active_tab == 'timeslots' ? 'active' : ''; ?>">
                        <i class="fas fa-clock"></i> Timeslots
                    </button>
                </a>
                <a href="interview.php?tab=applicants<?php echo $selected_recruitment ? '&recruitment=' . $selected_recruitment : ''; ?>">
                    <button class="tab-button <?php echo $active_tab == 'applicants' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Applicants
                    </button>
                </a>
            </div>
            
            <!-- Recruitment Filter -->
            <div class="recruitment-filter">
                <form method="GET" action="interview.php">
                    <input type="hidden" name="tab" value="<?php echo $active_tab; ?>">
                    <select name="recruitment" onchange="this.form.submit()">
                        <option value="0">All Recruitment Events</option>
                        <?php foreach ($recruitment_options as $option): ?>
                            <option value="<?php echo $option['recruit_id']; ?>" <?php echo ($selected_recruitment == $option['recruit_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($option['recruit_title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            
            <?php if ($active_tab == 'timeslots'): ?>
                <!-- Timeslot Tab Content -->
                <div class="tab-content">
                    <div class="interview-table-header">
                        <h3>Manage Timeslots</h3>
                        <button class="btn-create-new" onclick="location.href='create_timeslots.php';">
                            <i class="fas fa-plus"></i> Create Timeslot
                        </button>
                    </div>

                    <!-- Search Bar -->
                    <div class="search-bar-container">
                        <form method="POST" action="interview.php?tab=timeslots<?php echo $selected_recruitment ? '&recruitment=' . $selected_recruitment : ''; ?>" class="search-bar">
                            <input type="text" name="search" placeholder="Search by recruitment title or date..." value="<?php echo htmlspecialchars($search_term); ?>">
                            <button type="submit"><i class="fas fa-search"></i> Search</button>
                        </form>
                    </div>

                    <!-- Timeslot Table -->
                    <table class="interview-applicants-table">
                        <thead>
                            <tr>
                                <th>Recruitment Event</th>
                                <th>Timeslot Date</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Max Participants</th>
                                <th>Booked Count</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query to fetch interview timeslot data
                            $sql = "SELECT it.timeslot_id, it.timeslot_date, it.start_time, it.end_time, it.max_participants, 
                                        it.booked_count, it.status, r.recruit_title, r.recruit_id
                                    FROM interview_times it
                                    INNER JOIN recruitment r ON it.recruit_id = r.recruit_id";

                            // Add recruitment filter if selected
                            if ($selected_recruitment > 0) {
                                $sql .= " WHERE it.recruit_id = " . $selected_recruitment;
                                
                                // Add search term as additional filter
                                if (!empty($search_term)) {
                                    $search_term = $conn->real_escape_string($search_term);
                                    $sql .= " AND (r.recruit_title LIKE '%$search_term%' 
                                            OR it.timeslot_date LIKE '%$search_term%')";
                                }
                            } else {
                                // Only apply search if no recruitment filter
                                if (!empty($search_term)) {
                                    $search_term = $conn->real_escape_string($search_term);
                                    $sql .= " WHERE r.recruit_title LIKE '%$search_term%' 
                                            OR it.timeslot_date LIKE '%$search_term%'";
                                }
                            }

                            // Order by timeslot date
                            $sql .= " ORDER BY it.timeslot_date ASC, it.start_time ASC";

                            // Execute the query
                            $result = $conn->query($sql);

                            // Display the results
                            if ($result === false) {
                                echo "<tr><td colspan='8' class='empty-state'>Error executing query: " . $conn->error . "</td></tr>";
                            } elseif ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    // Determine if timeslot is full
                                    $is_full = ($row['booked_count'] >= $row['max_participants']);
                                    $status_class = $is_full ? 'status-full' : 'status-available';
                                    $row_class = $is_full ? 'status-full-row' : '';
                                    
                                    $action_onclick = "if(confirm('Are you sure you want to delete this timeslot?')) location.href='delete_timeslot.php?timeslot_id=" . htmlspecialchars($row['timeslot_id']) . "'";
                                    
                                    // Format the date to a more readable format
                                    $formatted_date = date('D, M j, Y', strtotime($row['timeslot_date']));
                                    
                                    echo "<tr class='" . $row_class . "'>
                                            <td>" . htmlspecialchars($row['recruit_title']) . "</td>
                                            <td>" . htmlspecialchars($formatted_date) . "</td>
                                            <td>" . htmlspecialchars($row['start_time']) . "</td>
                                            <td>" . htmlspecialchars($row['end_time']) . "</td>
                                            <td>" . htmlspecialchars($row['max_participants']) . "</td>
                                            <td>" . htmlspecialchars($row['booked_count']) . "</td>
                                            <td><span class='" . $status_class . "'>" . htmlspecialchars($row['status']) . "</span></td>
                                            <td>
                                                <button class='btn-delete' onclick=\"" . $action_onclick . "\">
                                                    <i class='fas fa-trash-alt'></i> Delete
                                                </button>
                                            </td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' class='empty-state'>No interview timeslots found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($active_tab == 'applicants'): ?>
                <!-- Applicants Tab Content -->
                <div class="tab-content">
                    <div class="interview-table-header">
                        <h3>Interview Applicants</h3>
                    </div>
                    
                    <!-- Search Bar -->
                    <div class="search-bar-container">
                        <form method="POST" action="interview.php?tab=applicants<?php echo $selected_recruitment ? '&recruitment=' . $selected_recruitment : ''; ?>" class="search-bar">
                            <input type="text" name="search" placeholder="Search applicant name or email..." value="<?php echo htmlspecialchars($search_term); ?>">
                            <button type="submit"><i class="fas fa-search"></i> Search</button>
                        </form>
                    </div>
                    
                    <!-- Applicants Table -->
                    <table class="interview-applicants-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Recruitment Event</th>
                                <th>Interview Date</th>
                                <th>Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Prepare the query using a prepared statement
                            $sql = "SELECT a.application_id, CONCAT(s.stu_first_name, ' ', s.stu_last_name) AS stu_name, s.stu_email,
                                        a.dept_choice_1, a.dept_choice_2, a.join_reason, a.past_experience, a.head_role, a.ig_link,
                                        a.recommendations, a.application_status, it.timeslot_date, it.start_time, it.end_time,
                                        r.recruit_title
                                    FROM recruitment_applications a
                                    LEFT JOIN students s ON a.stu_matric = s.stu_matric
                                    LEFT JOIN recruitment r ON a.recruit_id = r.recruit_id
                                    LEFT JOIN interview_times it ON a.timeslot_id = it.timeslot_id";

                            // Initialize conditions array
                            $conditions = [];
                            $params = [];
                            $types = "";

                            // Add recruitment filter if selected
                            if ($selected_recruitment > 0) {
                                $conditions[] = "a.recruit_id = ?";
                                $params[] = $selected_recruitment;
                                $types .= "i"; // Integer type
                            }

                            // Add search term as additional filter
                            if (!empty($search_term)) {
                                $search_term = $conn->real_escape_string($search_term);
                                $conditions[] = "(CONCAT(s.stu_first_name, ' ', s.stu_last_name) LIKE ? OR s.stu_email LIKE ?)";
                                $params[] = "%$search_term%";
                                $params[] = "%$search_term%";
                                $types .= "ss"; // String types
                            }

                            if (!empty($conditions)) {
                                $sql .= " WHERE " . implode(" AND ", $conditions);
                            }

                            // Order by interview date and time
                            $sql .= " ORDER BY it.timeslot_date ASC, it.start_time ASC";

                            // Prepare and execute the statement
                            $stmt = $conn->prepare($sql);
                            if ($stmt === false) {
                                echo "<tr><td colspan='6' class='empty-state'>Error preparing query: " . $conn->error . "</td></tr>";
                            } else {
                                if (!empty($params)) {
                                    $stmt->bind_param($types, ...$params);
                                }
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        // Format the date if available
                                        $formatted_date = $row['timeslot_date'] ? date('D, M j, Y', strtotime($row['timeslot_date'])) : 'Not Scheduled';
                                        
                                        echo "<tr>
                                                <td>" . htmlspecialchars($row['stu_name']) . "</td>
                                                <td>" . htmlspecialchars($row['stu_email']) . "</td>
                                                <td>" . htmlspecialchars($row['recruit_title']) . "</td>
                                                <td>" . htmlspecialchars($formatted_date) . "</td>
                                                <td>" . ($row['start_time'] ? htmlspecialchars($row['start_time']) . " - " . htmlspecialchars($row['end_time']) : 'N/A') . "</td>
                                                <td>
                                                    <button class='btn-view' onclick=\"location.href='view_applications.php?id=" . htmlspecialchars($row['application_id']) . "'\">
                                                        <i class='fas fa-eye'></i> View
                                                    </button>
                                                </td>
                                            </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='empty-state'>No applicants found with booked interviews.</td></tr>";
                                }
                                $stmt->close();
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Toggle account menu
        const account = document.querySelector('.account');
        const menu = document.querySelector('.account-menu');
        account.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!account.contains(event.target)) {
                menu.style.display = 'none';
            }
        });
    </script>
</body>
</html>