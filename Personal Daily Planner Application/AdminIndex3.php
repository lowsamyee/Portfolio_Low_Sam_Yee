<?php
session_start(); // Start session
require_once 'db_connection.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: AdminLogin.php"); // Redirect to login if not authenticated
    exit();
}

// Set logged-in username safely
$logged_in_user = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Unknown User';

// ADD USER
if (isset($_POST['add_user'])) {
    $user_name = $_POST['user_name'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO userregister_db (user_name, password, email) VALUES ('$user_name', '$hashed_password', '$email')";
    $conn->query($sql);
}

// DELETE USER
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM userregister_db WHERE id=$id");
}

// EDIT USER
if (isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $user_name = $_POST['user_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // If a new password is provided, store it as plain text (Not Recommended)
    if (!empty($password)) {
        $conn->query("UPDATE userregister_db SET user_name='$user_name', password='$password', email='$email' WHERE id=$id");
    } else {
        $conn->query("UPDATE userregister_db SET user_name='$user_name', email='$email' WHERE id=$id");
    }
}

// FETCH USERS
$result = $conn->query("SELECT * FROM userregister_db");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f4f4f4;
            text-align: center;
        }

        .header {
            background: #333;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            margin-left: 20px;
        }

        .header .nav-links {
            margin-right: 20px;
        }

        .header a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            font-weight: bold;
            padding: 10px 15px;
            background: red;
            border-radius: 5px;
        }

        .header a:hover {
            background: darkred;
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        th {
            background: #440870;
            color: white;
        }

        .add-form {
            margin-top: 20px;
            padding: 20px;
            background: white;
            width: 50%;
            margin: auto;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .add-form input {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .add-form button {
            background: #440870;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .add-form button:hover {
            background: #290445;
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <h2>Admin Panel</h2>
    <div class="user-info">Logged in as: <?= htmlspecialchars($logged_in_user) ?></div>
    <div class="nav-links">
        <a href="AdminIndex.php">Admin List</a>
        <a href="AdminIndex2.php">Planer Manager List</a>
        <a href="AdminIndex3.php">User List</a>
        <a href="AdminLogin.php">Logout</a>
    </div>
</div>

<h2>Admin Panel - User List</h2>

<!-- User Table -->
<table>
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Password (Hidden)</th>
        <th>Email</th>
        <th>Created At</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['user_name'] ?></td>
            <td>******</td>
            <td><?= $row['email'] ?></td>
            <td><?= $row['created_at'] ?></td>
            <td>
                <a href="AdminIndex3.php?edit=<?= $row['id'] ?>">Edit</a> |
                <a href="AdminIndex3.php?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<!-- Add User Form (Hidden when Editing) -->
<?php if (!isset($_GET['edit'])): ?>
    <div class="add-form">
        <h3>Add New User</h3>
        <form method="POST">
            <input type="text" name="user_name" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="email" name="email" placeholder="Email" required>
            <button type="submit" name="add_user">Add User</button>
        </form>
    </div>
<?php endif; ?>

<!-- Edit User Form (Shown when Editing) -->
<?php
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $edit_result = $conn->query("SELECT * FROM userregister_db WHERE id=$id");
    $edit_data = $edit_result->fetch_assoc();
?>
    <div class="add-form">
        <h3>Edit User</h3>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
            <input type="text" name="user_name" value="<?= $edit_data['user_name'] ?>" required>
            <input type="password" name="password" placeholder="New Password">
            <input type="email" name="email" value="<?= $edit_data['email'] ?>" required>
            <button type="submit" name="edit_user">Update User</button>
            <!-- Cancel Button -->
            <a href="AdminIndex3.php" style="text-decoration: none;">
                <button type="button" style="background: red; color: white; padding: 10px; border: none; cursor: pointer; border-radius: 5px;">Cancel</button>
            </a>
        </form>
    </div>
<?php } ?>
</body>
</html>

<?php $conn->close(); ?>
