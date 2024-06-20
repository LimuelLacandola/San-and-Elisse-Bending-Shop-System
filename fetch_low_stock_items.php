<?php

header('Content-Type: application/json');

// Database connection
require_once 'db_connection.php';

// Query to retrieve low stock items (quantity less than 20)
$query = "SELECT id, productname, category, quantity, price FROM inventory WHERE quantity <= 20";
$result = $conn->query($query);

$lowStockItems = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Create an associative array for each low stock item
        $item = array(
            'id' => $row['id'],
            'productname' => $row['productname'],
            'category' => $row['category'],
            'quantity' => $row['quantity'],
            'price' => $row['price']
        );
        // Add the item to the lowStockItems array
        $lowStockItems[] = $item;
    }
}

// Close the database connection
$conn->close();

// Check if there are low stock items
if (!empty($lowStockItems)) {
    require 'PHPMailer/PHPMailerAutoload.php';

    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls'; // Use 'tls' instead of 'ssl'
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587; // Port for TLS
    $mail->isHTML();
    $mail->Username = 'lacandolalimuelfelisan@gmail.com';
    $mail->Password = 'gbyrytrzbwqtebky'; // Replace with your Gmail App Password
    $mail->SetFrom('lacandolalimuelfelisan@gmail.com');
    $mail->Subject = 'Low Stock!';
    
    // Construct the email body with low stock items
    $emailBody = 'The following items are low on stock and need to be restocked:<br>';
    foreach ($lowStockItems as $item) {
        $emailBody .= "Product: {$item['productname']}, Quantity: {$item['quantity']}<br>";
    }
    
    $mail->Body = $emailBody;
    $mail->AddAddress('lacandola.l.bsinfotech@gmail.com');
    
    if ($mail->Send()) {
        echo "Email sent successfully.";
    } else {
        echo "Email sending failed: " . $mail->ErrorInfo;
    }
}

// Send the low stock items as JSON response
echo json_encode($lowStockItems);
?>
