<?php
require_once 'db.php';  // For session access

// Optional: Confirmation via GET param (e.g., ?confirm=1)
if (isset($_GET['confirm']) && $_GET['confirm'] === '1') {
    session_unset();
    session_destroy();
    header("Location: login.php?message=logged_out");
    exit();
}

// Redirect to login with logout message
header("Location: login.php?message=logout");
exit();
?>