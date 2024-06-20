<?php

require_once 'db_connection.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve product name from the GET request
$productName = $_GET['productname'];

// Query to fetch product description based on the product name
$sql = "SELECT description FROM inventory WHERE productname = ?";

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error in preparing the statement: " . $conn->error);
}

$stmt->bind_param("s", $productName);

// Execute the statement
$stmt->execute();

if ($stmt->errno) {
    die("Error in executing the statement: " . $stmt->error);
}

// Bind the result variables
$stmt->bind_result($description);

// Fetch the result
$stmt->fetch();

// Close the statement
$stmt->close();

// Close the database connection
$conn->close();

// Return the description
echo $description;
?>
