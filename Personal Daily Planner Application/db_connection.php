<?php
// Database Connection File

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "newdb";
$port = 3306; // Change if needed

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>