<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connection.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];
    $reason = $_POST['reason'];
    $category = $_POST['category']; // Add this line to get the category from the form

    // Validate the inputs (you can add more validation as needed)

    // Insert refund information into the refund_history table
    $sql_insert_refund = "INSERT INTO refund_history (product_id, quantity_to_refund, reason, category) VALUES (?, ?, ?, ?)";
    $stmt_insert_refund = $conn->prepare($sql_insert_refund);
    $stmt_insert_refund->bind_param("isss", $item_id, $quantity, $reason, $category);

    // Check if the insertion is successful
    if ($stmt_insert_refund->execute()) {
        header("Location: refund.php");
        exit();
    } else {
        // Handle the error if insertion fails
        echo "Error: " . $stmt_insert_refund->error;
        // You might want to handle the error more gracefully, e.g., display an error message
        // header("Location: refund.php?error=insert_failed");
        exit();
    }
} else {
    // Redirect if the form is not submitted
    header("Location: refund.php");
    exit();
}
?>
