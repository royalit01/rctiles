<?php
session_start();  // Always start with session_start()

// Check if the user is logged in, using 'user_id' or any session variable set upon login
if (!isset($_SESSION['user_id'])) {
    // If the session variable is not set, redirect to login page
    header('Location: ../authorization/login.php');
    exit;
}

// Optionally, you can check user roles to restrict access to certain pages
// Example: Restrict access if user is not admin
/*
if ($_SESSION['role_id'] != 1) {  // Assuming 1 is the Admin role
    header('Location: unauthorized.php');  // Redirect them to an unauthorized access page
    exit;
}
*/
?>
