<?php
session_start();

// Orders are now admin-only
// Redirect to admin login
header("Location: admin_login.php");
exit();
?>