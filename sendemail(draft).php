<?php
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
?>