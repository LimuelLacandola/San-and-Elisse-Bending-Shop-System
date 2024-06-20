<?php

date_default_timezone_set('Asia/Manila');

// Start a session
session_start();

// Database connection
require_once 'db_connection.php';

// Include SweetAlert CDN links
echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel = "icon" href = "images/saebs_logo.png" type = "image/x-icon"> 
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
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
<body>';

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch the user's data from the login table
    $query = "SELECT * FROM login WHERE username=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
  
  

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $hashedPassword = $row['password'];
      
          // Check the account_status
    if ($row['account_status'] == 'deactivated') {
        // Account is deactivated, show alert message and redirect to login.php
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: 'Your account is deactivated. Please contact the administrator for assistance.',
                }).then(() => {
                    window.location = 'index.php';
                });
            </script>";
        exit; // Stop further execution
    }
      
              // Check if the login_attempts session variable exists
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
        }

        // Verify the password using password_verify
        if (password_verify($password, $hashedPassword)) {

            $_SESSION['login_attempts'] = 0;

            // Login successful
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_role'] = $row['user_role'];
            $fullname = $row['fullname'];
          
         	

            date_default_timezone_set('Asia/Manila');
            $loginTime = date('Y-m-d H:i:s');

            // Add login information to the log table
            $logQuery = "INSERT INTO log (username, fullname, login_time) VALUES (?, ?, ?)";
            $logStmt = $conn->prepare($logQuery);
            $logStmt->bind_param('sss', $username, $fullname, $loginTime);
            $logStmt->execute();
            $logStmt->close();
          
// Check if the original quantities for today have already been saved
$today = date('Y-m-d');
$checkQuery = "SELECT * FROM daily_inventory WHERE date = '$today'";
$result = $conn->query($checkQuery);

if ($result->num_rows === 0) {
    // If not, save the original quantities for today
    $itemsQuery = "SELECT id, quantity FROM inventory";
    $itemsResult = $conn->query($itemsQuery);

    while ($row = $itemsResult->fetch_assoc()) {
        $itemID = $row['id'];
        $quantity = $row['quantity'];

        // Insert into daily_inventory table for today
        $insertQuery = "INSERT INTO daily_inventory (item_id, original_quantity, date) VALUES ($itemID, $quantity, '$today')";
        $conn->query($insertQuery);
    }

    // Save the original quantities to stock_on_hand on the previous date
    $yesterday = date('Y-m-d', strtotime($today . ' -1 day'));
    $updateQuery = "UPDATE daily_inventory
                    SET stock_on_hand = original_quantity
                    WHERE date = '$yesterday'";
    $conn->query($updateQuery);

} else {
}


            // Redirect to appropriate page based on the user's role
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Login Successful',
                    text: 'Welcome, $fullname!',
                    showConfirmButton: false,
 					 timer: 1000
                }).then(() => {
                    window.location = 'index.php';
                });
            </script>";
        } else {
            // Increment login attempts
            $_SESSION['login_attempts']++;

            // Check if login attempts reach the limit
            if ($_SESSION['login_attempts'] >= 3) {
                // Update the account_status to 'deactivated' in the login table
                $updateQuery = "UPDATE login SET account_status='deactivated' WHERE username=?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param('s', $username);
                $updateStmt->execute();
                $updateStmt->close();

                // Show alert message for account deactivation
                echo "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Account Deactivated',
                            text: 'Your account has been deactivated due to multiple failed login attempts. Please contact the administrator for assistance.',
                        }).then(() => {
                            window.location = 'index.php';
                        });
                    </script>";
                exit; // Stop further execution
            } else {
                // Login failed, but not reached the limit yet
                echo "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Failed',
                            text: 'Invalid username or password',
                        });
                    </script>";
            }
        }
    } else {
        // Login failed
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: 'Invalid username or password',
                });
            </script>";
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="/images/saebs_logo.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <meta charset="UTF-8">
    <title>Login</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

     body {
        background: url('images/hardwareloginbg.png') center/cover no-repeat fixed;
        background-size: cover;
        background-repeat: no-repeat;
        background-attachment: fixed;
        font-family: 'Poppins', sans-serif;
        color: #333333;
        margin: 0;
        padding: 0;
        overflow: hidden;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }

        .card {
            width: 60%;
            background: #FFFFFF;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 0 20px 6px #6f6f6f;
            padding: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: row;
        }

.left-section {
    flex: 1;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: start;
    justify-content: flex-start;
    text-align: left;
}

        .right-section {
            flex: 1;
            padding: 20px;
      /*    background-color: #424d63; */

        }

        .logo {
        max-width: 200px;
        max-height: 550px;
        margin-top: 40px;    
		margin-bottom: 40px;    
            
        }

        .text-container {
            max-width: 300px;
            margin-bottom: 20px;
            text-align: left;
        }

        .box-form {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .box-form h5 {
            font-size: 5vmax;
            line-height: 0;
        }

        .box-form .inputs {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .box-form input {
            width: 100%;
            padding: 15px;
            margin-top: 15px;
            font-size: 16px;
            border: none;
            outline: none;
            border-radius: 5px;
            border: 2px solid #B0B3B9;
            background-color: #f8fafc;
            transition: border-color 0.3s, background-color 0.3s;
        }

        .box-form input::placeholder {
            color: #94a3b8;
        }

        .box-form input:focus, .box-form input:hover {
            border-color: rgba(111, 111, 111);
            background-color: #fff;
        }

        .box-form button {
            background: #333;
            color: #fff;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            padding: 10px 75px;
            border-radius: 10px;
            border: 0;
            outline: 0;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-left: 35px;
            margin-top: 20px;
         
        }

        .box-form button:hover {
            background-color: #333;
        }
.text-container h5 {
    font-size: 3vmax;
    font-weight: bold; 
  color: #323031;
}

        .text-container h4 {
    font-size: 20px; 
    margin: 0;
          color: #323031;

}

.box-form label {
    margin-top: 15px;
    margin-right: 10px;
}

.eye-icon-container {
    position: absolute;
    top: 50%;
    right: -10px;
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
      
.forgot-password {
    text-align: center;
    margin-top: 1px;
}

.forgot-password a {
    color: #000000; /* Link color - you can change this to your preferred color */
    text-decoration: none;
    font-weight: bold;
  	font-size: 14px;
  	margin-left: 40px;
}

.forgot-password a:hover {
    text-decoration: underline; /* Underline link on hover */
}

    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="left-section">
                <div class="text-container">
                    <h5>Welcome to San and Elisse Bending Shop</h5>
                    <h4>Cabuyao, Laguna</h4><br>
                    <h4>0915-415-4919</h4>
                  	<h4>0938-176-3063</h4>
                </div>
            </div>
            <div class="right-section">
<div class="box-form">
                <img class="logo" src="images/saebslogo.png" alt="Store Logo">
    <div class="inputs">
        <form action="login.php" method="post">
            <input type="text" id="username" name="username" placeholder="Username" required><br>
            <div style="position: relative;">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <span id="eyeIcon" class="eye-icon-container">
        <img id="closedEye" class="eye-icon" src="images/hide.png" alt="Closed Eye">
        <img id="openEye" class="eye-icon" src="images/show.png" alt="Open Eye" style="display: none;">
    </span>
               <div class="forgot-password">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>
            </div>
            <center><button>LOG IN</button></center>
        </form>
                
    </div>
</div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const openEyeIcon = document.getElementById('openEye');
            const closedEyeIcon = document.getElementById('closedEye');
            const eyeIconContainer = document.getElementById('eyeIcon');

            eyeIconContainer.addEventListener('click', function () {
                openEyeIcon.style.display = openEyeIcon.style.display === 'none' ? 'block' : 'none';
                closedEyeIcon.style.display = closedEyeIcon.style.display === 'none' ? 'block' : 'none';
                passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
            });
        });
    </script>
</body>
</html>