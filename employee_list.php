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
    <link rel = "icon" href = "images/saebs_logo.png" type = "image/x-icon"> 

        
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <title>Employee List</title>
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
    width: 50%;
    border-radius: 10px;
    text-align: center;
}

/* Add this new style to override the text alignment to left */
.modal-content p {
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
        background-color: #323031; /* Green background color */
        color: white; /* White text color */
        padding: 10px; /* Padding */
        border: none; /* No border */
        border-radius: 50%; /* Rounded corners */
        cursor: pointer; /* Pointer cursor */
        font-size: 16px; /* Font size */
        transition: background-color 0.3s; /* Add transition for smooth hover effect */
    }

    .submit:disabled {
        background-color: #ddd; /* Grey background color when disabled */
        color: #666; /* Dark grey text color when disabled */
        cursor: not-allowed; /* Not-allowed cursor when disabled */
    }

    .submit:not(:disabled):hover {
        background-color: #45a049; /* Darker green background color on hover when not disabled */
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

    #usernameResult {
        margin-left: 10px; /* Adjust the margin as needed */
        color: black; /* Adjust the color as needed */
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
  content: "Add new user";
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
    
    label {
    position: relative;
}

.show-password-checkbox {
    position: absolute;
    top: 45%;
    right: -10px;
    transform: translateY(-50%);
}

.eye-icon-container {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    cursor: pointer;
}

.eye-icon {
    width: 24px; /* Adjust the width as needed */
    height: 24px; /* Adjust the height as needed */
    transition: opacity 0.3s;
}

.eye-icon:hover {
    opacity: 0.7;
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
    <h2>Employee List</h2>
    <div style="display: flex; align-items: center;">
    <input type="text" id="searchBox" placeholder="Search items">
     <button class="button" id="addUserBtn">
  <svg viewBox="0 0 448 512" class="svgIcon">
    <path d="M224 48h-32v160H48v32h144v144h32V240h144v-32H224z"></path>
</svg>

</button>
</div>
	


<div id="itemModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <div id="modalContent"></div>
    </div>
</div>
  
<!-- Modal for updating a user -->
<div id="updateModal" class="modal">
    <div class="modal-content">
<span class="close" id="closeUpdateModal">&times;</span>
        <div id="updateModalContent"></div>
    </div>
</div>
  <script>
const closeUpdateModalButton = document.getElementById('closeUpdateModal');
closeUpdateModalButton.addEventListener('click', () => {
    updateModal.style.display = 'none';
});
  </script>




<!-- Modal for adding/editing a user -->
<div id="addEditUserModal1" class="modal">
    <div class="modal-content">
<span class="close" id="closeAddUserModal">&times;</span>
        <h2 class="title">Add New User</h2>
        <form id="addEditUserForm" method="post" action="add_user.php" class="form" enctype="multipart/form-data">
            <label for="fullname">
                <input type="text" id="fullname" name="fullname" class="input" required>
                <span>Full Name</span>
            </label>
            
          <script>
            const closeAddUserModalButton = document.getElementById('closeAddUserModal');
closeAddUserModalButton.addEventListener('click', () => {
    addUserModal.style.display = 'none';
});

            </script>
            
<label for="username" style="position: relative;">
    <input type="text" id="username" name="username" class="input" required>
    <span class="label-text">Username</span>
        <div id="usernameResult" style="position: relative;"></div>

</label>




<label for="password" style="position: relative;">
    <input type="password" id="password" name="password" class="input" required oninput="checkPasswordRequirements()">
    <span>Password</span>
    <span id="eyeIcon" class="eye-icon-container">
        <img id="closedEye" class="eye-icon" src="images/hide.png" alt="Closed Eye">
        <img id="openEye" class="eye-icon" src="images/show.png" alt="Open Eye" style="display: none;">
    </span>
</label>
      <div id="passwordRequirementsMessage" style="color: red; margin-top: 5px;"></div>


            

<label for="user_role">
    <select id="user_role" name="user_role" class="input" required>
        <option value="admin">Admin</option>
        <option value="frontdesk">Front Desk</option>
        <option value="cashier">Cashier</option> 
    </select>
    <span>User Role</span>
</label>


            <label for="user_image">
    <input type="file" id="user_image" name="user_image" class="input" accept="image/*">
    <span>Upload User Image</span>
</label>
          
          

<label for="email" style="position: relative;">
    <input type="text" id="email" name="email" class="input" required>
    <span class="label-text">Email</span>
    <!-- Add a span for the validation message -->
    <span id="email-validation-message" style="color: red; position: absolute; top: 100%; left: 0;"></span>
</label>


            <!-- Add a hidden input for user ID -->
            <input type="hidden" id="user_id" name="user_id">
<br><br><button type="submit" class="submit" id="saveButton" disabled>
  <i class="material-icons">save</i>
</button>          
          
        </form>
    </div>
</div>
  
      <div id="employeeTable"></div>

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
// Define the checkPasswordRequirements function in the global scope
function checkPasswordRequirements() {
    const passwordInput = document.getElementById('password');
    const saveButton = document.getElementById('saveButton');
    const passwordRequirementsMessage = document.getElementById('passwordRequirementsMessage');

    const password = passwordInput.value;

    const hasUpperCase = /[A-Z]/.test(password);
    const hasSpecialCharacter = /[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]/.test(password);
    const isMinLength = password.length >= 8;

    if (hasUpperCase && hasSpecialCharacter && isMinLength) {
        saveButton.disabled = false;
        passwordRequirementsMessage.textContent = 'Password requirements met!';
        passwordRequirementsMessage.style.color = 'green';
    } else {
        saveButton.disabled = true;
        passwordRequirementsMessage.textContent = 'Password must have at least one uppercase letter, be a minimum of eight characters long, and include one special character.)';
        passwordRequirementsMessage.style.color = 'red';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password');
    const saveButton = document.getElementById('saveButton');
    const passwordRequirementsMessage = document.getElementById('passwordRequirementsMessage');

    passwordInput.addEventListener('input', checkPasswordRequirements);
});
</script>

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
        // Get the input field for the password
        const passwordInput = document.getElementById('password');
        // Get the eye icons
        const openEyeIcon = document.getElementById('openEye');
        const closedEyeIcon = document.getElementById('closedEye');
        // Get the eye icon container
        const eyeIconContainer = document.getElementById('eyeIcon');

        // Add an event listener to the eye icon container
        eyeIconContainer.addEventListener('click', function () {
            // Toggle the visibility of the eye icons
            openEyeIcon.style.display = openEyeIcon.style.display === 'none' ? 'block' : 'none';
            closedEyeIcon.style.display = closedEyeIcon.style.display === 'none' ? 'block' : 'none';

            // Change the type attribute of the password input based on eye icon visibility
            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
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
   
    
    // Get the input field for username
    const usernameInput = document.getElementById('username');
    
    // Get the result display container
const resultDisplay = document.getElementById('usernameResult');

    

    // Add an event listener to the username input
    usernameInput.addEventListener('input', function () {
        const username = this.value.trim();

        // Send an AJAX request to check the username
        fetch('check_username.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `username=${username}`,
        })
        .then(response => response.json())
        .then(data => {
            // Update UI based on the response
            if (data.status === 'available') {
                // Username is available
                // You can add visual feedback here (e.g., change color or icon)
                resultDisplay.textContent = 'Username is available';
                resultDisplay.style.color = 'green'; // Adjust styling as needed
            } else if (data.status === 'taken') {
                // Username is taken
                // You can add visual feedback here (e.g., change color or icon)
                resultDisplay.textContent = 'Username is taken';
                resultDisplay.style.color = 'red'; // Adjust styling as needed
            } else {
                // Handle other status or errors
                resultDisplay.textContent = 'Error checking username';
                resultDisplay.style.color = 'orange'; // Adjust styling as needed
            }
        })
        .catch(error => {
            resultDisplay.textContent = 'Error checking username';
            resultDisplay.style.color = 'orange'; // Adjust styling as needed
            console.error('Error checking username', error);
        });
    });

        // Function to open the modal and load user details for editing
        function openUpdateModal(userId) {
        fetch(`fetch_user_details_edit.php?id=${userId}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('updateModalContent').innerHTML = data;
                document.getElementById('updateModal').style.display = 'block';
            })
            .catch(error => {
                console.error('Error fetching user details for editing:', error);
            });
    }

    // Function to handle Update button click
    document.addEventListener('click', function (event) {
        const clickedElement = event.target;
        const updateButton = clickedElement.closest('.editButton');

        if (updateButton) {
            const userId = updateButton.getAttribute('data-user-id');
            openUpdateModal(userId);
        }
    });
});



</script>

<script>
    // Function to close the modal and redirect to "employee_list.php"
    function closeModalAndRedirect() {
        const modal = document.getElementById("closeModal");
        modal.style.display = "none";

        // Redirect to "employee_list.php"
        window.location.href = "employee_list.php";
    }

    // Add an event listener to the close button
    const closeModalButton = document.getElementById("closeModal");
    closeModalButton.addEventListener("click", closeModalAndRedirect);

</script>



<script>
    // Get the modal and button elements
    const addUserModal = document.getElementById('addEditUserModal1');
  	const itemModal = document.getElementById('itemModal');
  	const updateModal = document.getElementById('updateModal');
    const addUserBtn = document.getElementById('addUserBtn');
    const closeModal = document.getElementById('closeModal');

    // Show the modal when the button is clicked
    addUserBtn.addEventListener('click', () => {
        addUserModal.style.display = 'block';
    });

    // Close the modal when the close button is clicked
    closeModal.addEventListener('click', () => {
        addUserModal.style.display = 'none';
    });
  
      // Close the modal when the close button is clicked
    closeModal.addEventListener('click', () => {
        itemModal.style.display = 'none';
    });
  
        // Close the modal when the close button is clicked
    closeModal.addEventListener('click', () => {
        updateModal.style.display = 'none';
    });

    // Close the modal when clicking outside of it
    window.addEventListener('click', (event) => {
        if (event.target === addUserModal) {
            addUserModal.style.display = 'none';
        }
    });

    // Prevent the click inside the modal from closing it
    addUserModal.addEventListener('click', (event) => {
        event.stopPropagation();
    });
</script>
	
	
	
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
                    updateEmployee('', currentPage); // Refresh the table
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
    if (confirm(`Are you sure you want to activate again user with ID: ${id}?`)) {
        // Send an AJAX request to update the account_status to "deactivated"
        fetch(`activate_user.php?id=${id}`, { method: 'POST' })
            .then(response => {
                if (response.status === 200) {
                    // User deactivated successfully
                    alert(`User with ID: ${id} has been activated successfully.`);
                    updateEmployee('', currentPage); // Refresh the table
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
	

	
	<script>
    // Function to update the employee table with pagination and edit/delete buttons
    function updateEmployee(searchTerm, page) {
        const employeeTable = document.getElementById('employeeTable');
        // ... (Your existing JavaScript code)

    }

    // Function to deactivate the user
    function deactivateUser(id) {
        const confirmDeactivate = confirm('Are you sure? You are about to deactivate this user!');

        if (confirmDeactivate) {
            // User clicked OK
            // Send an AJAX request to deactivate the user
            fetch(`deactivate_user.php?id=${userId}`)
                .then(response => response.text())
                .then(data => {
                    alert('Deactivated! ' + data);
                    closeModal('itemModal'); // Close the user modal after deactivation
                    // You may also want to update the user list after deactivation
                    updateEmployee(document.getElementById('searchBox').value, currentPage);
                })
                .catch(error => {
                    console.error('Error deactivating user:', error);
                });
        }
    }
      
      
      
          function activateUser(id) {
        const confirmActivate = confirm('Are you sure? You are about to activate this user again?');

        if (confirmActivate) {
            // User clicked OK
            // Send an AJAX request to deactivate the user
            fetch(`activate_user.php?id=${userId}`)
                .then(response => response.text())
                .then(data => {
                    alert('Activated! ' + data);
                    closeModal('itemModal'); // Close the user modal after deactivation
                    // You may also want to update the user list after deactivation
                    updateEmployee(document.getElementById('searchBox').value, currentPage);
                })
                .catch(error => {
                    console.error('Error activating user:', error);
                });
        }
    }
</script>

<script>
// Get the modal and button elements
const addUserModal = document.getElementById('addEditUserModal');
const addUserBtn = document.getElementById('addUserBtn');
const closeModal = document.getElementById('closeModal');
const modalTitle = document.getElementById('modalTitle');
const userIdField = document.getElementById('user_id');

// Show the modal when the button is clicked
addUserBtn.addEventListener('click', () => {
    modalTitle.textContent = 'Add New User'; // Set the modal title
    userIdField.value = ''; // Reset the user ID field
    addUserModal.style.display = 'block';
});

// Close the modal when the close button is clicked
closeModal.addEventListener('click', () => {
    addUserModal.style.display = 'none';
});

// Close the modal when clicking outside of it
window.addEventListener('click', (event) => {
    if (event.target === addUserModal) {
        addUserModal.style.display = 'none';
    }
});

// Prevent the click inside the modal from closing it
addUserModal.addEventListener('click', (event) => {
    event.stopPropagation();
});

// Function to handle the form submission for adding a new user
addEditUserForm.addEventListener('submit', function (e) {
    e.preventDefault();

    // Collect form data
    const formData = new FormData(addEditUserForm);

    // Send an AJAX request to add the new user
    fetch('add_user.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => {
        if (response.status === 200) {
            // User added successfully
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'User has been added successfully.',
            }).then(() => {
                addUserModal.style.display = 'none'; // Close the modal
                updateEmployee('', currentPage); // Refresh the employee table
            });
        } else {
            // Handle errors
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error adding user.',
            });
        }
    });
});
</script>




    <script>
// Remove the pagination related code and simplify the script
document.getElementById('searchBox').addEventListener('input', function () {
    const searchTerm = this.value.toLowerCase().trim();
    updateEmployee(searchTerm);
});

// Initial table update without search term
updateEmployee('');

// Function to update the employee table with all data
function updateEmployee(searchTerm) {
    const employeeTable = document.getElementById('employeeTable');

    // Send an AJAX request to fetch all employee data with the search term
    fetch(`fetch_employee_list.php?search=${searchTerm}`)
        .then(response => response.text())
        .then(data => {
            employeeTable.innerHTML = data;
        });
}

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
    const employeeTable = document.getElementById('employeeTable');

    // Function to open the modal and load user details
    function openModal(userId) {
        fetch(`fetch_user_details.php?id=${userId}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('modalContent').innerHTML = data;
                document.getElementById('itemModal').style.display = 'block';
            })
            .catch(error => {
                console.error('Error fetching user details:', error);
            });
    }

    // Function to close the modal
    function closeModal() {
        const modalContent = document.getElementById('modalContent');
        modalContent.innerHTML = '';
        document.getElementById('itemModal').style.display = 'none';
    }

    // Close the modal if the user clicks outside of it
    window.onclick = function (event) {
        if (event.target == document.getElementById('itemModal')) {
            closeModal();
        }
    };

    // Use event delegation on the parent container (employeeTable)
    employeeTable.addEventListener('click', function (event) {
        const target = event.target;
        if (target.tagName === 'TD') {
            const userId = target.closest('tr').querySelector('td:first-child').textContent;
            openModal(userId);
        }
    });
});

        </script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
// Function to deactivate the user with SweetAlert
function deactivateUser(userId) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to deactivate this user!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, deactivate!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // User clicked OK
            // Send an AJAX request to deactivate the user
            fetch(`deactivate_user.php?id=${userId}`)
                .then(response => response.text())
                .then(data => {
                    Swal.fire({
                        title: 'Deactivated!',
                        text: `User has been deactivated.\n${data}`,
                        icon: 'success',
                        showConfirmButton: false, // Remove the "OK" button
                        timer: 1000 // Auto-close the alert after 1 second
                    });
                })
                .catch(error => {
                    console.error('Error deactivating user:', error);
                })
                .finally(() => {
                    // Reload the current page after deactivation
              	setTimeout(() => {
                    window.location.reload();
                  }, 1000);
                });
        }
    });
}


// Function to activate the user with SweetAlert
function activateUser(userId) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to activate this user again?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, activate!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // User clicked OK
            // Send an AJAX request to activate the user
            fetch(`activate_user.php?id=${userId}`)
                .then(response => response.text())
                .then(data => {
                    Swal.fire({
                        title: 'Activated!',
                        text: `User has been activated.\n${data}`,
                        icon: 'success',
                        showConfirmButton: false, // Remove the "OK" button
                        timer: 1000 // Auto-close the alert after 1 second
                    });
                })
                .catch(error => {
                    console.error('Error activating user:', error);
                })
                .finally(() => {
              		setTimeout(() => {
                    // Reload the current page after activation
                    window.location.reload();
                      }, 1000);
                    // You may also want to update the user list after activation
                    updateEmployee(document.getElementById('searchBox').value, currentPage);
                });
        }
    });
}

         // Function to close the modal
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'none';
        }

        // Function to fetch user information for updating
        function fetchUserForUpdate(userId) {
            // Close any open modal
            closeModal('updateUserModal');

            fetch(`fetch_user_details.php?id=${userId}`)
                .then(response => response.text())
                .then(data => {
                    const updateUserModalContent = document.getElementById('updateUserModal').getElementsByClassName('modal-content')[0];
                    updateUserModalContent.innerHTML = `
                        <span class="close" onclick="closeModal('updateUserModal')">&times;</span>
                        ${data}`;

                    // Show the update user modal
                    const updateUserModal = document.getElementById('updateUserModal');
                    updateUserModal.style.display = 'block';
                });
        }

        // Function to handle Update button click
        document.addEventListener('click', function (event) {
            const clickedElement = event.target;
            const updateButton = clickedElement.closest('.editButton');

            if (updateButton) {
                const userId = updateButton.getAttribute('data-user-id');
                fetchUserForUpdate(userId);
            }
        });
    
// Function to handle modal close buttons for update modal
document.addEventListener('click', function (event) {
    if (event.target.id === 'closeUpdateModalButton') {
        closeModal('updateModal');
    }
});

  </script>
    
    
	
</div>
</body>
</html>