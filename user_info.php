<?php
// Start a session
session_start();

// Include the database connection code
include 'db_connection.php';

// Check if the user is logged in
if (isset($_SESSION['username'])) {
    // Get the username from the session
    $username = $_SESSION['username'];
  
      if (isset($_POST['action']) && $_POST['action'] == 'verifyOldPassword') {
        // Retrieve the hashed password from the login table based on the username
        $sql = "SELECT password FROM login WHERE username = '$username'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hashedPassword = $row['password'];

            // Get the old password from the POST request
            $oldPassword = $_POST['oldPassword'];

            // Verify if the old password matches the hashed password
            $verificationResult = password_verify($oldPassword, $hashedPassword);

            // Prepare the response
            $response = array(
                'success' => $verificationResult,
                'message' => $verificationResult ? 'Old password is correct.' : 'Old password is incorrect.'
            );

            echo json_encode($response);
            exit(); // Exit to stop further execution
        } else {
            echo json_encode(array('success' => false, 'message' => 'User not found.'));
            exit();
        }
    }

    // Check if the action is to save the new password
    if (isset($_POST['action']) && $_POST['action'] == 'saveNewPassword') {
        // Get the new password from the POST request
        $newPassword = $_POST['newPassword'];

        // Hash the new password
        $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the password in the database
        $updateSql = "UPDATE login SET password = '$hashedNewPassword' WHERE username = '$username'";
        if ($conn->query($updateSql) === TRUE) {
            echo json_encode(array('success' => true, 'message' => 'Password updated successfully.'));
            exit();
        } else {
            echo json_encode(array('success' => false, 'message' => 'Error updating password: ' . $conn->error));
            exit();
        }
    }
  
  
  if (isset($_FILES['newImage'])) {
    // Handle image upload
    $uploadDir = 'user_images/';
    $uploadFile = $uploadDir . basename($_FILES['newImage']['name']);
    
    if (move_uploaded_file($_FILES['newImage']['tmp_name'], $uploadFile)) {
        // Update the user_image column in the database with the new image path
        $updateImageSql = "UPDATE login SET user_image = '$uploadFile' WHERE username = '$username'";
        if ($conn->query($updateImageSql) === TRUE) {
            echo json_encode(array('success' => true, 'message' => 'Image uploaded and updated successfully.'));
            exit();
        } else {
            echo json_encode(array('success' => false, 'message' => 'Error updating image path: ' . $conn->error));
            exit();
        }
    } else {
        echo json_encode(array('success' => false, 'message' => 'Error uploading image.'));
        exit();
    }
}
  

    // Retrieve user information from the login table based on the username
    $sql = "SELECT id, user_image, fullname, username, user_role, lockscreen FROM login WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch the user data
        $userData = $result->fetch_assoc();
    } else {
        echo "User not found";
    }
} else {
    // Redirect to the login page if the user is not logged in
    header("Location: login.php");
    exit();
}

echo '<script>';
echo 'var lockscreenStatus = "' . $userData['lockscreen'] . '";';
echo '</script>';
// Close the database connection
$conn->close();
?>

<!DOCTYPE html>

<html lang="en">
<head>
        <link rel = "icon" href =  "images/saebs_logo.png"        type = "image/x-icon"> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <title>My Profile</title>
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
          	overflow: auto;
        }

  .navigation {
            background-color: #333;
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

        .user-info {
            margin-top: auto;
            display: flex;
            align-items: center;
            text-decoration: none; /* Remove underline from the link */
            color: #fff;
        }

        .user-info:hover {
            text-decoration: none; /* Remove underline when hovering */
        }

        .user-info img {
            width: 30px;
            height: 30px;
            margin-right: 10px;
            border-radius: 50%;
        }

        .profile-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .profile-info label {
            margin-bottom: 5px;
        }

        .profile-info input {
            margin-bottom: 10px;
            padding: 5px;
            width: 200px;
          	height: auto;
        }

        .profile-info input[readonly] {
            background-color: #f7f7f7;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 5px;
          	
            text-align: center;
        }

        .profile-info img {
            max-width: 70%;
            height: auto;
            margin-top: 10px;
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

            #logoLink img {
        width: 250px; /* Adjust the width as needed */
        margin-bottom: -10px; /* Adjust this value to fine-tune the alignment */
        margin-left: -20px;
    }
      #changePasswordButton {
  background-color: #4CAF50; /* Green */
  color: white;
  padding: 10px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

#changePasswordModal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black background */
}

.modal-content {
  background-color: #fefefe;
  margin: 10% auto;
  padding: 20px;
  border: 1px solid #888;
  width: 50%;
  border-radius: 5px;
}

.close {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
}

.close:hover,
.close:focus {
  color: #000;
}

#changePasswordForm {
  text-align: center;
}

label {
  display: block;
  margin-bottom: 8px;
}

input {
  width: 100%;
  height: 20px;
  padding: 10px 10px 20px 10px;
  outline: 0;
  border: 1px solid rgba(105, 105, 105, 0.397);
  border-radius: 5px;
}

button {
  color: white;
  padding: 10px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

button:hover {
  background-color: #45a049;
}

#saveButton:disabled {
  background-color: #dddddd; 
  cursor: not-allowed;
}
      
#uploadImageButton, #changePasswordButton, #saveButton{
  background-color: #323031;
      }
      
#uploadImageButton:hover, #changePasswordButton:hover{
  background-color: #595557;
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


    <a href="logout.php" id="logoutLink"><i class="material-icons">exit_to_app</i> Logout</a>

    <?php
    // Check if the user has a custom image, otherwise, display a default image
    $userImage = isset($userData["user_image"]) ? $userData["user_image"] : "path/to/default-image.png";
    ?>

</div>
  
 



<div class="content">
    <h2><?php echo $_SESSION['username']; ?>'s Profile</h2>
    <?php if (isset($userData)) : ?>
        <div class="profile-info">
            <b><h2>User Information:</h2></b>

            <?php
            // Display the image URL if available
            if (isset($userData["image_url"])) {
                echo '<img src="' . $userData["image_url"] . '" alt="User Image" style="max-width: 150px; max-height: 150px; margin-bottom: 10px;">';
            }
            ?>
          
          

 
            <img src="<?php echo $userImage; ?>" alt="User Image" style="max-width: 20%; height: auto; margin-top: 10px;"><br>

          	
<form id="imageUploadForm" action="" method="post" enctype="multipart/form-data">
    <label for="newImage">Upload New Image:</label>
    <input type="file" name="newImage" accept="image/*">
    <button type="submit" id="uploadImageButton">Upload</button>
</form>

          
            <label for="id">ID:</label>
            <input type="text" id="id" value="<?php echo $userData["id"]; ?>" readonly>

            <label for="fullname">Fullname:</label>
            <input type="text" id="fullname" value="<?php echo $userData["fullname"]; ?>" readonly>

            <label for="username">Username:</label>
            <input type="text" id="username" value="<?php echo $userData["username"]; ?>" readonly>

            <label for="userRole">User Role:</label>
            <input type="text" id="userRole" value="<?php echo $userData["user_role"]; ?>" readonly><br>

<button id="changePasswordButton" onclick="openChangePasswordModal()">Change Password</button><br>
          
           <?php
    if ($_SESSION['user_role'] === 'admin') {
          echo '<h3>Scan QR Code to download the android application.</h3>';
          echo '<img src="images/SAEBSSMSDOWNLOADQR.png" alt="Download QR Code" style="height: 200px; width: 200px;">';
      }
          else {
        
    }
          ?>
          
          	<div id="changePasswordModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeChangePasswordModal()">&times;</span>
    <h2>Change Password</h2>
    <form id="changePasswordForm">
<label for="oldPassword">Old Password:</label>
<input type="password" id="oldPassword" required oninput="checkOldPassword()"><br>
<span id="oldPasswordMessage" style="color: red;"></span><br><br>


<!-- New Password -->
<label for="newPassword">New Password:</label>
<input type="password" id="newPassword" required oninput="checkNewPassword()"><br>
<span id="passwordMessage"></span><br><br>

<!-- Confirm Password -->
<label for="confirmPassword">Confirm Password:</label>
<input type="password" id="confirmPassword" required oninput="checkConfirmPassword()"><br>

      
<!-- Checkbox to show/hide password -->
<input type="checkbox" id="showPassword" onchange="togglePasswordVisibility()" title="Show Password">

<br><br>
<!-- Save Button -->
<button type="button" id="saveButton" onclick="savePassword()" disabled>Save</button>

    </form>
  </div>
</div>
          
            <?php
            // Display the image URL if available
            if (isset($userData["image_url"])) {
                echo '<label for="imageUrl">Image URL:</label>';
                echo '<input type="text" id="imageUrl" value="' . $userData["image_url"] . '" readonly>';
            }
            ?>
        </div>
    <?php endif; ?>
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
  
function openChangePasswordModal() {
    var modal = document.getElementById('changePasswordModal');
    modal.style.display = 'block';
}

function closeChangePasswordModal() {
    var modal = document.getElementById('changePasswordModal');
    modal.style.display = 'none';
}  

function checkOldPassword() {
    var oldPassword = document.getElementById('oldPassword').value;

    // Send an AJAX request to verify the old password
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);

            // Display the verification result with colored text
            var oldPasswordMessage = document.getElementById('oldPasswordMessage');
            oldPasswordMessage.innerText = response.message;
            oldPasswordMessage.style.color = response.success ? 'green' : 'red';


        }
    };

    xhr.open('POST', '', true); // Use the same page URL
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('action=verifyOldPassword&oldPassword=' + encodeURIComponent(oldPassword));
}
  
function checkNewPassword() {
    var newPassword = document.getElementById('newPassword').value;

    // Define the password requirements
    var minLength = 8;
    var hasUpperCase = /[A-Z]/.test(newPassword);
    var hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(newPassword);

    // Display the validation message based on the requirements
    var passwordMessage = document.getElementById('passwordMessage');
    if (newPassword.length >= minLength && hasUpperCase && hasSpecialChar) {
        passwordMessage.innerText = 'New password meets the requirements';
        passwordMessage.style.color = 'green';
    } else {
        passwordMessage.innerText = 'New password must be at least 8 characters long, include one uppercase letter, and one special character';
        passwordMessage.style.color = 'red';
    }
}

function checkConfirmPassword() {
    var newPassword = document.getElementById('newPassword').value;
    var confirmPassword = document.getElementById('confirmPassword').value;

    // Define the password requirements
    var minLength = 8;
    var hasUpperCase = /[A-Z]/.test(newPassword);
    var hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(newPassword);

    // Display the validation message based on the requirements
    var passwordMessage = document.getElementById('passwordMessage');
    if (newPassword.length >= minLength && hasUpperCase && hasSpecialChar) {
        passwordMessage.innerText = 'New password meets the requirements';
        passwordMessage.style.color = 'green';

        // Enable or disable the "Save" button based on password match
        var saveButton = document.getElementById('saveButton');
        saveButton.disabled = !(newPassword === confirmPassword);
    } else {
        passwordMessage.innerText = 'New password must be at least 8 characters long, include one uppercase letter, and one special character';
        passwordMessage.style.color = 'red';

        // Disable the "Save" button if requirements are not met
        var saveButton = document.getElementById('saveButton');
        saveButton.disabled = true;
    }
}

  function savePassword() {
    var newPassword = document.getElementById('newPassword').value;

    // Send an AJAX request to save the new password
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);

            // Display a message based on the response
            if (response.success) {
                // Password saved successfully
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                });

                // Close the modal
                closeChangePasswordModal();
            } else {
                // Error saving password
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }
        }
    };

    xhr.open('POST', '', true); // Use the same page URL
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('action=saveNewPassword&newPassword=' + encodeURIComponent(newPassword));
}


 
</script>
  
  <script>
    document.getElementById('imageUploadForm').addEventListener('submit', function (event) {
    event.preventDefault();

    // Show loader on form submission
    document.getElementById('loaderContainer').style.display = 'flex';

    // Create FormData object to send the file
    var formData = new FormData(this);

    // Send an AJAX request to handle image upload
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);

            // Display a message based on the response
            if (response.success) {
                // Image uploaded successfully
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                });

                // Reload the page to reflect the new image
                location.reload();
            } else {
                // Error uploading image
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }

            // Hide loader
            document.getElementById('loaderContainer').style.display = 'none';
        }
    };

    xhr.open('POST', '', true); // Use the same page URL
    xhr.send(formData);
});

  </script>
  
<script>
    function togglePasswordVisibility() {
        // Get the checkbox
        var showPasswordCheckbox = document.getElementById('showPassword');

        // Get the password input fields
        var oldPasswordInput = document.getElementById('oldPassword');
        var newPasswordInput = document.getElementById('newPassword');
        var confirmPasswordInput = document.getElementById('confirmPassword');

        // Toggle password visibility based on checkbox state
        oldPasswordInput.type = showPasswordCheckbox.checked ? 'text' : 'password';
        newPasswordInput.type = showPasswordCheckbox.checked ? 'text' : 'password';
        confirmPasswordInput.type = showPasswordCheckbox.checked ? 'text' : 'password';
    }

    // Add event listener for the checkbox
    document.getElementById('showPassword').addEventListener('change', togglePasswordVisibility);

</script>

</body>
</html>

