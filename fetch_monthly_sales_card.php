<?php
require_once 'db_connection.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the current year
$currentYear = date('Y');

// Fetch monthly total sales for the current year from the transaction_history table
$sql = "SELECT MONTH(transaction_date) as month, FORMAT(SUM(total_price), 2) as totalSales
        FROM transaction_history
        WHERE YEAR(transaction_date) = $currentYear
        GROUP BY MONTH(transaction_date)";
$result = $conn->query($sql);

if (!$result) {
    die("Error in SQL query: " . $conn->error);
}

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
