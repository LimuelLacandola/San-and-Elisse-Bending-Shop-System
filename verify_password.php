<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false]);
    exit();
}

// Database connection
require_once 'db_connection.php';

// Retrieve the hashed password from the database based on the username
$sql = "SELECT password FROM login WHERE username = '{$_SESSION['username']}'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hashedPassword = $row['password'];

    // Check if the entered password matches the hashed password
    $enteredPassword = $_POST['password'];
    $passwordMatch = password_verify($enteredPassword, $hashedPassword);

    if ($passwordMatch) {
        // Password is correct, update the lockscreen column to "unlocked"
        $updateSql = "UPDATE login SET lockscreen = 'unlocked' WHERE username = '{$_SESSION['username']}'";
        $updateResult = $conn->query($updateSql);

        if (!$updateResult) {
            echo json_encode(['success' => false]);
        } else {
            echo json_encode(['success' => true]);
        }
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}

// Close the database connection
$conn->close();
?>
