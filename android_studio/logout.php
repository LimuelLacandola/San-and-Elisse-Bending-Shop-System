<?php
session_start();
date_default_timezone_set('Asia/Manila');

// Database connection
require_once 'db_connection.php';

// Debug: Log session variables
error_log("Session Variables: " . print_r($_SESSION, true));

// Check if the user is logged in
if (isset($_SESSION['username'])) {
    // Get the username and log the logout information
    $username = $_SESSION['username'];

    // Debug: Log the username for debugging
    error_log("Logging out user: " . $username);

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
    session_unset();

    // Destroy the session
    session_destroy();

    // Respond with success
    $response = array('status' => 'success', 'message' => 'Logout successful');
    echo json_encode($response);
    exit();
} else {
    // If the user is not logged in, respond with an error
    $response = array('status' => 'error', 'message' => 'User not logged in');
    echo json_encode($response);
    exit();
}

// Close the database connection (moved this to the end to ensure it's closed in all scenarios)
$conn->close();
?>
