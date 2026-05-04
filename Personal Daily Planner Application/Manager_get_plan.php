<?php
// Database connection
require_once 'db_connection.php';

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$date = isset($_GET['date']) ? $_GET['date'] : '';

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(["error" => "Invalid date format"]);
    exit;
}

$stmt = $conn->prepare("SELECT time, category, person_involved, description, files FROM plans WHERE date = ? AND user_id = ?");
$stmt->bind_param("si", $date, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $row['files'] = !empty($row['files']) ? explode(',', $row['files']) : [];
    echo json_encode($row);
} else {
    echo json_encode(["message" => "No plan found"]);
}

$stmt->close();
$conn->close();
?>
