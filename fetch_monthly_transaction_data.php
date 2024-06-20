<?php

require_once 'db_connection.php';

// Query to fetch monthly sales data
$query = "SELECT DATE_FORMAT(transaction_date, '%M') AS month, SUM(total_price) AS total_sales
          FROM transaction_history
          GROUP BY month
          ORDER BY MONTH(transaction_date)";

$result = $conn->query($query);

if ($result) {
    $data = array(
        'labels' => array(),
        'transactionCounts' => array() // Change this key to 'transactionCounts'
    );

    while ($row = $result->fetch_assoc()) {
        $data['labels'][] = $row['month'];
        $data['transactionCounts'][] = (float)$row['total_sales']; // Assuming total_price is a numeric field
    }

    // Close the database connection
    $conn->close();

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($data);
} else {
    // Handle query error
    echo json_encode(['error' => 'Error fetching data']);
}
?>
