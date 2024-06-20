<?php
require_once 'db_connection.php';


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from the inventory table
$sql = "SELECT productname, quantity, price FROM inventory";
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
