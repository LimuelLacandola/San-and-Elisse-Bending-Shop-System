<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}


// Database connection
require_once 'db_connection.php';

// Retrieve user information from the login table based on the username
$sql = "SELECT id, user_image, fullname, username, user_role FROM login WHERE username = '{$_SESSION['username']}'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch the user data
    $userData = $result->fetch_assoc();
} else {
    echo "User not found";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        body {
            margin: 0;
            display: flex;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            -moz-transform: scale(0.75, 0.75); /* Moz-browsers */
             zoom: 0.75; /* Other non-webkit browsers */
              zoom: 75%; /* Webkit browsers */
        }
    </style>
</head>
<body>
    <?php
    // Convert and format the date in the Philippines time zone
    $transactionDate = new DateTime();
    $transactionDate->setTimezone(new DateTimeZone('Asia/Manila'));
    $transactionDateStr = $transactionDate->format('Y-m-d H:i:s'); // Format the date in the Philippines time zone
    ?>
    
    <section class="receipt">
        <div class="receipt-header">
           <center> <h2>San and Elisse Bending Shop</h2><br></center>
            <center><p>Date: <?php echo $transactionDateStr; ?></p></center>
            
            
            <?php
if (isset($_POST['products'])) {
    $productIdsArray = explode(',', $_POST['products']);
} else {
    // Handle the case when no products are selected
    $productIdsArray = [];
}

// Database connection
require_once 'db_connection.php';


    // Convert and format the date in the Philippines time zone
    $transactionDate = new DateTime();
    $transactionDate->setTimezone(new DateTimeZone('Asia/Manila'));
    $transactionDateStr = $transactionDate->format('Y-m-d H:i:s'); // Format the date in the Philippines time zone

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['products'])) {
        $productIdsArray = explode(',', $_POST['products']);
        $paymentMethod = $_POST['payment_method'];
        $amountPaid = $_POST['amount_paid'];
        $referenceNumber = $_POST['reference_number'];


        

// Insert a record into the transaction history to get a valid transaction_id
$customerName = $_POST['name'];
$transactionDate = date('Y-m-d H:i:s'); // Get the current date and time
$referenceNumber = isset($_POST['reference_number']) ? $_POST['reference_number'] : ''; // Get the reference number

$insertQuery = "INSERT INTO transaction_history (customer_name, transaction_date, total_price, payment_method, amount_paid, referenceNumber, cashier_name) VALUES ('$customerName', '$transactionDateStr', 0, '$paymentMethod', '$amountPaid', '$referenceNumber', '{$userData['fullname']}')";

$conn->query($insertQuery);

// Get the auto-generated transaction_id
$transactionId = $conn->insert_id;


        // Deduct quantities from inventory and calculate total price
        $totalPrice = 0; // Initialize total price

        foreach ($productIdsArray as $productId) {
            $updateQuery = "UPDATE inventory SET quantity = quantity - 1 WHERE id = '$productId' AND quantity > 0";
            $conn->query($updateQuery);

            // Get product details (name and price)
            $productQuery = "SELECT productname, price FROM inventory WHERE id = '$productId'";
            $productResult = $conn->query($productQuery);

            if ($productResult->num_rows > 0) {
                $productData = $productResult->fetch_assoc();
                $productName = $productData['productname'];
                $productPrice = $productData['price'];

                // Check if the product is already in product_details
                $existingProductQuery = "SELECT * FROM transaction_product_details WHERE transaction_id = '$transactionId' AND product_id = '$productId'";
                $existingProductResult = $conn->query($existingProductQuery);

                if ($existingProductResult->num_rows > 0) {
                    // Update the existing row by increasing the quantity
                    $existingProductData = $existingProductResult->fetch_assoc();
                    $newQuantity = $existingProductData['quantity'] + 1;
                    $updateProductQuery = "UPDATE transaction_product_details SET quantity = $newQuantity, transaction_date = '$transactionDateStr' WHERE transaction_id = '$transactionId' AND product_id = '$productId'";
                    $conn->query($updateProductQuery);
                } else {
                    // Insert the product name, quantity, and transaction date into product_details
                    $insertProductDetailsQuery = "INSERT INTO transaction_product_details (transaction_id, product_id, product_name, quantity, price, transaction_date) VALUES ('$transactionId', '$productId', '$productName', 1, '$productPrice', '$transactionDate')";
                    $conn->query($insertProductDetailsQuery);
                }

                // Calculate the total price
                $totalPrice += $productPrice;
            }
        }

        // Update the total price in the transaction history
        $updateTotalPriceQuery = "UPDATE transaction_history SET total_price = $totalPrice WHERE id = $transactionId";
        $conn->query($updateTotalPriceQuery);
    }
}






$products = [];
$totalPrice = 0;

foreach ($productIdsArray as $productId) {
    $query = "SELECT * FROM inventory WHERE id = '$productId'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if (isset($products[$productId])) {
            // Item already exists, increment quantity
            $products[$productId]['quantity']++;
        } else {
            // Item doesn't exist yet, create a new entry
            $products[$productId] = [
                'name' => $row['productname'],
                'value' => $row['price'],
                'quantity' => 1
            ];
        }
        
        $totalPrice += $row['price'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>

    <style>
   /* Reset default margin and padding */
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

.header h1 {
    font-size: 24px;
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

.product-value {
    font-weight: bold;
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

.product-value {
    text-align: right;
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

@media print {
            .print-button-container {
                display: none;
            }
        }
        .print-button-container {
            text-align: center;
            margin-top: 20px;
        }

        .print-button {
        text-decoration: none;
        color: #fff;
        background-color: #333;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-align: center;
        display: inline-block;
        
    }

    /* Style for the print button when hovered */
    .print-button:hover {
        background-color: #333;
    }

    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h3>San and Elisse Bending Shop</h3>
			<h5> Cabuyao, Laguna </h5>
			<h5> +639 12 345 6789 </h5>
        </div>
  
        <div class="content">
    <center><h3>Customer Details</h3></center>
    <div class="customer-info">
        <strong><span>Customer name:</strong></span><p><span><?php echo $_POST['name']; ?></span></p><br>
        </div>


    <div class="product-headers">
        <span class="product-header">Product Name</span>
        <span class="product-header">Quantity</span>
        <span class="product-header">Price</span>
    </div>

    <?php foreach ($products as $productId => $product) { ?>
        <div class="product">
            <span class="product-name"><?php echo $product['name']; ?></span>
            <span class="product-quantity">x<?php echo $product['quantity']; ?></span>
            <span class="product-value">₱<?php echo $product['value']; ?></span>
        </div>
    <?php } ?>

    <div class="total">
        <span>Total:</span>
        <span class="total-value">₱<?php echo $totalPrice; ?></span>
    </div>
    <div class="payment-details">
<div class="payment-details">
    <span><strong>Payment Method:</strong> <?php echo $_POST['payment_method']; ?></span><br>
    <span><strong>Amount Paid:</strong> <?php echo $_POST['amount_paid']; ?></span><br>
<span><strong>Reference Number:</strong> <?php echo isset($_POST['reference_number']) ? $_POST['reference_number'] : 'Not Set'; ?></span><br>
    <span><strong>Change:</strong> ₱<?php echo ($_POST['amount_paid'] - $totalPrice); ?></span><br>
    <span><strong>Cashier name: </strong><?php echo $userData['fullname']; ?></span>
</div>

</div>
</div>
    </div>
    

    
<div class="print-button-container">
    <button class="print-button" onclick="printReceipt()">Print Receipt</button>
</div>

<div class="print-button-container">
    <a href="pos.php" class="print-button">Back</a>
</div>


    <script>
        // Function to print the receipt
   
    
    </script>
    
</body>
</html>
