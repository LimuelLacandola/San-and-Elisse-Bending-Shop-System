<?php
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database connection
require_once 'db_connection.php';
require_once 'access_control.php';

          // Check access control for cashier
    restrictAccessToCashier($_SESSION['user_role']);
// Retrieve user information
function getUserData($conn) {
    $stmt = $conn->prepare("SELECT id, user_image, fullname, username, user_role FROM login WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();

    } else {
        echo "User not found";
    }
}

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

echo '<script>';
echo 'var lockscreenStatus = "' . $userData['lockscreen'] . '";';
echo '</script>';

$userData = getUserData($conn);

// Retrieve low stock and danger items in a single query
function getLowDangerItems($conn, $lowStockThreshold, $dangerThreshold) {
    $stmt = $conn->prepare("SELECT * FROM inventory WHERE quantity <= ? OR quantity <= ?");
    $stmt->bind_param("ii", $lowStockThreshold, $dangerThreshold);
    $stmt->execute();
    $result = $stmt->get_result();

    $lowStockItems = [];
    $dangerItems = [];

    while ($row = $result->fetch_assoc()) {
        if ($row['quantity'] <= $lowStockThreshold) {
            $lowStockItems[] = $row;
        } elseif ($row['quantity'] <= $dangerThreshold) {
            $dangerItems[] = $row;
        }
    }

    return ['lowStockItems' => $lowStockItems, 'dangerItems' => $dangerItems];
}

$thresholds = ['lowStock' => 20, 'danger' => 50];
$items = getLowDangerItems($conn, $thresholds['lowStock'], $thresholds['danger']);
$lowStockItems = $items['lowStockItems'];
$dangerItems = $items['dangerItems'];

// Function to insert notification into notification_history
function insertNotification($conn, $type, $productName, $quantity, $userTriggered) {
    // Check if a similar notification already exists
    $stmt = $conn->prepare("SELECT * FROM notification_history WHERE type = ? AND product_name = ? AND quantity = ?");
    $stmt->bind_param("ssi", $type, $productName, $quantity);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Insert the notification only if it doesn't exist
        $notificationDate = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO notification_history (type, product_name, quantity, notification_date, user_triggered) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $type, $productName, $quantity, $notificationDate, $userTriggered);

        if ($stmt->execute() !== TRUE) {
            echo "Error inserting record into notification_history: " . $stmt->error;
        }
    }
}

// Save low stock notifications
foreach ($lowStockItems as $item) {
    insertNotification($conn, 'Low Stock', $item['productname'], $item['quantity'], $_SESSION['username']);
}

// Save danger notifications
foreach ($dangerItems as $item) {
    insertNotification($conn, 'Critical', $item['productname'], $item['quantity'], $_SESSION['username']);
}

// Fetch restock notifications
$sqlRestockNotifications = "SELECT * FROM notification_history WHERE type = 'restock' AND MONTH(notification_date) = MONTH(CURRENT_DATE()) AND YEAR(notification_date) = YEAR(CURRENT_DATE()) ORDER BY notification_date DESC";
$resultRestockNotifications = $conn->query($sqlRestockNotifications);

$conn->close();

echo '<style>';
echo '.low-stock { color: red; }';
echo '.critical { color: orange; }';
echo '.restock { color: green; }';
echo '</style>';
?>
<!DOCTYPE html>
<html>
<head>
    
    <link rel = "icon" href = "images/saebs_logo.png" type = "image/x-icon"> 

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <title>Notification</title>
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
        
        .content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
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


.notification-icon {
    position: fixed;
    top: 10px;
    margin-top: 15px;
    margin-right: 15px;
    right: calc(10px + 30px + 10px); /* Adjust margin as needed */
    font-size: 24px; /* Adjust font size as needed */
    color: black; /* Adjust icon color as needed */
    cursor: pointer; /* Optional: add a pointer cursor for better UX */
}

    .user-info img {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin-left: -10px; /* Adjust the negative margin to make it overlap */
    }
        
            #logoLink img {
        width: 250px; /* Adjust the width as needed */
        margin-bottom: 20px; /* Adjust this value to fine-tune the alignment */
		margin-left: -20px;
    }
    
       #chartCard {
            width: 450px; /* Adjust the width as needed to make it bigger than other cards */
            margin: 40px;
        }

.notifications-container {
    max-height: 1000px !important;
    overflow-y: auto;
}
	
        .notification {
            padding: 10px;
            background-color: #ffffff; /* Adjust the color as needed */
            margin-bottom: 10px;
            border-radius: 5px;
            position: relative;
            padding: 10px;
            border: 1px solid #ddd;
      
        }
        
        .notification .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 16px;
            color: #555;
            font-weight: bold;
            background-color: transparent;
            border: none;
            outline: none;
            padding: 0;
        }

        .notification .close-btn:hover {
            color: #000;
        }

/* Style the container holding the dropdown */
.select-container {
    position: relative;
    display: inline-block;
    width: 150px; /* Adjust width as needed */
  	height: 25px;
}

/* Style the dropdown button */
.select-container select {
    appearance: none;
    -webkit-appearance: none;
    padding: 10px;
    font-size: 30px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #fff;
    cursor: pointer;
    width: 100%; /* Make the dropdown button fill the container width */
    box-sizing: border-box; /* Include padding and border in width calculation */
    height: 100px; /* Adjust height as needed */
}

/* Style the dropdown arrow */
.select-container select::after {
    content: '\25BC';
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
}

/* Style the dropdown options */
.select-container select option {
    padding: 10px;
}

/* Style the selected option */
.select-container select option:checked {
    background-color: #e0e0e0;
    font-weight: bold;
}
      

.low-stock-text {
        color: red;
}

.critical-text {
  		color: orange;
}

.restocked-text {
  		color: green;
}
      
.danger-orange {
    background-color: #fedd9e; /* Adjust the orange color as needed */
    color: #333; /* Adjust text color for visibility */
}

    .success {
        background-color: #77dd77; /* Adjust the green color as needed */
        color: #333; /* Adjust text color for visibility */
    }
    
        .notifications-container {
        max-height: 750px; /* Set the maximum height as needed */
        overflow-y: auto; /* Enable vertical scroll when content exceeds the height */
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
    </style>
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
            <div class="notifications-container">



  
        <h2>Notification</h2>
               <!-- Dropdown for selecting notification type -->
        <label for="notificationType">Select Notification Type:</label>
        <select id="notificationType" class="select-container" onchange="filterNotifications()">
    <option value="all">All Notifications</option>
    <option value="lowstock">Low Stock</option>
    <option value="critical">Critical</option>
    <option value="restocked">Restocked</option>
</select>
        <?php
        // Display low stock items as notifications
        if (!empty($lowStockItems)) {
            foreach ($lowStockItems as $item) {
                echo '<div class="notification" data-type="lowstock">';
                echo '<span class="close-btn" onclick="dismissNotification(this)">&times;</span>';
                echo '<strong class="low-stock-text">Low Stock:</strong> ' . $item['productname'] . ' has ' . $item['quantity'] . ' left.';
                echo '</div>';
            }
        } else {
            echo '<p>No low stock items at the moment.</p>';
        }

        // Display danger items as notifications with an orange background
        if (!empty($dangerItems)) {
            foreach ($dangerItems as $item) {
                echo '<div class="notification" data-type="critical">';
                echo '<span class="close-btn" onclick="dismissNotification(this)">&times;</span>';
                echo '<strong class="critical-text">Critical:</strong> ' . $item['productname'] .' '. $item['quantity'] . ' left.';
                echo '</div>';
            }
        } else {
            echo '<p>No danger items at the moment.</p>';
        }
        
    // Display restock notifications
    if ($resultRestockNotifications->num_rows > 0) {
        foreach ($resultRestockNotifications as $row) {
            echo '<div class="notification"  data-type="restocked">'; // Set the background color to green
                            echo '<span class="close-btn" onclick="dismissNotification(this)">&times;</span>';

            echo '<strong class="restocked-text"> Restocked:</strong> ' . $row['user_triggered'] . '</strong> restocked <strong>' . $row['product_name'] . '</strong> with a quantity of <strong>' . $row['quantity'] . '</strong> on ' . $row['notification_date'];
            echo '</div>';
        }
    } else {
        echo '<p>No restock at the moment.</p>';
    }
    ?>
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
    document.addEventListener('DOMContentLoaded', function () {
        // Show loader on form submission
        document.getElementById('loaderContainer').style.display = 'flex';

        // Redirect to login.php after a delay (adjust as needed)
        setTimeout(function () {
           document.getElementById('loaderContainer').style.display = 'none';
        }, 1000); // Redirect after 1 second (1000 milliseconds)
    });
</script>

 
    <script>
    function filterNotifications() {
        var selectedType = document.getElementById('notificationType').value;
        var notifications = document.querySelectorAll('.notification');

        notifications.forEach(function (notification) {
            var notificationType = notification.getAttribute('data-type');
            if (selectedType === 'all' || notificationType === selectedType) {
                notification.style.display = 'block';
            } else {
                notification.style.display = 'none';
            }
        });
    }
    </script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Show loader on form submission
        document.getElementById('loaderContainer').style.display = 'flex';

        // Redirect to login.php after a delay (adjust as needed)
        setTimeout(function () {
            document.getElementById('loaderContainer').style.display = 'none';
        }, 1000); // Redirect after 1 second (1000 milliseconds)

        // Hide restock notifications after 5 minutes
        setTimeout(function () {
            var restockNotifications = document.querySelectorAll('.notification[data-type="restock"]');
            restockNotifications.forEach(function (notification) {
                notification.style.display = 'none';
            });
        }, 300000); // 5 minutes in milliseconds
    });

    function dismissNotification(closeBtn) {
        // Hide the parent notification element
        closeBtn.parentElement.style.display = 'none';
        // If you want to perform additional actions based on the notification type, you can add a switch statement
        var notificationType = closeBtn.parentElement.getAttribute('data-type');
        switch (notificationType) {
            case 'lowstock':
                // Do something specific for low stock notifications
                break;
            case 'critical':
                // Do something specific for critical notifications
                break;
            case 'restocked':
                // Do something specific for restock notifications
                break;
            // Add more cases for other notification types if needed
        }
    }
</script>
        </body>
</html>