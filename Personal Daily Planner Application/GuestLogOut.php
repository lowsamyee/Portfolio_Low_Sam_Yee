<?php
session_start();
require_once 'db_connection.php';

if (isset($_SESSION['guest_id'])) {
    $guest_id = $_SESSION['guest_id'];

    // Delete guest tasks
    $sql = "DELETE FROM plans WHERE guest_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $guest_id);
    $stmt->execute();

    // Delete guest session
    $sql = "DELETE FROM guest_sessions WHERE guest_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $guest_id);
    $stmt->execute();

    unset($_SESSION['guest_id']);
}

// Destroy session and redirect
session_destroy();
header("Location: UserLogin.php");
exit();
?>