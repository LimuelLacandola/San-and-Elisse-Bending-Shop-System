<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

require_once 'db_connection.php';

$userId = $_GET['id'];

$query = "SELECT * FROM login WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Display user information in the modal
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
      	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Information</title>

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

            body {
            margin: 0;
            display: flex;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            -moz-transform: scale(0.75, 0.75); /* Moz-browsers */
             zoom: 0.75; /* Other non-webkit browsers */
              zoom: 75%; /* Webkit browsers */
            }

            h2 {
                color: #333;
            }

            p {
                margin-bottom: 10px;
            }

            strong {
                font-weight: bold;
            }

            img {
                max-width: 100px;
                max-height: 100px;
                margin-top: 10px;
            }

            .flex {
                display: flex;
                gap: 10px;
                justify-content: center;
                margin-top: 20px;
            }

            .editButton{ 
                padding: 10px;
                background-color: #333;
                color: #fff;
                border: none;
                border-radius: 50%;
                cursor: pointer;
                transition: background-color 0.3s;
            }
          
          	.deactivateButton{
 				padding: 10px;
                background-color: #F46D75;
                color: #fff;
                border: none;
                border-radius: 50%;
                cursor: pointer;
                transition: background-color 0.3s;
          }
          
				.activateButton{
 				padding: 10px;
                background-color: #76BA1B;
                color: #fff;
                border: none;
                border-radius: 50%;
                cursor: pointer;
                transition: background-color 0.3s;
          }
          
          

            .editButton:hover{ 
                background-color: #555;
            }
          
          .deactivateButton:hover{ 
                background-color: #f7989e;
            }
          
          .activateButton:hover{ 
                background-color: #9fce5f;
            }
        </style>
    </head>
    <body>
        <h2>User Information</h2>
        <p><strong>ID:</strong> <?= $user['id'] ?></p>
        <p><strong>Full Name:</strong> <?= $user['fullname'] ?></p>
        <p><strong>Username:</strong> <?= $user['username'] ?></p>
        <p><strong>User Role:</strong> <?= $user['user_role'] ?></p>
        <p><strong>Email:</strong> <?= $user['email'] ?></p>
        <p><strong>User Image:</strong> <br><img src="<?= $user['user_image'] ?>" alt="User Image"></p>



<?php
if ($_SESSION['user_role'] === 'admin') {
    echo '<div class="flex">';
echo '<button class="editButton" title="Edit user" data-user-id="' . $user['id'] . '"><i class="material-icons">edit</i></button>';
    
    // Assuming $user['account_status'] contains the account status from the database
    if ($user['account_status'] === 'active') {
	echo '<button class="deactivateButton" title="Deactivate user" onclick="deactivateUser(' . $userId . ')"><i class="material-icons">lock</i></button>';
    } elseif ($user['account_status'] === 'deactivated') {
	echo '<button class="activateButton" title="Activate user" onclick="activateUser(' . $userId . ')"><i class="material-icons">lock_open</i></button>';
    }

    echo '</div>';
} elseif ($_SESSION['user_role'] === 'employee' || $_SESSION['user_role'] === 'cashier') {
    // No action for employees and cashiers (you can add specific employee/cashier logic here)
} else {
    echo "Invalid user role.";
}
?>


        <script>
            // Function to handle deactivate button click
            function deactivateUser(id) {
                // Implement the logic to deactivate the user with the given id
                // You can confirm the deactivation and send an AJAX request to update the account_status
                if (confirm(`Are you sure you want to deactivate user with ID: ${id}?`)) {
                    // Send an AJAX request to update the account_status to "deactivated"
                    fetch(`deactivate_user.php?id=${id}`, { method: 'POST' })
                        .then(response => {
                            if (response.status === 200) {
                                // User deactivated successfully
                                alert(`User with ID: ${id} has been deactivated successfully.`);
                                // Redirect or handle as needed
                            } else {
                                // Handle errors
                                alert(`Error deactivating user with ID: ${id}`);
                            }
                        })
                        .catch(error => {
                            console.error('Error deactivating user:', error);
                        });
                }
            }
        </script>
      
             <script>
            // Function to handle deactivate button click
            function activateUser(id) {
                // Implement the logic to deactivate the user with the given id
                // You can confirm the deactivation and send an AJAX request to update the account_status
                if (confirm(`Are you sure you want to activate user with ID: ${id}?`)) {
                    // Send an AJAX request to update the account_status to "deactivated"
                    fetch(`activate_user.php?id=${id}`, { method: 'POST' })
                        .then(response => {
                            if (response.status === 200) {
                                // User deactivated successfully
                                alert(`User with ID: ${id} has been activated successfully.`);
                                // Redirect or handle as needed
                            } else {
                                // Handle errors
                                alert(`Error activating user with ID: ${id}`);
                            }
                        })
                        .catch(error => {
                            console.error('Error activating user:', error);
                        });
                }
            }
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
