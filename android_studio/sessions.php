<?php
date_default_timezone_set('Asia/Manila');
// Database connection
require_once 'db_connection.php';


// Get the current date
$currentDate = date('Y-m-d');

// SQL query to retrieve sessions 
$sql = "SELECT username, fullname, login_time, logout_time, time_spent FROM log WHERE DATE(login_time) = '$currentDate'";

$result = $conn->query($sql);

// Check if any rows are returned
if ($result->num_rows > 0) {
    // Fetch data and encode it as JSON
    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode($rows);
} else {
    // No rows found
    echo "No Session found.";
}

// Close the connection
$conn->close();

?>
