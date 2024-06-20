<?php
require_once 'db_connection.php';
require 'PHPMailer/PHPMailerAutoload.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['itemId']) && isset($_POST['itemName'])) {
    $itemId = $_POST['itemId'];
    $itemName = $_POST['itemName'];
    $quantity = $_POST['quantity'];

    // Update the email status for the item
    $updateEmailStatusQuery = "UPDATE inventory SET email_status = 'Requested' WHERE id = $itemId";
    $updateEmailStatusResult = $conn->query($updateEmailStatusQuery);

    // Fetch supplier email from the inventory table
    $supplierEmailQuery = "SELECT supplier_email FROM inventory WHERE id = $itemId";
    $supplierEmailResult = $conn->query($supplierEmailQuery);

    if ($supplierEmailResult->num_rows > 0) {
        $supplierEmailRow = $supplierEmailResult->fetch_assoc();
        $supplierEmail = $supplierEmailRow['supplier_email'];

 // Send an email to the supplier
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
$mail->Subject = 'Product Request: ' . $itemName;

// Construct the email body for product request
$emailBody = "
    <p>Dear $supplierEmail,</p>

    <p>
        We are currently in need of additional stock for our inventory, and after reviewing our requirements, we believe that your <strong>$itemName</strong> would be an excellent fit for our needs. Your products have consistently met our expectations for quality and performance, making them a preferred choice for our customers.
    </p>

    <p>
        Here are the details of the products we are interested in:
        <ul>
            <li><strong>Product Name:</strong> $itemName</li>
            <li><strong>Quantity Needed:</strong> $quantity</li>
        </ul>
    </p>

    <p>
        Additionally, please provide your preferred delivery date for the products by clicking the following link:
        <a href='https://saeb.lightsolus.xyz/restock_schedule.php?item_id=$itemId' target='_blank'>Provide Delivery Date</a>
    </p>

    <p>
        We would appreciate it if you could provide us with a formal quote for the specified products, including any applicable discounts, shipping costs, and terms of payment. If there are any minimum order quantities or bulk purchase discounts, please let us know.
    </p>

    <p>
        Please find our shipping address below:
        San and Elisse Bending Shop
        Cabuyao, Laguna
    </p>

    <p>
        If you have any questions or require further clarification, feel free to contact me at safalaga.bsinfotech@gmail.com or 0912 345 6789.
    </p>

    <p>
        Thank you for your prompt attention to this matter. We look forward to continuing our positive business relationship and appreciate your efforts to fulfill our request.
    </p>

    <p>
        Best regards,<br>
        San and Elisse Bending Shop
    </p>
";

$mail->Body = $emailBody;

// Add supplier's email address as a recipient
$mail->AddAddress($supplierEmail);

        // Send the email
        if ($mail->Send()) {
          $updateEmailStatusResult;
     echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "Request email sent successfully!",
                            icon: "success"
                        }).then(() => {
                            window.location.href = "restock.php";
                        });
                     </script>';
        } else {
     echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Failed to send request email",
                            icon: "error"
                        }).then(() => {
                            window.location.href = "restock.php";
                        });
                     </script>';
        }
    } else {
     echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "No supplier on the current item",
                            icon: "error"
                        }).then(() => {
                            window.location.href = "restock.php";
                        });
                     </script>';
    }
} else {
     echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Invalid request",
                            icon: "error"
                        }).then(() => {
                            window.location.href = "restock.php";
                        });
                     </script>';
}

$conn->close();
?>
