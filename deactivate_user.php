<?php
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Perform the deactivation logic
    $query = "UPDATE login SET account_status = 'deactivated' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);

    if ($stmt->execute()) {
        echo 'User deactivated successfully.';
    } else {
        echo 'Error deactivating user.';
    }

    $stmt->close();
    $conn->close();
} else {
    echo 'Invalid request.';
}
?>
