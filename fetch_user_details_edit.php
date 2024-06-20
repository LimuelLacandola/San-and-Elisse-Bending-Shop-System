<?php
require_once 'db_connection.php';

$userId = $_GET['id'];

// Prepare and execute the SQL query to fetch user information by ID
$query = "SELECT * FROM login WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Display user information in the modal for editing
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
      	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit User Information</title>

        <!-- Add your existing styles and scripts -->
        <style>
            /* Add your existing styles here */

            .form {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .form-label {
                position: relative;
                margin-bottom: 20px;
            }

            .form-label .input {
                width: 100%;
                padding: 10px;
                outline: 0;
                border: 1px solid rgba(105, 105, 105, 0.397);
                border-radius: 10px;
            }

            .form-label .input + span {
                position: absolute;
                left: 10px;
                top: 15px;
                color: grey;
                font-size: 0.9em;
                cursor: text;
                transition: 0.3s ease;
            }

            .form-label .input:placeholder-shown + span {
                top: 15px;
                font-size: 0.9em;
            }

            .form-label .input:focus + span, .form-label .input:valid + span {
                top: 30px;
                font-size: 0.7em;
                font-weight: 600;
            }

            .form-label .input:valid + span {
                color: #333;
            }

            .submit {
                border: none;
                outline: none;
                background-color: #323031;
                padding: 10px;
                border-radius: 50%;
                color: white;
                font-size: 16px;
                cursor: pointer;
                transition: 0.3s ease;
            }

            .submit:hover {
                background-color: #595557;
            }
        </style>
    </head>
    <body>
        <h2>Edit User Information</h2>
        <form id="editUserForm" action="update_user_details.php" method="post">
            <input type="hidden" name="userId" value="<?= $user['id'] ?>">
            <label for="fullname" class="form-label">
                Full Name:
                <input type="text" id="fullname" name="fullname" value="<?= $user['fullname'] ?>" required class="input">
            </label>
            <label for="username" class="form-label">
                Username:
                <input type="text" id="username" name="username" value="<?= $user['username'] ?>" required class="input">
            </label>
            <label for="user_role" class="form-label">
                User Role:
                <select id="user_role" name="user_role" class="input" required>
                    <option value="admin" <?= ($user['user_role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                    <option value="frontdesk" <?= ($user['user_role'] == 'frontdesk') ? 'selected' : '' ?>>Front Desk</option>
                    <option value="cashier" <?= ($user['user_role'] == 'cashier') ? 'selected' : '' ?>>Cashier</option>
                </select>
            </label>
            <!-- Add additional fields as needed -->

<label for="email" class="form-label">
    Email:
    <input type="text" id="email" name="email" value="<?= $user['email'] ?>" required class="input">
    <div id="email-validation-message" style="color: red;"></div>
</label>

           <button type="submit" class="submit" title="Save"><i class="material-icons">save</i></button>
        </form>

        <!-- Add your existing scripts if needed -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Get the email input, reset button, and validation message
    const emailInput = document.querySelector('input[name="email"]');
    const resetButton = document.querySelector('button[type="submit"]');
    const emailValidationMessage = document.getElementById('email-validation-message');
    
    // Disable the reset button by default
    resetButton.disabled = true;

    // Add an input event listener to check the email and enable/disable the button
    emailInput.addEventListener('input', function () {
        const enteredEmail = emailInput.value;

        // Your AJAX request to check if the email exists in the login table
        fetch('verify_email_already_used.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'email=' + encodeURIComponent(enteredEmail),
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                resetButton.disabled = true;
                emailValidationMessage.innerText = 'The email is already registered'; // Display the validation message
            } else {
                resetButton.disabled = false;
                emailValidationMessage.innerText = ''; // Clear the validation message
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

    <?php
} else {
    echo 'User not found.';
}

$stmt->close();
$conn->close();
?>
