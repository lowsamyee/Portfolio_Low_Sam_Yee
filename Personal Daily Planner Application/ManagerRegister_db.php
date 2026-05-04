<?php
session_start();
require_once 'db_connection.php';

if (!isset($_POST['username'], $_POST['password'], $_POST['email'])) {
    header("Location: ManagerRegister.php?error=All fields are required");
    exit();
}

$username = trim($_POST['username']);
$password = trim($_POST['password']);
$email = trim($_POST['email']);

// Check for empty fields
if (empty($username) || empty($password) || empty($email)) {
    header("Location: ManagerRegister.php?error=Please fill in all fields");
    exit();
}

// Check if username already exists in the correct table
$s = "SELECT * FROM managerregister_db WHERE user_name = ?";
$stmt = $conn->prepare($s);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$num = $result->num_rows;

if ($num > 0) {
    header("Location: ManagerRegister.php?error=Username already taken");
    exit();
} else {

    // Insert new user into the correct table
    $reg = "INSERT INTO managerregister_db (user_name, password, email) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($reg);
    $stmt->bind_param("sss", $username, $password, $email);

    if ($stmt->execute()) {
        echo "<script>
                alert('Registration successful! Redirecting to login...');
                setTimeout(function(){
                    window.location.href = 'ManagerLogin.php';
                }, 500);
              </script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
