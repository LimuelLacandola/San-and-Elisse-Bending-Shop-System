<?php
// get_items.php
include 'db_connection.php';

if (isset($_GET['supplier_name'])) {
    $supplierName = $_GET['supplier_name'];

    // Fetch items with the selected supplier_name
    $sql = "SELECT productname, quantity FROM inventory WHERE supplier_name = '$supplierName' AND quantity <= 20";
    $result = $conn->query($sql);

    $items = array();
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    // Return the items as JSON
    echo json_encode($items);
}

$conn->close();


?>