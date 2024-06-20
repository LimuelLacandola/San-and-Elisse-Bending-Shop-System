<?php
date_default_timezone_set('Asia/Manila');
// Database connection
require_once 'db_connection.php';

// Get the first day and last day of the current month
$firstDayOfMonth = date('Y-m-01');
$lastDayOfMonth = date('Y-m-t');

// SQL query to retrieve the item with the highest total quantity sold during the current month
$highestQuantityItemQuery = "SELECT product_name, SUM(quantity) as quantity 
    FROM transaction_product_details 
    WHERE DATE(transaction_date) BETWEEN '$firstDayOfMonth' AND '$lastDayOfMonth'
    GROUP BY product_name 
    ORDER BY quantity DESC 
    LIMIT 1";

$highestQuantityItemResult = $conn->query($highestQuantityItemQuery);

$highestQuantityItem = null;

if ($highestQuantityItemResult->num_rows > 0) {
    $highestQuantityItem = $highestQuantityItemResult->fetch_assoc();
}

    // Display the product information
    echo $highestQuantityItem['product_name'];
    echo " " . $highestQuantityItem['quantity'] . " pcs";

// Close the connection
$conn->close();
?>
