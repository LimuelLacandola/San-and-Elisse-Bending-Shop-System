<?php
session_start();

// Include database connection
require_once 'db_connection.php';

// Check if the token is provided in the URL
if (!isset($_GET['token'])) {
    echo 'Token not provided. Please use the password reset link from your email.';
    exit;
}

$token = $_GET['token'];

// Fetch the user associated with the provided token
$query = "SELECT email FROM password_reset WHERE token = ? AND expiration_time > ?";
$stmt = $conn->prepare($query);
$currentTime = time();
$stmt->bind_param('si', $token, $currentTime);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo 'Invalid or expired token. Please request a new password reset.';
    exit;
}

$row = $result->fetch_assoc();
$email = $row['email'];

// Handle password reset form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        echo 'Passwords do not match. Please try again.';
        exit;
    }

    // Update the user's password in the database
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateQuery = "UPDATE login SET password = ? WHERE email = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param('ss', $hashedPassword, $email);

    if ($updateStmt->execute()) {
        // Password updated successfully
		
		echo '
<!DOCTYPE html>
<html lang="en">
<head>
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
<body>
</body>
<script>
    Swal.fire({
        icon: "success",
        title: "Password Reset Successful",
        html: "You can now <a href=\"login.php\">login</a> with your new password.",
        showCancelButton: false,
        showConfirmButton: false,
        allowOutsideClick: true,
        allowEscapeKey: false,
        allowEnterKey: false,
        footer: "<a href=\"login.php\">Back to Login</a>"
    });
</script>';


       // echo 'Password reset successful. You can now <a href="login.php">login</a> with your new password.';
        // Optionally, you may want to delete the used token from the password_reset table.
    } else {
        echo 'Error updating password. Please try again.';
    }

    $updateStmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
body {
    font-family: 'Arial', sans-serif;
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

label {
    margin-top: 10px;
    font-size: 16px;
    color: #333;
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
    background-color: #4caf50;
    color: #fff;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

button:disabled {
    background-color: #ddd; /* Change the color for the disabled state */
    color: #888; /* Change the text color for the disabled state */
    cursor: not-allowed; /* Change the cursor for the disabled state */
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

    </style>
      <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Get references to the password input fields and the submit button
            var newPasswordInput = document.querySelector('input[name="new_password"]');
            var confirmPasswordInput = document.querySelector('input[name="confirm_password"]');
            var submitButton = document.querySelector('button[type="submit"]');

            // Function to check if passwords match and enable/disable the button accordingly
            function checkPasswordMatch() {
                var newPassword = newPasswordInput.value;
                var confirmPassword = confirmPasswordInput.value;

                submitButton.disabled = newPassword !== confirmPassword;
            }

            // Add event listeners to the password input fields to check for changes
            newPasswordInput.addEventListener("input", checkPasswordMatch);
            confirmPasswordInput.addEventListener("input", checkPasswordMatch);

            // Initially disable the button
            checkPasswordMatch();
        });
    </script>
</head>
<body>
    <div class="box-form">
        <h3>Reset Password</h3>
        <form action="reset_password.php?token=<?php echo $token; ?>" method="post">
            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" required>

<button type="submit" disabled>Reset Password</button>
        </form>
        <div>
            <a href="login.php">Back to Login</a>
        </div>
    </div>
  
</body>
</html>
