<?php
// Database connection
require_once 'db_connection.php';

// Get the search term from the query string
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare and execute the SQL query with the search term filter
$query = "SELECT * FROM inventory WHERE productname LIKE ?";
$stmt = $conn->prepare($query);
$searchTermWithWildcards = "%$searchTerm%"; // Add wildcards for partial matching
$stmt->bind_param("s", $searchTermWithWildcards);
$stmt->execute();
$result = $stmt->get_result();

// Output the HTML and CSS
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

// Output the scrollable table div
echo '<div class="scrollable-table">';
if ($result->num_rows > 0) {
    echo '<table>';
    
    // Table header (fixed)
    echo '<thead class="fixed-header">';
    echo '<tr><th>Image</th><th>Item Name</th><th>Brand</th><th>Description</th><th>Unit of Measure</th><th>Quantity</th><th>Price</th><th>Quantity Status</th><th>Product Status</th></tr>';
    echo '</thead>';
    
    // Table body
    echo '<tbody>';
    while ($row = $result->fetch_assoc()) {
        // Determine the status based on the quantity
        if ($row['quantity'] >= 50) {
            $status = 'On Stock';
            $statusClass = 'high-stock';
        } elseif ($row['quantity'] > 20) {
            $status = 'Critical';
            $statusClass = 'mid-stock';
        } elseif ($row['quantity'] > 0) {
            $status = 'Low Stock';
            $statusClass = 'low-stock';
        } else {
            $status = 'No Stock';
            $statusClass = 'disabled';
        }

        // Format the price with peso sign and commas
        $formattedPrice = '₱ ' . number_format($row['price'], 2);

        echo '<tr class="inventory-row" data-product-id="' . $row['id'] . '">';
        echo '<td><img src="' . $row['image_url'] . '" alt="Product Image" style="max-width: 100px;"></td>';
        echo '<td>' . $row['productname'] . '</td>';
        echo '<td>' . $row['brand'] . '</td>';
        echo '<td>' . $row['description'] . '</td>';
      	echo '<td>' . $row['unit_of_measure'] . '</td>';
      	//echo '<td>' . $row['measurement'] . '</td>';
        echo '<td>' . $row['quantity'] . '</td>';
        echo '<td>' . $formattedPrice . '</td>';
        echo '<td class="' . $statusClass . '">' . $status . '</td>';
     //   echo '<td>' . $row['supplier_name'] . '</td>';
       // echo '<td>' . $row['supplier_email'] . '</td>';
$statusText = ($row['product_status'] === 'active') ? 'Active' : 'Deactivated';
echo '<td>' . $statusText . '</td>';

        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
} else {
    echo "No inventory data available.";
}
echo '</div>';

echo '</body>';
echo '</html>';

$stmt->close();
$conn->close();
?>
