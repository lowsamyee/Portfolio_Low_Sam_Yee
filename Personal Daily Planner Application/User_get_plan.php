<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

$user_id = $_SESSION['id'];
$date = $_GET['date'] ?? '';

if (!$date) {
    echo json_encode(["error" => "Date not provided"]);
    exit();
}

$stmt = $conn->prepare("SELECT time, category, person_involved, description, files FROM plans WHERE date = ? AND user_id = ?");
$stmt->bind_param("si", $date, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->fetch_assoc();

if ($task) {
    echo json_encode($task);
} else {
    echo json_encode([]);
}

$stmt->close();
?>