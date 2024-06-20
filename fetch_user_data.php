<?php
require_once 'db_connection.php';

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    $query = "SELECT * FROM login WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $userData = $result->fetch_assoc();
        echo json_encode($userData);
    } else {
        echo json_encode(array()); // User not found
    }
    $stmt->close();
} else {
    echo json_encode(array()); // No user ID provided
}
