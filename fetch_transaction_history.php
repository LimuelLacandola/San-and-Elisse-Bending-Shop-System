<?php
date_default_timezone_set('Asia/Manila');
// Database connection
require_once 'db_connection.php';

// Get the search term from the query string
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare and execute the SQL query with the search term filter
$query = "SELECT * FROM transaction_history WHERE customer_name LIKE ? ORDER BY id DESC";
$stmt = $conn->prepare($query);
$searchTermWithWildcards = "%$searchTerm%"; // Add wildcards for partial matching
$stmt->bind_param("s", $searchTermWithWildcards);
$stmt->execute();
$result = $stmt->get_result();

echo '<html>';
echo '<head>';
echo '<style>';
echo '.low-stock { color: red; font-weight: bold; }';
echo '.mid-stock { color: orange; font-weight: bold; }';
echo '.high-stock { color: green; font-weight: bold; }';
echo '.disabled { color: gray; font-weight: bold; }';
echo '.scrollable-table { max-height: 750px; overflow-y: auto; position: relative; }'; // Adjust max-height as needed
echo '.fixed-header { position: sticky; top: 0; background-color: #fff; }';
echo '</style>';
echo '</head>';
echo '<body>';

if ($result->num_rows > 0) {
    // Output the scrollable table div
    echo '<div class="scrollable-table">';
    echo '<table>';
    
    // Table header (fixed)
    echo '<thead class="fixed-header">';
    echo '<tr><th>ID</th><th>Customer Name</th><th>Transaction Date</th><th>Total Price</th><th>Cashier</th></tr>';
    echo '</thead>';
    
    // Table body
    echo '<tbody>';
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['id'] . '</td>';
        echo '<td>' . $row['customer_name'] . '</td>';
        $transactionDate = new DateTime($row['transaction_date']);
        $transactionDate->setTimezone(new DateTimeZone('Asia/Manila'));
        echo '<td>' . $transactionDate->format('Y-m-d H:i:s') . '</td>';
        echo '<td>₱' . $row['total_price'] . '</td>';
        echo '<td>' . $row['cashier_name'] . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
} else {
    echo "No transaction history data available.";
}

echo '</body>';
echo '</html>';

$stmt->close();
$conn->close();
?>
