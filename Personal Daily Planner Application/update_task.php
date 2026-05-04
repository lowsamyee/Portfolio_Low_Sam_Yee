<?php
session_start();
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $category = $_POST['category'];
    $person_involved = $_POST['person_involved'];
    $description = $_POST['description'];
    $user_id = $_POST['user_id'];

    // File upload handling
    $uploadDir = 'uploads/';
    $filePaths = [];
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

    if (!empty($files)) {
        $stmt = $conn->prepare("UPDATE plans SET date=?, time=?, category=?, person_involved=?, description=?, files=?, user_id=? WHERE id=?");
        $stmt->bind_param("ssssssii", $date, $time, $category, $person_involved, $description, $files, $user_id, $task_id);
    } else {
        $stmt = $conn->prepare("UPDATE plans SET date=?, time=?, category=?, person_involved=?, description=?, user_id=? WHERE id=?");
        $stmt->bind_param("sssssii", $date, $time, $category, $person_involved, $description, $user_id, $task_id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    $stmt->close();
}
?>