<?php
session_start();
require_once 'db_connection.php';

// Validate form input
if (!isset($_POST['username'], $_POST['password'], $_POST['email'])) {
    header("Location: UserRegister.php?error=All fields are required");
    exit();
}

$username = trim($_POST['username']);
$password = trim($_POST['password']);  // ⚠ Store as plain text (not secure)
$email = trim($_POST['email']);

// Check for empty fields
if (empty($username) || empty($password) || empty($email)) {
    header("Location: UserRegister.php?error=Please fill in all fields");
    exit();
}

// Check if username already exists
$s = "SELECT * FROM userregister_db WHERE user_name = ?";
$stmt = $conn->prepare($s);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$num = $result->num_rows;

if ($num > 0) {
    header("Location: UserRegister.php?error=Username already taken");
    exit();
} else {
    // Insert new user into database (storing password as plain text)
    $reg = "INSERT INTO userregister_db (user_name, password, email) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($reg);
    $stmt->bind_param("sss", $username, $password, $email);
    $stmt->execute();

    // Get new user ID
    $user_id = $stmt->insert_id;

    // Check if user was a guest and transfer tasks
    if (isset($_SESSION['guest_id'])) {
        $guest_id = $_SESSION['guest_id'];

        // Transfer guest tasks to registered user
        $update_tasks = "UPDATE plans SET user_id = ?, guest_id = NULL WHERE guest_id = ?";
        $stmt = $conn->prepare($update_tasks);
        $stmt->bind_param("is", $user_id, $guest_id);
        $stmt->execute();

        // Delete guest session from guest_sessions table
        $delete_guest_session = "DELETE FROM guest_sessions WHERE guest_id = ?";
        $stmt = $conn->prepare($delete_guest_session);
        $stmt->bind_param("s", $guest_id);
        $stmt->execute();

        // Remove guest session data
        unset($_SESSION['guest_id']);
    }

    // Show registration success message and redirect
    echo "<script>
            alert('Registration successful! Redirecting to login...');
            setTimeout(function(){
                window.location.href = 'UserLogin.php';
            }, 3000);
          </script>";
}
?>