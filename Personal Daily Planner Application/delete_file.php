<?php
include 'db_connection.php';  // Ensure database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $filePath = $data['files'];
    $taskId = $data['task_id'];

    // Check if the file exists
    if (file_exists($filePath)) {
        unlink($filePath); // Delete file from server
    } else {
        echo json_encode(["success" => false, "message" => "File not found"]);
        exit;
    }

    // Remove file from database
    $stmt = $conn->prepare("SELECT files FROM plans WHERE id=?");
    $stmt->bind_param("i", $taskId);
    $stmt->execute();
    $stmt->bind_result($existingFiles);
    $stmt->fetch();
    $stmt->close();

    $fileArray = explode(',', $existingFiles);
    $newFileArray = array_filter($fileArray, function($file) use ($filePath) {
        return $file !== $filePath; // Remove deleted file
    });
    $newFileString = implode(',', $newFileArray);

    // Update the task
    $stmt = $conn->prepare("UPDATE plans SET files=? WHERE id=?");
    $stmt->bind_param("si", $newFileString, $taskId);
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Database update failed"]);
    }
}
?>
