<?php
date_default_timezone_set('Asia/Manila');
// Start a session
session_start();

// Database connection
require_once 'db_connection.php';

// Check if the user is logged in
if (isset($_SESSION['username'])) {
    // Get the username and log the logout information
    $username = $_SESSION['username'];

    // Update the existing row with the logout time and calculate time spent
$updateQuery = "UPDATE log 
                SET logout_time = NOW(), 
                    time_spent = TIMEDIFF(NOW(), login_time) 
                WHERE username = ? AND logout_time IS NULL";

    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("s", $username);
    $updateStmt->execute();
    $updateStmt->close();

    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to the login page
    header("Location: login.php");
    exit();
} else {
    // If the user is not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

$conn->close();
?>
