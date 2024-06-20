<?php
date_default_timezone_set('Asia/Manila');
// Database connection
require_once 'db_connection.php';

// Get the current date
$currentDate = date('Y-m-d');

// SQL query to retrieve inventory items for the current date
$sql = "SELECT customer_name, transaction_date, total_price, payment_method FROM transaction_history WHERE DATE(transaction_date) = '$currentDate'";

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
    echo "No transactions found for the current date.";
}

// Close the connection
$conn->close();
?>
