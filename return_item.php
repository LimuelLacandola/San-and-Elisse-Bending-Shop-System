<?php
// Include the database connection file
include('db_connection.php');

// Function to fetch transaction details based on transaction ID
function getTransactionDetails($transactionId, $conn) {
    $query = "SELECT * FROM transaction_history WHERE id = :transactionId";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':transactionId', $transactionId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to handle item return
function processItemReturn($selectedItem, $returnReason, $conn) {
    // Your logic to update the database with return information goes here
    // Example: Update the 'returned' column for the selected item in the transaction_history table
    $query = "UPDATE transaction_history SET returned = 1, return_reason = :returnReason WHERE item_id = :selectedItem";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':returnReason', $returnReason, PDO::PARAM_STR);
    $stmt->bindParam(':selectedItem', $selectedItem, PDO::PARAM_INT);
    $stmt->execute();

    // You may want to add additional logic or error handling based on your requirements
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get transaction ID from the user input
    $transactionId = $_POST['transactionId'];

    // Fetch transaction details from the database
    $transactionDetails = getTransactionDetails($transactionId, $conn);

    // Check if the transaction exists
    if ($transactionDetails) {
        // Display transaction details and return form
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Return Item Page</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    height: 100vh;
                }

                form {
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    width: 300px;
                }

                label {
                    display: block;
                    margin-bottom: 8px;
                }

                select,
                input {
                    width: 100%;
                    padding: 8px;
                    margin-bottom: 12px;
                }

                input[type="submit"] {
                    background-color: #4caf50;
                    color: #fff;
                    cursor: pointer;
                }
            </style>
        </head>
        <body>
            <form method="post" action="">
                <h2>Transaction Details</h2>
                <p><strong>Transaction ID:</strong> <?php echo $transactionDetails['id']; ?></p>
                <p><strong>Item:</strong> <?php echo $transactionDetails['item_name']; ?></p>
                <p><strong>Amount:</strong> <?php echo $transactionDetails['amount']; ?></p>

                <h2>Return Item Form</h2>
                <label for="selectedItem">Select Item to Return:</label>
                <select name="selectedItem">
                    <!-- Populate dropdown with items from the transaction -->
                    <option value="<?php echo $transactionDetails['item_id']; ?>">
                        <?php echo $transactionDetails['item_name']; ?>
                    </option>
                </select>

                <label for="returnReason">Select Return Reason:</label>
                <select name="returnReason">
                    <!-- Add your return reasons here -->
                    <option value="Defective">Defective</option>
                    <option value="Wrong item">Wrong item</option>
                    <option value="Changed mind">Changed mind</option>
                </select>

                <input type="submit" value="Submit Return">
            </form>
        </body>
        </html>
        <?php
    } else {
        echo "Transaction not found.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selectedItem'])) {
    // Process the item return
    $selectedItem = $_POST['selectedItem'];
    $returnReason = $_POST['returnReason'];

    processItemReturn($selectedItem, $returnReason, $conn);

    // Display a success message or redirect to another page
    echo "Item return processed successfully!";
}
?>
