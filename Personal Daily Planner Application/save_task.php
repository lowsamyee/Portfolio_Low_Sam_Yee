<?php
session_start();
require_once 'db_connection.php';

$user_id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'] ?? null;
    $date = $_POST['date'] ?? null;
    $time = $_POST['time'] ?? null;
    $category = $_POST['category'] ?? null;
    $person_involved = $_POST['person_involved'] ?? null;
    $description = $_POST['description'] ?? null;

    if (!$date || !$category || !$description) {
        die(json_encode(['success' => false, 'message' => 'All fields are required.']));
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
        // Update existing task
        $stmt = $conn->prepare("UPDATE plans SET date=?, time=?, category=?, person_involved=?, description=?, files=? WHERE id=? AND user_id=?");
        $stmt->bind_param("ssssssii", $date, $time, $category, $person_involved, $description, $files, $task_id, $user_id);
    } else {
        // Add new task
        $stmt = $conn->prepare("INSERT INTO plans (date, time, category, person_involved, description, files, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $date, $time, $category, $person_involved, $description, $files, $user_id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving task: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();
}
?>