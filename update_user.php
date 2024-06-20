<?php
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect user data from the form
    $userId = $_POST['user_id'];
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $userRole = $_POST['user_role'];

    // Update the user data in the database
    $query = "UPDATE login SET fullname = ?, username = ?, password = ?, user_role = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssi', $fullname, $username, $password, $userRole, $userId);

    if ($stmt->execute()) {
        http_response_code(200); // Success
    } else {
        http_response_code(500); // Server error
    }

    $stmt->close();
} else {
    http_response_code(400); // Bad request
}
?>