<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Database connection
require_once 'db_connection.php';


    // Retrieve item ID from form
    $id = $_POST['id'];

    // Delete data from inventory table
    $query = "DELETE FROM inventory WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: inventory.php"); // Redirect back to inventory page
    } else {
        echo "Error deleting item: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
