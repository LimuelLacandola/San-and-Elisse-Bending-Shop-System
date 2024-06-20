<?php
// fetch_return_history.php

// Database connection
require_once 'db_connection.php';

// Get search term from GET parameter
$searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Construct the SQL query with search condition
$actionLogQuery = "SELECT id, username, action, log_date FROM action_log WHERE username LIKE '%$searchTerm%' OR action LIKE '%$searchTerm%' ORDER BY log_date DESC";
$actionLogResult = $conn->query($actionLogQuery);

// Fetch the results and generate HTML
$html = '';
if ($actionLogResult->num_rows > 0) {
    while ($row = $actionLogResult->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . $row['action'] . '</td>';
        $html .= '<td>' . $row['log_date'] . '</td>';
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="4">No action log available.</td></tr>';
}

// Close the database connection
$conn->close();

echo $html;
?>
