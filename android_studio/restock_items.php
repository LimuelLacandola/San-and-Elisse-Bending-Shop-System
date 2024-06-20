<?php
require_once('db_connection.php');


// Fetch data from inventory table where quantity is 20 or below
$query = "SELECT * FROM inventory WHERE quantity <= 20";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error in query: " . mysqli_error($conn));
}

// Fetch data as an associative array
$rows = array();
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

// Return data as JSON
echo json_encode($rows);

// Close the database connection
mysqli_close($conn);
?>
