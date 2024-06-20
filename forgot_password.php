<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Include database connection
require_once 'db_connection.php';
require 'PHPMailer/PHPMailerAutoload.php';

echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel = "icon" href = "images/saebs_logo.png" type = "image/x-icon"> 
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <meta charset="UTF-8">
    <title>Login</title>
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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if the email exists in the login table
    $checkEmailQuery = "SELECT COUNT(*) as count FROM login WHERE email = ?";
    $stmtCheckEmail = $conn->prepare($checkEmailQuery);
    $stmtCheckEmail->bind_param('s', $email);
    $stmtCheckEmail->execute();
    $result = $stmtCheckEmail->get_result();
    $count = $result->fetch_assoc()['count'];

    // Close the statement
    $stmtCheckEmail->close();

    if ($count > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(32));

        // Save the token and email in the database
        $insertTokenQuery = "INSERT INTO password_reset (email, token, expiration_time) VALUES (?, ?, ?)";
        $expirationTime = time() + 240; // Token expiration time (e.g., 4 minutes for testing)
        $stmt = $conn->prepare($insertTokenQuery);
        $stmt->bind_param('ssi', $email, $token, $expirationTime);

        if ($stmt->execute()) {
            // Send an email with the password reset link
            sendResetEmail($email, $token);

        echo '<script>
            Swal.fire({
                icon: "success",
                title: "Password reset link sent!",
                text: "Check your email for the reset link.",
                confirmButtonText: "OK"
            }).then(function() {
                window.location.href="login.php";
            });
        </script>';
    } else {
        // Display a SweetAlert error message and redirect the user to login.php
        echo '<script>
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Unable to save token to the database.",
                confirmButtonText: "OK"
            }).then(function() {
                window.location.href="login.php";
            });
        </script>';
    }

        $stmt->close();
    } else {
        // Email is not registered
        echo 'The email is not registered';
    }
}

// Function to send email with token
function sendResetEmail($email, $token) {
    global $conn;

    $mail = new PHPMailer(true);

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
        $mail->setFrom('safalaga.bsinfotech@gmail.com', 'San and Elisse Bending Shop'); // Change to your email and name
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset';
        $mail->Body = 'Click the following link to reset your password: <a href="https://localhost/public_html/reset_password.php?token=' . $token . '">Reset Password</a>';
        $mail->AltBody = 'Click the following link to reset your password: https://localhost/public_html/reset_password.php?token=' . $token;

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="images/saebs_logo.png" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  	
    <title>Forgot Password</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .box-form {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }

        h3 {
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        button {
            background-color: #323031;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #595557;
        }

        a {
            color: #333;
            text-decoration: none;
            margin-top: 10px;
            display: inline-block;
        }

        a:hover {
            text-decoration: underline;
        }

        button:disabled {
            background-color: #aaaaaa;
            /* Change the color for disabled state */
            cursor: not-allowed;
        }

        /* Add this style for the validation message */
        #validation-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="box-form">
    <h3>Forgot Password?</h3>
    <!-- Add the validation message div -->
    <div id="validation-message"></div>
    <form action="forgot_password.php" method="post">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Reset Password</button>
    </form>
    <a href="login.php">Back to Login</a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Get the email input, reset button, and validation message
        const emailInput = document.querySelector('input[name="email"]');
        const resetButton = document.querySelector('button[type="submit"]');
        const validationMessage = document.getElementById('validation-message');

        // Disable the reset button by default
        resetButton.disabled = true;

        // Add an input event listener to check the email and enable/disable the button
        emailInput.addEventListener('input', function () {
            const enteredEmail = emailInput.value;

            // Your AJAX request to check if the email exists in the login table
            fetch('verify_email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'email=' + encodeURIComponent(enteredEmail),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        resetButton.disabled = false;
                        validationMessage.innerText = ''; // Clear the validation message
                    } else {
                        resetButton.disabled = true;
                        validationMessage.innerText = 'The email is not registered'; // Display the validation message
                    }
                })
                .catch(error => {
                    console.error('Error checking email:', error);
                });
        });
    });
</script>
</body>
</html>
