<?php
// Include the database connection file
include 'db_connection.php';

// Check if the POST data is set
if (isset($_POST['id']) && isset($_POST['quantityToAdd'])) {
    // Sanitize input data
    $id = intval($_POST['id']);
    $quantityToAdd = intval($_POST['quantityToAdd']);

    // Check if the product exists
    $checkProductQuery = "SELECT * FROM inventory WHERE id = '$id'";
    $checkProductResult = mysqli_query($conn, $checkProductQuery);

    if (mysqli_num_rows($checkProductResult) > 0) {
        // Product exists, proceed with the update

        // Use prepared statements to prevent SQL injection
        $currentQuantityQuery = "SELECT quantity FROM inventory WHERE id = ?";
        $stmt = mysqli_prepare($conn, $currentQuantityQuery);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $currentQuantity);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // Log for debugging
        echo "Current Quantity: " . $currentQuantity . "\n";

        // Calculate the new quantity
        $newQuantity = $currentQuantity + $quantityToAdd;

        // Log for debugging
        echo "New Quantity: " . $newQuantity . "\n";

        // Update the quantity in the database
        $updateQuery = "UPDATE inventory SET quantity = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "ii", $newQuantity, $id);
        $updateResult = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($updateResult) {
            // Update successful
            echo "Success";
        } else {
            // Update failed
            echo "Error updating quantity: " . mysqli_error($conn);
        }
    } else {
        // Product does not exist
        echo "Product does not exist";
    }
} else {
    // Invalid POST data
    echo "Invalid data";
}

// Close database connection
mysqli_close($conn);
?>
