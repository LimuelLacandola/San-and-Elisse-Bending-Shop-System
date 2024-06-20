<?php
date_default_timezone_set('Asia/Manila');

// Retrieve parameters from the URL
$customerName = $_GET['customerName'];
$totalPrice = $_GET['totalPrice'];
$paymentMethod = $_GET['paymentMethod'];
$amountPaid = $_GET['amountPaid'];
$referenceNumber = $_GET['referenceNumber'];
$cashierName = $_GET['cashierName'];
$selectedItemsJSON = $_GET['selectedItems'];

// Decode the JSON string to an array
$selectedItems = json_decode($selectedItemsJSON, true);

// Now, you can use these variables in your receipt.php file as needed.
// For example, you can echo them or use them in your HTML code.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  		    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
          <link rel = "icon" href =  "images/saebs_logo.png"        type = "image/x-icon"> 
      <title>Receipt</title>

    <style>
        /* Reset default margin and padding */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
			-moz-transform: scale(0.75, 0.75);  
             zoom: 0.75;  
              zoom: 75%;  
        }

        .receipt {
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h3 {
            font-size: 24px;
        }

        .header h5 {
            font-size: 14px;
            margin-bottom: 5px;
        }
.footer {
    text-align: justify; /* Set text-align for paragraphs */
    margin-top: 20px;
    padding-top: 10px;
    border-top: 1px solid #ccc;
}

.footer p {
    text-align: justify; /* Text-align for paragraphs */
}

.footer span {
    display: block; /* Ensures span takes full width */
    text-align: center; /* Center-align the span */
    margin-top: 10px; /* Add margin for better spacing */
}

      
        .content {
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }

        .product {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .product-name {
            flex-grow: 1;
        }

        .product-quantity,
        .product-value {
            text-align: right;
        }

        .total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
        }

        .total span:first-child {
            font-weight: bold;
        }

        .total-value {
            font-weight: bold;
        }

        .product-headers {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ccc;
        }

        .product-header {
            text-align: left;
        }

        .product-quantity {
            flex-grow: 2;
            text-align: center;
        }

        .customer-info {
            display: flex;
            align-items: center;
            margin-top: 10px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ccc;
        }

        .customer-info strong {
            margin-right: 10px;
        }

        .print-button-container {
            text-align: center;
            margin-top: 20px;
			display: flex;
            margin-bottom: 10px;
          	justify-content: center;
        }

        .print-button {
          	border-radius: 50%;
            text-decoration: none;
            color: #fff;
            background-color: #333;
            padding: 20px;
            border: none;
            cursor: pointer;
            text-align: center;
            display: inline-block;
          margin-right: 20px;
        }

        /* Style for the print button when hovered */
        .print-button:hover {
            background-color: #333;
        }

        @media print {
            .print-button-container {
                display: none;
            }
        }

    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
			<img src="images/saebslogo.png" alt="Logo" style="max-width: 250px;">
            <h5>Cabuyao, Laguna</h5>
            <h5>+639 12 345 6789</h5>
          	<h5>TIN ID: 123-456-789-000
        </div>

        <div class="content">
            <center><h3>Customer Details</h3></center>
<p><strong>Transaction Date: </strong><?php echo date("Y-m-d H:i:s"); ?></p>
            <div class="customer-info">
                <strong>Customer name:</strong>
                <p><?php echo $_GET['customerName']; ?></p>
            </div>

            <div class="product-headers">
                <span class="product-header">Product Name</span>
                <span class="product-header">Quantity</span>
                <span class="product-header">Price</span>
            </div>

            <?php
            // Loop through the selected items and display them in the receipt
            foreach ($selectedItems as $item) {
                ?>
                <div class="product">
                    <span class="product-name"><?php echo $item['productName']; ?></span>
                    <span class="product-quantity">x<?php echo $item['quantity']; ?></span>
                    <span class="product-value">₱<?php echo $item['price'] * $item['quantity']; ?></span>
                </div>
                <?php
            }
            ?>

            <div class="total">
                <span>Total:</span>
                <span class="total-value">₱<?php echo $totalPrice; ?></span>
            </div>

            <div class="payment-details">
                <span><strong>Payment Method:</strong> <?php echo $_GET['paymentMethod']; ?></span><br>
                <span><strong>Amount Paid:</strong> <?php echo $_GET['amountPaid']; ?></span><br>
                <span><strong>Reference Number:</strong> <?php echo $_GET['referenceNumber']; ?></span><br>
                <span><strong>Change:</strong> ₱<?php echo ($_GET['amountPaid'] - $totalPrice); ?></span><br>
                <span><strong>Cashier name: </strong><?php echo $_GET['cashierName']; ?></span>
            </div>
                    <div class="footer">
                      <p>All returned merchandise must be accompanied by a sales receipt  within 30 days of purchase.</p>
                      <p>Items must be in original condition with the original packaging intact. Custom Mixed parts are non-refundable.</p>
                      <strong><span>Thank you for shopping!</span></strong>
          </div>
        </div>

    </div>

    <div class="print-button-container">
        <button class="print-button" onclick="window.print()"><i class="material-icons">print</i></button>
        <button class="print-button" onclick="goToPosPage()"><i class="material-icons">arrow_back</i></button>
        <button class="print-button" onclick="goToHomePage()"><i class="material-icons">home</i></button>
    </div>
      
      
  
      <script>
        function goToPosPage() {
            window.location.href = 'pos.php';
        }
        
		function goToHomePage() {
            window.location.href = 'index.php';
        }
    </script>
</body>
</html>


