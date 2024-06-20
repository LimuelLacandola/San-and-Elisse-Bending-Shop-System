<?php
date_default_timezone_set('Asia/Manila');
// Database connection
require_once 'db_connection.php';

// Get the current month and year
$currentMonth = date('m');
$currentYear = date('Y');

// SQL query to retrieve notification items for the current month
$sql = "SELECT * FROM notification_history WHERE YEAR(notification_date) = $currentYear AND MONTH(notification_date) = $currentMonth";

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
    echo "No notification found for the current month.";
}

// Close the connection
$conn->close();
?>
