<?php
session_start();
session_destroy();
header("Location: ManagerLogin.php");
exit();
?>