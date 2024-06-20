<?php
date_default_timezone_set('Asia/Manila');
// Assuming you have a database connection established, replace 'your_db_connection' with your actual connection variable
require_once 'db_connection.php';

// Get today's date
$currentDate = date("Y-m-d");

// Initialize variable to store the total price for today
$totalPriceToday = 0;

// Query to calculate the total amount of transactions for today
$totalPriceQuery = "SELECT SUM(total_price) as total_price_today FROM transaction_history WHERE DATE(transaction_date) = '$currentDate'";
$totalPriceResult = $conn->query($totalPriceQuery);

if ($totalPriceResult->num_rows > 0) {
    $row = $totalPriceResult->fetch_assoc();
    $totalPriceToday = $row['total_price_today'];
}

// Output the total price for today
echo '₱' . $totalPriceToday;

// Close the database connection
$conn->close();
?>
