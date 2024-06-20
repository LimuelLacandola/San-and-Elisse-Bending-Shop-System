      <?php
      session_start();

      if (!isset($_SESSION['username'])) {
          header("Location: login.php"); // Redirect to login page if not logged in
          exit();
      }

      // Database connection
      require_once 'db_connection.php';
      require_once 'access_control.php';


// Fetch user information including lockscreen status
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

      // Fetch log entries from the database
      $query = "SELECT id, username, fullname, login_time, logout_time FROM log ORDER BY login_time DESC";
      $result = $conn->query($query);

      $logs = [];
      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              // Set the time zone to 'Asia/Manila'
              $manilaTimeZone = new DateTimeZone('Asia/Manila');

              // Convert login and logout times to DateTime objects with Manila time zone
              $loginTime = new DateTime($row['login_time'], $manilaTimeZone);
              $logoutTime = !empty($row['logout_time']) ? new DateTime($row['logout_time'], $manilaTimeZone) : null;

              // Initialize variables for time spent
              $timeSpentText = '';

              // Calculate time spent if logout time is available
              if ($logoutTime) {
                  $interval = $loginTime->diff($logoutTime);

                  $hours = $interval->h;
                  $minutes = $interval->i;
                  $seconds = $interval->s;

                  $timeSpentText = '';

                  if ($hours > 0) {
                      $timeSpentText .= $hours . ' hour';
                      if ($hours > 1) {
                          $timeSpentText .= 's';
                      }
                  }

                  if ($minutes > 0) {
                      if (!empty($timeSpentText)) {
                          $timeSpentText .= ', ';
                      }
                      $timeSpentText .= $minutes . ' minute';
                      if ($minutes > 1) {
                          $timeSpentText .= 's';
                      }
                  }

                  if ($seconds > 0) {
                      if (!empty($timeSpentText)) {
                          $timeSpentText .= ', ';
                      }
                      $timeSpentText .= $seconds . ' second';
                      if ($seconds > 1) {
                          $timeSpentText .= 's';
                      }
                  }
              } else {
                  // If logout time is not available
                  $timeSpentText = 'Still logged in';
              }

              $logs[] = [
                  'log_id' => $row['id'],
                  'username' => $row['username'],
                  'fullname' => $row['fullname'],
                  'login_time' => $loginTime->format('Y-m-d H:i:s'), // Format login time with Manila time zone
                  'logout_time' => $logoutTime ? $logoutTime->format('Y-m-d H:i:s') : '', // Format logout time with Manila time zone if available
                  'time_spent' => $timeSpentText,
              ];
          }
      }

      // Close the database connection
      $conn->close();
      ?>




      <!DOCTYPE html>
      <html lang="en">
      <head>
              <link rel = "icon" href =  "images/saebs_logo.png"              type = "image/x-icon"> 
              <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>View Logs</title>
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
                  border-radius: 8px !important;
                  margin-top: 20px;
                  /* Set a max-height for the table container */
                  max-height: 750px; /* Adjust the height as needed */
              }

              tbody {
                  display: block;
                  overflow-y: auto; /* Enable vertical scrolling for the tbody */
              }

              thead, tbody tr {
                  display: table;
                  width: 100%;
                  table-layout: fixed; /* Force the same column width */
              }

              tbody {
                  height: 890px; /* Adjust the height as needed */
              }

              th, td {
                  padding: 12px;
                  text-align: left;
                  border-bottom: 1px solid #ccc;
                  /* Specify a fixed width for the columns */
                  width: 16.66%; /* 100% divided by the number of columns */
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
              <h2>Audit Logs</h2>

              <?php if (empty($logs)): ?>
                  <p>No log entries found.</p>
              <?php else: ?>
                  <table>
      <thead>
          <tr>
              <th>Log ID</th>
              <th>Username</th>
              <th>Full name</th>
              <th>Login Time</th>
              <th>Logout Time</th>
              <th>Time Spent</th>
          </tr>
      </thead>
      <tbody>
          <?php foreach ($logs as $log): ?>
              <tr>
                  <td><?php echo $log['log_id']; ?></td>
                  <td><?php echo $log['username']; ?></td>
                  <td><?php echo $log['fullname']; ?></td>
                  <td><?php echo $log['login_time']; ?></td>
                  <td><?php echo $log['logout_time']; ?></td>
                  <td><?php echo $log['time_spent']; ?></td>
              </tr>
          <?php endforeach; ?>
      </tbody>
                  </table>
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
    var idleTimeout = 600000; // 10 minutes of inactivity
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




      </body>
      </html>