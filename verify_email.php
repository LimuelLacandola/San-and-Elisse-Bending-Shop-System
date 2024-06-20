<?php

// Include database connection
require_once 'db_connection.php';

// Check if the email parameter is set in the POST request
if (isset($_POST['email'])) {
    $enteredEmail = $_POST['email'];

    // Query to check if the entered email exists in the login table
    $checkEmailQuery = "SELECT COUNT(*) AS count FROM login WHERE email = ?";
    $stmt = $conn->prepare($checkEmailQuery);
    $stmt->bind_param('s', $enteredEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $count = $row['count'];

    // Return a JSON response indicating whether the email exists or not
    header('Content-Type: application/json');
    echo json_encode(['exists' => ($count > 0)]);

    $stmt->close();
    $conn->close();
} else {
    // Handle the case where the email parameter is not set
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Email parameter is missing']);
}
?>
