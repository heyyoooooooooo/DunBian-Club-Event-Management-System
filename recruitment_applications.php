<?php
include('config.php');

// Start the session
session_start();

// Retrieve the matric number from the session
if (isset($_SESSION['stu_matric'])) {
    $stu_matric = $_SESSION['stu_matric']; // Assuming this is the matric number
} else {
    // If not logged in, redirect to the login page or show an error
    header("Location: login_form.php");
    exit();
}

// Retrieve the selected recruit_title from the previous page (GET request)
$recruit_title = isset($_GET['recruit_title']) ? $_GET['recruit_title'] : '';

// Fetch recruit_id based on recruit_title
$recruit_query = "SELECT recruit_id FROM recruitment WHERE recruit_title = '$recruit_title'";
$recruit_result = mysqli_query($conn, $recruit_query);

// Check if the recruit_title exists
if ($recruit_result && mysqli_num_rows($recruit_result) > 0) {
    $recruit_row = mysqli_fetch_assoc($recruit_result);
    $recruit_id = $recruit_row['recruit_id'];

    // Now fetch available timeslots where booked_count is less than max_participants
    $timeslot_query = "SELECT * FROM interview_times WHERE recruit_id = '$recruit_id' AND status = 'available' AND booked_count < max_participants";
    $timeslot_result = mysqli_query($conn, $timeslot_query);

    // Check if timeslots are retrieved
    if ($timeslot_result && mysqli_num_rows($timeslot_result) > 0) {
        $timeslots = mysqli_fetch_all($timeslot_result, MYSQLI_ASSOC);
    } else {
        // Handle case where no available timeslots are found
        $message = "No available timeslots. All timeslots are fully booked.";
        $success = false;
    }
} else {
    // Handle case where recruit_title is not found
    die("Error: Recruit title not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize form data to prevent SQL injection
    $timeslot_id = mysqli_real_escape_string($conn, $_POST['timeslot_id']); // Timeslot ID selected by the student
    $dept_choice_1 = mysqli_real_escape_string($conn, $_POST['dept_choice_1']);
    $dept_choice_2 = mysqli_real_escape_string($conn, $_POST['dept_choice_2']);
    $join_reason = mysqli_real_escape_string($conn, $_POST['join_reason']);
    $past_experience = mysqli_real_escape_string($conn, $_POST['past_experience']);
    $head_role = mysqli_real_escape_string($conn, $_POST['head_role']);
    $ig_link = mysqli_real_escape_string($conn, $_POST['ig_link']);
    $recommendations = mysqli_real_escape_string($conn, $_POST['recommendations']);

    // Set preferred_time to 0000-00-00 00:00:00 and application_status to 'approve'
    $preferred_time = '0000-00-00 00:00:00';
    $application_status = 'approve';

    // Insert application details into recruitment_applications table
    $sql = "INSERT INTO recruitment_applications (stu_matric, recruit_id, timeslot_id, preferred_time, dept_choice_1, dept_choice_2, join_reason, past_experience, head_role, ig_link, recommendations, application_status) 
            VALUES ('$stu_matric', '$recruit_id', '$timeslot_id', '$preferred_time', '$dept_choice_1', '$dept_choice_2', '$join_reason', '$past_experience', '$head_role', '$ig_link', '$recommendations', '$application_status')";

    if (mysqli_query($conn, $sql)) {
        // If application is successfully inserted, update booked_count for the selected timeslot
        $update_timeslot_query = "UPDATE interview_times SET booked_count = booked_count + 1 WHERE timeslot_id = '$timeslot_id'";
        mysqli_query($conn, $update_timeslot_query);

        // Check if the timeslot has reached max_participants
        $check_timeslot_query = "SELECT max_participants, booked_count FROM interview_times WHERE timeslot_id = '$timeslot_id'";
        $check_result = mysqli_query($conn, $check_timeslot_query);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $check_row = mysqli_fetch_assoc($check_result);
            if ($check_row['booked_count'] == $check_row['max_participants']) {
                // Update timeslot status to "full" and make it unavailable for new students
                $update_status_query = "UPDATE interview_times SET status = 'full' WHERE timeslot_id = '$timeslot_id'";
                mysqli_query($conn, $update_status_query);
            }
        }
        $message = "Application submitted successfully and timeslot updated!";
        $success = true;
    } else {
        $message = "Error: " . mysqli_error($conn);
        $success = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment Application</title>
    <link rel="stylesheet" href="applypage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-container">
        <div class="logo-section">
            <img src="img/logo.jpg" alt="Club Logo" class="logo">
            <span class="club-name">DunBian Club</span>
        </div>
            <nav class="nav-items">
                <a href="homepage.php">Home</a>
                <a href="aboutpage.php">About</a>
                <a href="eventlisting.php">Events</a>
                <a href="recruitmentopportunities.php">Recruitments</a>
                <a href="contactus.php">Contact Us</a>
            </nav>
            <?php if (isset($_SESSION['stu_matric'])): ?>
            <!-- Logged-in State -->
            <div class="user-controls">
                <div class="notifications">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="account">
                    <i class="fas fa-user"></i>
                    <div class="account-menu" style="display: none;">
                        <a href="studentprofile.php">Profile</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Logged-out State -->
            <div class="auth-controls">
                <a href="login_form.php" class="login-btn">Login Now</a>
            </div>
            <?php endif; ?>
        </div>      
    </header>

    <div class="container">
        <form class="application-form" method="POST">
            <h2 class="form-title">Recruitment Application</h2>


    <!-- Application form -->
    
        <div class="form-group">
            <label for="stu_matric">Student Matric Number</label>
            <input type="text" id="stu_matric" name="stu_matric" value="<?php echo $stu_matric; ?>" class="form-control" readonly required>
        </div>
        
        <div class="form-group">
            <label for="recruit_title">Recruitment Title</label>
            <input type="text" name="recruit_title" id="recruit_title" value="<?php echo htmlspecialchars($recruit_title); ?>" class="form-control" readonly />
        </div>

    <div class="form-group">
    <label for="timeslot_id">Select Timeslot:</label>
                <select name="timeslot_id" required class="form-control">
                    <option value="">Select a timeslot</option>
                    <?php foreach ($timeslots as $timeslot): ?>
                        <option value="<?php echo $timeslot['timeslot_id']; ?>">
                            <?php echo $timeslot['timeslot_date'] . ' - ' . $timeslot['start_time'] . ' to ' . $timeslot['end_time']; ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>


        <!-- Department choices -->
        <div class="form-group">
    <label for="dept_choice_1">Department Choice 1</label>
    <select id="dept_choice_1" name="dept_choice_1" class="form-control" required onchange="updateDeptChoice2()">
        <option value="">Select Department</option>
        <option value="Clerical Team">Clerical Team</option>
        <option value="Finance Team">Finance Team</option>
        <option value="General Affairs Team">General Affairs Team</option>
        <option value="Multimedia and Publicity Team">Multimedia and Publicity Team</option>
        <option value="Event Planning Team">Event Planning Team</option>
    </select>
</div>

<div class="form-group">
    <label for="dept_choice_2">Department Choice 2</label>
    <select id="dept_choice_2" name="dept_choice_2" class="form-control" required>
    <option value="">Select Department</option>
        <option value="Clerical Team">Clerical Team</option>
        <option value="Finance Team">Finance Team</option>
        <option value="General Affairs Team">General Affairs Team</option>
        <option value="Multimedia and Publicity Team">Multimedia and Publicity Team</option>
        <option value="Event Planning Team">Event Planning Team</option>
    </select>
</div>



        <!-- Reason for joining -->
        <div class="form-group">
            <label for="join_reason">Reason for Joining This Event?</label>
            <textarea id="join_reason" name="join_reason" class="form-control" required></textarea>
        </div>

        <!-- Past experience -->
        <div class="form-group">
            <label for="past_experience">Do You Have Any Past Experience?</label>
            <textarea id="past_experience" name="past_experience" class="form-control" required></textarea>
        </div>

        <!-- Head role selection -->
        <div class="form-group">
            <label for="head_role">Are You Interested To Be The Leader Of The Team?</label>
            <div class="radio-group">
                    <div class="radio-option">
                        <input type="radio" id="head_yes" name="head_role" value="yes">
                        <label for="head_yes">Yes</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="head_no" name="head_role" value="no">
                        <label for="head_no">No</label>
                    </div>
                </div>
            </div>


        <!-- Instagram Link -->
        <div class="form-group">
            <label for="ig_link">Instagram Link</label>
            <input type="url" id="ig_link" name="ig_link" class="form-control" required>
        </div>

        <!-- Recommendations -->
        <div class="form-group">
            <label for="recommendations">Any Recommendations?</label>
            <textarea id="recommendations" name="recommendations" class="form-control" required></textarea>
        </div>
        
        <button type="submit" class="submit-btn">Submit Application</button>
    </form>
</div>
 </div>

 <!-- Footer -->
 <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3 class="footer-title">About Us</h3>
                <p>We strive to promote the art of debate, create an environment where students can learn, practice and understand the different aspects of debating through different engaging activities.</p>
            </div>
            <div class="footer-section">
                <h3 class="footer-title">Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="homepage.php">Home</a></li>
                    <li><a href="aboutpage.php">About</a></li>
                    <li><a href="events.php">Events</a></li>
                    <li><a href="recruitmentopportunities.php">Recruitments</a></li>
                    <li><a href="contactus.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3 class="footer-title">Contact Us</h3>
                <ul class="footer-links">
                    <li>Email: dunbianclub@gmail.com</li>
                    <li>Phone: 0123456789</li>
                    <li>University Tun Hussein Onn Malaysia</li>
                    <li>Address: Persiaran Tun Dr. Ismail, 86400 Parit Raja, Johor Darul Ta'zim</li>
                </ul>
            </div>
        </div>
    </footer>

<script>
    // Preload timeslots into a JavaScript object
const timeslots = <?php echo json_encode($timeslots); ?>;

function updateTimeslots() {
    const recruitSelect = document.getElementById('recruit_title');
    const recruitId = recruitSelect.options[recruitSelect.selectedIndex].dataset.recruitId;

    const timeslotSelect = document.getElementById('timeslot_id');
    timeslotSelect.innerHTML = '<option value="">Select a timeslot</option>'; // Reset options

    // Filter available timeslots based on the selected recruitment title
    const filteredTimeslots = timeslots.filter(function (timeslot) {
        return timeslot.recruit_id === recruitId && timeslot.available_slots > 0;
    });

    // Populate timeslot options dynamically
    filteredTimeslots.forEach(function (timeslot) {
        const option = document.createElement('option');
        option.value = timeslot.timeslot_id;
        option.textContent = `${timeslot.start_time} - ${timeslot.end_time} (Available: ${timeslot.available_slots})`;
        timeslotSelect.appendChild(option);
    });
}
    window.onload = function() {
        <?php if (isset($success) && $success): ?>
            // Show success message
            alert("<?php echo addslashes($message); ?>");
            // Redirect to recruitment opportunities page after the message
            window.location.href = "studentprofile.php";
        <?php elseif (isset($message)): ?>
            // Show error message if something went wrong
            alert("<?php echo addslashes($message); ?>");
        <?php endif; ?>
    };

    function updateDeptChoice2() {
    const deptChoice1 = document.getElementById("dept_choice_1").value; // Get selected value of dept_choice_1
    const deptChoice2 = document.getElementById("dept_choice_2");

    // List of all department options
    const departments = [
        { value: "Clerical Team", text: "Clerical Team" },
        { value: "Finance Team", text: "Finance Team" },
        { value: "General Affairs Team", text: "General Affairs Team" },
        { value: "Multimedia and Publicity Team", text: "Multimedia and Publicity Team" },
        { value: "Event Planning Team", text: "Event Planning Team" }
    ];

    // Clear existing options in dept_choice_2
    deptChoice2.innerHTML = `<option value="">Select Department</option>`;

    // Repopulate dept_choice_2 with all options except the one selected in dept_choice_1
    departments.forEach(dept => {
        if (dept.value !== deptChoice1) {
            const option = document.createElement("option");
            option.value = dept.value;
            option.textContent = dept.text;
            deptChoice2.appendChild(option);
        }
    });
}

// Toggle account menu
document.querySelector('.account').addEventListener('click', function () {
            const menu = document.querySelector('.account-menu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });

        // Close menu when clicking outside
        document.addEventListener('click', function (event) {
            const account = document.querySelector('.account');
            const menu = document.querySelector('.account-menu');
            if (!account.contains(event.target)) {
                menu.style.display = 'none';
            }
        });




</script>
</body>
</html>