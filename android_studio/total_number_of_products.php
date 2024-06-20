<?php
// Database connection
require_once 'db_connection.php';

// SQL query to retrieve products
$sql = "SELECT COUNT(*) AS num_products FROM inventory";

$result = $conn->query($sql);

// Check if any rows are returned
if ($result->num_rows > 0) {
    // Fetch the total number of products
    $row = $result->fetch_assoc();
    $numProducts = $row['num_products'];

    // Display the total number of products
    echo $numProducts;
} else {
    // No products found
    echo "No products found.";
}

// Close the connection
$conn->close();
?>
