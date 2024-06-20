<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Include your database connection file (db_connection.php)
require_once 'db_connection.php';

// Function to convert UTC time to Philippine Manila time
function convertToManilaTime($dateTime) {
    $utcTimeZone = new DateTimeZone('UTC');
    $manilaTimeZone = new DateTimeZone('Asia/Manila');
    
    $dateTimeObj = new DateTime($dateTime, $utcTimeZone);
    $dateTimeObj->setTimezone($manilaTimeZone);

    return $dateTimeObj->format('Y-m-d');
}

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Get the current date in Philippine Manila time
$currentDate = convertToManilaTime(date('Y-m-d H:i:s'));

// SQL query to fetch data from the tables
$sql = "SELECT i.id, i.productname, i.quantity, tpd.total_sales, rh.return_quantity, di.original_quantity
        FROM inventory i
        LEFT JOIN transaction_product_details tpd ON i.id = tpd.product_id AND DATE_FORMAT(tpd.transaction_date, '%Y-%m-%d') = '$currentDate'
        LEFT JOIN return_history rh ON i.id = rh.product_id AND DATE_FORMAT(rh.return_date, '%Y-%m-%d') = '$currentDate'
        LEFT JOIN daily_inventory di ON i.id = di.product_id";

$result = mysqli_query($conn, $sql);

// Check for errors
if (!$result) {
    die("Error: " . mysqli_error($conn));
}

// Display the data in an HTML table
echo "<table border='1'>
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Total Sales</th>
            <th>Return Quantity</th>
            <th>Original Quantity</th>
        </tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['productname']}</td>
            <td>{$row['quantity']}</td>
            <td>{$row['total_sales']}</td>
            <td>{$row['return_quantity']}</td>
            <td>{$row['original_quantity']}</td>
        </tr>";
}

echo "</table>";

// Close the database connection
mysqli_close($conn);
?>
