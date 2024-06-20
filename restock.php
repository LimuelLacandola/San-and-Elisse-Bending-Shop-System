<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once 'access_control.php';



require_once 'db_connection.php';

$sql = "SELECT id, user_image, fullname, username, user_role, lockscreen FROM login WHERE username = '{$_SESSION['username']}'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $userData = $result->fetch_assoc();


    // Check access control for cashier
    restrictAccessToCashier($_SESSION['user_role']);
} else {
    echo "User not found";
}

	// Pass lockscreen status to JavaScript
echo '<script>';
echo 'var lockscreenStatus = "' . $userData['lockscreen'] . '";';
echo '</script>';

$currentDate = date("Y-m-d");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel = "icon" href = "images/saebs_logo.png" type = "image/x-icon"> 
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


    <title>Restock</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

        body {
            margin: 0;
            display: flex;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
   -moz-transform: scale(0.75, 0.75);
    zoom: 0.75;
    zoom: 75%;
        }

        .content {
            flex: 1;
            padding: 20px;
            margin-top: 50px;
          	overflow-y: auto;
        }

          .navigation {
            background-color: #323031;
            color: #fff;
            width: 300px;
            height: 1000px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            transition: width 0.5s ease; /* Add transition for smoother resizing */

            
        }

        .navigation a {
            color: #fff;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
            padding: 10px; /* Add padding for better hover effect */
    border-radius: 8px; /* Add border radius */
    transition: background-color 0.3s ease, color 0.3s ease; /* Add transitions for background and text color */
        }
        
        .navigation a:not(:last-child) {
            margin-bottom: 10px;
        }
        .navigation a:hover {
                background-color: #cccccc; /* Change background color on hover */
    color: #333; /* Change text color on hover */
    text-decoration: none; /* Remove underline on hover */
        }
        
        .navigation i {
    margin-right: 10px; /* Adjust the space between icon and text */
        vertical-align: middle; /* Align the icon vertically in the middle */
        margin-bottom: 8px;

}

.card-container {
    display: flex;
    flex-wrap: wrap;
}

.card {
    border: 1px solid #ccc;
    padding: 15px;
    margin-right: 20px;
    margin-bottom: 20px;
    background-color: #f9f9f9;
    border-radius: 5px;
    width: 200px;
    overflow: hidden;
}

.card h3 {
    margin-top: 0;
    font-size: 16px; /* Adjust the font size as needed */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.card p {
    margin: 0;
    margin-bottom: 10px;
}

.card button {
    background-color: #333;
    color: #fff;
    border: none;
    border-radius: 3px;
    padding: 8px 16px;
    cursor: pointer;
}

.card button:hover {
    background-color: #666;
}


        .loader-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            display: none;
        }

        .loader {
            --dim: 3rem;
            width: var(--dim);
            height: var(--dim);
            position: relative;
            animation: spin988 2s linear infinite;
        }

        .loader .circle {
            --color: #333;
            --dim: 1.2rem;
            width: var(--dim);
            height: var(--dim);
            background-color: var(--color);
            border-radius: 50%;
            position: absolute;
        }

        .loader .circle:nth-child(1) {
            top: 0;
            left: 0;
        }

        .loader .circle:nth-child(2) {
            top: 0;
            right: 0;
        }

        .loader .circle:nth-child(3) {
            bottom: 0;
            left: 0;
        }

        .loader .circle:nth-child(4) {
            bottom: 0;
            right: 0;
        }

        @keyframes spin988 {
            0% {
                transform: scale(1) rotate(0);
            }

            20%,
            25% {
                transform: scale(1.3) rotate(90deg);
            }

            45%,
            50% {
                transform: scale(1) rotate(180deg);
            }

            70%,
            75% {
                transform: scale(1.3) rotate(270deg);
            }

            95%,
            100% {
                transform: scale(1) rotate(360deg);
            }
        }

        .user-info {
            margin-top: auto;
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #fff;
        }

        .user-info img {
            width: 30px;
            height: 30px;
            margin-right: 10px;
            border-radius: 50%;
        }

        #logoLink img {
        width: 250px; /* Adjust the width as needed */
        margin-bottom: 20px; /* Adjust this value to fine-tune the alignment */
		margin-left: -20px;
        }

      .modal-backdrop {
    -moz-transform: scale(1.33, 1.33);
    zoom: 1.33;
    zoom: 133%;
}
      

      
.btn-primary.disabled, .btn-primary:disabled{
  
  color: #fff;
    background-color: #d3d3d3;
    border-color: #d3d3d3;
  pointer-events: none;
      }
     
                  body.locked {
            overflow: hidden;
          }

        #lockscreen {
          display: none;
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(0, 0, 0, 0.8);
          color: #333;
          justify-content: center;
          align-items: center;
          text-align: center;
          font-size: 18px; /* Decreased font size for better readability */
          z-index: 9999;
        }

        #lockModal {
          display: none;
          background: #fff; /* Changed background color to white */
          padding: 20px;
          border-radius: 10px; /* Increased border radius for a softer look */
          text-align: center;
          z-index: 10000;
          box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Added box shadow for a subtle lift */
        }

        #unlockButton {
          padding: 12px; /* Adjusted padding for better button appearance */
          background-color: #333;
          color: #fff;
          border: none;
          border-radius: 5px;
          cursor: pointer;
          font-size: 16px; /* Decreased font size for better fit */
          transition: background-color 0.3s ease, color 0.3s ease;
        }

        #unlockButton:hover {
          background-color: #999; /* Darker shade on hover */
        }
     
.supplier {
    text-decoration: none;
    color: #333; /* Change the color as needed */
    position: fixed;
    bottom: 1115px;
    right: -550px;
    z-index: 1; /* Set a higher z-index to make sure it overlaps other content */
}

.supplier i {
    font-size: 50px; /* Adjust the font size as needed */
}

/* Optional: If you want to style the icon when hovered */
.supplier:hover i {
    color: #555; /* Change the hover color as needed */
}

    </style>
    <script>
$(document).ready(function () {
    // Function to handle restock button click
$('.restock-btn').click(function () {
    // Get the item details from the card
    var itemId = $(this).data('itemid');
    var itemName = $(this).data('itemname');

    // Open the restock modal
    $('#restockModal').modal('show');

    // Set the item details in the modal form
    $('#restockItemId').val(itemId);
    $('#modalItemName').text('Item Name: ' + itemName); // Update the element to display the full name
});


    // Function to handle confirm restock button click
    $('#confirmRestockBtn').click(function () {
        // Get the restock quantity from the input field
        var restockQuantity = $('#restockQuantity').val();

        // Check if the quantity is valid (you may add additional validation)
        if (restockQuantity > 0) {
            // Submit the restock form
            $('#restockForm').submit();
        } else {
            // Display an error message or take appropriate action
            alert('Please enter a valid restock quantity.');
        }
    });
  

});

    </script>
</head>

<body>

    <div class="loader-container" id="loaderContainer">
        <div class="loader">
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="circle"></div>
        </div>
    </div>
    
<div class="navigation">
    <a href="index.php" id="logoLink">
        <img src="images/saebslogo.png" alt="Logo">
    </a>
    <a href="index.php"><i class="material-icons">home</i> Home</a>

    <?php
    if ($_SESSION['user_role'] === 'admin') {
        echo '<a href="inventory.php"><i class="material-icons">handyman</i> Inventory</a>';
        echo '<a href="notification.php"><i class="material-icons">notifications</i>Notifications</a>';
        echo '<a href="pos.php"><i class="material-icons">point_of_sale</i> Point of Sale</a>';
        echo '<a href="transaction_history.php"><i class="material-icons">receipt_long</i> Transaction History</a>';
        echo '<a href="refund.php"><i class="material-icons">history</i> Returns</a>';
        echo '<a href="restock.php"><i class="material-icons">inventory</i> Low Stock Items</a>';
       	echo '<a href="restock_history.php"><i class="material-icons">manage_history</i> Restock History</a>';
        echo '<a href="supplier.php"><i class="material-icons">local_shipping</i> Supplier</a>';
        echo '<a href="view_sessions.php"><i class="material-icons">access_time</i> Audit Logs</a>';
      	echo '<a href="action_log.php"><i class="material-icons">manage_accounts</i> Action Logs</a>';
      	echo '<a href="employee_list.php"><i class="material-icons">groups</i> User Accounts</a>';
    } elseif ($_SESSION['user_role'] === 'frontdesk') {
        echo '<a href="inventory.php"><i class="material-icons">handyman</i> Inventory</a>';
        echo '<a href="notification.php"><i class="material-icons">notifications</i>Notifications</a>';
        echo '<a href="transaction_history.php"><i class="material-icons">receipt_long</i> Transaction History</a>';
        echo '<a href="restock.php"><i class="material-icons">inventory</i> Low Stock Items</a>';
        echo '<a href="refund.php"><i class="material-icons">history</i> Returns</a>';

    } elseif ($_SESSION['user_role'] === 'cashier') {
        echo '<a href="pos.php"><i class="material-icons">point_of_sale</i> Point of Sale</a>';
        echo '<a href="transaction_history.php"><i class="material-icons">receipt_long</i> Transaction History</a>';
    } else {
        echo "Invalid user role.";
    }
    ?>

    <a href="logout.php" id="logoutLink"><i class="material-icons">logout</i> Logout</a>
</div>

    <div class="content">
        <h2>Low stock items</h2>
        <div class="card-container">

            <?php
          	echo '<a href="sample_email.php" class="supplier"><i class="material-icons">manage_supplier</i></a>';
            require_once 'db_connection.php';

$query = "SELECT * FROM inventory WHERE quantity <= lowstock_trigger";
$result = $conn->query($query);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="card">';
	       			echo '<img src="' . $row['image_url'] . '" alt="' . $row['productname'] . '" style="max-width: 100%; max-height: 150px;">';

                    echo '<h3>' . $row['productname'] . '</h3>';
                    echo '<p>Category: ' . $row['category'] . '</p>';
                    echo '<p>Quantity: ' . $row['quantity'] . '</p>';
                    echo '<p>Price: ' . $row['price'] . '</p>';
					echo '<p>Delivery Date: ' . $row['delivery_date'] . '</p>';
// Check the value of email_status for the item
//if ($row['email_status'] === 'Requested') {
    // If email_status is "Requested", make the button unclickable with a custom class
 //   echo '<button class="btn btn-primary sendemail-btn disabled-btn" data-itemid="' . $row['id'] . '" data-itemname="' . $row['productname'] . '" disabled>Send Email (Requested)</button><br>';
//} else {
    // If email_status is not "Requested", allow the button to be clicked
 //   echo '<button class="btn btn-primary sendemail-btn" data-itemid="' . $row['id'] . '" data-itemname="' . $row['productname'] . '">Send Email</button><br>';
//}
// Check if delivery_date is null or has no date value
if (empty($row['delivery_date']) || $row['delivery_date'] == '0000-00-00') {
    // If delivery_date is null or has no date value, disable the Restock button
    echo '<button class="btn btn-primary restock-btn" data-itemid="' . $row['id'] . '" data-itemname="' . $row['productname'] . '" disabled>Restock</button>';
} elseif ($currentDate < $row['delivery_date']) {
    // If current date is before the delivery date, disable the Restock button
    echo '<button class="btn btn-primary restock-btn" data-itemid="' . $row['id'] . '" data-itemname="' . $row['productname'] . '" disabled>Restock</button>';
} else {
    // If delivery_date is past or today, allow the Restock button to be clicked
    echo '<button class="btn btn-primary restock-btn" data-itemid="' . $row['id'] . '" data-itemname="' . $row['productname'] . '">Restock</button>';
}					
                    echo '</div>';
                }
            } else {
                echo '<p>No low stock items found.</p>';
            }

            $conn->close();
            ?>
        </div>
    </div>

  
  
  
    <!-- Restock Modal -->
    <div class="modal" id="restockModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Restock Quantity</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal Body -->
<div class="modal-body">
    <p id="modalItemName"></p>
    <form method="post" action="restock_process.php" id="restockForm">
        <input type="hidden" name="item_id" id="restockItemId" value="">
        <label for="restockQuantity">Enter Quantity:</label>
        <input type="number" name="restock_quantity" id="restockQuantity" class="form-control" required>
    </form>
</div>


<!-- Modal Footer -->
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-primary" id="confirmRestockBtn">Confirm</button>
</div>

            </div>
        </div>
    </div>
  
  
  <div class="modal" id="sendEmailModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Request Stock Email</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <p id="modalSendEmailItemName"></p>
                <form method="post" action="send_low_stock_email.php" id="sendEmailForm">
                    <input type="hidden" name="item_id" id="sendEmailItemId" value="">
                    <label for="sendEmailQuantity">Enter Quantity:</label>
                    <input type="number" name="send_email_quantity" id="sendEmailQuantity" class="form-control" required>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="confirmSendEmailBtn">Confirm</button>
            </div>

        </div>
    </div>
</div>
  
       <div id="lockscreen">
<div id="lockModal" style="display: none; text-align: center;">
    <img src="images/pagelocked.png" style="height: 256px; width: 256px;">
    <h3>Your session is locked due to inactivity.</h3>
    <p>Please click the button below to unlock.</p>
    <button id="unlockButton">Unlock</button>
</div>
      </div>
        
<div id="passwordModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); color: #333; justify-content: center; align-items: center; text-align: center; font-size: 18px; z-index: 9999;">
    <div style="background: #fff; padding: 20px; border-radius: 10px; text-align: center; z-index: 10000; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); max-width: 400px; margin: 0 auto;">
        <h2 style="margin-bottom: 20px;">Unlock Screen</h2>
        <p style="margin-bottom: 20px;">To unlock, please enter your password:</p>
        <input type="password" id="passwordInput" name="passwordInput" style="width: 80%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px;">
        <button id="passwordSubmitButton" style="background-color: #333; color: #fff; border: none; border-radius: 5px; padding: 10px 20px; cursor: pointer; font-size: 16px;">Submit</button>
    </div>
</div>
           
  
        <script>
document.addEventListener('DOMContentLoaded', function () {
    var idleTimeout = 600000; // 5 seconds of inactivity
    var lockscreen = document.getElementById('lockscreen');
    var lockModal = document.getElementById('lockModal');

    var timeoutId;

  
      // Check lockscreen status and adjust behavior
    if (lockscreenStatus === 'locked') {
        showLockscreen();
    } else {
        startTimer();
    }

  
    function showLockscreen() {
        lockscreen.style.display = 'flex';
        lockModal.style.display = 'block';
        document.body.classList.add('locked');

        // Update lockscreen column in the login table to "locked"
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_lockscreen.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);

                if (!response.success) {
                    console.error('Failed to update lockscreen status in the database.');
                }
            }
        };

        xhr.send();
    }

    function hideLockscreen() {
        lockscreen.style.display = 'none';
        document.body.classList.remove('locked');
        resetTimer();
    }

    function resetTimer() {
        clearTimeout(timeoutId);
        startTimer();
    }

    function startTimer() {
        timeoutId = setTimeout(function () {
            showLockscreen();
        }, idleTimeout);
    }

    document.addEventListener('mousemove', resetTimer);
    document.addEventListener('keypress', resetTimer);
    document.addEventListener('wheel', resetTimer);

    var unlockButton = document.getElementById('unlockButton');

    unlockButton.addEventListener('click', function () {
        // Create a password input modal
        var passwordModal = document.getElementById('passwordModal');
        passwordModal.style.display = 'flex';

        var passwordInput = document.getElementById('passwordInput');
        passwordInput.value = ''; // Clear the input field

        var passwordSubmitButton = document.getElementById('passwordSubmitButton');

        passwordSubmitButton.addEventListener('click', function () {
            var enteredPassword = passwordInput.value;

            if (enteredPassword.trim() === '') {
                alert('Password is required. Please try again.');
                return;
            }

            // Send AJAX request to the server for password verification
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'verify_password.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);

                    if (response.success) {
                        hideLockscreen();
                        passwordModal.style.display = 'none'; // Hide the password modal
                    } else {
                        alert('Incorrect password. Please try again.');
                        passwordInput.value = '';
                    }
                }
            };

            xhr.send('password=' + encodeURIComponent(enteredPassword));
        });
    });

    startTimer(); // Start the timer initially

    // Optionally, you may want to stop the timer when the page unloads
    window.addEventListener('beforeunload', function () {
        clearTimeout(timeoutId);
    });
});

      </script>
<script>
    $(document).ready(function () {
        // Function to handle send email button click
        $('.sendemail-btn').click(function () {
            // Get the item details from the button
            var itemId = $(this).data('itemid');
            var itemName = $(this).data('itemname');

            // Open the restock modal
            $('#sendEmailModal').modal('show');

            // Set the item details in the modal form
            $('#sendEmailItemId').val(itemId);
            $('#modalSendEmailItemName').text('Item Name: ' + itemName);
        });

        // Function to handle confirm send email button click
        $('#confirmSendEmailBtn').click(function () {
            // Get the send email quantity from the input field
            var sendEmailQuantity = $('#sendEmailQuantity').val();

            // Check if the quantity is valid (you may add additional validation)
            if (sendEmailQuantity > 0) {
                // Send an AJAX request to the server to trigger the email
                $.ajax({
                    url: 'send_low_stock_email.php',
                    method: 'POST',
                    data: {
                        itemId: $('#sendEmailItemId').val(),
                        itemName: $('#modalSendEmailItemName').text().replace('Item Name: ', ''),
                        quantity: sendEmailQuantity
                    },
           success: function (response) {
                // Display SweetAlert success message
                Swal.fire({
                    title: 'Success',
                    text: 'Email sent to the supplier for ' + $('#modalSendEmailItemName').text(),
                    icon: 'success',
                }).then((result) => {
                    // Redirect to restock.php after the user clicks "OK"
                    if (result.isConfirmed) {
                        window.location.href = 'restock.php';
                    }
                });
            },
            error: function (error) {
                // Display SweetAlert error message
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to send email. Please try again.',
                    icon: 'error',
                }).then((result) => {
                    // Redirect to restock.php after the user clicks "OK"
                    if (result.isConfirmed) {
                        window.location.href = 'restock.php';
                    }
                });
                console.error('Error sending email:', error);
            }
        });
    } else {
        // Display an error message with SweetAlert and redirect to restock.php
        Swal.fire({
            title: 'Error',
            text: 'Please enter a valid send email quantity.',
            icon: 'error',
        }).then((result) => {
            // Redirect to restock.php after the user clicks "OK"
            if (result.isConfirmed) {
                window.location.href = 'restock.php';
            }
        });
    }
});
      
          // Prevent form submission when pressing Enter in the sendEmailModal form
    $('#sendEmailForm').submit(function (e) {
        e.preventDefault();
    });
    });
</script>
</body>

</html>
