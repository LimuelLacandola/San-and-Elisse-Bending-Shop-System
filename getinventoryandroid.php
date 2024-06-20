<?php
// Database connection
require_once 'db_connection.php';


// SQL query to retrieve inventory items
$sql = "SELECT productname, quantity, price FROM inventory";

$result = $conn->query($sql);

// Check if any rows are returned
if ($result->num_rows > 0) {
    // Fetch data and encode it as JSON
    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode($rows);
} else {
    // No rows found
    echo "No inventory items found.";
}

// Close the connection
$conn->close();

?>
