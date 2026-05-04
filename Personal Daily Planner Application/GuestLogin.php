<?php
session_start();
require_once 'db_connection.php';

// Check if the database connection is successful
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// If a guest session doesn't exist, create one
if (!isset($_SESSION['guest_id'])) {
    $_SESSION['guest_id'] = uniqid('guest_'); // Generate unique guest ID

    // Prepare the SQL query to insert guest session
    $sql = "INSERT INTO guest_sessions (guest_id, created_at) VALUES (?, NOW())";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("s", $_SESSION['guest_id']);
    $execute = $stmt->execute();

    if (!$execute) {
        die("Error executing query: " . $stmt->error);
    }

    $stmt->close();
}
// Debug: Check if session is being created
if (!isset($_SESSION['guest_id'])) {
    $_SESSION['guest_id'] = uniqid('guest_'); // Generate unique guest ID
    echo "Generated Guest ID: " . $_SESSION['guest_id']; // Debugging line
} else {
    echo "Existing Guest ID: " . $_SESSION['guest_id']; // Debugging line
}


// Redirect to GuestDashboard.php
header("Location: GuestDashboard.php");
exit();
?>