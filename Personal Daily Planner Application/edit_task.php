<?php
include 'db_connection.php';  // Ensure database connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $task_id = $_POST['task_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $category = $_POST['category'];
    $person = $_POST['person'];
    $description = $_POST['description'];
    $files = $_POST['files'];

    $stmt = $pdo->prepare("UPDATE tasks SET date=?, time=?, category=?, person_involved=?, description=?, files=? WHERE id=?");
    if ($stmt->execute([$date, $time, $category, $person, $description, $files, $task_id])) {
        echo "<script>alert('Task updated successfully!'); window.location.href='calendar.php';</script>";
    } else {
        echo "<script>alert('Error updating task!');</script>";
    }
}
?>