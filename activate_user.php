<?php
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Perform the deactivation logic
    $query = "UPDATE login SET account_status = 'active' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);

    if ($stmt->execute()) {
        echo 'User activated successfully.';
    } else {
        echo 'Error activating user.';
    }

    $stmt->close();
    $conn->close();
} else {
    echo 'Invalid request.';
}
?>
