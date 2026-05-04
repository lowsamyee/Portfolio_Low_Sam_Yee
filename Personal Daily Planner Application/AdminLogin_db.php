<?php 
session_start(); 
require_once 'db_connection.php';

if (isset($_POST['username']) && isset($_POST['password'])) {

	function validate($data){
       $data = trim($data);
	   $data = stripslashes($data);
	   $data = htmlspecialchars($data);
	   return $data;
	}

	$username = validate($_POST['username']);
	$pass = validate($_POST['password']);

	if (empty($username)) {
		header("Location: AdminLogin.php?error=User Name is required");
	    exit();
	}else if(empty($pass)){
        header("Location: AdminLogin.php?error=Password is required");
	    exit();
	}else{
		$sql = "SELECT * FROM adminregister_db WHERE user_name='$username' AND password='$pass'";

		$result = mysqli_query($conn, $sql);

		if (mysqli_num_rows($result) === 1) 
			$row = mysqli_fetch_assoc($result);
            if ($row['user_name'] === $username && $row['password'] === $pass) {
            	$_SESSION['user_name'] = $row['user_name'];
            	$_SESSION['email'] = $row['email'];
            	$_SESSION['id'] = $row['id'];
            	header("Location: AdminIndex.php");
		        exit();
            }else{
				header("Location: AdminLogin.php?error=Incorect User name or password");
		        exit();
			}
		}
	}
	
else{
	header("Location: AdminLogin.php");
	exit();
}

?> 