<?php
// Include database connection
require_once 'db_connection.php';

// Check if the form is submitted with a valid email parameter
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Check if the email exists in the login table
    $checkEmailQuery = "SELECT COUNT(*) as count FROM login WHERE email = ?";
    $stmtCheckEmail = $conn->prepare($checkEmailQuery);
    $stmtCheckEmail->bind_param('s', $email);
    $stmtCheckEmail->execute();
    $result = $stmtCheckEmail->get_result();
    $count = $result->fetch_assoc()['count'];

    // Close the statement
    $stmtCheckEmail->close();

    // Return a JSON response indicating whether the email exists
    header('Content-Type: application/json');
    echo json_encode(['exists' => ($count > 0)]);
} else {
    // Invalid request, return an empty JSON response
    header('Content-Type: application/json');
    echo json_encode(['exists' => false]);
}
?>
