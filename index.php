  <?php
  date_default_timezone_set('Asia/Manila');
  session_start();

  if (!isset($_SESSION['username'])) {
      header("Location: login.php"); // Redirect to login page if not logged in
      exit();
  }

  // Database connection
  require_once 'db_connection.php';
  require 'PHPMailer/PHPMailerAutoload.php';

  $totalProducts = 0;
  $lowStockItems = [];

  // Retrieve user information from the login table based on the username
  $sql = "SELECT id, user_image, fullname, username, user_role, lockscreen FROM login WHERE username = '{$_SESSION['username']}'";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
      // Fetch the user data
      $userData = $result->fetch_assoc();
  } else {
      echo "User not found";
  }

      // Pass lockscreen status to JavaScript
  echo '<script>';
  echo 'var lockscreenStatus = "' . $userData['lockscreen'] . '";';
  echo '</script>';

  // Get low stock items (quantity less than or equal to 20)
  $lowStockQuery = "SELECT * FROM inventory WHERE quantity <= lowstock_trigger";
  $lowStockResult = $conn->query($lowStockQuery);

  if ($lowStockResult->num_rows > 0) {
      while ($row = $lowStockResult->fetch_assoc()) {
          $lowStockItems[] = [
              'id' => $row['id'],
              'name' => $row['productname'],
              'quantity' => $row['quantity'],
            //  'supplierEmail' => $row['supplier_email'] 
          ];
      }
  }

  // Check if an email for low stock items has already been sent in this session
  // if (!isset($_SESSION['lowStockEmailSent'])) {
  //     // Send email only if there are low stock items
  //     if (!empty($lowStockItems)) {
  //         $_SESSION['displayLowStockModal'] = true;

  //         foreach ($lowStockItems as $item) {
  //             // Send email to each supplier
  //             $mail = new PHPMailer;
  //             $mail->isSMTP();
  //             $mail->SMTPAuth = true;
  //             $mail->SMTPSecure = 'tls';
  //             $mail->Host = 'smtp.gmail.com';
  //             $mail->Port = 587;
  //             $mail->isHTML();
  //             $mail->Username = 'safalaga.bsinfotech@gmail.com';
  //             $mail->Password = 'yrseezbcsgplmkdl'; // Replace with your Gmail App Password
  //             $mail->SetFrom('safalaga.bsinfotech@gmail.com');
  //             $mail->Subject = 'Low Stock';

  //             $currentUser = $_SESSION['username'];
  //             $currentTime = date('Y-m-d H:i:s');

  //             // Construct the email body with low stock item details
  //             $emailBody = "Product: {$item['name']}, Quantity: {$item['quantity']}<br>";
  //             $mail->Body = $emailBody;

  //             // Add supplier's email address as a recipient
  //             $mail->AddAddress($item['supplierEmail']);

  //             // Send the email
  //             if ($mail->Send()) {
  //                 // Email sent successfully to the supplier
  //             } else {
  //                 // Email sending failed
  //             }
  //         }

  //         // Set a session variable to indicate that the low stock email should be sent only once
  //         $_SESSION['lowStockEmailSent'] = true;
  //     }
  // }

  // Set a session variable to indicate that the low stock modal should be displayed only if there are low stock items
  $_SESSION['displayLowStockModal'] = !empty($lowStockItems);

  // Get the total number of sales today
  $currentDate = date("Y-m-d"); // Get the current date in the format YYYY-MM-DD
  $totalTransactions = 0;

      // Query to get the total number of sales for today
      $totalTransactionsQuery = "SELECT COUNT(*) as inventory FROM transaction_history WHERE DATE(transaction_date) = '$currentDate'";
      $totalTransactionsResult = $conn->query($totalTransactionsQuery);

      if ($totalTransactionsResult->num_rows > 0) {
          $row = $totalTransactionsResult->fetch_assoc();
          $totalTransactions = $row['inventory'];
      }

      // Get the total amount of all transactions today
      $totalSales = 0;

      // Query to calculate the total amount of transactions for today
      $totalSalesQuery = "SELECT SUM(total_price) as inventory FROM transaction_history WHERE DATE(transaction_date) = '$currentDate'";
      $totalSalesResult = $conn->query($totalSalesQuery);

      if ($totalSalesResult->num_rows > 0) {
          $row = $totalSalesResult->fetch_assoc();
          $totalSales = $row['inventory'];
      }



      // Get the total amount of transactions yesterday
      $yesterday = date("Y-m-d", strtotime("-1 day", strtotime($currentDate)));
      $totalSalesYesterday = 0;

      // Query to calculate the total amount of transactions for yesterday
      $totalSalesYesterdayQuery = "SELECT SUM(total_price) as inventory FROM transaction_history WHERE DATE(transaction_date) = '$yesterday'";
      $totalSalesYesterdayResult = $conn->query($totalSalesYesterdayQuery);

      if ($totalSalesYesterdayResult->num_rows > 0) {
          $row = $totalSalesYesterdayResult->fetch_assoc();
          $totalSalesYesterday = $row['inventory'];
      }

      // Calculate the percentage change in total sales today vs yesterday
      $percentageChange = 0;
      if ($totalSalesYesterday > 0) {
          $percentageChange = (($totalSales - $totalSalesYesterday) / abs($totalSalesYesterday)) * 100;
      }

      // Format the percentage change to two decimal places
      $percentageChange = number_format($percentageChange, 2);






      // Get the total amount of sales for the current month
      $currentDate = date("Y-m-d"); // Get the current date in the format YYYY-MM-DD
      $totalSalesMonth = 0;

      // Query to calculate the total amount of transactions for the current month
      $totalSalesMonthQuery = "SELECT SUM(total_price) as inventory FROM transaction_history WHERE MONTH(transaction_date) = MONTH('$currentDate') AND YEAR(transaction_date) = YEAR('$currentDate')";
      $totalSalesMonthResult = $conn->query($totalSalesMonthQuery);

      if ($totalSalesMonthResult->num_rows > 0) {
          $row = $totalSalesMonthResult->fetch_assoc();
          $totalSalesMonth = $row['inventory'];
      }

      // Get the total amount of sales for the last month
      $lastMonth = date("Y-m-d", strtotime("-1 month", strtotime($currentDate)));
      $totalSalesLastMonth = 0;

      // Query to calculate the total amount of transactions for the last month
      $totalSalesLastMonthQuery = "SELECT SUM(total_price) as inventory FROM transaction_history WHERE MONTH(transaction_date) = MONTH('$lastMonth') AND YEAR(transaction_date) = YEAR('$lastMonth')";
      $totalSalesLastMonthResult = $conn->query($totalSalesLastMonthQuery);

      if ($totalSalesLastMonthResult->num_rows > 0) {
          $row = $totalSalesLastMonthResult->fetch_assoc();
          $totalSalesLastMonth = $row['inventory'];
      }



      // Calculate the percentage change in total sales today vs yesterday
      $percentageChangeMonth = 0;
      if ($totalSalesLastMonth > 0) {
          $percentageChangeMonth = (($totalSalesMonth - $totalSalesLastMonth) / abs($totalSalesLastMonth)) * 100;
      }

      // Format the percentage change to two decimal places
      $percentageChangeMonth = number_format($percentageChangeMonth, 2);







      // Get the item with the highest total quantity sold during the current month
      $firstDayOfMonth = date('Y-m-01'); // Get the first day of the current month
      $lastDayOfMonth = date('Y-m-t');   // Get the last day of the current month

      $highestQuantityItemQuery = "SELECT product_name, SUM(quantity) as quantity 
          FROM transaction_product_details 
          WHERE DATE(transaction_date) BETWEEN '$firstDayOfMonth' AND '$lastDayOfMonth'
          GROUP BY product_name 
          ORDER BY quantity DESC 
          LIMIT 1";

      $highestQuantityItemResult = $conn->query($highestQuantityItemQuery);

      $highestQuantityItem = null;

      if ($highestQuantityItemResult->num_rows > 0) {
          $highestQuantityItem = $highestQuantityItemResult->fetch_assoc();
      }





      // Get the total number of transactions yesterday
      $yesterday = date("Y-m-d", strtotime("-1 day", strtotime($currentDate)));
      $totalTransactionsYesterday = 0;

      // Query to get the total number of transactions for yesterday
      $totalTransactionsYesterdayQuery = "SELECT COUNT(*) as transactions FROM transaction_history WHERE DATE(transaction_date) = '$yesterday'";
      $totalTransactionsYesterdayResult = $conn->query($totalTransactionsYesterdayQuery);

      if ($totalTransactionsYesterdayResult->num_rows > 0) {
          $row = $totalTransactionsYesterdayResult->fetch_assoc();
          $totalTransactionsYesterday = $row['transactions'];
      }

      // Calculate the difference in total transactions today vs yesterday
      $transactionDifference = $totalTransactions - $totalTransactionsYesterday;




      // Display low stock modal if there are low stock items
      if (!empty($lowStockItems)) {
          $_SESSION['displayLowStockModal'] = true;
      }







      $conn->close();




      ?>

  <!DOCTYPE html>
  <html>
  <head>
        <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">


      <link rel = "icon" href = "images/saebs_logo.png" type = "image/x-icon"> 
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
      <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

      <title>Homepage</title>
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
              overflow-y: auto;
          }

          .navigation {
              background-color: #323031;
              color: #fff;
              width: 250px;
              min-height: 1000px;
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

          .card {
    box-sizing: border-box;
    width: 250px;
    height: 200px;
    background: rgba(217, 217, 217, 0.58);
    border: 1px solid white;
    box-shadow: 12px 17px 51px rgba(0, 0, 0, 0.22);
    backdrop-filter: blur(6px);
    border-radius: 17px;
    text-align: center;
    cursor: pointer;
    transition: all 0.5s;
    display: flex;
    align-items: center;
    justify-content: center;
    user-select: none;
    font-weight: bolder;
    color: black;
  }

  .card:hover {
    border: 1px solid black;
    transform: scale(1.05);
  }

  .card:active {
    transform: scale(0.95) rotateZ(1.7deg);
  }
          /* Additional styles for the cards */
          .cards-container {
      display: flex;
      flex-wrap: wrap;

      gap: 20px;
      width: 100%;
          }

          .card {
      background-color: #f7f7f7;
      border: 1px solid #ccc;
      border-radius: 5px;
      padding: 10px;
      display: inline-block;
      overflow: hidden;

          }
          .card-header {
              background-color: #333;
              color: #fff;
              padding: 10px;
              border-top-left-radius: 5px;
              border-top-right-radius: 5px;
          }
          .card-body {
              justify-content: center;
              padding-top: 25px;
              max-height: 100px; /* Limit the height of the card body */
          }



                  .card-body-charts {
              justify-content: center;
              padding-top: 25px;
              max-height: 180px; /* Limit the height of the card body */
              overflow: auto; /* Add scrollbars if content overflows */
          }

          .low-stock {
              color: red;
              font-weight: bold;
              margin-bottom: 5px; /* Add some spacing between each low-stock item */
              white-space: nowrap; /* Prevent text wrapping */
              overflow: hidden; /* Hide any overflow text */
              text-overflow: ellipsis; /* Display ellipsis (...) for overflow text */
          }
          .black-link {
          color: black;
          text-decoration: none; /* Remove underline */
  }

  /* Modal styles */
  .modal {
      display: none;
      position: fixed;
      z-index: 1;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      overflow: auto;
  }

  .modal-content {
      background-color: #f7f7f7;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      border-radius: 10px;
      padding: 20px;
      text-align: center;
      box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
  }

  .close {
      position: absolute;
      right: 10px;
      top: 10px;
      color: red;
      font-size: 24px;
      font-weight: bold;
      cursor: pointer;
  }

  .close:hover{
      color: #FFBFBF;
  }

  #restockButton{
          padding: 10px 20px;
      margin: 10px;
      background-color: #333;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
  }

  #restockButton:hover{
      background-color: #555;
  }

  #modalHeader{
      color: red;
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
          position: fixed;
              margin-top: 15px;
                  margin-right: 15px;

          top: 10px;
          right: 10px;
          display: flex;
          align-items: center;
          text-decoration: none;
          color: #fff;
          z-index: 1; /* Set a higher z-index to make sure it overlaps other content */
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
              width: 520px; /* Adjust the width as needed to make it bigger than other cards */
               margin-left: 20px;
              height: 300px;
          }


          #chartCard2 {
              width: 545px; /* Adjust the width as needed to make it bigger than other cards */
              margin-left: 30px;
              height: 300px;

          }

           #chartCard3 {
               width: 1100px; /* Adjust the width as needed to make it bigger than other cards */
               margin-left: 20px;
              height: 300px;

          }





          .charts {
      display: block;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: flex-start;
      gap: 20px;
      width: 100%;
  }

  .charts .card {
  width: 100%;

  }

  #dailyTransactionChart{
      justify-content: center;
      margin-top: 30px;
  }

  #dailySalesChart{
      justify-content: center;
      margin-top: 50px;
  }

  /* Media query for screens smaller than 600 pixels */
  @media only screen and (max-width: 600px) {
      /* Adjust styles for smaller screens */
      body {
          font-size: 14px;
      }

      /* Add more adjustments as needed */
  }

  /* Media query for screens between 601 and 1024 pixels */
  @media only screen and (min-width: 601px) and (max-width: 1024px) {
      /* Adjust styles for medium-sized screens */
      body {
          font-size: 16px;
      }


  }

      #modalCard1, #modalCard2, #modalCard3, #modalCard4, #modalCard5, #modalCard6, #modalCard7 {
          display: none; /* Hidden by default */
          position: fixed;
          z-index: 1;
          left: 0;
          top: 0;
          width: 100%;
          height: 100%;
          overflow: auto;
          background-color: rgba(0, 0, 0, 0.8);
      }



  .table-container {
      max-height: 500px; /* Set a fixed height for the table container */
      overflow-y: auto; /* Add a scrollbar if the content exceeds the container height */
      margin-top: 10px;
  }

  #inventoryTable, #salesTable, #dailySalesTable, #mostSoldProductsTable, #transactionTable, #weeklySalesTable {
      width: 100%; /* Make the table fill the container width */
      border-collapse: collapse;
      margin-top: 10px; /* Add some spacing */
  }

  #inventoryTable th, #inventoryTable td, #salesTable th, #salesTable td, #dailySalesTable th, #dailySalesTable td, #mostSoldProductsTable th, #mostSoldProductsTable td, #transactionTable th, #transactionTable td, #weeklySalesTable th, #weeklySalesTable td {
      border: 1px solid #ddd;
      padding: 12px; /* Increase padding for better readability */
      text-align: left;
  }

  #inventoryTable th, #salesTable th, #dailySalesTable th, #mostSoldProductsTable th, #transactionTable th,  #weeklySalesTable th{
      background-color: black; /* Darker background color for header cells */
      color: #fff; /* White text color for header cells */
        position: sticky;
      top: 0;
  }

  #inventoryTable tbody tr:nth-child(odd),
  #salesTable tbody tr:nth-child(odd),
  #dailySalesTable tbody tr:nth-child(odd),
  #mostSoldProductsTable tbody tr:nth-child(odd),
  #transactionTable tbody tr:nth-child(odd),
  #weeklySalesTable tbody tr:nth-child(odd){

      background-color: #f9f9f9; /* Odd row color */
  }

  #inventoryTable tbody tr:nth-child(even),
  #salesTable tbody tr:nth-child(even),
  #dailySalesTable tbody tr:nth-child(even),
  #mostSoldProductsTable tbody tr:nth-child(even),
  #transactionTable tbody tr:nth-child(even), 
  #weeklySalesTable tbody tr:nth-child(even){
      background-color: #ffffff; /* Even row color */
  }

  #inventoryTable tbody tr:hover,
  #salesTable tbody tr:hover,
  #dailySalesTable tbody tr:hover,
  #mostSoldProductsTable tbody tr:hover,
  #transactionTable tbody tr:hover, 
  #weeklySalesTable tbody th:hover{
      background-color: #f5f5f5; /* Change background color on hover for better interaction */
  }
  /* Style for the "Expand" button */
  #expandButton {
      background-color: #4CAF50; /* Green background color */
      color: white; /* White text color */
      padding: 10px 20px; /* Add padding for better spacing */
      border: none; /* Remove border */
      border-radius: 5px; /* Add rounded corners */
      cursor: pointer; /* Add cursor pointer on hover */
      margin-top: 10px;
  }

  #expandButton:hover {
      background-color: #45a049; /* Darker green color on hover */
  }

  #expandDailySalesButton {
      background-color: #4CAF50; /* Green background color */
      color: white; /* White text color */
      padding: 10px 20px; /* Add padding for better spacing */
      border: none; /* Remove border */
      border-radius: 5px; /* Add rounded corners */
      cursor: pointer; /* Add cursor pointer on hover */
      margin-top: 10px;
  }

  #expandDailySalesButton:hover {
      background-color: #45a049; /* Darker green color on hover */
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
            .percentage {
          font-weight: bold;
          margin-right: 5px;
      }

           /* Print styles */
      @media print {
          body * {
              visibility: hidden;
          }
          #modalCard6, #modalCard6 * {
              visibility: visible;
          }
          #modalCard6 {
              position: absolute;
              left: 0;
              top: 0;
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

      <!-- The Modal -->
  <div id="lowStockModal" class="modal">
      <div class="modal-content">
          <span class="close" id="closeModal">&times;</span>
          <img src="images/lowstock.png" style="max-width: 200px;">
          <h1 id="modalHeader">Low Stock Alert!</h1>
          <p id="modalMessage"></p>
        <?php
  if ($_SESSION['user_role'] === 'admin') {
      echo '<button id="restockButton">Request</button>';
  } elseif ($_SESSION['user_role'] === 'frontdesk') {
      // No action for employees (you can add specific employee logic here)
  } 
     elseif ($_SESSION['user_role'] === 'cashier') {
      // No action for employees (you can add specific employee logic here)
  }else {
      echo "Invalid user role.";
  }
  ?>
      </div>
  </div>

 

  <!-- Modal for Card 2 -->
  <div id="modalCard2" class="modal">
      <div class="modal-content">
          <span class="close" id="closeModal2">&times;</span>
          <h1 id="modalHeader2">Transaction Summary</h1>
          <div class="table-container">
              <table id="transactionTable">
                  <thead>
                      <tr>
                          <th>Date</th>
                          <th>Total Transactions</th>
                      </tr>
                  </thead>
                  <tbody id="modalMessage2">
                      <!-- Content will be dynamically added here -->
                  </tbody>
              </table>
          </div>
  <button id="expandButton" onclick="expandTransactionSummary()">Expand</button>
      </div>
  </div>

  <!-- Modal for Expanded Content for transaction table-->
  <div id="expandedModal" class="modal">
      <div class="modal-content">
          <span class="close" id="closeExpandedModal" onclick="closeExpandedModal()">&times;</span>
          <h1 id="expandedModalHeader">Expanded Transaction Summary</h1>
          <h4 id="expandedModalHeader">Transaction Summary for the past 7 days.</h4>
          <button onclick="printModalContent()">
          <i class="material-icons">print</i>
          </button>
          <div class="table-container" id="expandedModalContent">
              <!-- Content will be dynamically added here -->
          </div>
      </div>
  </div>




  <!-- Modal for Card 3 -->
  <div id="modalCard3" class="modal">
      <div class="modal-content">
          <span class="close" id="closeModal3" onclick="closeModal3()">&times;</span>
          <h1 id="modalHeader3">Daily Sales Summary</h1>

          <div class="table-container">
              <table id="dailySalesTable">
                  <thead>
                      <tr>
                          <th>Date</th>
                          <th>Total Sales</th>
                      </tr>
                  </thead>
                  <tbody id="modalMessage3">
                      <!-- Content will be dynamically added here -->
                  </tbody>
              </table>
          </div>
          <!-- Add the "Expand" button for daily sales -->
          <button id="expandDailySalesButton" onclick="expandDailySalesSummary()">Expand</button>
      </div>
  </div>



  <!-- Modal for Expanded Daily Sales Content -->
  <div id="expandedDailySalesModal" class="modal">
      <div class="modal-content">
          <span class="close" id="closeExpandedDailySalesModal" onclick="closeExpandedDailySalesModal()">&times;</span>
          <h1 id="expandedDailySalesModalHeader">Expanded Daily Sales Summary</h1>
          <h4 id="expandedDailySalesModalHeader">Daily Sales for the past 7 days.</h4>
          <button onclick="printExpandedDailySalesModalContent()"><i class="material-icons">print</i></button>
          <div class="table-container" id="expandedDailySalesModalContent">
              <!-- Content will be dynamically added here -->
          </div>
      </div>
  </div>




  <!-- Modal for Card 4 -->
  <div id="modalCard4" class="modal">
      <div class="modal-content">
          <span class="close" id="closeModal4">&times;</span>
          <h1 id="modalHeader4">Sold Products Summary</h1>
          <button onclick="printModalContent4()"><i class="material-icons">print</i></button>

          <div class="table-container">
              <table id="mostSoldProductsTable">
                  <thead>
                      <tr>
                          <th>Product Name</th>
                          <th>Total Quantity Sold</th>
                      </tr>
                  </thead>
                  <tbody id="modalMessage4"></tbody>
              </table>
          </div>
      </div>
  </div>


  <!-- Modal for Card 5 -->
  <div id="modalCard5" class="modal">
      <div class="modal-content">
          <span class="close" id="closeModal5">&times;</span>
          <h1 id="modalHeader5">Monthly Sales Summary</h1>
          <button onclick="printModalContent5()"><i class="material-icons">print</i></button>

          <div class="table-container">
              <table id="salesTable">
                  <thead>
                      <tr>
                          <th>Month</th>
                          <th>Total Sales</th>
                      </tr>
                  </thead>
                  <tbody id="modalMessage5">

                  </tbody>
              </table>
          </div>
      </div>
  </div>

  <!-- Modal for Card 6 -->
  <div id="modalCard6" class="modal">
      <div class="modal-content">
          <span class="close" id="closeModal6">&times;</span>
          <h1 id="modalHeader6">Sold Products Summary</h1>
          <button onclick="printCard6()"><i class="material-icons">print</i></button>

          <canvas id="topProductsChart" width="1020" height="700"></canvas>

      </div>
  </div>


  <!-- Modal for Card 7 -->
  <div id="modalCard7" class="modal">
      <div class="modal-content">
          <span class="close" id="closeModal7">&times;</span>
          <h1 id="modalHeader7">Weekly Sales Summary</h1>

          <button onclick="printModalContent7()"><i class="material-icons">print</i></button>

          <div class="table-container">
              <table id="weeklySalesTable">
                  <thead>
                      <tr>
                          <th>Week</th>
                          <th>Total Sales</th>
                      </tr>
                  </thead>
                  <tbody id="modalMessage7">
                      <!-- Content will be dynamically added here -->
                  </tbody>
              </table>
          </div>
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


      <?php
      // Check if the user has a custom image, otherwise, display a default image
      $userImage = isset($userData["user_image"]) ? $userData["user_image"] : "path/to/default-image.png";
      ?>
      <a href="user_info.php" class="user-info">
          <img src="<?php echo $userImage; ?>" alt="User Image">
      </a>
          <h1>Welcome back, <?php echo $_SESSION['username']; ?>!</h1>
          <h2>Dashboard</h2>

          <div class="cards-container">
                <a href="compare.php">  

              <div class="card" id="totalProductsCard">
                  <div class="card-header">
                   <center>Total Number of Products</center>
                  </div>
                  <div class="card-body">
                      <strong><center><h1><?php echo $totalProducts; ?></h1></center></strong>
                    
                  </div>
                
              </div>
 </a>



                  <div class="card" id="totalTransactionsCard">
                  <div class="card-header">
             <center> Transactions Today</center>
                  </div>
                  <div class="card-body">
              <strong><center><h1><?php echo $totalTransactions; ?> </h1></center></strong>
   <?php
  if ($transactionDifference > 0) {
      echo "<span style='color: #76BA1B;'>{$transactionDifference} more than yesterday</span>";
  } elseif ($transactionDifference < 0) {
      echo "<span style='color: #F46D75;'>" . abs($transactionDifference) . " less than yesterday</span>";
  } else {
      echo "No change in transactions compared to yesterday";
  }
  ?>

                  </div>

              </div>




          <div class="card" id="totalAmountCard">
          <div class="card-header">
              <center>  Total Sales Today </center> 
          </div>

          <div class="card-body">
              <strong><center><h1><?php echo '' . number_format($totalSales, 0); ?></h1></center></strong>
                    <?php
              // Check if the percentage change is positive or negative
              if ($percentageChange > 0) {
                  echo '<p style="color: #76BA1B;">+' . number_format(floatval($percentageChange), 2) . '% vs yesterday</p>';
              } elseif ($percentageChange < 0) {
                  echo '<p style="color: #F46D75;">' . number_format(floatval($percentageChange), 2) . '% vs yesterday</p>';
              } else {
                  echo '<p>No transaction yesterday</p>';
              }
              ?>

          </div>

      </div>



  <div class="card" id="mostSoldCard">
      <div class="card-header">
          <center>Most Sold Product</center>
      </div>
      <div class="card-body">
          <?php if ($highestQuantityItem !== null) { ?>
              <b><center><p><?php echo $highestQuantityItem['product_name']; ?> - <?php echo $highestQuantityItem['quantity']; ?> pcs</p></center>
          <?php } else { ?>
              <center><p>No transaction data available.</p></center></b>
          <?php } ?>
      </div>

  </div>

  <div class="charts">



      <div class="card" id="chartCard">
          <div class="card-header">
              <center>Monthly Sales</center>
          </div>
          <div class="card-body-charts">
              <!-- Specify the canvas for the chart -->
  <canvas id="dailyTransactionChart"></canvas>
                  <strong><center><?php if ($percentageChangeMonth > 0) {
  echo '<h1 style="color: #76BA1B;">+' . number_format(floatval($percentageChangeMonth), 2) . '% vs Last Month</h1></center></strong>';}
                  elseif ($percentageChangeMonth < 0) { echo '<h1 style="color: #F46D75;">' . number_format($percentageChangeMonth,  2) . '% vs Last Month</h1>';}
                  else {
                  echo '<p>No transaction Last Month</p>';
              }?>
                  <p>Last Month: <?php echo '₱' . number_format($totalSalesLastMonth, 0); ?></p>
                  <p>This Month: <?php echo '₱' . number_format($totalSalesMonth, 0); ?></p>

          </div>
      </div>


  <div class="card" id="chartCard2">
      <div class="card-header">
          <center>Top Products</center>
      </div>
      <div class="card-body-charts">
          <!-- Specify the canvas for the pie chart -->
          <canvas id="productCategoriesChart" width="520" height="200"></canvas>
      </div>
  </div>



      <div class="card" id="chartCard3">
          <div class="card-header">
              <center>Weekly Sales</center>
          </div>
          <div class="card-body-charts">
              <!-- Specify the canvas for the line chart -->
          <canvas id="weeklySalesChart"></canvas>
          </div>
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
          // Function to print the content of Modal Card 1
          function printModalContent1() {
          var content = document.getElementById('modalCard1').innerHTML;
          var printWindow = window.open('', '_blank');
          printWindow.document.open();
          printWindow.document.write('<html><head><title>Print</title></head><body>');
          printWindow.document.write('<style>@media print{button{display:none;}}</style>');
          printWindow.document.write(content);
          printWindow.document.write('</body></html>');
          printWindow.document.close();
          printWindow.print();
      }

      // Function to close Modal Card 1
      function closeModal1() {
          document.getElementById('modalCard1').style.display = 'none';
      }

      // Function to print the modal content
      function printModalContent() {
          var content = document.getElementById('expandedModalContent').innerHTML;
          var printWindow = window.open('', '_blank');
          printWindow.document.open();
          printWindow.document.write('<html><head><title>Print</title></head><body>');
          printWindow.document.write(content);
          printWindow.document.write('</body></html>');
          printWindow.document.close();
          printWindow.print();
      }

          // Function to print the content of Expanded Daily Sales Modal
          function printExpandedDailySalesModalContent() {
          var content = document.getElementById('expandedDailySalesModal').innerHTML;
          var printWindow = window.open('', '_blank');
          printWindow.document.open();
          printWindow.document.write('<html><head><title>Print</title></head><body>');
          printWindow.document.write('<style>@media print{button{display:none;}}</style>');
          printWindow.document.write(content);
          printWindow.document.write('</body></html>');
          printWindow.document.close();
          printWindow.print();
      }

      // Function to close Expanded Daily Sales Modal
      function closeExpandedDailySalesModal() {
          document.getElementById('expandedDailySalesModal').style.display = 'none';
      }
          // Function to print the content of Modal Card 4
          function printModalContent4() {
          var content = document.getElementById('modalCard4').innerHTML;
          var printWindow = window.open('', '_blank');
          printWindow.document.open();
          printWindow.document.write('<html><head><title>Print</title></head><body>');
          printWindow.document.write('<style>@media print{button{display:none;}}</style>');
          printWindow.document.write(content);
          printWindow.document.write('</body></html>');
          printWindow.document.close();
          printWindow.print();
      }

      // Function to close Modal Card 4
      function closeModal4() {
          document.getElementById('modalCard4').style.display = 'none';
      }

          // Function to print the content of Modal Card 5
          function printModalContent5() {
          var content = document.getElementById('modalCard5').innerHTML;
          var printWindow = window.open('', '_blank');
          printWindow.document.open();
          printWindow.document.write('<html><head><title>Print</title></head><body>');
          printWindow.document.write('<style>@media print{button{display:none;}}</style>');
          printWindow.document.write(content);
          printWindow.document.write('</body></html>');
          printWindow.document.close();
          printWindow.print();
      }

      // Function to close Modal Card 5
      function closeModal5() {
          document.getElementById('modalCard5').style.display = 'none';
      }

      // Function to print the content of Modal Card 7
      function printModalContent7() {
          var content = document.getElementById('modalCard7').innerHTML;
          var printWindow = window.open('', '_blank');
          printWindow.document.open();
          printWindow.document.write('<html><head><title>Print</title></head><body>');
          printWindow.document.write('<style>@media print{button{display:none;}}</style>');
          printWindow.document.write(content);
          printWindow.document.write('</body></html>');
          printWindow.document.close();
          printWindow.print();
      }

      // Function to close Modal Card 7
      function closeModal7() {
          document.getElementById('modalCard7').style.display = 'none';
      }
  </script>




      <script>
          document.addEventListener('DOMContentLoaded', function () {
              <?php
              // Display low stock modal if session variable is set
              if (isset($_SESSION['displayLowStockModal']) && $_SESSION['displayLowStockModal']) {
                  echo 'showLowStockModal("Request a stock immediately for the following products:", ' . json_encode($lowStockItems) . ');';
                  // Reset the session variable to avoid displaying the modal on subsequent visits
                  unset($_SESSION['displayLowStockModal']);
              }
              ?>
          });
      </script>

  <script>

      // Function to fetch product category data for the pie chart
      function fetchProductCategoryData() {
          fetch('fetch_product_category_data.php') // Replace with the actual API endpoint for fetching product category data
              .then(response => response.json())
              .then(data => {
                  // Update the chart data with fetched values
                  myPieChart.data.labels = data.categories;
                  myPieChart.data.datasets[0].data = data.categoryCounts;

                  // Use specific color codes
                  myPieChart.data.datasets[0].backgroundColor = ['#9cf6f6', '#ff7477', '#d8cdf0'];

                  // Update the chart
                  myPieChart.update();
              })
              .catch(error => {
                  console.error('Error fetching data:', error);
              });
      }

      // Get the canvas element and create a pie chart with initial data
      var pieCanvas = document.getElementById('productCategoriesChart');
      pieCanvas.width = 520; // Set your desired width
      pieCanvas.height = 180; // Set your desired height

      var pieCtx = pieCanvas.getContext('2d');
      var myPieChart = new Chart(pieCtx, {
          type: 'doughnut',
          data: {
              labels: [],
              datasets: [{
                  data: [],
                  backgroundColor: [] // You can set default colors here
              }]
          },
          options: {
              responsive: false, // Disable responsiveness
              plugins: {
                  legend: {
                      position: 'right'
                  }
              }
          }
      });

      // Fetch and update pie chart data when the page loads
      fetchProductCategoryData();

      // Optionally, you can set up a periodic interval to update the data
      // setInterval(fetchProductCategoryData, 60000); // Update every minute (adjust as needed)
  </script>


  <script>
      function populateInventoryTable() {
          var tableBody = document.getElementById("modalMessage1");

          // Clear existing content
          tableBody.innerHTML = '';

          // Make an AJAX request to fetch HTML content from the server
          var xhr = new XMLHttpRequest();
          xhr.onreadystatechange = function () {
              if (xhr.readyState === XMLHttpRequest.DONE) {
                  if (xhr.status === 200) {
                      // Update the modal with the HTML response
                      tableBody.innerHTML = xhr.responseText;
                  } else {
                      console.error("Error fetching data from server");
                  }
              }
          };

          // Specify the URL of your compare.php file or your server endpoint
          xhr.open("GET", "compare.php", true);
          xhr.send();
      }

      // Call the function to populate the modal content when needed
    //  function openModal1() {
          // Call the function to populate the table when the modal is opened
      //    populateInventoryTable();

          // Open the modal
        //  document.getElementById("modalCard1").style.display = "block";
      //}

      // Function to close the modal
      //function closeModal1() {
       //   document.getElementById("modalCard1").style.display = "none";
     // }


      // Call the function to populate the table when the page loads
      populateInventoryTable();
  </script>


  <script>
      function populateSalesTable() {
          var tableBody = document.getElementById("modalMessage5");

          // Clear existing content
          tableBody.innerHTML = '';

          // Make an AJAX request to fetch data from the server
          var xhr = new XMLHttpRequest();
          xhr.onreadystatechange = function () {
              if (xhr.readyState === XMLHttpRequest.DONE) {
                  if (xhr.status === 200) {
                      // Parse the JSON response
                      var salesData = JSON.parse(xhr.responseText);

                      // Loop through the months and add rows to the table
                      for (var monthNumber = 1; monthNumber <= 12; monthNumber++) {
                          var currentMonthData = salesData.find(item => item.month == monthNumber) || { month: monthNumber, totalSales: "0.00" };

                          // Find the data for the previous month
                          var previousMonthNumber = (monthNumber === 1) ? 12 : monthNumber - 1;
                          var previousMonthData = salesData.find(item => item.month == previousMonthNumber) || { totalSales: "0.00" };

                          // Ensure total sales values are valid numbers
                          var currentMonthSales = parseFloat(currentMonthData.totalSales.replace(/[^\d.]/g, '')) || 0;
                          var previousMonthSales = parseFloat(previousMonthData.totalSales.replace(/[^\d.]/g, '')) || 0;

                          // Calculate total sales difference
                          var totalSalesDifference = currentMonthSales - previousMonthSales;

                          // Set the color and font weight based on the total sales difference
                          var color = (totalSalesDifference > 0) ? 'green' : (totalSalesDifference < 0) ? 'red' : 'gray';
                          var fontWeight = (totalSalesDifference !== 0) ? 'bold' : 'normal';

                          // Format the total sales difference text
                          var totalSalesDifferenceText = (totalSalesDifference !== 0) ? `(${totalSalesDifference > 0 ? '+' : ''}${totalSalesDifference.toFixed(2)})` : '(0)';

                          // Create and append the row to the table
                          var row = document.createElement("tr");
                          row.innerHTML = `<td>${getMonthName(currentMonthData.month)}</td><td>${currentMonthSales.toFixed(2)} <span style="color: ${color}; font-weight: ${fontWeight};">${totalSalesDifferenceText}</span></td>`;
                          tableBody.appendChild(row);
                      }
                  } else {
                      console.error("Error fetching data from server");
                  }
              }
          };

          // Specify the URL of your fetch_sales.php file or your server endpoint
          xhr.open("GET", "fetch_monthly_sales_card.php", true);
          xhr.send();
      }

      // Function to get month name from month number
      function getMonthName(monthNumber) {
          var months = [
              "January", "February", "March", "April", "May", "June",
              "July", "August", "September", "October", "November", "December"
          ];
          return months[monthNumber - 1];
      }

      // Call the function to populate the table when needed
      populateSalesTable();
  </script>





  <script>
      function populateMostSoldProductsTable() {
          var tableBody = document.getElementById("modalMessage4");

          // Clear existing content
          tableBody.innerHTML = '';

          // Make an AJAX request to fetch data from the server
          var xhr = new XMLHttpRequest();
          xhr.onreadystatechange = function () {
              if (xhr.readyState === XMLHttpRequest.DONE) {
                  if (xhr.status === 200) {
                      // Parse the JSON response
                      var mostSoldProductsData = JSON.parse(xhr.responseText);

                      // Loop through the data and add rows to the table
                      mostSoldProductsData.forEach(function (item) {
                          var row = document.createElement("tr");
                          row.innerHTML = `<td>${item.product_name}</td><td>${item.totalQuantity}</td>`;
                          tableBody.appendChild(row);
                      });
                  } else {
                      console.error("Error fetching data from server");
                  }
              }
          };

          // Specify the URL of your fetch_most_sold_products.php file or your server endpoint
          xhr.open("GET", "fetch_most_sold_product_card.php", true);
          xhr.send();
      }

      // Call the function to populate the table when needed
      populateMostSoldProductsTable();
  </script>

  <script>
(function () {
    function populateWeeklySalesTable() {
        var tableBody = document.getElementById("modalMessage7");

        // Clear existing content
        tableBody.innerHTML = '';

        // Make an AJAX request to fetch data from the server
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Parse the JSON response
                    var weeklySalesData = JSON.parse(xhr.responseText);

                    // Initialize previous week's total sales
                    var previousWeekTotalSales = 0;

                    // Loop through the weeks and add rows to the table
                    for (var i = 0; i < weeklySalesData.weekRanges.length; i++) {
                        var weekData = weeklySalesData.weekRanges[i];
                        var totalSales = weeklySalesData.weeklySalesData[i];

                        // Calculate sales difference
                        var salesDifference = totalSales - previousWeekTotalSales;
                        var salesDifferenceText = (salesDifference > 0) ? `(+${formatCurrency(salesDifference)})` : (salesDifference < 0) ? `(${formatCurrency(Math.abs(salesDifference))})` : '(0)';

                        // Set the color based on the sales difference
                        var color = (salesDifference > 0) ? 'green' : (salesDifference < 0) ? 'red' : 'gray';
                        var fontWeight = (salesDifference !== 0) ? 'bold' : 'normal';

                        // Update previous week's total sales for the next iteration
                        previousWeekTotalSales = totalSales;

                        // Create a table row
                        var row = document.createElement("tr");

                        // Add columns to the row
                        row.innerHTML = `<td>${weekData}</td><td>${formatCurrency(totalSales)} <span style="color: ${color}; font-weight: ${fontWeight};">${salesDifferenceText}</span></td>`;

                        // Append the row to the table body
                        tableBody.appendChild(row);
                    }
                } else {
                    console.error("Error fetching data from server");
                }
            }
        };

        // Specify the URL of your fetch_weekly_sales_card.php file or your server endpoint
        xhr.open("GET", "fetch_weekly_sales_card.php", true);
        xhr.send();
    }

    // Call the function to populate the table when needed
    populateWeeklySalesTable();
})();

function expandWeeklySalesSummary() {
    // Make an AJAX request to fetch the content of expanded_weekly_sales_summary.php
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // Update the modal content with the fetched data
                document.getElementById("expandedWeeklySalesModalContent").innerHTML = xhr.responseText;

                // Show the new modal for expanded weekly sales summary
                document.getElementById("expandedWeeklySalesModal").style.display = "block";
            } else {
                console.error("Error fetching data from server");
            }
        }
    };

    // Specify the URL of expanded_weekly_sales_summary.php or the appropriate endpoint
    xhr.open("GET", "expanded_weekly_sales_summary.php", true);
    xhr.send();
}

// Function to format currency
function formatCurrency(amount) {
    return `₱${amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

  </script>


  <script>
      (function() {
          function populateDailySalesTable() {
              var tableBody = document.getElementById("modalMessage3");

              // Clear existing content
              tableBody.innerHTML = '';

              // Make an AJAX request to fetch data from the server
              var xhr = new XMLHttpRequest();
              xhr.onreadystatechange = function () {
                  if (xhr.readyState === XMLHttpRequest.DONE) {
                      if (xhr.status === 200) {
                          // Parse the JSON response
                          var dailySalesData = JSON.parse(xhr.responseText);

                          // Loop through the past 7 days and add rows to the table
                          for (var i = 6; i >= 0; i--) {
                              var currentDate = new Date();
                              currentDate.setDate(currentDate.getDate() - i);
                              var formattedDate = formatDate(currentDate);

                              // Find the data for the current date
                              var dayData = dailySalesData.find(item => item.date === formattedDate) || {
                                  date: formattedDate,
                                  totalSales: 0,
                                  dayName: getDayName(currentDate.getDay())
                              };

                              // Find the data for the previous date
                              var previousDate = new Date(currentDate);
                              previousDate.setDate(previousDate.getDate() - 1);
                              var formattedPreviousDate = formatDate(previousDate);
                              var previousDayData = dailySalesData.find(item => item.date === formattedPreviousDate) || {
                                  totalSales: 0
                              };

                              // Calculate sales difference
                              var salesDifference = dayData.totalSales - previousDayData.totalSales;
                              var salesDifferenceText = (salesDifference > 0) ? `(+${salesDifference})` : (salesDifference < 0) ? `(${salesDifference})` : '(0)';

                              // Set the color based on the sales difference
                              var color = (salesDifference > 0) ? 'green' : (salesDifference < 0) ? 'red' : 'gray';
                              var fontWeight = (salesDifference !== 0) ? 'bold' : 'normal';

                              // Create a table row
                              var row = document.createElement("tr");

                              // Add columns to the row
                              row.innerHTML = `<td>${dayData.date} ${dayData.dayName}</td><td>₱${dayData.totalSales} <span style="color: ${color}; font-weight: ${fontWeight};">${salesDifferenceText}</span></td>`;

                              // Append the row to the table body
                              tableBody.appendChild(row);
                          }
                      } else {
                          console.error("Error fetching data from server");
                      }
                  }
              };

              // Specify the URL of your fetch_daily_sales.php file or your server endpoint
              xhr.open("GET", "fetch_daily_sales_card.php", true);
              xhr.send();
          }

          // Function to format date as 'YYYY-MM-DD'
          function formatDate(date) {
              var yyyy = date.getFullYear();
              var mm = String(date.getMonth() + 1).padStart(2, '0'); // January is 0!
              var dd = String(date.getDate()).padStart(2, '0');
              return `${yyyy}-${mm}-${dd}`;
          }

          // Function to get day name from day number (0-6)
          function getDayName(dayNumber) {
              var days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
              return days[dayNumber];
          }

          // Call the function to populate the table when needed
          populateDailySalesTable();
      })();
  </script>



  <script>
      function populateDailyTransactionTable() {
          var tableBody = document.getElementById("modalMessage2");

          // Clear existing content
          tableBody.innerHTML = '';

          // Make an AJAX request to fetch data from the server
          fetch('fetch_daily_transaction_card.php')
              .then(response => {
                  if (!response.ok) {
                      throw new Error(`HTTP error! Status: ${response.status}`);
                  }
                  return response.json();
              })
              .then(dailyTransactionData => {
                  // Loop through the past 7 days and add rows to the table
                  for (var i = 6; i >= 0; i--) {
                      var currentDate = new Date();
                      currentDate.setDate(currentDate.getDate() - i);
                      var formattedDate = formatDate(currentDate);

                      // Find the data for the current date
                      var dayData = dailyTransactionData.find(item => item.date === formattedDate) || {
                          date: formattedDate,
                          transactionCount: 0,
                          dayName: getDayName(currentDate.getDay())
                      };

                      // Find the data for the previous date
                      var previousDate = new Date(currentDate);
                      previousDate.setDate(previousDate.getDate() - 1);
                      var formattedPreviousDate = formatDate(previousDate);
                      var previousDayData = dailyTransactionData.find(item => item.date === formattedPreviousDate) || {
                          transactionCount: 0
                      };

                      // Calculate transactions difference
                      var transactionsDifference = dayData.transactionCount - previousDayData.transactionCount;
                      var transactionsDifferenceText = (transactionsDifference > 0) ? `(+${transactionsDifference})` : (transactionsDifference < 0) ? `(${transactionsDifference})` : '(0)';

                      // Set the color and font weight based on the transactions difference
                      var color = (transactionsDifference > 0) ? '#76BA1B' : (transactionsDifference < 0) ? '#F46D75' : 'gray';
                      var fontWeight = (transactionsDifference !== 0) ? 'bold' : 'normal';

                      // Create a table row
                      var row = document.createElement("tr");

                      // Add columns to the row
                      row.innerHTML = `<td>${dayData.date} ${dayData.dayName}</td><td>${dayData.transactionCount} Transaction(s) <span style="color: ${color}; font-weight: ${fontWeight};">${transactionsDifferenceText}</span></td>`;

                      // Append the row to the table body
                      tableBody.appendChild(row);
                  }
              })
              .catch(error => {
                  console.error("Error fetching data from server:", error.message);
              });
      }

      // Function to format date as 'YYYY-MM-DD'
      function formatDate(date) {
          var yyyy = date.getFullYear();
          var mm = String(date.getMonth() + 1).padStart(2, '0'); // January is 0!
          var dd = String(date.getDate()).padStart(2, '0');
          return `${yyyy}-${mm}-${dd}`;
      }

      // Function to get day name from day number (0-6)
      function getDayName(dayNumber) {
          var days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
          return days[dayNumber];
      }

      // Call the function to populate the table when needed
      populateDailyTransactionTable();

      function expandTransactionSummary() {
      // Make an AJAX request to fetch the content of expanded_transaction_summary.php
      var xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
          if (xhr.readyState === XMLHttpRequest.DONE) {
              if (xhr.status === 200) {
                  // Update the modal content with the fetched data
                  document.getElementById("expandedModalContent").innerHTML = xhr.responseText;

                  // Show the new modal
                  document.getElementById("expandedModal").style.display = "block";
              } else {
                  console.error("Error fetching data from server");
              }
          }
      };

      // Specify the URL of expanded_transaction_summary.php
      xhr.open("GET", "expanded_transaction_summary.php", true);
      xhr.send();
  }



  </script>



  <script>


  // Function to fetch top products data for the new pie chart
  function fetchTopProductsData() {
      fetch('piecharttopproducts.php') // Replace with the actual API endpoint
          .then(response => response.json())
          .then(data => {
              // Update the chart data with fetched values
              myTopProductsChart.data.labels = data.productNames;
              myTopProductsChart.data.datasets[0].data = data.productQuantities;

              // Use specific color codes
              myTopProductsChart.data.datasets[0].backgroundColor = [
      '#a3d8ca', '#f7e4a9', '#c2f0d6', '#f2b6c9', '#d6e7ff',
      '#ffd8b3', '#e0f4ef', '#f5d7eb', '#b3e8f7', '#f9eadc',
      '#c5f7cf', '#ffd6f4', '#e9f3c9', '#f4d1e3', '#c7f1ed',
      '#fbd9e4', '#cfe1ff', '#ffebd3', '#d4f9e5', '#f3cbd8',
      '#b7eef3', '#ffe5cc', '#c9f2d1', '#f6d6e9', '#d2ebf7',
      '#fce9cf', '#eaf6d1', '#f8d6f0', '#c3f0d7', '#f5e0cf',
      '#d7f3eb', '#f0d8e6', '#b8ecf6', '#ffebcc', '#d3f5db',
      '#f4d9e5', '#c9f7e2', '#f9e8cf', '#b5e6f2', '#ffd0e0',
      '#e7f5cb', '#f1d9ea', '#ccf1e5', '#f6e0d4', '#c4f8e3',
      '#ffd4e7', '#dfe9f7', '#f7e5d0', '#c6f3e9', '#fce2d6'
  ]
  ;

              myTopProductsChart.update();

              // Update the legend with percentage labels
              updateLegendWithPercentage(data.productPercentages);
          })
          .catch(error => {
              console.error('Error fetching data:', error);
          });
  }

  // Function to format tooltip content to display percentages
  function formatTooltipPercentage(context) {
      var label = context.dataset.label || '';
      var value = context.formattedValue;
      var percentage = context.dataset.data[context.dataIndex] + '%';
      return label + ': ' + percentage;
  }

  // Function to update legend labels with percentages
  function updateLegendWithPercentage(percentages) {
      var legendLabels = document.getElementById('legend-labels');
      legendLabels.innerHTML = '';

      percentages.forEach(function (percentage, index) {
          var listItem = document.createElement('li');
          listItem.innerHTML = '<span class="percentage">' + percentage + '%</span> ' + myTopProductsChart.data.labels[index];
          legendLabels.appendChild(listItem);
      });
  }


  // Get the canvas element and create a new pie chart with initial data
  var topProductsCanvas = document.getElementById('topProductsChart');
  topProductsCanvas.width = 1020;
  topProductsCanvas.height = 700;

  var topProductsCtx = topProductsCanvas.getContext('2d');
  var myTopProductsChart = new Chart(topProductsCtx, {
      type: 'bar',
      data: {
          labels: [],
          datasets: [{
              data: [],
              backgroundColor: []
          }]
      },
      options: {
          responsive: false,
          plugins: {
              legend: {
                  position: 'right',
                  onClick: null, // Disable legend click events
                  labels: {
                      usePointStyle: true,
                      generateLabels: function (chart) {
                          var data = chart.data;
                          if (data.labels.length && data.datasets.length) {
                              return data.labels.map(function (label, i) {
                                  var meta = chart.getDatasetMeta(0);
                                  var ds = data.datasets[0];
                                  var arc = meta.data[i];
                                  var custom = {
                                      text: ds.data[i] + '% ' + label,
                                      fillStyle: ds.backgroundColor[i],
                                      hidden: isNaN(ds.data[i]) || meta.data[i].hidden,
                                      index: i,
                                  };
                                  return custom;
                              });
                          }
                          return [];
                      }
                  }
              },
              tooltip: {
                  callbacks: {
                      label: formatTooltipPercentage
                  }
              }
          }
      }
  });

  // Fetch and update top products data when the page loads
  fetchTopProductsData();

  // Optionally, you can set up a periodic interval to update the data
  // setInterval(fetchTopProductsData, 60000); // Update every minute (adjust as needed)

  </script>





  <script>
      document.addEventListener('DOMContentLoaded', function () {
      // Add event listeners for each card
      document.getElementById('totalProductsCard').addEventListener('click', function () {
          showModal('modalCard1');
      });

      document.getElementById('totalTransactionsCard').addEventListener('click', function () {
          showModal('modalCard2');
      });

          document.getElementById('totalAmountCard').addEventListener('click', function () {
          showModal('modalCard3');
      });

          document.getElementById('mostSoldCard').addEventListener('click', function () {
          showModal('modalCard4');
      });

              document.getElementById('chartCard').addEventListener('click', function () {
          showModal('modalCard5');
      });

                  document.getElementById('chartCard2').addEventListener('click', function () {
          showModal('modalCard6');
                  fetchTopProductsData();

      });

                  document.getElementById('chartCard3').addEventListener('click', function () {
          showModal('modalCard7');
      });


      // Function to show a modal
      function showModal(modalId) {
          const modal = document.getElementById(modalId);
          modal.style.display = 'block';

          // Add event listener for the "x" button
          const closeButton = modal.querySelector('.close');
          if (closeButton) {
              closeButton.addEventListener('click', function () {
                  closeModal(modalId);
              });
          }
      }



      // Function to close a modal
      function closeModal(modalId) {
          const modal = document.getElementById(modalId);
          modal.style.display = 'none';
      }

      // Close the modal when clicking outside it
      window.onclick = function (event) {
          if (event.target.className === 'modal') {
              event.target.style.display = 'none';
          }
      };
  });

  </script>


  <script>
      document.addEventListener('DOMContentLoaded', function () {
          fetch('fetch_daily_transaction_data.php')
              .then(response => response.json())
              .then(data => {
                  var weekRanges = data.weekRanges;
                  var weeklySalesData = data.weeklySalesData;

                  // Define an array of pastel colors
                  var pastelColors = [
                      'rgba(255, 209, 220)', // Pastel Pink
                      'rgba(182, 225, 228)', // Pastel Blue
                      'rgba(255, 245, 157)', // Pastel Yellow
                      'rgba(223, 205, 252)', // Pastel Purple
                      // Add more pastel colors as needed
                  ];

                  var ctx = document.getElementById('weeklySalesChart').getContext('2d');

                  var chart = new Chart(ctx, {
                      type: 'bar',
                      data: {
                          labels: weekRanges,
                          datasets: [{
                              label: 'Weekly Sales',
                              data: weeklySalesData,
                              backgroundColor: pastelColors
                          }]
                      },
                      options: {
                          scales: {
                              x: {
                                  type: 'category',
                                  labels: weekRanges
                              },
                              y: {
                                  beginAtZero: true
                              }
                          },
                          maintainAspectRatio: false,
                          responsive: true,
                          plugins: {
                              legend: {
                                  display: true,
                                  position: 'top'
                              }
                          },
                          width: 800, // Set the custom width
                          height: 400 // Set the custom height
                      }
                  });
              })
              .catch(error => console.error('Error fetching data:', error));
      });
  </script>






  <script>
      // Function to fetch monthly transaction data
      function fetchMonthlyTransactionData() {
          // Make an AJAX request to fetch data from the server
          fetch('fetch_monthly_transaction_data.php')
              .then(response => response.json())
              .then(data => {
                  // Update the chart data with fetched values
                  myBarChart.data.labels = data.labels;
                  myBarChart.data.datasets[0].data = data.transactionCounts;

                  // Update the chart
                  myBarChart.update();
              })
              .catch(error => {
                  console.error('Error fetching data:', error);
              });
      }

      // Sample data for the initial state of the bar graph
      var initialData = {
          labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
          datasets: [{
              label: 'Monthly Sales',
              backgroundColor: ['#ff686b', '#ffa3c2', '#ceb5b7', '#abc4ff', '#e8d1c5', '#daeaf6', '#6e78ff', '#b5d6d6', '#dfb2f4', '#f6bc66', '#cdb4db', '#ffcaaf'],
              data: [15, 25, 20, 30, 35]
          }]
      };

      // Get the canvas element and create a bar chart with initial data
      var ctx = document.getElementById('dailyTransactionChart').getContext('2d');
      var myBarChart = new Chart(ctx, {
          type: 'bar',
          data: initialData,
          options: {
              responsive: true,
              maintainAspectRatio: false
          }
      });

      // Fetch and update data when the page loads
      fetchMonthlyTransactionData();

      // Optionally, you can set up a periodic interval to update the data
      // setInterval(fetchMonthlyTransactionData, 60000); // Update every minute (adjust as needed)
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




      <script>

          // Function to update total products and low stock items
          function updateData() {
              fetch('fetch_index.php')
                  .then(response => response.json())
                  .then(data => {
                      // Update total products card
                      document.getElementById('totalProductsCard').innerHTML = `
                          <div class="card-header">
                              <center>Total Number of Products</center>
                          </div>
                          <div class="card-body">
                              <strong><center><h1>${data.totalProducts} </h1></center></strong>
                          </div>
                      `;

                      // Update low stock items card
                      const lowStockItemsCard = document.getElementById('lowStockItemsCard');
                      lowStockItemsCard.innerHTML = `
                          <div class="card-header">
                              <center>Low Stock Items</center>
                          </div>
                          <div class="card-body">
                              ${data.lowStockItems.length > 0 ? data.lowStockItems.map(item => `<center><p class="low-stock">${item.name} ${item.quantity} pcs left.</p></center>`).join('') : '<center><p>No low stock items.</p></center>'}
                          </div>
                      `;
                      document.getElementById('totalTransactions').textContent = `${data.totalTransactions} SALES`;
                  });
          }

          // Initial data update
          updateData();

          // Set up interval to periodically update the data (adjust as needed)
          setInterval(updateData, 5000); // Update every 5 seconds
      </script>

      <script>
  // Function to display the low stock modal
  function showLowStockModal(message, lowStockItems) {
      const modal = document.getElementById("lowStockModal");
      const modalMessage = document.getElementById("modalMessage");
          console.log("Inside showLowStockModal");

      modalMessage.innerHTML = message;

      // Create a list of low stock items and add it to the modal message
      const itemDetails = lowStockItems.map(item => `${item.name} - ${item.quantity} pcs left`).join('<br>');
      modalMessage.innerHTML += `<br><strong><br>${itemDetails}</strong>`;

      modal.style.display = "block";

      // Close the modal when clicking the "Close" button or outside the modal
      const closeModal = document.getElementById("closeModal");
      closeModal.onclick = function () {
          modal.style.display = "none";

          // Set a flag in localStorage to indicate that the modal has been closed
          localStorage.setItem("lowStockModalClosed", "true");
      }

      // Redirect to restock.php when clicking the "Restock" button
      const restockButton = document.getElementById("restockButton");
      restockButton.onclick = function () {
          window.location.href = "restock.php";
      }

      // Close the modal when clicking outside it
      window.onclick = function (event) {
          if (event.target === modal) {
              modal.style.display = "none";

              // Set a flag in localStorage to indicate that the modal has been closed
              localStorage.setItem("lowStockModalClosed", "true");
          }
      }
  }

  // Function to update total products and low stock items
  function updateData() {
      fetch('fetch_index.php')
          .then(response => response.json())
          .then(data => {
              // Update total products card
              document.getElementById('totalProductsCard').innerHTML = `
                  <div class="card-header">
                      <center>Total Number of Products</center>
                  </div>
                  <div class="card-body">
                      <strong><center><h1>${data.totalProducts} </h1></center></strong>
                  </div>
              `;

              // Update low stock items card
              const lowStockItemsCard = document.getElementById('lowStockItemsCard');
              lowStockItemsCard.innerHTML = `
                  <div class="card-header">
                      <center>Low Stock Items</center>
                  </div>
                  <div class="card-body">
                      ${data.lowStockItems.length > 0 ? data.lowStockItems.map(item => `<center><p class="low-stock">${item.name} ${item.quantity} pcs left.</p></center>`).join('') : '<center><p>No low stock items.</p></center>'}
                  </div>
              `;

              // Check if there are low stock items and if the session variable is set to display the modal
              if (data.lowStockItems.length > 0 && <?php echo (isset($_SESSION['displayLowStockModal']) && $_SESSION['displayLowStockModal']) ? 'true' : 'false'; ?>) {
                  const message = "An email has been sent to the supplier for the following low stock items.";
                  showLowStockModal(message, data.lowStockItems);
              }
          });


  }


  // Initial data update
  updateData();


  </script>

  </body>
  </html>