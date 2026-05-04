<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['id']) || !isset($_GET['task_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized or missing task ID"]);
    exit();
}

$user_id = $_SESSION['id'];
$task_id = $_GET['task_id'];

$stmt = $conn->prepare("SELECT * FROM plans WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        "success" => true,
        "date" => $row["date"],
        "time" => $row["time"],
        "category" => $row["category"],
        "person_involved" => $row["person_involved"],
        "description" => $row["description"]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Task not found"]);
}
$stmt->close();
?>
