<?php
// fetch_return_history.php

// Database connection
require_once 'db_connection.php';

// Get search term from GET parameter
$searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Construct the SQL query with search condition
$query = "SELECT r.id, r.transaction_id, r.item_id, r.quantity_returned, r.reason, r.return_date, i.productname
          FROM return_history r
          JOIN inventory i ON r.item_id = i.id
          WHERE i.productname LIKE '%$searchTerm%'
          ORDER BY r.id DESC";

// Execute the query
$result = $conn->query($query);

// Fetch the results and generate HTML
$html = '';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . $row['id'] . '</td>';
        $html .= '<td>' . $row['transaction_id'] . '</td>';
        $html .= '<td>' . $row['productname'] . '</td>';
        $html .= '<td>' . $row['quantity_returned'] . '</td>';
        $html .= '<td>' . $row['reason'] . '</td>';
        $html .= '<td>' . $row['return_date'] . '</td>';
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="6">No return history available.</td></tr>';
}

// Close the database connection
$conn->close();

echo $html;
?>
