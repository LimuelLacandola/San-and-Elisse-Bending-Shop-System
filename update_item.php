<?php
require_once 'db_connection.php';

if (isset($_POST['productId'])) {
    $productId = $_POST['productId'];
    $productName = $_POST['productName'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $imageLink = $_POST['imageLink'];

    // Prepare and execute the SQL query to update the product
    $query = "UPDATE inventory SET productname=?, category=?, quantity=?, price=?, image_url=? WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssidsi", $productName, $category, $quantity, $price, $imageLink, $productId);

    if ($stmt->execute()) {
        // Product updated successfully, add JavaScript alert
        echo '<script>alert("Product updated successfully.");</script>';
    } else {
        // Error updating product, add JavaScript alert
        echo '<script>alert("Error updating product.");</script>';
    }

    $stmt->close();
} else {
    echo 'Invalid request.';
}

$conn->close();
?>
