<?php
require_once 'db_connection.php';

date_default_timezone_set('Asia/Manila');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the current date
$currentDate = date('Y-m-d');

// Fetch daily total sales for the past 7 days from the transaction_history table
$sql = "SELECT DATE_FORMAT(transaction_date, '%Y-%m-%d') as date, DAYNAME(transaction_date) as dayName, COUNT(*) as transactionCount
        FROM transaction_history
        WHERE DATE(transaction_date) BETWEEN (CURDATE() - INTERVAL 6 DAY) AND CURDATE()
        GROUP BY DATE_FORMAT(transaction_date, '%Y-%m-%d')
        ORDER BY transaction_date DESC";

$result = $conn->query($sql);

// Close the database connection
$conn->close();

// Convert the result to a JSON array
$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Send the JSON response
header('Content-Type: application/json');
echo json_encode($data);
?>
