<?php
date_default_timezone_set('Asia/Manila');
// Database connection
require_once 'db_connection.php';

// Get today's date
$currentDate = date("Y-m-d");

// Get the total number of transactions today
$totalTransactionsQuery = "SELECT COUNT(*) as total_transactions FROM transaction_history WHERE DATE(transaction_date) = '$currentDate'";
$totalTransactionsResult = $conn->query($totalTransactionsQuery);

if ($totalTransactionsResult->num_rows > 0) {
    $transactionRow = $totalTransactionsResult->fetch_assoc();
    $totalTransactions = $transactionRow['total_transactions'];

    // Output the total number of transactions made today
    echo $totalTransactions;
} else {
    // No transactions found today
    echo "No transactions made today.";
}

// Close the connection
$conn->close();
?>
