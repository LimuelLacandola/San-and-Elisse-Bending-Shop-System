<?php
include('db_connection.php');
require 'PHPMailer/PHPMailerAutoload.php';

echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="images/saebs_logo.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <meta charset="UTF-8">
    <style>
        /* Custom CSS for SweetAlert with Poppins font */
        .swal2-title,
        .swal2-text,
        .swal2-modal {
            font-family: "Poppins", sans-serif !important;
        }

        /* Additional styles can be added here */
    </style>
</head>
<body>';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['selected_items'], $_POST['request_quantity'])) {
        $selectedItems = $_POST['selected_items'];
        $requestQuantity = $_POST['request_quantity'];

        // Fetch selected items details including supplier_email
        $sql = "SELECT id, productname, supplier_email FROM inventory WHERE id IN (" . implode(',', $selectedItems) . ")";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $emails = array(); // Array to store unique emails

            while ($row = $result->fetch_assoc()) {
                $to = $row['supplier_email'];
                $itemName = $row['productname'];
                $quantity = $requestQuantity[$row['id']];
                $itemId = $row['id'];

                // If the supplier is not yet in the array, create a new entry
                if (!isset($emails[$to])) {
                    $emails[$to] = array(
                        'subject' => "Stock Request",
                        'products' => array(),
                    );
                }

                // Add product information to the array for the specific supplier
                $emails[$to]['products'][] = array(
                    'itemName' => $itemName,
                    'quantity' => $quantity,
                    'itemId' => $itemId,
                );
            }

            // Create a new PHPMailer instance
            $mail = new PHPMailer;

            foreach ($emails as $to => $info) {
                try {
                    // Server settings
                    $mail->SMTPDebug = 0; // Set to 2 for debugging
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'safalaga.bsinfotech@gmail.com';
                    $mail->Password = 'yrseezbcsgplmkdl';
                    $mail->SMTPSecure = 'tls'; // Change to 'ssl' if needed
                    $mail->Port = 587; // Change accordingly

        // Recipients
        $mail->setFrom('safalaga.bsinfotech@gmail.com', 'San and Elisse Bending');
        $mail->addAddress($to);

        // Constructing a more formal email body
        $emailBody = "
            <p>Dear Supplier,</p>

            <p>
                We hope this message finds you well. We are reaching out to express our need for additional stock in our inventory, and after careful consideration, we believe that your products would be an excellent fit for our requirements. Your consistently high-quality products have made them a preferred choice for our customers.
            </p>

            <p>
                Here are the details of the products we are interested in:
                <ul>
        ";

        foreach ($info['products'] as $product) {
            $emailBody .= "
                <li><strong>Product Name:</strong> {$product['itemName']}</li>
                <li><strong>Quantity Needed:</strong> {$product['quantity']}</li>
                <br>
            ";
        }

        $emailBody .= "
                </ul>
            </p>

            <p>
                Additionally, we kindly request you to provide your preferred delivery date for the products by clicking the following link:
                <a href='https://saeb.lightsolus.xyz/restock_schedule.php?item_ids=" . implode(",", array_column($info['products'], 'itemId')) . "' target='_blank'>Provide Delivery Date</a>
            </p>

            <p>
                We would appreciate it if you could furnish us with a formal quote for the specified products, inclusive of any applicable discounts, shipping costs, and terms of payment. If there are any minimum order quantities or bulk purchase discounts, please inform us accordingly.
            </p>

            <p>
                Please find our shipping address below:
                San and Elisse Bending Shop
                Cabuyao, Laguna
            </p>

            <p>
                Should you have any inquiries or require further clarification, feel free to contact us at safalaga.bsinfotech@gmail.com or 0912 345 6789.
            </p>

            <p>
                We appreciate your prompt attention to this matter and look forward to the opportunity of continuing our positive business relationship.
            </p>

            <p>
                Best regards,<br>
                San and Elisse Bending Shop
            </p>
        ";

        // Add the email body to the PHPMailer instance
        $mail->isHTML(true);
        $mail->Subject = $info['subject'];
        $mail->Body = $emailBody;

        // Send the email
        $mail->send();

        // Update email_status column to "Requested"
        $updateSql = "UPDATE inventory SET email_status = 'Requested' WHERE supplier_email = '$to'";
        $conn->query($updateSql);

                    // Display success alert using JavaScript and redirect to sample_email.php
                    echo "<script>
                            Swal.fire({
                                icon: 'success',
                                title: 'Email sent',
                                text: 'The email has been successfully sent to the suppliers.',
                                showConfirmButton: false,
                                timer: 1000
                            }).then(() => {
                                window.location = 'sample_email.php';
                            });
                          </script>";
                } catch (Exception $e) {
                    // Display error alert using JavaScript and redirect to sample_email.php
                    echo '<script>alert("An error occurred while sending the email: ' . $mail->ErrorInfo . '"); window.location.href = "sample_email.php";</script>';
                }
            }
        } else {
            // Display alert for no items selected using JavaScript and redirect to sample_email.php
            echo "<script>
                    Swal.fire({
                        icon: 'info',
                        title: 'Info',
                        text: 'No items or request quantity selected.',
                        showConfirmButton: false,
                        timer: 1000
                    }).then(() => {
                        window.location = 'sample_email.php';
                    });
                  </script>";
        }
    } else {
        // Display alert for no items or request quantity selected using JavaScript and redirect to sample_email.php
        echo "<script>
                Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: 'No items or request quantity selected.',
                    showConfirmButton: false,
                    timer: 1000
                }).then(() => {
                    window.location = 'sample_email.php';
                });
              </script>";
    }
} else {
    // Display alert for invalid request method using JavaScript and redirect to sample_email.php
    echo "<script>
            Swal.fire({
                icon: 'info',
                title: 'Info',
                text: 'Invalid Request Method.',
                showConfirmButton: false,
                timer: 1000
            }).then(() => {
                window.location = 'sample_email.php';
            });
          </script>";
}

$conn->close();
?>
