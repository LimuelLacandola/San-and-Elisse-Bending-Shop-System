<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false]);
    exit();
}

// Database connection
require_once 'db_connection.php';

// Update the lockscreen column to "locked"
$sql = "UPDATE login SET lockscreen = 'locked' WHERE username = '{$_SESSION['username']}'";
$result = $conn->query($sql);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

// Close the database connection
$conn->close();
?>
