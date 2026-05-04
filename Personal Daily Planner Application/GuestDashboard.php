<?php
session_start();
require_once 'db_connection.php';


// Check if the user is logged in as a guest
if (!isset($_SESSION['guest_id'])) {
    $_SESSION['guest_id'] = uniqid('guest_');

    // Store guest session in the database
    $sql = "INSERT INTO guest_sessions (guest_id, created_at) VALUES (?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_SESSION['guest_id']);
    $stmt->execute();
    header("Location: GuestLogin.php"); // Redirect to generate guest session
    exit();
}


$guest_id = $_SESSION['guest_id'];

// Set default month and year
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Ensure valid month and year
if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

// Format for queries
$formattedMonth = str_pad($month, 2, '0', STR_PAD_LEFT);
$firstDayOfMonth = "$year-$formattedMonth-01";
$totalDays = date('t', strtotime($firstDayOfMonth));
$firstDay = date('w', strtotime($firstDayOfMonth));

$guest_id = $_SESSION['guest_id'] ?? null;
$plans = [];

// Fetch tasks for the guest
if ($guest_id) {
    $stmt = $conn->prepare("SELECT date, category FROM plans WHERE guest_id = ? AND date LIKE ?");
    $monthQuery = "$year-$formattedMonth%";
    $stmt->bind_param("ss", $guest_id, $monthQuery);  // Corrected line
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $plans[$row['date']] = $row['category'];
    }
}

// Fetch current guest task count
$sql = "SELECT COUNT(*) as task_count FROM plans WHERE guest_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $guest_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$taskCount = $row['task_count'];

// Restrict task creation if limit (3) is reached
$canAddTask = ($taskCount < 3);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canAddTask) {
    $date = $_POST['date'];
    $time = $_POST['time'];
    $category = $_POST['category'];
    $person_involved = $_POST['person_involved'];
    $description = $_POST['description'];

    $sql = "INSERT INTO plans (date, time, category, person_involved, description, guest_id) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $date, $time, $category, $person_involved, $description, $guest_id);
    $stmt->execute();

    header("Location: GuestDashboard.php"); // Refresh page to update task count
    exit();
}

// Fetch tasks for the guest
$plans = [];

$stmt = $conn->prepare("SELECT date, time, category, person_involved, description FROM plans WHERE guest_id = ?");
$stmt->bind_param("s", $guest_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    // ✅ Ensure $plans[$row['date']] is always an array
    if (!isset($plans[$row['date']])) {
        $plans[$row['date']] = [];
    }
    $plans[$row['date']][] = $row;
}

$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Planner</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .plan { font-size: 12px; margin-top: 5px; cursor: pointer; }
        .modal { display: none; position: fixed; top: 50%; left: 50%;
                 transform: translate(-50%, -50%); background: white; padding: 20px;
                 box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); border-radius: 10px; width: 300px; text-align: center; }
        .modal-content { padding: 20px; }
        .close-button { background: red; color: white; padding: 10px; border: none; cursor: pointer; border-radius: 5px; margin-top: 10px; }
        .close-button:hover { background: darkred; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📅 Guest Planner</h1>

       <!-- View Selection Buttons -->
    <div class="view-buttons">
        <button onclick="showNotification('week')">Week View</button>
        <button onclick="showNotification('day')">Day View</button>
        <button onclick="switchToMonthView()">Month View</button>
    </div>

    <?php if (!$canAddTask) { ?>
        <div style="color: red; font-weight: bold;">
            You have reached the trial limit (3 tasks). <a href="UserRegister.php">Register</a> to save more!
        </div>
    <?php } ?>

        <div class="calendar-box">
            <h2> 
                <a href="?month=<?php echo ($month == 1) ? 12 : $month - 1; ?>&year=<?php echo ($month == 1) ? $year - 1 : $year; ?>">◀</a>
                <?php echo date('F Y', strtotime($firstDayOfMonth)); ?>
                <a href="?month=<?php echo ($month == 12) ? 1 : $month + 1; ?>&year=<?php echo ($month == 12) ? $year + 1 : $year; ?>">▶</a>
            </h2>
            <table class='calendar'>
                <tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr><tr>
                <?php
        for ($i = 0; $i < $firstDay; $i++) echo "<td></td>";

        for ($day = 1; $day <= $totalDays; $day++) {
                    $date = "$year-$formattedMonth-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                    echo "<td>";

                    // Display date and make it clickable
                    echo "<a href='#' onclick='openPlanModal(\"$date\")'>$day</a>";

                    if (isset($plans[$date])) {
                        foreach ($plans[$date] as $plan) {  // ✅ Loop through all tasks for the same date
                            echo "<div class='plan' onclick='showTaskDetails(\"$date\", \"{$plan['time']}\", \"{$plan['category']}\", \"{$plan['person_involved']}\", \"{$plan['description']}\")' 
                            style='border: 1px solid #ddd; padding: 5px; margin-top: 5px; border-radius: 5px; background-color: #f9f9f9;'>";
                            echo "<strong>📌 Task:</strong> " . htmlspecialchars($plan['category']) . "<br>";
                            echo "<strong>📝 Description:</strong> " . htmlspecialchars(substr($plan['description'], 0, 20)) . "..."; // Show only first 20 characters
                            echo "</div>";
                        }
                    }
                    
                    echo "</td>";
                    if (($day + $firstDay) % 7 == 0) echo "</tr><tr>";
                }

                while (($day + $firstDay) % 7 != 0) {
                    echo "<td></td>";
                    $day++;
                }
        ?>
        </tr>
            </table>
        </div>
        <a href="GuestLogout.php" class="logout-button">
            <i class="fas fa-sign-out-alt"></i> Exit
        </a>
    </div>

    <!-- Registration Notification Modal -->
<div id="notificationModal" class="modal">
    <div class="modal-content">
        <h2>🔒 Unlock More Features</h2>
        <p>The <span id="viewType"></span> view is available for registered users only.</p>
        <p>Sign up now to access full planning features!</p>
        <a href="UserRegister.php" class="register-button">Register Now</a>
        <button onclick="closeNotification()" class="close-button">Close</button>
    </div>
</div>

<!-- Task Limit Notification Modal -->
<div id="taskLimitModal" class="modal">
    <div class="modal-content">
        <h2>🔒 Task Limit Reached</h2>
        <p>You have reached the trial limit of 3 tasks.</p>
        <p><strong>Register now</strong> to unlock unlimited task planning!</p>
        <a href="UserRegister.php" class="register-button">Register Now</a>
        <button onclick="closeTaskLimitNotification()" class="close-button">Close</button>
    </div>
</div>

<!-- Task Details Modal -->
<div id="taskDetailsModal" class="modal">
        <h2>Task Details</h2>
        <p><strong>Date:</strong> <span id="taskDate"></span></p>
        <p><strong>Time:</strong> <span id="taskTime"></span></p>
        <p><strong>Category:</strong> <span id="taskCategory"></span></p>
        <p><strong>Person Involved:</strong> <span id="taskPerson"></span></p>
        <p><strong>Description:</strong> <span id="taskDescription"></span></p>
        <button onclick="closeTaskDetailsModal()" class="close-button">Close</button>
    </div>

<script>
   function openPlanModal(date) {
    <?php if (!$canAddTask) { ?>
        closePlanModal();
        showTaskLimitNotification();
        return;
    <?php } ?>

    document.getElementById('planDate').value = date;
    document.getElementById('planModal').style.display = 'block';
}

// Show task details modal
function showTaskDetails(date, time, category, person, description) {
    document.getElementById('taskDate').innerText = date;
    document.getElementById('taskTime').innerText = time && time.trim() !== "" ? time : "N/A";
    document.getElementById('taskCategory').innerText = category && category.trim() !== "" ? category : "N/A";
    document.getElementById('taskPerson').innerText = person && person.trim() !== "" ? person : "N/A";
    document.getElementById('taskDescription').innerText = description && description.trim() !== "" ? description : "N/A";
    document.getElementById('taskDetailsModal').style.display = 'block';
}

function closeTaskDetailsModal() {
    document.getElementById('taskDetailsModal').style.display = 'none';
}

    function closePlanModal() {
        document.getElementById('planModal').style.display = 'none';
    }

    function showTaskLimitNotification() {
        document.getElementById('taskLimitModal').style.display = 'block';
    }

    function closeTaskLimitNotification() {
        document.getElementById('taskLimitModal').style.display = 'none';
    }

    function showNotification(view) {
        document.getElementById('viewType').innerText = view.charAt(0).toUpperCase() + view.slice(1);
        document.getElementById('notificationModal').style.display = 'block';
    }

    function closeNotification() {
        document.getElementById('notificationModal').style.display = 'none';
    }

    function switchToMonthView() {
        alert("Month view is already displayed!");
    }
</script>

<style>
    .view-buttons {
        text-align: center;
        margin-bottom: 20px;
    }

    .view-buttons button {
        padding: 10px 15px;
        margin: 5px;
        border: none;
        background: #007bff;
        color: white;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
    }

    .view-buttons button:hover {
        background: #0056b3;
    }

    .modal {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        text-align: center;
        border-radius: 10px;
        width: 300px;
    }

    .modal-content {
        padding: 20px;
    }

    .register-button {
        background: green;
        color: white;
        padding: 10px 15px;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
    }

    .register-button:hover {
        background: darkgreen;
    }

    .close-button {
        background: red;
        color: white;
        padding: 10px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        margin-top: 10px;
    }

    .close-button:hover {
        background: darkred;
    }

    .logout-button {
    display: inline-block;
    padding: 10px 20px;
    background-color: #f44336; /* Red color for logout */
    color: white;
    text-decoration: none;
    border: none;
    border-radius: 5px;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.logout-button:hover {
    background-color: #d32f2f; /* Darker red on hover */
}
</style>

    <div id="planModal" class="modal">
    <div class="modal-content">
        <h2>Add Task</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" id="planDate" name="date">
            <label>Time:</label>
            <input type="time" id="time" name="time"><br>
            <label>Category:</label>
            <select id="category" name="category" required>
                <option value="Deadlines">Deadlines</option>
                <option value="Personal">Personal</option>
                <option value="Travel">Travel</option>
                <option value="Work">Work</option>
                <option value="Appointments">Appointments</option>
            </select><br>
            <label>Person Involved:</label>
            <input type="text" id="person_involved" name="person_involved"><br>
            <label>Description:</label>
            <textarea id="description" name="description" required></textarea><br>
            <button type="submit">Save Task</button>
            <button type="button" onclick="closePlanModal()">Cancel</button>
        </form>
    </div>


</body>
</html>