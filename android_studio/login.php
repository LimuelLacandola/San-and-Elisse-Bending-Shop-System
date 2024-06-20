<?php
session_start();
date_default_timezone_set('Asia/Manila');


// Database connection
require_once 'db_connection.php';
error_log(print_r($_POST, true));


// Response array to hold the status
$response = array();

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch the user's data from the login table
    $query = "SELECT * FROM login WHERE username=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $hashedPassword = $row['password'];

        // Verify the password using password_verify
        if (password_verify($password, $hashedPassword)) {
            // Login successful
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_role'] = $row['user_role'];
            $fullname = $row['fullname'];

            date_default_timezone_set('Asia/Manila');
            $loginTime = date('Y-m-d H:i:s');

 

            // Set the status to success
            $response['status'] = 'success';
            $response['message'] = 'Login successful';
        } else {
            // Login failed
            $response['status'] = 'failure';
            $response['message'] = 'Invalid username or password';
        }
    } else {
        // Login failed
        $response['status'] = 'failure';
        $response['message'] = 'Invalid username or password';
    }
}

// Close the database connection
$conn->close();

// Return the JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
