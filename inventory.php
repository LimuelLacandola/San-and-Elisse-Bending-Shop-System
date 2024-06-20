<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}


// Database connection
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
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=0.75, maximum-scale=0.75, minimum-scale=0.75, user-scalable=no">
    
    
    <link rel = "icon" href = "images/saebs_logo.png" type = "image/x-icon"> 

                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <title>Inventory</title>
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
            width: 75%; /* Adjust the width to 75% */
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

.scrollable-table {
    max-height: 895px !important;
    overflow-y: auto;
    position: relative;
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
		
		    .pagination {
            display: flex;
            list-style: none;
            padding: 0;
			justify-content: center;
		
			
        }

        .pagination li {
            margin-right: 5px;
        }

        .pagination button {
            background-color: #333;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            cursor: pointer;
			margin-right: 10px;
			text-align: center;
			
        }

        .pagination button:hover {
            background-color: #666;
        }
		
		        /* Highlight low stock quantities in red */
        .low-stock {
            color: red;
        }

        /* Highlight mid stock quantities in orange */
        .mid-stock {
            color: orange;
        }

        /* Highlight high stock quantities in green */
        .high-stock {
            color: green;
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
    margin: 3% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 60%;
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
    list-style-type: none; /* Remove bullet points */
}




.flex {
  display: flex;
  width: 100%;
  gap: 6px;
}

.form {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  grid-gap: 20px;
}

.form label {
  position: relative;
  margin-bottom: 10px;
}

.form label .input {
  width: 100%;
  padding: 10px;
  outline: 0;
  border: 1px solid rgba(105, 105, 105, 0.397);
  border-radius: 10px;
  height: 50px;
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

.form label .input:focus + span,
.form label .input:valid + span {
  top:	55px;
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
  width: 50px; /* Set a fixed width for a circle */
  height: 50px; /* Set a fixed height for a circle */
  border-radius: 50%;
  color: white;
  font-size: 16px;
  transform: .3s ease;
  grid-column: span 2; /* Span both columns */
  justify-self: center; /* Center the button within the column */
  margin-top: 20px; /* Adjust top margin as needed */
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
  content: "Add new product";
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
    <h2>Inventory</h2>
    <div style="display: flex; align-items: center;">
    <input type="text" id="searchBox" placeholder="Search items">
     
     <?php
     if ($_SESSION['user_role'] === 'admin') {
     echo '<button class="button" id="openModalButton">';
     }
     elseif ($_SESSION['user_role'] === 'frontdesk') {
        echo '<button class="button" id="openModalButton">';
    } 
	elseif ($_SESSION['user_role'] === 'cashier') {
        // Employee can only access limited features
    } else {
        echo "Invalid user role.";
    }
     ?>
  <svg viewBox="0 0 448 512" class="svgIcon">
    <path d="M224 48h-32v160H48v32h144v144h32V2document.getElementById('openModalButton').addEventListener('click', function () {
40h144v-32H224z"></path>
</svg>

</button>
</div>
    <div id="inventoryTable"></div>

<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeProductModalButton">&times;</span>
        <!-- Existing content here -->
    </div>
</div>

<div id="confirmationModal" class="modal">
    <div class="modal-content">
        <!-- Content will be dynamically added by JavaScript -->
    </div>
</div>

<div id="updateModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeUpdateModalButton">&times;</span>
        <h2 class="title">Update Product</h2>
        <form id="updateProductForm" class="form">
            <!-- Add fields for updating product information -->
            <!-- You can use the existing form structure with appropriate changes -->
            <!-- For example, change the form action to "update_product.php" -->
        </form>
    </div>
</div>



<div id="myModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeModalButton">&times;</span>
    <h2 class="title">Add New Product</h2>
    <form action="insert_product.php" method="post" class="form" enctype="multipart/form-data">
            <label for="productName">
                <input type="text" id="productName" name="productName" class="input" required>
                <span>Product Name</span>
            </label>
            
                <label for="brand">
                <input type="text" id="brand" name="brand" class="input" required>
                <span>Brand</span>
            </label>
            
<label for="description">
    <textarea id="description" name="description" class="input" required></textarea>
    <span>Description</span>
</label>

    

<label for="category">
    <select id="category" name="category" class="input" required>
        <option value="Light Bulb">Light Bulb</option>
        <option value="Grinder">Grinder</option>
        <option value="Sander">Sander</option>
      	<option value="Sprayer">Sprayer</option>
      	<option value="Calipers">Calipers</option>
      	<option value="Tape Measures">Tape Measures</option>
      	<option value="Meter">Meter</option>
      	<option value="Squares">Squares</option>
      	<option value="Pulleys">Pulleys</option>
      	<option value="Chisels">Chisels</option>
      	<option value="Drills">Drills</option>
      	<option value="Garden Tools">Garden Tools</option>
      	<option value="Hammer">Hammer</option>
      	<option value="Levels">Levels</option>
      	<option value="Brush">Brush</option>
      	<option value="Pliers">Pliers</option>
      	<option value="Saw">Saw</option>
      	<option value="Screwdriver">Screwdriver</option>
      	<option value="Wire Cutter">Wire Cutter</option>
      	<option value="Wrench">Wrench</option>
      	<option value="Heat Gun">Heat Gun</option>
      	<option value="Jigsaw">Jigsaw</option>
      	<option value="Impact Driver">Impact Driver</option>
      	<option value="Oscillating Tool">Oscillating Tool</option>
      	<option value="Hoses">Hoses</option>
      	<option value="Shovel">Shovel</option>
      	
    </select>
    <span>Product Category</span>
</label>
            
          <label for="unitofmeasure">
    <select id="unitofmeasure" name="unitofmeasure" class="input" required>
        <option value="Per Piece">Per Piece</option>
        <option value="Bundled">Bundled</option>
    </select>
    <span>Unit of measure</span>
</label>
            
                <label for="measurement">
                <input type="text" id="measurement" name="measurement" class="input" required>
                <span>Size</span>
            </label>

            <label for="quantity">
                <input type="number" id="quantity" name="quantity" class="input" required>
                <span>Quantity</span>
            </label>

            <label for="price">
                <input type="number" step="0.01" id="price" name="price" class="input" required>
                <span>Price</span>
            </label>

            <label for="image_url">
    <input type="file" id="image_url" name="image_url" class="input" accept="image/*">
    <span>Upload Product Image</span>
</label>


            
                <label for="supplierName">
                    <input type="text" id="supplierName" name="supplierName" class="input" required>
                <span>Supplier Name:</span>
            </label>
    
     <label for="supplierEmail">
                    <input type="text" id="supplierEmail" name="supplierEmail" class="input" required>
                <span>Supplier Email:</span>
            </label>
          
               <label for="supplierLocation">
                    <input type="text" id="supplierLocation" name="supplierLocation" class="input" required>
                <span>Supplier Location:</span>
            </label>
          
          
			<label for="contactNumber">
                    <input type="number" id="contactNumber" name="contactNumber" class="input"  required oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)" required>
                <span>Contact Number:</span>
            </label>
          
          
			<label for="contactPerson">
                    <input type="text" id="contactPerson" name="contactPerson" class="input" required>
                <span>Contact Person:</span>
            </label>
      
			<label for="LowStockTrigger">
                    <input type="number" id="LowStockTrigger" name="LowStockTrigger" class="input" required>
                <span>Lowstock Limit:</span>
            </label>
    

            <input type="submit"  value="save" class="submit" title="Save" style="font-family: 'Material Icons'; font-size: 24px;"> 

  </form>
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
        // Function to disable the product
    function disableProduct(productId) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to disable this product!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, disable it!',
        }).then((result) => {
            if (result.isConfirmed) {
                // User clicked the "Yes, disable it!" button
                // Send an AJAX request to disable the product
                fetch(`disable_product.php?id=${productId}`, {
                    method: 'POST',
                })
                .then(response => response.text())
                .then(data => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Disabled!',
                        
                    });
                    closeModal('productModal'); // Close the product modal after disabling
                    // You may also want to update the inventory table after disabling
                    updateInventory(document.getElementById('searchBox').value, currentPage);
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to disable product',
                    });
                });
            }
        });
    }
    
     // Function to enable the product
    function enableProduct(productId) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to enable this product!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, enable it!',
        }).then((result) => {
            if (result.isConfirmed) {
                // User clicked the "Yes, enable it!" button
                // Send an AJAX request to enable the product
                fetch(`enable_product.php?id=${productId}`, {
                    method: 'POST',
                })
                .then(response => response.text())
                .then(data => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Enabled!',
                    });
                    // Reload the page or update UI as needed
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to enable product',
                    });
                });
            }
        });
    }
      
// Function to fetch product information for updating
function fetchProductForUpdate(productId) {
    // Close the product modal
    closeModal('productModal');

    fetch(`fetch_product_details.php?id=${productId}`)
        .then(response => response.text())
        .then(data => {
            const updateModalContent = document.getElementById('updateModal').getElementsByClassName('modal-content')[0];
            updateModalContent.innerHTML = `
                <span class="close" id="closeUpdateModalButton">&times;</span>
                ${data}`;

            // Show the update modal
            const updateModal = document.getElementById('updateModal');
            updateModal.style.display = 'block';
        });
}


// Function to handle Update button click
document.addEventListener('click', function (event) {
    const clickedElement = event.target;
    const updateButton = clickedElement.closest('.editButton');

    if (updateButton) {
        const productId = updateButton.getAttribute('data-product-id');
        fetchProductForUpdate(productId);
    }
});

// Function to handle modal close buttons for update modal
document.addEventListener('click', function (event) {
    if (event.target.id === 'closeUpdateModalButton') {
        closeModal('updateModal');
    }
});
</script>

    <script>
        // Function to show the product modal
function showProductModal(productId) {
    fetch(`fetch_product.php?id=${productId}`)
        .then(response => response.text())
        .then(data => {
            const modalContent = document.getElementById('productModal').getElementsByClassName('modal-content')[0];
            modalContent.innerHTML = `
                <span class="close" id="closeProductModalButton">&times;</span>
                ${data}`;

            // Show the product modal
            const productModal = document.getElementById('productModal');
            productModal.style.display = 'block';
        });
}


document.addEventListener('click', function (event) {
    const clickedElement = event.target;

    // Check if the clicked element or its parent is a row
    const inventoryRow = clickedElement.closest('.inventory-row');
    
    if (inventoryRow) {
        const productId = inventoryRow.getAttribute('data-product-id');
        showProductModal(productId);
    }
});


        // Function to handle modal close buttons
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'none';
        }

document.addEventListener('click', function (event) {
    if (event.target.id === 'closeProductModalButton') {
        closeModal('productModal');
    }
});

        document.getElementById('closeModalButton').addEventListener('click', function () {
            closeModal('myModal');
        });
        

    </script>

   <script>
        // JavaScript to open and close the modal
        document.getElementById('openModalButton').addEventListener('click', function () {
            const modal = document.getElementById('myModal');
            modal.style.display = 'block';
        });

        document.getElementById('closeModalButton').addEventListener('click', function () {
            const modal = document.getElementById('myModal');
            modal.style.display = 'none';
        });
    </script>
    <script>
        let currentPage = 1; // Current page

        // Function to update the inventory table without pagination
        function updateInventory(searchTerm) {
            const inventoryTable = document.getElementById('inventoryTable');

            // Send an AJAX request to fetch inventory data with the search term
            fetch(`fetch_inventory.php?search=${searchTerm}`)
                .then(response => response.text())
                .then(data => {
                    inventoryTable.innerHTML = data;
                });
        }

        // Function to handle search box input
        document.getElementById('searchBox').addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase().trim();
            updateInventory(searchTerm);
        });

        // Initial table update without search term
        updateInventory('');
    </script>




</div>
</body>
</html>