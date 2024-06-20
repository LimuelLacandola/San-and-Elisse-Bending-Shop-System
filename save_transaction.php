<?php
date_default_timezone_set('Asia/Manila');
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assuming you're sending the data via POST

    // Get data from the POST request
    $customerName = $_POST['customerName'];
    $transactionDate = date('Y-m-d H:i:s'); // Current date and time
    $totalPrice = $_POST['totalPrice'];
    $paymentMethod = $_POST['paymentMethod'];
    $amountPaid = $_POST['amountPaid'];
    $referenceNumber = $_POST['referenceNumber'];
    $cashierName = $_POST['cashierName'];
    $selectedItems = json_decode($_POST['selectedItems'], true);

    // Insert data into the 'transaction_history' table
    $insertQuery = "INSERT INTO transaction_history (customer_name, transaction_date, total_price, payment_method, amount_paid, referenceNumber, cashier_name) VALUES ('$customerName', '$transactionDate', '$totalPrice', '$paymentMethod', '$amountPaid', '$referenceNumber', '$cashierName')";

    if ($conn->query($insertQuery) === TRUE) {
        // Get the last inserted transaction ID
        $lastTransactionID = $conn->insert_id;

        // Update the inventory table based on selected items
        foreach ($selectedItems as $item) {
            $productId = $item['id'];
            $quantity = $item['quantity'];

            // Update the inventory table
            $updateInventoryQuery = "UPDATE inventory SET quantity = quantity - $quantity WHERE id = $productId";

            if (!$conn->query($updateInventoryQuery)) {
                echo 'Error updating inventory: ' . $conn->error;
                // Rollback transaction if an error occurs
                $conn->rollback();
                exit();
            }

            // Insert data into the 'transaction_product_details' table
            $insertDetailsQuery = "INSERT INTO transaction_product_details (transaction_id, product_id, product_name, quantity, price, transaction_date) VALUES ('$lastTransactionID', '$productId', '{$item['productName']}', '$quantity', '{$item['price']}', '$transactionDate')";

            if (!$conn->query($insertDetailsQuery)) {
                echo 'Error inserting product details: ' . $conn->error;
                // Rollback transaction if an error occurs
                $conn->rollback();
                exit();
            }
        }

        echo 'Transaction saved successfully';
    } else {
        echo 'Error: ' . $conn->error;
    }
} else {
    echo 'Invalid request';
}

$conn->close();
?>
