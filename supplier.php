<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

require_once 'access_control.php';
require_once 'db_connection.php';

// Fetch supplier information from the inventory table
$sql = "SELECT * FROM inventory";
$result = $conn->query($sql);

// Check for errors in the query
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Fetch user information from the login table based on the username
$sqlUser = "SELECT id, user_image, fullname, username, user_role, lockscreen FROM login WHERE username = '{$_SESSION['username']}'";
$resultUser = $conn->query($sqlUser);

// Check for errors in the query
if (!$resultUser) {
    die("Query failed: " . $conn->error);
}

if ($resultUser->num_rows > 0) {
    // Fetch the user data
    $userData = $resultUser->fetch_assoc();

    // Check access control for employee
    restrictAccessToEmployee($_SESSION['user_role']);

    // Check access control for cashier
    restrictAccessToCashier($_SESSION['user_role']);
} else {
    echo "User not found";
}

// Pass lockscreen status to JavaScript
echo '<script>';
echo 'var lockscreenStatus = "' . $userData['lockscreen'] . '";';
echo '</script>';
?>

<!DOCTYPE html>
<html>
<head>
    <link rel = "icon" href =  "images/saebs_logo.png"        type = "image/x-icon"> 
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <title>Supplier Information</title>
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
        
    /* Add styles for the modal */
    
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    border-radius: 10px;
    text-align: center;
}



       .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
/* Center the form within the modal */
form {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.title {
align-items: center;
  font-size: 28px;
  color: #333;
  font-weight: 600;
  letter-spacing: -1px;
  position: relative;
  display: flex;
  align-items: center;
  padding-left: 30px;
justify-content: center; /* Center the title horizontally */

}

.title::before,.title::after {
  position: absolute;
  content: "";
  height: 16px;
  width: 16px;
  border-radius: 50%;
left: 400px; /* Place the pseudo-elements to the left */

  background-color: #333;
  
}

.title::before {
  width: 18px;
  height: 18px;
  background-color: #333;
}

.title::after {
  width: 18px;
  height: 18px;
  animation: pulse 1s linear infinite;
  
}

.flex {
  display: flex;
  width: 100%;
  gap: 6px;
}

.form label {
  position: relative;
  margin-bottom: 10px;

}

.form label .input {
  width: 100%;
  padding: 10px 10px 20px 10px;
  outline: 0;
  border: 1px solid rgba(105, 105, 105, 0.397);
  border-radius: 10px;
}

.form label .input + span {
  position: absolute;
  left: 10px;
  top: 15px;
  color: grey;
  font-size: 0.9em;
  cursor: text;
  transition: 0.3s ease;
}

.form label .input:placeholder-shown + span {
  top: 15px;
  font-size: 0.9em;
}

.form label .input:focus + span,.form label .input:valid + span {
  top: 30px;
  font-size: 0.7em;
  font-weight: 600;
}

.form label .input:valid + span {
  color: #333;
}

.submit {
  border: none;
  outline: none;
  background-color: #333;
  padding: 10px;
  border-radius: 10px;
  color: white;
  font-size: 16px;
  transform: .3s ease;
}

.submit:hover {
  background-color: rgb(56, 90, 194);
}

@keyframes pulse {
  from {
    transform: scale(0.9);
    opacity: 1;
  }

  to {
    transform: scale(1.8);
    opacity: 0;
  }
}
    
        #searchBox {
            border: 2px solid transparent;
            width: 15em;
            height: 2.5em;
            padding-left: 0.8em;
            outline: none;
            overflow: hidden;
            background-color: #F3F3F3;
            border-radius: 10px;
            transition: all 0.5s;
        }

        #searchBox:hover,
        #searchBox:focus {
            border: 2px solid #333;
            box-shadow: 0px 0px 0px 7px rgb(102, 102, 102, 20%);
            background-color: white;
        }

.button {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background-color: rgb(20, 20, 20);
  border: none;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.164);
  cursor: pointer;
  transition-duration: .3s;
  overflow: hidden;
  position: relative;
}

.svgIcon {
  width: 12px;
  transition-duration: .3s;
}

.svgIcon path {
  fill: white;
}

.button:hover {
  width: 140px;
  border-radius: 50px;
  transition-duration: .3s;
  background-color: rgb(255, 69, 69);
  align-items: center;
}



.button::before {
  position: absolute;
  top: -20px;
  content: "Add new supplier";
  color: white;
  transition-duration: .3s;
  font-size: 2px;
}

.button:hover::before {
  font-size: 13px;
  opacity: 1;
  transform: translateY(30px);
  transition-duration: .3s;
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
        width: 250px; /* Adjust the width as needed */
        margin-bottom: 20px; /* Adjust this value to fine-tune the alignment */
		margin-left: -20px;
        }
        
        .table-wrapper {
    max-height: 750px; /* Set a fixed height for the table body */
    overflow-y: auto; /* Enable vertical scrolling for the table body */
}

#supplierTable thead th {
    position: sticky;
    top: 0;
    background-color: #f2f2f2;
    z-index: 2; /* Ensure the header is above the scrollable content */
}

#logoLink img {
        width: 250px; /* Adjust the width as needed */
        margin-bottom: 20px; /* Adjust this value to fine-tune the alignment */
		margin-left: -20px;
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
    <h2>Supplier Information</h2>
    <div style="display: flex; align-items: center;">
            <input type="text" id="searchBox" placeholder="Search items" oninput="filterTable()">
    </div>
    <div class="table-wrapper">
        <table id="supplierTable">
            <thead>
            <tr>
                <th>Supplier Name</th>
                <th>Email Address</th>
                <th>Location</th>
                <th>Contact No</th>
                <th>Contact Person</th>
            </tr>
            </thead>
            <tbody id="supplierData">
                <?php
                // Keep track of fetched supplier names to avoid duplication
                $fetchedSupplierNames = [];

                // Iterate through the result set and display supplier information
                while ($row = $result->fetch_assoc()) {
                    // Check if the supplier name has been fetched before
                    if (!in_array($row['supplier_name'], $fetchedSupplierNames)) {
                        echo '<tr>';
                        echo '<td>' . $row['supplier_name'] . '</td>';
                        echo '<td>' . $row['supplier_email'] . '</td>';
                        echo '<td>' . $row['supplier_location'] . '</td>';
                        echo '<td>' . $row['contact_number'] . '</td>';
                        echo '<td>' . $row['contact_person'] . '</td>';
                        echo '</tr>';

                        // Add the supplier name to the fetched list
                        $fetchedSupplierNames[] = $row['supplier_name'];
                    }
                }
                ?>
            </tbody>
        </table>
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
        // Function to filter the table based on the search input
        function filterTable() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchBox");
            filter = input.value.toUpperCase();
            table = document.getElementById("supplierTable");
            tr = table.getElementsByTagName("tr");

            // Loop through all table rows and hide those that don't match the search query
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0]; // Assuming the supplier name is in the first column

                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
         <script>
document.addEventListener('DOMContentLoaded', function () {
    var idleTimeout = 600000; 
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
  
<!-- Add the following script just before the closing </body> tag -->
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


</body>
</html>
