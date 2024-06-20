<?php
date_default_timezone_set('Asia/Manila');

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'db_connection.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the selected start and end dates from the form or use the current date as default
if (isset($_POST['startDate']) && isset($_POST['endDate'])) {
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
} else {
    // Default to the current date if not provided
    $startDate = date("Y-m-d");
    $endDate = date("Y-m-d");
}

$searchProduct = isset($_POST['searchProduct']) ? $_POST['searchProduct'] : '';

// Fetch data from the inventory table
$sqlInventory = "SELECT id, productname, quantity FROM inventory";
$resultInventory = $conn->query($sqlInventory);

// Fetch data from the daily_inventory table for the selected date range
$sqlDailyInventory = "SELECT item_id, SUM(stock_on_hand) AS total_stock_on_hand, SUM(original_quantity) AS total_original_quantiy FROM daily_inventory WHERE DATE(date) >= ? AND DATE(date) <= ? GROUP BY item_id";
$stmtDailyInventory = $conn->prepare($sqlDailyInventory);
$stmtDailyInventory->bind_param("ss", $startDate, $endDate);
$stmtDailyInventory->execute();
$resultDailyInventory = $stmtDailyInventory->get_result();

// Fetch data from the transaction_product_details table for the selected date range
$sqlTransactionDetails = "SELECT product_id, SUM(quantity) AS total_sales
                          FROM transaction_product_details
                          WHERE DATE(transaction_date) BETWEEN ? AND ?
                          GROUP BY product_id";
$stmtTransactionDetails = $conn->prepare($sqlTransactionDetails);
$stmtTransactionDetails->bind_param("ss", $startDate, $endDate);
$stmtTransactionDetails->execute();
$resultTransactionDetails = $stmtTransactionDetails->get_result();

// Fetch total quantity returned along with the reason from the return_history table for the selected date range
$sqlReturnHistory = "SELECT item_id, SUM(quantity_returned) AS total_returned_quantity, GROUP_CONCAT(DISTINCT reason SEPARATOR ', ') AS return_reason
                    FROM return_history
                    WHERE DATE(return_date) BETWEEN ? AND ?
                    GROUP BY item_id";
$stmtReturnHistory = $conn->prepare($sqlReturnHistory);
$stmtReturnHistory->bind_param("ss", $startDate, $endDate);
$stmtReturnHistory->execute();
$resultReturnHistory = $stmtReturnHistory->get_result();

// Check if there are results in any of the tables
if ($resultInventory->num_rows > 0 && $resultDailyInventory->num_rows > 0 && $resultTransactionDetails->num_rows > 0 && $resultReturnHistory->num_rows > 0) {
    // Fetch data from the tables
    $inventoryData = $resultInventory->fetch_all(MYSQLI_ASSOC);
    $dailyInventoryData = $resultDailyInventory->fetch_all(MYSQLI_ASSOC);
    $transactionDetailsData = $resultTransactionDetails->fetch_all(MYSQLI_ASSOC);
    $returnHistoryData = $resultReturnHistory->fetch_all(MYSQLI_ASSOC);

echo "
    <head>
        <link rel='icon' href='images/saebs_logo.png' type='image/x-icon'> 
        <link rel='stylesheet' type='text/css' href='https://cdn.jsdelivr.net/npm/sweetalert2@11'>
<script src='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js'></script>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script src='https://code.jquery.com/jquery-3.3.1.slim.min.js'></script>
        <script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js'></script>
        <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js'></script>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <link rel='stylesheet' href='https://fonts.googleapis.com/icon?family=Material+Icons'>
        <title>Inventory Report</title>
    </head>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');
        body {
            margin: 0;
            display: flex;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
        }

        .container {
            margin: 20px;
            margin-top: 30px;
            overflow: auto;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 8px !important;
            margin-top: 20px;
            overflow-y: auto;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ccc;
        }

        th {
            background-color: #f2f2f2;
            position: sticky;
            top: 0;
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

        form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            margin-right: 10px;
        }

        input[type='date'], input[type='text'] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            height: 44px;
        }

        input[type='submit'],
        button {
            padding: 8px;
            background-color: #323031;
            height: 40px;
            width: 40px;
            color: #fff;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            margin-top: 10px;
            margin-left: 10px;
            margin-right: 20px;
        }

        input[type='submit']:hover,
        button:hover {
            background-color: #595557;
        }
        .centered-forms {
    text-align: center;
    margin: auto;
}
		.hidden{
        display: none;
        }
	
        .modal {
            display: none;
            align-items: center;
            justify-content: center;
        }

        .modal-dialog {
            max-width: 800px;
            width: 100%;
            margin: auto;
        }

        .modal-content {
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        .modal-header {
            background-color: #f2f2f2;
            padding: 15px;
            border-bottom: 1px solid #ccc;
        }

        .modal-body {
            padding: 20px;
        }
    </style>";

    // Place the table inside a div with class 'container'
    echo '<div class="container">';
    echo '<a href="index.php"><button><i class="material-icons" title="Home">home</i></button></a>';
	echo '<button onclick="printTable()"><i class="material-icons" title="Print">print</i></button>';
    echo '<h3>Selected Date Range: ' . $startDate . ' to ' . $endDate . '</h3>';

    // Display the form for selecting a date range
    echo '<div class="centered-forms">
    <form method="post" action="">
          <label for="startDate">Start Date:</label>
          <input type="date" id="startDate" name="startDate" value="' . $startDate . '">

          <label for="endDate">End Date:</label>
          <input type="date" id="endDate" name="endDate" value="' . $endDate . '">

      <button type="submit" name="searchButton"><i class="material-icons" title="Search Transaction">receipt_long</i></button>
           </form>
           
      <div class="hidden">    
      <form method="post" action="">
      <label for="searchProduct">Search Product:</label>
      <input type="text" id="searchProduct" name="searchProduct" placeholder="Enter product name">
      <button type="submit" name="searchButton"><i class="material-icons" title="Search Product">search</i></button>
      </form>
      </div>
      </div>';

// Display the results in an HTML table with added CSS styles
echo "<table id='printableTable'>
<tr>
  <th>ID</th>
  <th>Product Name</th>
  <th>Stock on Hand</th>
  <th>Total Sales</th>
  <th>Total Returns</th>
  <th>Original Stock</th>
  <th>Discrepancy</th>
</tr>
<tbody>";

foreach ($inventoryData as $rowInventory) {
      if (stripos($rowInventory['productname'], $searchProduct) !== false || empty($searchProduct)) {

        

    // Check if the selected date range is the current date
    if ($startDate === date("Y-m-d") && $endDate === date("Y-m-d")) {
        // Fetch stock on hand directly from the inventory table's quantity column
        $stockOnHand = $rowInventory['quantity'];
    } else {
        // Fetch cumulative stock_on_hand from the daily_inventory table for the selected date range
        $stockOnHand = 0;
        foreach ($dailyInventoryData as $rowDailyInventory) {
            if ($rowDailyInventory['item_id'] == $rowInventory['id']) {
                // Check if the selected date is the current date
                if ($endDate === date("Y-m-d")) {
                    // If it's the current date, add the quantity from the inventory table
                    $stockOnHand = $rowDailyInventory['total_stock_on_hand'] + $rowInventory['quantity'];
                } else {
                    // If it's a previous date, only add the total_stock_on_hand
                    $stockOnHand = $rowDailyInventory['total_stock_on_hand'];
                }
                break;
            }
        }
    }



        // Find the corresponding total sales from the transaction_details table
        $totalSales = 0;
        foreach ($transactionDetailsData as $rowTransactionDetails) {
            if ($rowTransactionDetails['product_id'] == $rowInventory['id']) {
                $totalSales = $rowTransactionDetails['total_sales'];
                break;
            }
        }


        // Find the corresponding total returned quantity from the return_history table
        $totalReturnedQuantity = 0;
        foreach ($returnHistoryData as $rowReturnHistory) {
            if ($rowReturnHistory['item_id'] == $rowInventory['id']) {
                $totalReturnedQuantity = $rowReturnHistory['total_returned_quantity'];
                break;
            }
        }


        // Display the original_quantity from the daily_inventory table
        $originalQuantity = 0;
        foreach ($dailyInventoryData as $rowDailyInventory) {
            if ($rowDailyInventory['item_id'] == $rowInventory['id']) {
                $originalQuantity = $rowDailyInventory['total_original_quantiy'];
                break;
            }
        }
        
                    $returnReason = "";
            foreach ($returnHistoryData as $rowReturnHistory) {
                if ($rowReturnHistory['item_id'] == $rowInventory['id']) {
                    $returnReason = $rowReturnHistory['return_reason'];
                    break;
                }
            }

        // Calculate the Missing Items
        $missingItems = $stockOnHand + $totalSales + $totalReturnedQuantity - $originalQuantity;

        // This block echoes the row with discrepancy information
            echo "<tr onclick=\"showModal('{$rowInventory['id']}', '{$rowInventory['productname']}', '{$stockOnHand}', '{$totalSales}', '{$totalReturnedQuantity}', '{$originalQuantity}', '{$missingItems}', '{$returnReason}')\">";
        echo "<td>{$rowInventory['id']}</td>";
        echo "<td>{$rowInventory['productname']}</td>";
        echo "<td>{$stockOnHand}</td>";
        echo "<td>{$totalSales}</td>";
        echo "<td>{$totalReturnedQuantity}</td>";
        echo "<td>{$originalQuantity}</td>";



 
        echo "<td>";

        if ($missingItems < 0) {
            echo abs($missingItems) . " missing";
        } elseif ($missingItems > 0) {
            echo $missingItems . " excess";
        } else {
            echo "No Discrepancies";
        }

        echo "</td></tr>";
    }
}
    echo "</tbody></table>";
  
    echo '</div>';


// JavaScript function to populate modal data
    echo "
    <script>
        function showModal(productId, productName, stockOnHand, totalSales, totalReturns, originalStock, discrepancy, returnReason) {
            $('#modalProductId').text(productId);
            $('#modalProductName').text(productName);
            $('#modalStockOnHand').text(stockOnHand);
            $('#modalTotalSales').text(totalSales);
            $('#modalTotalReturns').text(totalReturns);
            $('#modalOriginalStock').text(originalStock);
            $('#modalDiscrepancy').text(discrepancy);
            $('#modalReturnReason').text(returnReason); // New line to display return reason
            $('#myModal').modal('show');
        }
        
    function printTable() {
        var printWindow = window.open('', '_blank');
        var contentToPrint = document.getElementById('printableTable').outerHTML;
        printWindow.document.write('<html><head><title>Print</title></head><body>' + contentToPrint + '</body></html>');
        printWindow.document.close();
        printWindow.print();
    }
    </script>";
  
  echo '
<div class="modal" tabindex="-1" role="dialog" id="myModal" hidden>
    <div class="modal-dialog modal-dialog-centered" role="document"> <!-- Add "modal-dialog-centered" class -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Return Details for Product ID <span id="modalProductId"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
<div class="modal-body">
    <p><strong>Product Name:</strong> <span id="modalProductName"></span></p>
    <p><strong>Reason for Returning:</strong> <span id="modalReturnReason"></span></p>

</div>


        </div>
    </div>
</div>';
} else {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>";

    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'No results found',
                    showConfirmButton: false,
                    timer: 1000 // Set the timer as needed
                }).then(() => {
                    window.location.href = 'index.php'; // Redirect to compare.php
                });
            });
          </script>";
}

// Close the prepared statements and the database connection
$stmtTransactionDetails->close();
$stmtReturnHistory->close();
$stmtDailyInventory->close();
$conn->close();
?>