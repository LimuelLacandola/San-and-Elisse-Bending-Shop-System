<?php
// Include the database connection
require_once 'db_connection.php';

// Get the current year and month
$currentYear = date('Y');
$currentMonth = date('m');

// Query to get the top product categories with the highest quantities for the current month
$query = "SELECT product_name, SUM(quantity) as quantity 
          FROM transaction_product_details 
          WHERE YEAR(transaction_date) = $currentYear AND MONTH(transaction_date) = $currentMonth
          GROUP BY product_name
          ORDER BY quantity DESC";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    $productNames = [];
    $productQuantities = [];
    $totalQuantity = 0;

    while ($row = $result->fetch_assoc()) {
        $productNames[] = $row['product_name'];
        $productQuantities[] = $row['quantity'];
        $totalQuantity += $row['quantity'];
    }

$productPercentages = [];
foreach ($productQuantities as $quantity) {
    $percentage = ($quantity / $totalQuantity) * 100;
    $productPercentages[] = round($percentage, 2);
}


    $response = [
        'productNames' => $productNames,
        'productQuantities' => $productPercentages,
    ];

    echo json_encode($response);
} else {
    // No data found
    echo json_encode(['productNames' => [], 'productQuantities' => [], 'productPercentages' => []]);
}

// Close the database connection
$conn->close();
?>
