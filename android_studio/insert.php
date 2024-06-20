<?php
// Include the database connection file
include('db_connection.php');

// Check if data is sent from the Android app
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get data from the POST request
    $productName = $_POST['productName'];
    $brand = $_POST['brand'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $measurement = $_POST['measurement'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $imageUrl = $_POST['imageUrl'];
    $supplierName = $_POST['supplierName'];
    $supplierEmail = $_POST['supplierEmail'];

    // SQL query to insert data into the 'inventory' table
    $sql = "INSERT INTO inventory (productname, brand, description, category, measurement, quantity, price, image_url, supplier_name, supplier_email)
            VALUES ('$productName', '$brand', '$description', '$category', '$measurement', '$quantity', '$price', '$imageUrl', '$supplierName', '$supplierEmail')";

    if ($conn->query($sql) === TRUE) {
        echo "Record added successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    echo "Invalid request";
}

// Close the database connection
$conn->close();
?>
