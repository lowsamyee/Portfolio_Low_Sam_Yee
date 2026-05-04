<?php
session_start();
require_once 'db_connection.php'; // Ensure connection is established

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($_SESSION['id']) || !isset($data['task_id'])) {
        echo json_encode(["success" => false, "message" => "Unauthorized or missing task ID"]);
        exit();
    }

    $user_id = $_SESSION['id'];
    $task_id = $data['task_id'];

    // Delete only if the task belongs to the logged-in user
    $stmt = $conn->prepare("DELETE FROM plans WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete task"]);
    }

    $stmt->close();
    exit();
}
echo json_encode(["success" => false, "message" => "Invalid request"]);
?>
