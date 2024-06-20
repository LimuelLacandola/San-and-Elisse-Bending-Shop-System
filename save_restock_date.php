<?php
require_once 'db_connection.php';

// Check if item_ids are set in the POST data
$itemIds = isset($_POST['itemIds']) ? $_POST['itemIds'] : array();

// Fetch item details from the database
$itemDetails = array();

if (!empty($itemIds)) {
    // Loop through each item_id and update the delivery_date
    foreach ($itemIds as $itemId) {
        $itemId = (int)$itemId;
        $dateValue = $_POST['dateValue'];

        // Update the delivery_date for the item
        $updateDeliveryDateQuery = "UPDATE inventory SET delivery_date = '$dateValue' WHERE id = $itemId";
        $updateDeliveryDateResult = $conn->query($updateDeliveryDateQuery);

        if ($updateDeliveryDateResult) {
            echo 'Successfully saved date for item with ID ' . $itemId . '.';
        } else {
            // Log the error to a custom log file
            error_log('Failed to save date for item with ID ' . $itemId . '. Error: ' . $conn->error, 3, 'custom_error_log.log');

            // Send a generic error message to the client
            http_response_code(500);
            echo 'Internal Server Error. Please check the server logs for more information.';
        }
    }
} else {
    // Handle the case of an invalid request
    http_response_code(400);
    echo 'Invalid request.';
}

$conn->close();
?>
