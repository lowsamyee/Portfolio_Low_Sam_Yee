<?php
session_start();
session_destroy();
header("Location: UserLogin.php");
exit();
?>