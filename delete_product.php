<?php
require_once 'db_connection.php';

// Check if the 'id' parameter is set
if (isset($_GET['id'])) {
    $productId = $_GET['id'];

    session_start();
    $username = $_SESSION['username'];

    // Get the product name before deleting
    $productNameQuery = "SELECT productname FROM inventory WHERE id = ?";
    $stmtProductName = $conn->prepare($productNameQuery);
    $stmtProductName->bind_param("i", $productId);
    $stmtProductName->execute();
    $stmtProductName->bind_result($productName);

    // Check for errors in fetching the product name
    if ($stmtProductName->fetch()) {
        $stmtProductName->close();

        // Prepare and execute the SQL query to delete the product
        $deleteQuery = "DELETE FROM inventory WHERE id = ?";
        $stmtDelete = $conn->prepare($deleteQuery);
        $stmtDelete->bind_param("i", $productId);

        if ($stmtDelete->execute()) {
            echo 'Product deleted successfully.';

            // Log the action in the action_log table with the product name
            $logMessage = "$username deleted the product '$productName'";
            $logQuery = "INSERT INTO action_log (username, action, log_date) VALUES (?, ?, NOW())";
            $stmtLog = $conn->prepare($logQuery);
            $action = 'Delete Product';
            $stmtLog->bind_param("ss", $username, $logMessage);
            $stmtLog->execute();
            $stmtLog->close();
        } else {
            echo 'Error deleting product.';
        }

        $stmtDelete->close();
    } else {
        echo 'Error fetching product name.';
    }
} else {
    echo 'Product ID not provided.';
}

$conn->close();
?>
