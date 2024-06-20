<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the username from the POST data
    $username = $_POST['username'];

    // Sanitize the username to prevent SQL injection
    $username = mysqli_real_escape_string($conn, $username);

    // Query to check if the username exists
    $query = "SELECT COUNT(*) as count FROM login WHERE username = '$username'";
    $result = $conn->query($query);

    if ($result) {
        $row = $result->fetch_assoc();
        $count = $row['count'];

        // Return JSON response
        echo json_encode(['status' => ($count > 0) ? 'taken' : 'available']);
    } else {
        // Handle database error
        echo json_encode(['status' => 'error']);
    }
} else {
    // Handle invalid request method
    echo json_encode(['status' => 'error']);
}
