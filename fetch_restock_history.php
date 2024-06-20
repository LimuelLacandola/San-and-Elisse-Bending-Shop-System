<?php
// fetch_restock_history.php

// Database connection
require_once 'db_connection.php';

// Get search term from GET parameters
$searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Construct the SQL query with search condition
$query = "SELECT * FROM restock_history WHERE product_name LIKE '%$searchTerm%' OR user_restocked LIKE '%$searchTerm%'";

// Execute the query
$result = $conn->query($query);

// Fetch the results and generate HTML
$html = '';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . $row['id'] . '</td>';
        $html .= '<td>' . $row['product_name'] . '</td>';
        $html .= '<td>' . $row['quantity_restocked'] . '</td>';
        $html .= '<td>' . $row['restock_date'] . '</td>';
        $html .= '<td>' . $row['user_restocked'] . '</td>';
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="5">No restock history available.</td></tr>';
}

// Close the database connection
$conn->close();

echo $html;
?>
