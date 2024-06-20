<?php
require_once 'db_connection.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the current year and month
$currentYear = date('Y');
$currentMonth = date('m');

// Fetch most sold products and quantities for the current month from the transaction_product_details table
$sql = "SELECT product_name, SUM(quantity) as totalQuantity
        FROM transaction_product_details
        WHERE YEAR(transaction_date) = $currentYear AND MONTH(transaction_date) = $currentMonth
        GROUP BY product_name
        ORDER BY totalQuantity DESC";
$result = $conn->query($sql);

// Close the database connection
$conn->close();

// Convert the result to a JSON array
$data = array();
while ($row = $result->fetch_assoc()) {
        $row['totalQuantity'] = $row['totalQuantity'] . ' pcs';
    $data[] = $row;
}

// Send the JSON response
header('Content-Type: application/json');
echo json_encode($data);
?>
