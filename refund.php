<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

require_once 'db_connection.php';
require_once 'access_control.php';

// Retrieve user information from the login table based on the username
$sql = "SELECT id, user_image, fullname, username, user_role, lockscreen FROM login WHERE username = '{$_SESSION['username']}'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch the user data
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

// Handle returning items
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transactionId = mysqli_real_escape_string($conn, $_POST['transaction_id']);
    
    // Fetch transaction details
    $transactionSql = "SELECT * FROM transaction_history WHERE id = '$transactionId'";
    $transactionResult = $conn->query($transactionSql);

    if ($transactionResult->num_rows > 0) {
        $transactionData = $transactionResult->fetch_assoc();

        // Fetch related items from transaction_product_details table
        $itemsSql = "SELECT * FROM transaction_product_details WHERE transaction_id = '$transactionId'";
        $itemsResult = $conn->query($itemsSql);

        if ($itemsResult->num_rows > 0) {
            // Fetch all items into an array
            $itemsData = array();
            while ($row = $itemsResult->fetch_assoc()) {
                $itemsData[] = $row;
            }

            // Combine transaction details and items data
            $responseData = array(
                'transaction' => $transactionData,
                'items' => $itemsData
            );
        } else {
            $error = "No items found for the given transaction.";
        }
    } else {
        $error = "Transaction not found.";
    }
}


?>

<!DOCTYPE html>
<html>
<head>
    <link rel = "icon" href = "images/saebs_logo.png" type = "image/x-icon"> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <title>Return Item</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            display: flex;
            min-height: 100vh;
            justify-content: center;
            -moz-transform: scale(0.75, 0.75); /* Moz-browsers */
             zoom: 0.75; /* Other non-webkit browsers */
              zoom: 75%; /* Webkit browsers */
        }

        .content {
            flex: 1;
            padding: 20px;
        }

        .navigation {
            background-color: #323031;
            color: #fff;
            width: 250px;
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

        table {
            border-collapse: collapse;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 8px !important; /* Add rounded borders to the table */
            margin-top: 20px; /* Add some space above the table */
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ccc;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f5f5f5;
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

            20%, 25% {
                transform: scale(1.3) rotate(90deg);
            }

            45%, 50% {
                transform: scale(1) rotate(180deg);
            }

            70%, 75% {
                transform: scale(1.3) rotate(270deg);
            }

            95%, 100% {
                transform: scale(1) rotate(360deg);
            }
        }

        .user-info {
            margin-top: auto;
            display: flex;
            align-items: center;
            text-decoration: none; /* Remove underline from the link */
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

        .refund-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 400px;
            max-width: 100%;
            text-align: center;
        }

        .refund-container h2 {
            color: #333;
        }

form {
    display: flex;
    flex-direction: column;
    align-items: center;
}

label {
    margin-top: 15px;
    font-size: 16px;
    color: #555;
}

input,
textarea {
    width: 60%; /* Increase width to 60% for both input and textarea */
    padding: 12px; /* Increase padding for better appearance */
    margin-top: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 14px;
}

textarea {
    resize: vertical;
    height: 120px; /* Increase height for textarea */
}

button {
    background-color: #333;
    color: #fff;
    padding: 14px 24px; /* Adjust padding for better button appearance */
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 15px;
}

button:hover {
    background-color: #555;
}
        
                .scrollable-table-container {
            max-height: 400px; /* Set the maximum height as needed */
            overflow: auto;
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
      
              .content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .item-details {
            width: 50%; /* Adjust the width as needed */
			margin-top:100px;

        }

        .return-form {
            width: 50%; /* Adjust the width as needed */
              margin-top:100px;

        }

        /* Add media query for responsive layout */
        @media (max-width: 768px) {
            .content {
                flex-direction: column;
            }

            .item-details,
            .return-form {
                width: 100%;
            }
        }
      


        .centered-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 400px;
            max-width: 100%;
            text-align: center;
          	margin-left: 50%;
          	margin-top: 20%;
        }
      #submit, #returnHistoryButton{
        border-radius: 50%;
        padding: 10px;
      }
    </style>
</head>
<body>

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

          <div class="item-details">


    <?php
if (isset($error)) {
    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    echo "<script>
            Swal.fire({
                title: 'Error!',
                text: '$error',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'refund.php';
            });
          </script>";
} elseif (isset($transactionData)) {
        ?>
        <!-- Display transaction details -->
		<strong><p>Transaction ID:</strong> <?php echo $transactionData['id']; ?></p>
        <strong><p>Transaction Date:</strong> <?php echo $transactionData['transaction_date']; ?></p>
        <strong><p>Customer Name:</strong> <?php echo $transactionData['customer_name']; ?></p>
  		<strong><p>Total Price:</strong> <?php echo $transactionData['total_price']; ?></p>
  
  <?php
if (isset($itemsData) && count($itemsData) > 0) {
    // Display product names
    echo "<strong><p>Products</p></strong>";
    echo "<ul>";
    foreach ($itemsData as $item) {
        echo "<li>" ."<strong>Product ID:</strong> ". $item['product_id'] ."<br>". $item['product_name'] . "<br>"." <strong>Quantity: </strong>" . $item['quantity'] . " pc(s)" . "</li><br>";
    }
    echo "</ul>";
} else {
    echo "<p>No items found for the given transaction.</p>";
}
?>
  </div>
          <div class="return-form">
        <!-- Add the return form -->
        <form method="post" action="process_return.php">
            <input type="hidden" name="transaction_id" value="<?php echo $transactionData['id']; ?>">

            <label for="item_id"><strong>Item ID:</strong></label>
            <input type="text" id="item_id" name="item_id" required>

            <label for="quantity"><strong>Quantity to Return:</strong></label>
            <input type="number" id="quantity" name="quantity" min="1" required>

            <label for="reason"><strong>Reason for Return:</strong></label>
            <textarea id="reason" name="reason" rows="4" required></textarea>

            <button type="submit" class="submit">Return</button>
        </form>
              </div>


        <?php
    } else {
        ?>
        <!-- Add the form to enter transaction ID for fetching details -->
<div class="centered-form-container">
    <div class="centered-form">
        <form method="post" action="">
          	<img src="images/return_item.png" style="max-width: 150px; margin-left: 20px;">
            <label for="transaction_id"><strong>Enter Transaction ID:</strong></label>
            <input type="text" id="transaction_id" name="transaction_id" required>
<div style="display: flex;">
    <button type="submit" id="submit" title="Find Transaction" style="margin-right: 10px;">
        <i class="material-icons" title="Search Transaction">search</i>
    </button>
    <button id="returnHistoryButton" title="Return History">
        <i class="material-icons" title="Transaction History">history</i>
    </button>
</div>

        </form>
    </div>
</div>

        <?php
    }
    ?>
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
    // Function to display simple success message
    function showSuccessMessage(message) {
        alert('Success!\n' + message);
    }

    // Function to display simple error message
    function showErrorMessage(message) {
        alert('Error!\n' + message);
    }
</script>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
          
          
var returnHistoryButton = document.getElementById("returnHistoryButton");
    returnHistoryButton.addEventListener("click", function() {
        window.location.href = "return_history.php";
});


      </script>
 

</body>
</html>
