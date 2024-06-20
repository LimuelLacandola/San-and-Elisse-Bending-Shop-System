<?php
// Database connection
require_once 'db_connection.php';

// Get the search term from the query string
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare and execute the SQL query with the search term filter
$query = "SELECT * FROM login WHERE fullname LIKE ?";
$stmt = $conn->prepare($query);
$searchTermWithWildcards = "%$searchTerm%"; // Add wildcards for partial matching
$stmt->bind_param("s", $searchTermWithWildcards);
$stmt->execute();
$result = $stmt->get_result();

// Output the HTML and CSS
echo '<html>';
echo '<head>';
echo '<style>';
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
    echo '<tr><th>ID</th><th>Full Name</th><th>Username</th><th>User Role</th><th>User Image</th><th>Account Status</th></tr>';
    echo '</thead>';

    // Table body
    echo '<tbody>';
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['id'] . '</td>';
        echo '<td>' . $row['fullname'] . '</td>';
        echo '<td>' . $row['username'] . '</td>';
        echo '<td>' . $row['user_role'] . '</td>';
        echo '<td><img src="' . $row['user_image'] . '" alt="User Image" style="max-width: 100px;"></td>';
      echo '<td>' . $row['account_status'] . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
} else {
    echo "No employee data available.";
}
echo '</div>';

echo '</body>';
echo '</html>';

$stmt->close();
$conn->close();
?>
