<?php
session_start();
// Database connection
require_once 'db_connection.php';

// ✅ Redirect to login if manager is not logged in
if (!isset($_SESSION['id'])) {
    header("Location: ManagerLogin.php");
    exit();
}

$manager_id = $_SESSION['id']; // Store the logged-in manager ID for filtering tasks

// Set default timezone
date_default_timezone_set('UTC');

// Get current month and year
$view = isset($_GET['view']) ? $_GET['view'] : 'month';
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$day = isset($_GET['day']) ? intval($_GET['day']) : date('d');


// Get first day of the month and total days in the month
$firstDay = date('w', strtotime("$year-$month-01"));
$totalDays = date('t', strtotime("$year-$month-01"));

// ✅ Fetch all users for the manager to assign tasks
$userregister_db = [];
$stmt = $conn->prepare("SELECT id, user_name FROM userregister_db");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $users[$row['id']] = $row['user_name'];
}
$stmt->close();

// ✅ Fetch tasks for the selected user (or all users if no user is selected)
$selected_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$plans = [];

$query = "SELECT id, date, time, category, person_involved, description, files, user_id FROM plans";
if ($selected_user_id) {
    $query .= " WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $selected_user_id);
} else {
    $stmt = $conn->prepare($query);
}
$stmt->execute();
$result = $stmt->get_result();

// ✅ Fix: Ensure $plans[$date] is always an array
while ($row = $result->fetch_assoc()) {
    if (!isset($plans[$row['date']])) {
        $plans[$row['date']] = [];  // ✅ Initialize as an empty array
    }
    $plans[$row['date']][] = $row;  // ✅ Store multiple tasks for the same date
}
$stmt->close();

// ✅ Handle Adding or Editing a Task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_task'])) {
    require 'db_connection.php';

    $task_id = $_POST['task_id'] ?? null;
    $date = $_POST['date'] ?? null;
    $time = $_POST['time'] ?? null;
    $category = $_POST['category'] ?? null;
    $person_involved = $_POST['person_involved'] ?? null;
    $description = $_POST['description'] ?? null;
    $user_id = $_POST['user_id'] ?? null;

    if (!$date || !$time || !$category || !$person_involved || !$description || !$user_id) {
        die("Error: All fields are required.");
    }

    // File Upload Handling
    $uploadDir = 'uploads/';
    $filePaths = [];

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!empty($_FILES['files']['name'][0])) {
        foreach ($_FILES['files']['tmp_name'] as $index => $tmpName) {
            $fileName = basename($_FILES['files']['name'][$index]);
            $filePath = $uploadDir . time() . '_' . $fileName;
            if (move_uploaded_file($tmpName, $filePath)) {
                $filePaths[] = $filePath;
            }
        }
    }

    $files = implode(',', $filePaths);

    if ($task_id) {
        // ✅ Check if the task exists before updating
        $stmtCheck = $conn->prepare("SELECT id FROM plans WHERE id = ?");
        $stmtCheck->bind_param("i", $task_id);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();

        if ($result->num_rows > 0) {
            // ✅ Update existing task
            if (!empty($files)) {
                $stmt = $conn->prepare("UPDATE plans SET date=?, time=?, category=?, person_involved=?, description=?, files=?, user_id=? WHERE id=?");
                $stmt->bind_param("ssssssii", $date, $time, $category, $person_involved, $description, $files, $user_id, $task_id);
            } else {
                // ✅ If no new file uploaded, keep existing files
                $stmt = $conn->prepare("UPDATE plans SET date=?, time=?, category=?, person_involved=?, description=?, user_id=? WHERE id=?");
                $stmt->bind_param("ssssiii", $date, $time, $category, $person_involved, $description, $user_id, $task_id);
            }

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: ManagerIndex.php");
                exit();
            } else {
                die("Error updating task: " . $conn->error);
            }
        } else {
            die("Error: Task not found for updating.");
        }
    } else {
        // ✅ Insert new task
        $stmt = $conn->prepare("INSERT INTO plans (date, time, category, person_involved, description, files, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $date, $time, $category, $person_involved, $description, $files, $user_id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: ManagerIndex.php");
            exit();
        } else {
            die("Error saving task: " . $conn->error);
        }
    }
}

// ✅ Handle Task Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_task'])) {
    $task_id = $_POST['task_id'] ?? null;
    if (!$task_id) {
        die("Error: Task ID is missing.");
    }

    $stmt = $conn->prepare("DELETE FROM plans WHERE id = ?");
    $stmt->bind_param("i", $task_id);
    
    if ($stmt->execute()) {
        $stmt->close();
         // Get the current view and date parameters
         $view = $_GET['view'] ?? 'month';
         $month = $_GET['month'] ?? date('m');
         $year = $_GET['year'] ?? date('Y');
         $day = $_GET['day'] ?? date('d');
 
         // Redirect back to the current view
         if ($view === 'day') {
             header("Location: ManagerIndex.php?view=day&day=$day&month=$month&year=$year");
         } elseif ($view === 'week') {
             header("Location: ManagerIndex.php?view=week&day=$day&month=$month&year=$year");
         } else {
             header("Location: ManagerIndex.php?view=month&month=$month&year=$year");
         }
        exit();
    } else {
        die("Error deleting task: " . $conn->error);
    }
}


// Fetch plans for the current month
$stmt = $conn->prepare("SELECT id, date, time, category, person_involved, description, files FROM plans WHERE MONTH(date) = ? AND YEAR(date) = ?");
$stmt->bind_param("ii", $month, $year);
$stmt->execute();
$result = $stmt->get_result();

$plans = [];
while ($row = $result->fetch_assoc()) {
    $date = $row['date'];
    if (!isset($plans[$date])) {
        $plans[$date] = []; // Initialize as an array
    }
    $plans[$date][] = $row; // Add the task to the array for this date
}
$stmt->close();

// ✅ Define generateCalendar() function (BEFORE calling it)
function generateCalendar($view, $month, $year, $day, $plans) {
    if ($view === 'week') return generateWeekView($month, $year, $day, $plans);
    if ($view === 'day') return generateDayView($day, $month, $year, $plans);
    return generateMonthView($month, $year, $plans);
}

// ✅ Function to generate the user-specific calendar
function generateMonthView($month, $year, $plans) {
    $firstDay = date('w', strtotime("$year-$month-01"));
    $totalDays = date('t', strtotime("$year-$month-01"));

    $calendar = "<table class='calendar' id='calendar-table'><tr>";
    $calendar .= "<th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr><tr>";

    $dayCount = 0;
    for ($i = 0; $i < $firstDay; $i++) {
        $calendar .= "<td></td>";
        $dayCount++;
    }

    for ($day = 1; $day <= $totalDays; $day++) {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);

        // ✅ Clicking the cell opens "Add Task" modal
        $calendar .= "<td class='calendar-cell' data-date='$date' onclick='openAddTaskModal(\"$date\")' 
              style='cursor:pointer; position:relative; padding:5px;'>";

        // ✅ Clicking the date text navigates to "Day View"
        $calendar .= "<a href='?view=day&day=$day&month=$month&year=$year' class='date-link' 
                      onclick='event.stopPropagation();' 
                      style='font-weight: bold; text-decoration: none; color: black; display: block;'>$day</a>";

        // ✅ Check if there are tasks for this date
        if (isset($plans[$date]) && is_array($plans[$date])) {
            foreach ($plans[$date] as $plan) {
                // ✅ Ensure variables are extracted correctly
                $time = htmlspecialchars($plan['time'] ?? 'N/A');
                $category = htmlspecialchars($plan['category'] ?? 'N/A');
                $person = htmlspecialchars($plan['person_involved'] ?? 'N/A');
                $description = htmlspecialchars($plan['description'] ?? 'N/A');
                $files = htmlspecialchars($plan['files'] ?? '');

                // ✅ Display the task properly
                $calendar .= "<div class='plan' onclick='event.stopPropagation(); showTaskDetails(\"{$plan['id']}\", \"$date\", \"$time\", \"$category\", \"$person\", \"$description\", \"$files\")' 
                                style='border: 1px solid #ddd; padding: 5px; margin-top: 5px; border-radius: 5px; 
                                background-color: rgb(187, 203, 230); cursor: pointer;'>";
                $calendar .= "<strong>📌 Task:</strong> $category <br>";
                $calendar .= "<strong>📝 Description:</strong> " . (strlen($description) > 20 ? substr($description, 0, 20) . "..." : $description);
                $calendar .= "</div>";
            }
        }

        $calendar .= "</td>";

        if (++$dayCount % 7 == 0) {
            $calendar .= "</tr><tr>";
        }
    }

    while ($dayCount % 7 != 0) {
        $calendar .= "<td></td>";
        $dayCount++;
    }

    $calendar .= "</tr></table>";
    return $calendar;
}



function generateWeekView($month, $year, $day, $plans) {
    $startOfWeek = date('Y-m-d', strtotime("$year-$month-$day last Sunday"));
    $calendar = "<table class='calendar'><tr>";

    // Weekday headers
    for ($i = 0; $i < 7; $i++) {
        $calendar .= "<th>" . date('D', strtotime("$startOfWeek +$i days")) . "</th>";
    }
    $calendar .= "</tr><tr>";

    // Generate week view
    for ($i = 0; $i < 7; $i++) {
        $date = date('Y-m-d', strtotime("$startOfWeek +$i days"));

        // ✅ Clicking the cell opens "Add Task" modal
        $calendar .= "<td class='calendar-cell' data-date='$date' onclick='openAddTaskModal(\"$date\")' style='cursor:pointer; padding:5px;'>";

        // ✅ Clicking the date text navigates to "Day View"
        $calendar .= "<a href='?view=day&day=" . date('d', strtotime($date)) . "&month=$month&year=$year' class='date-link' 
                      onclick='event.stopPropagation();' style='font-weight: bold; text-decoration: none; color: black; display: block;'>" . date('d', strtotime($date)) . "</a>";

        // ✅ Show tasks in the same style as Month View
        if (isset($plans[$date]) && is_array($plans[$date])) {
            foreach ($plans[$date] as $plan) {
                $time = htmlspecialchars($plan['time'] ?? 'N/A');
                $category = htmlspecialchars($plan['category'] ?? 'N/A');
                $person = htmlspecialchars($plan['person_involved'] ?? 'N/A');
                $description = htmlspecialchars($plan['description'] ?? 'N/A');
                $files = htmlspecialchars($plan['files'] ?? '');

                $calendar .= "<div class='plan' onclick='event.stopPropagation(); showTaskDetails(\"$plan[id]\", \"$date\", \"$time\", \"$category\", \"$person\", \"$description\", \"$files\")' 
                                style='border: 1px solid #ddd; padding: 5px; margin-top: 5px;  border-radius: 5px; background-color: rgb(187, 203, 230); cursor: pointer;'>";
                $calendar .= "<strong>📌 Task:</strong> $category <br>";
                $calendar .= "<strong>📝 Description:</strong> " . (strlen($description) > 20 ? substr($description, 0, 20) . "..." : $description);
                $calendar .= "</div>";
            }
        }

        $calendar .= "</td>";
    }

    $calendar .= "</tr></table>";
    return $calendar;
}

function generateDayView($day, $month, $year, $plans) {
    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
    $calendar = "<table class='calendar'><tr><th>Tasks on $date</th></tr><tr><td>";

    // Add Task Button
    $calendar .= "<button class='add-task-button' onclick='openAddTaskModal(\"$date\")'>➕ Add Task</button><br><br>";

    if (isset($plans[$date])) {
        foreach ($plans[$date] as $plan) {
            $calendar .= "<div class='plan'>
                            <strong>Time:</strong> " . htmlspecialchars($plan['time'] ?? 'N/A') . "<br>
                            <strong>Category:</strong> " . htmlspecialchars($plan['category'] ?? 'N/A') . "<br>
                            <strong>Person Involved:</strong> " . htmlspecialchars($plan['person_involved'] ?? 'N/A') . "<br>
                            <strong>Description:</strong> " . htmlspecialchars($plan['description'] ?? 'N/A') . "<br>
                            <strong>Files:</strong> " . (isset($plan['files']) ? generateFileLinks($plan['files']) : 'No files uploaded') . "<br>
                            <div class='task-actions'>
                                <button class='edit-button' onclick='editTaskFromDayView(\"{$plan['id']}\", \"{$plan['date']}\", \"{$plan['time']}\", \"{$plan['category']}\", \"{$plan['person_involved']}\", \"{$plan['description']}\")'>✏ Edit</button>
                                <form method='POST' action='?view=day&day=$day&month=$month&year=$year' onsubmit='return confirm(\"Are you sure you want to delete this task?\");' style='display: inline;'>
                                    <input type='hidden' name='task_id' value='{$plan['id']}'>
                                    <button type='submit' name='delete_task' class='delete-button'>🗑 Delete</button>
                                </form>
                            </div>
                            <hr>
                        </div>";
        }
    } else {
        $calendar .= "No tasks for this day.";
    }

    $calendar .= "</td></tr></table>";
    return $calendar;
}

// Helper function to generate file links
function generateFileLinks($files) {
    if (empty($files)) {
        return 'No files uploaded';
    }
    $fileArray = explode(',', $files);
    $links = [];
    foreach ($fileArray as $file) {
        $fileName = basename($file);
        $links[] = "<a href='$file' target='_blank'>📂 $fileName</a>";
    }
    return implode('<br>', $links);
}

// ✅ Function to generate navigation links
function getNavigationLinks($view, $month, $year, $day) {
    if ($view === 'month') {
        $prevMonth = ($month == 1) ? 12 : $month - 1;
        $prevYear = ($month == 1) ? $year - 1 : $year;
        $nextMonth = ($month == 12) ? 1 : $month + 1;
        $nextYear = ($month == 12) ? $year + 1 : $year;
        return "<a href='?view=month&month=$prevMonth&year=$prevYear'>◀</a> " . date('F Y', strtotime("$year-$month-01")) . " <a href='?view=month&month=$nextMonth&year=$nextYear'>▶</a>";
    }
    if ($view === 'week') {
        $currentDate = strtotime("$year-$month-$day");
        $prevWeek = strtotime('-7 days', $currentDate);
        $nextWeek = strtotime('+7 days', $currentDate);
        return "<a href='?view=week&day=" . date('d', $prevWeek) . "&month=" . date('m', $prevWeek) . "&year=" . date('Y', $prevWeek) . "'>◀</a> " . date('M d, Y', $currentDate) . " <a href='?view=week&day=" . date('d', $nextWeek) . "&month=" . date('m', $nextWeek) . "&year=" . date('Y', $nextWeek) . "'>▶</a>";
    }
    if ($view === 'day') {
        $currentDate = strtotime("$year-$month-$day");
        $prevDay = strtotime('-1 day', $currentDate);
        $nextDay = strtotime('+1 day', $currentDate);
        return "<a href='?view=day&day=" . date('d', $prevDay) . "&month=" . date('m', $prevDay) . "&year=" . date('Y', $prevDay) . "'>◀</a> " . date('M d, Y', $currentDate) . " <a href='?view=day&day=" . date('d', $nextDay) . "&month=" . date('m', $nextDay) . "&year=" . date('Y', $nextDay) . "'>▶</a>";
    }
    return "";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planner Manager - Calendar</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            }
            .modal-content {
                padding: 10px;
            }
            .modal-buttons {
                display: flex;
                justify-content: space-between;
                margin-top: 20px;
                gap: 10px; /* Adds spacing between buttons */
            }

            .modal-button {
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 14px;
                transition: background-color 0.3s ease, transform 0.2s ease;
                color: white; /* Text color for all buttons */
                flex: 1; /* Ensures buttons take equal width */
                text-align: center;
            }
            .calendar {
                width: 100%;
                border-collapse: collapse;
            }
            .calendar td {
                width: 14%;
                height: 120px;
                text-align: center;
                vertical-align: top;
                border: 1px solid #ccc;
                padding: 5px;
                position: relative;
            }
            .calendar td:hover {
                background: #f0f0f0;
                cursor: pointer;
            }
            .calendar-cell {
                cursor: pointer;
                position: relative;
            }

            .plan {
                cursor: pointer;
                background: rgb(188, 219, 243);
                color: black;
                padding: 5px;
                margin-top: 5px;
                border-radius: 5px;
            }

            .task-details {
                font-size: 11px;
                background: rgb(161, 182, 228);
                border-radius: 5px;
                padding: 5px;
                margin-top: 5px;
                display: none;
            }

            .edit-button {
                background-color:rgb(26, 90, 216); /* Orange */
            }

            .edit-button:hover {
                background-color:rgb(23, 41, 130); /* Darker orange on hover */
                transform: scale(1.05); /* Slightly enlarge on hover */
            }

            .delete-button {
                background-color:rgb(236, 57, 44); /* Tomato red */
            }

            .delete-button:hover {
                background-color: #e55337; /* Darker red on hover */
                transform: scale(1.05); /* Slightly enlarge on hover */
            }

            .close-button {
                padding: 8px 16px; /* Adjust padding to make the button smaller */
                background-color:rgb(51, 153, 53); /* Gray */
            }

            .close-button:hover {
                background-color: #5a6268; /* Darker gray on hover */
                transform: scale(1.05); /* Slightly enlarge on hover */
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
</head>
<body>


    <div class="container">
        <h1>📅 Manager Planner</h1>
        <div class="view-options">
            <button onclick="changeView('month')">📅 Month</button>
            <button onclick="changeView('week')">🗓️ Week</button>
            <button onclick="changeView('day')">🗓️ Day</button>
        </div>


        <div class="calendar-box">
        <h2>
        <h2><?php echo getNavigationLinks($view, $month, $year, $day); ?></h2>
            <?php echo generateCalendar($view, $month, $year, $day, $plans); ?>
        </div>

        <a href="ManagerLogout.php" class="logout-button">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>


    </div>

    <div id="taskDetailsModal" class="modal">
    <div class="modal-content">
        <h2>Task Details</h2>
        <p><strong>Date:</strong> <span id="taskDate"></span></p>
        <p><strong>Time:</strong> <span id="taskTime"></span></p>
        <p><strong>Category:</strong> <span id="taskCategory"></span></p>
        <p><strong>Person Involved:</strong> <span id="taskPerson"></span></p>
        <p><strong>Description:</strong> <span id="taskDescription"></span></p>
        <p><strong>Files:</strong></p>
        <div id="taskFiles"></div> <!-- Ensure this exists -->
        <input type="hidden" id="taskUserId">
        <div class="modal-buttons">
            <form method="POST" action="" style="display: inline;">
                <input type="hidden" name="task_id" id="editTaskId">
                <button type="button" class="modal-button edit-button" onclick="editTask()">✏ Edit</button>
            </form>
            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this task?');" style="display: inline;">
                <input type="hidden" name="task_id" id="deleteTaskId">
                <button type="submit" name="delete_task" class="modal-button delete-button">🗑 Delete</button>
            </form>
            <button class="modal-button close-button" onclick="closeTaskDetailsModal()">Close</button>
        </div>
    </div>
</div>


<div id="planModal" class="modal">
    <div class="modal-content">
        <h2>Add/Edit Task</h2>
        <form method="POST" action="" enctype="multipart/form-data" id="taskForm">
            <input type="hidden" id="task_id" name="task_id"> <!-- Hidden input for task ID -->

            <label>Date:</label>
            <input type="date" id="planDate" name="date" required><br>

            <label>Time:</label>
            <input type="time" id="time" name="time"><br>

            <label>Category:</label>
            <select id="category" name="category" required>
                <option value="Assignment">Assignment</option>
                <option value="Homework">Homework</option>
                <option value="Lecture">Lecture Slide</option>
                <option value="Tutorial">Tutorial</option>
                <option value="Additional Information">Additional Information</option>
            </select><br>

            <label>Person Involved:</label>
            <select id="user_id" name="user_id" required onchange="updatePersonInvolved()">
                <?php foreach ($users as $id => $name): ?>
                    <option value="<?php echo htmlspecialchars($id); ?>" data-username="<?php echo htmlspecialchars($name); ?>">
                        <?php echo htmlspecialchars($name); ?>
                    </option>
                <?php endforeach; ?>
            </select><br>

            <!-- Hidden input to store username -->
            <input type="hidden" id="person_involved" name="person_involved">

            <label>Description:</label>
            <textarea id="description" name="description" required></textarea><br>

            <label>Upload File(s):</label>
            <input type="file" name="files[]" multiple> <!-- Allow users to upload new files -->

            <button type="submit" name="save_task" id="saveTaskButton">Save Task</button>
            <button type="button" onclick="closeModal('planModal')">Cancel</button>
        </form>
    </div>
</div>



    <script>

       function changeView(view) {
            window.location.href = `?view=${view}&month=<?php echo $month; ?>&year=<?php echo $year; ?>&day=<?php echo $day; ?>`;
        }


        function openAddTaskModal(date) {
            var modal = document.getElementById("planModal");
            var planDateInput = document.getElementById("planDate");

            if (modal && planDateInput) {
                planDateInput.value = date; // Set the selected date in the modal
                modal.style.display = "block"; // Show the modal
            }
                }
        window.onclick = function(event) {
            var planModal = document.getElementById("planModal");
            var taskModal = document.getElementById("taskDetailsModal");
            
            if (event.target === planModal) {
                planModal.style.display = "none";
            }
            if (event.target === taskModal) {
                taskModal.style.display = "none";
            }
        }

        function showTaskDetails(id, date, time, category, person, description, files) {
            document.getElementById('taskDate').innerText = date;
            document.getElementById('taskTime').innerText = time && time.trim() !== "" ? time : "N/A";
            document.getElementById('taskCategory').innerText = category && category.trim() !== "" ? category : "N/A";
            document.getElementById('taskPerson').innerText = person && person.trim() !== "" ? person : "N/A";
            document.getElementById('taskDescription').innerText = description && description.trim() !== "" ? description : "N/A";

            // ✅ Display files correctly
            const fileList = document.getElementById('taskFiles');
            fileList.innerHTML = '';

            if (files && files.trim() !== '') {
                let fileArray = files.split(',');
                fileArray.forEach(file => {
                    let fileLink = document.createElement('a');
                    fileLink.href = file;
                    fileLink.target = '_blank';  // Open in a new tab
                    fileLink.innerText = "📂 " + file.split('/').pop(); // Show filename with an icon
                    fileLink.style.display = "block"; // Make each link appear on a new line
                    fileList.appendChild(fileLink);

                    
                });
            } else {
                fileList.innerText = "No files uploaded.";
            }
            document.getElementById("editTaskId").value = id;
            document.getElementById("deleteTaskId").value = id;

            document.getElementById('taskDetailsModal').style.display = 'block';
        }

        function closeModal(modalId) {
    var modal = document.getElementById(modalId);
    if (modal) {
        // Find the form inside the modal and reset it
        var form = modal.querySelector('form');
        if (form) {
            form.reset(); // Clears all input fields
        }

        // If there are custom fields that need to be cleared manually, do it here
        var fileInput = modal.querySelector('input[type="file"]');
        if (fileInput) {
            fileInput.value = ''; // Clear file input manually
        }
        
        // Close the modal
        modal.style.display = "none";
    }
}

function closeTaskDetailsModal() {
    document.getElementById('taskDetailsModal').style.display = 'none';
}

function closePlanModal() {
    document.getElementById('planModal').style.display = 'none';
}

function editTask() {
    // Populate fields with existing task data
    document.getElementById("task_id").value = document.getElementById("editTaskId").value;
    document.getElementById("planDate").value = document.getElementById("taskDate").textContent;
    document.getElementById("time").value = document.getElementById("taskTime").textContent.trim() !== "N/A" ? document.getElementById("taskTime").textContent : "";

    // Set the correct category in the dropdown
    var categoryDropdown = document.getElementById("category");
    var taskCategory = document.getElementById("taskCategory").textContent;
    for (var i = 0; i < categoryDropdown.options.length; i++) {
        if (categoryDropdown.options[i].text === taskCategory) {
            categoryDropdown.selectedIndex = i;
            break;
        }
    }

    // Set the correct user ID and person involved in dropdown
    var userDropdown = document.getElementById("user_id");
    var personInvolved = document.getElementById("taskPerson").textContent;
    for (var i = 0; i < userDropdown.options.length; i++) {
        if (userDropdown.options[i].text === personInvolved) {
            userDropdown.selectedIndex = i;
            break;
        }
    }

    // Ensure person_involved hidden field is updated correctly
    updatePersonInvolved();

    // Set the task description
    document.getElementById("description").value = document.getElementById("taskDescription").textContent;

    // Open the edit modal
    document.getElementById("planModal").style.display = "block";
}

document.getElementById('taskForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent default form submission

    var formData = new FormData(this);
    var taskId = document.getElementById("task_id").value; // Get task ID

    // Determine the URL based on whether it's an edit or add operation
    var url = taskId ? 'update_task.php' : 'save_task.php';

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Task saved successfully!');
            window.location.reload(); // Reload to reflect changes
        } else {
            alert('Error saving task: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
    
});
    function editTaskFromDayView(taskId, date, time, category, person, description) {
        // Populate the edit modal with task details
        document.getElementById("task_id").value = taskId;
        document.getElementById("planDate").value = date;
        document.getElementById("time").value = time;
        document.getElementById("category").value = category;
        document.getElementById("person_involved").value = person;
        document.getElementById("description").value = description;

        // Open the edit modal
        document.getElementById("planModal").style.display = "block";
    }

       
        function updatePersonInvolved() {
        var select = document.getElementById("user_id");
        var selectedOption = select.options[select.selectedIndex];

        // Ensure the selected username is correctly stored
        document.getElementById("person_involved").value = selectedOption.getAttribute("data-username");
    }

    
    </script>


</body>
</html>

