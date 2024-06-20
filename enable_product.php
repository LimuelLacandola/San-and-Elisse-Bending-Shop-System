<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_GET['id'];

    // Update product status to 'active' in the database
    $query = "UPDATE inventory SET product_status = 'active' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $productId);
    
    if ($stmt->execute()) {
        // Successfully enabled the product
        echo json_encode(['success' => true]);
    } else {
        // Failed to enable the product
        echo json_encode(['success' => false, 'error' => 'Failed to enable product']);
    }

    $stmt->close();
    $conn->close();
} else {
    // Invalid request method
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
