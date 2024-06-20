<?php
date_default_timezone_set('Asia/Manila');

session_start();

// Database connection
require_once 'db_connection.php';
require_once 'access_control.php';
require 'PHPMailer/PHPMailerAutoload.php'; // Include PHPMailer

echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Product</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Customize the font for SweetAlert2 */
        .swal2-popup {
            font-family: \'Poppins\', sans-serif;
        }
    </style>
</head>
<body>';

if (isset($_POST['restock_quantity'])) {
    $itemId = $_POST['item_id'];
    $restockQuantity = $_POST['restock_quantity'];

  
        $updateEmailStatusQuery = "UPDATE inventory SET email_status = ' ' WHERE id = $itemId";
  	$updateEmailStatusResult = $conn->query($updateEmailStatusQuery);
  
    // Validate restock quantity
    if ($restockQuantity > 0) {
        // Perform the restock
        $query = "UPDATE inventory SET quantity = quantity + $restockQuantity WHERE id = $itemId";

        if ($conn->query($query) === TRUE) {
            // Insert a record into the restock_history table
            $productName = getProductName($conn, $itemId);
            $restockDate = date('Y-m-d H:i:s');
            $userRestocked = $_SESSION['username'];

            $insertQuery = "INSERT INTO restock_history (product_name, quantity_restocked, restock_date, user_restocked) VALUES ('$productName', $restockQuantity, '$restockDate', '$userRestocked')";
            
            // After inserting into restock_history, add this code to insert into notification_history
$insertNotificationQuery = "INSERT INTO notification_history (type, product_name, quantity, notification_date, user_triggered) VALUES ('Restock', '$productName', $restockQuantity, '$restockDate', '$userRestocked')";

          
    // Clear the delivery_date value for the specific product being restocked
    $clearDeliveryDateQuery = "UPDATE inventory SET delivery_date = NULL WHERE id = $itemId";
    $clearDeliveryDateResult = $conn->query($clearDeliveryDateQuery);
          
if ($conn->query($insertNotificationQuery) === TRUE) {
    // Notification record inserted successfully
} else {
    echo "Error inserting record into notification_history: " . $conn->error;
}


            if ($conn->query($insertQuery) === TRUE) {
                // Send email to admin
                $mail = new PHPMailer;
                $mail->isSMTP();
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = 'tls';
                $mail->Host = 'smtp.gmail.com';
                $mail->Port = 587;
                $mail->isHTML();
                $mail->Username = 'safalaga.bsinfotech@gmail.com';
                $mail->Password = 'yrseezbcsgplmkdl'; // Replace with your Gmail App Password
                $mail->SetFrom('safalaga.bsinfotech@gmail.com');
                $mail->Subject = 'Restock Alert';

                $currentUser = $_SESSION['username'];
                $currentTime = date('Y-m-d H:i:s');

                $emailBody = "$currentUser restocked $productName with the quantity of $restockQuantity on $currentTime";
                $mail->Body = $emailBody;
                $mail->AddAddress('lacandolalimuel6@gmail.com'); // Replace with the admin's email

                if ($mail->Send()) {
                    $updateEmailStatusResult;
                } else {
                    // Email sending failed to the admin
                    echo "Error sending email to admin: " . $mail->ErrorInfo;
                }

                // Display SweetAlert when restocking is successful
                echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "Restocked Successfully!",
                            icon: "success"
                        }).then(() => {
                            window.location.href = "restock.php";
                        });
                     </script>';
                exit;
            } else {
                echo "Error inserting record into restock_history: " . $conn->error;
            }
        } else {
            echo "Error restocking item: " . $conn->error;
        }
    } else {
        echo "Invalid restock quantity.";
    }
} else {
    // If the form was not submitted properly, redirect to the restock page
    header("Location: restock.php");
    exit;
}

$conn->close();

// Helper function to get product name based on item ID
function getProductName($conn, $itemId) {
    $query = "SELECT productname FROM inventory WHERE id = $itemId";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['productname'];
    } else {
        return "Unknown Product";
    }
}
?>
