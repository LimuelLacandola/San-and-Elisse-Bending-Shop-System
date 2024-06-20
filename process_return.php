<?php
session_start();
require_once 'db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Page Title</title>
    <!-- Include SweetAlert script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
     <style>
        /* Set the font for SweetAlert to Poppins */
        .swal2-popup {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transactionId = mysqli_real_escape_string($conn, $_POST['transaction_id']);
    $itemId = mysqli_real_escape_string($conn, $_POST['item_id']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    // Perform validation as needed

    // Check if the item has already been returned for the given transaction ID and item ID
    $sqlCheckReturn = "SELECT * FROM return_history WHERE transaction_id = '$transactionId' AND item_id = '$itemId'";
    $resultCheckReturn = $conn->query($sqlCheckReturn);

    if ($resultCheckReturn->num_rows > 0) {
        // Item has already been returned, show error message
        echo '<script>
                Swal.fire({
                    title: "Error!",
                    text: "This product has already been returned.",
                    icon: "error",
                    confirmButtonText: "OK",
                }).then(() => {
                    window.location.href = "refund.php";
                });
              </script>';
        exit();
    }

    // Check if the item exists in the inventory
    $sqlCheckItem = "SELECT * FROM inventory WHERE id = '$itemId'";
    $resultCheckItem = $conn->query($sqlCheckItem);

    if ($resultCheckItem->num_rows > 0) {
        // Check if the returned quantity exceeds the quantity originally bought
        $sqlCheckQuantity = "SELECT * FROM transaction_product_details WHERE transaction_id = '$transactionId' AND product_id = '$itemId'";
        $resultCheckQuantity = $conn->query($sqlCheckQuantity);

        if ($resultCheckQuantity->num_rows > 0) {
            $productDetails = $resultCheckQuantity->fetch_assoc();

            if ($quantity > $productDetails['quantity']) {
                // Quantity to return exceeds the originally bought quantity, show error message
                echo '<script>
                        Swal.fire({
                            title: "Error!",
                            text: "Quantity to return exceeds the originally bought quantity.",
                            icon: "error",
                            confirmButtonText: "OK",
                        }).then(() => {
                            window.location.href = "refund.php";
                        });
                      </script>';
                exit();
            }
        }

        // Insert return information into the return_history table
        $sqlReturn = "INSERT INTO return_history (transaction_id, item_id, quantity_returned, reason)
                      VALUES ('$transactionId', '$itemId', '$quantity', '$reason')";
        $resultReturn = $conn->query($sqlReturn);

        if ($resultReturn) {
            // Update inventory (adjust quantity back)
            $sqlUpdateInventory = "UPDATE inventory SET quantity = quantity - '$quantity' WHERE id = '$itemId'";
            $resultUpdateInventory = $conn->query($sqlUpdateInventory);

            if ($resultUpdateInventory) {
                // Success message with SweetAlert
                echo '<script>
                        Swal.fire({
                            title: "Success!",
                            text: "Item returned successfully.",
                            icon: "success",
                            confirmButtonText: "OK",
                        }).then(() => {
                            window.location.href = "refund.php";
                        });
                      </script>';
                exit();
            } else {
                // Error message with SweetAlert
                echo '<script>
                        Swal.fire({
                            title: "Error!",
                            text: "Failed to update inventory.",
                            icon: "error",
                            confirmButtonText: "OK",
                        }).then(() => {
                            window.location.href = "refund.php";
                        });
                      </script>';
                exit();
            }
        } else {
            // Error message with SweetAlert
            echo '<script>
                    Swal.fire({
                        title: "Error!",
                        text: "Failed to record return history.",
                        icon: "error",
                        confirmButtonText: "OK",
                    }).then(() => {
                        window.location.href = "refund.php";
                    });
                  </script>';
            exit();
        }
    } else {
        // Error message with SweetAlert
        echo '<script>
                Swal.fire({
                    title: "Error!",
                    text: "Item not found in the inventory.",
                    icon: "error",
                    confirmButtonText: "OK",
                }).then(() => {
                    window.location.href = "refund.php";
                });
              </script>';
        exit();
    }
} else {
    // Error message with SweetAlert
    echo '<script>
            Swal.fire({
                title: "Error!",
                text: "Item return failed.",
                icon: "error",
                confirmButtonText: "OK",
            }).then(() => {
                window.location.href = "refund.php";
            });
          </script>';
    exit();
}
?>

</body>
</html>
