<?php

	session_start();
	require_once 'db_connection.php';
	$username = $_POST['username'];
	$password = $_POST['password'];
	$email = $_POST['email'];
	
	$s = "select * from adminregister_db where user_name = 'username'";
	$result = mysqli_query($conn, $s);
	$num = mysqli_num_rows($result);

	if($num == 1 ){
    echo "Username Already Taken";
} else {
    // Insert user data into the database
    $reg = "INSERT INTO adminregister_db(user_name, password, email) VALUES('$username', '$password', '$email')";
    mysqli_query($conn, $reg);
    
    // Show registration success message
    echo "Registration successful";

    // Use JavaScript to delay the redirection
    echo '<script>
            setTimeout(function(){
                window.location.href = "AdminLogin.php";
            }, 5000); // 5000 milliseconds (5 seconds)
          </script>';
}

	
	
?> 