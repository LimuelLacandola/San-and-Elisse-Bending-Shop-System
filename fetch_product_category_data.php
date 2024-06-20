<?php
// Include the database connection
require_once 'db_connection.php';

// Get the current year and month
$currentYear = date('Y');
$currentMonth = date('m');

// Query to get the top 3 products with the highest quantities for the current month
$query = "SELECT product_name, SUM(quantity) as quantity 
          FROM transaction_product_details 
          WHERE YEAR(transaction_date) = $currentYear AND MONTH(transaction_date) = $currentMonth
          GROUP BY product_name
          ORDER BY quantity DESC
          LIMIT 3";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    $categories = [];
    $categoryCounts = [];

    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['product_name'];
        $categoryCounts[] = $row['quantity'];
    }

    $response = [
        'categories' => $categories,
        'categoryCounts' => $categoryCounts,
    ];

    echo json_encode($response);
} else {
    // No data found
    echo json_encode(['categories' => [], 'categoryCounts' => []]);
}

// Close the database connection
$conn->close();
?>
