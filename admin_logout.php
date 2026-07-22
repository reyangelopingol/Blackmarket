<?php
session_start();
include "db.php";

// Log admin logout
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    mysqli_query($conn, "INSERT INTO activity_log (admin_id, action, details, ip_address) 
                         VALUES ('$admin_id', 'admin_logout', 'Admin logged out', '$ip')");
}

session_destroy();
header("Location: admin_login.php");
exit();
?>