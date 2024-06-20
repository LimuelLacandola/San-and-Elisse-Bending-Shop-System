<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Details</title>
    <style>
        /* Reset default margin and padding */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');
 
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            text-align: center;
        }
        .details-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
                .header img {
            max-width: 50%; /* Set the image width to 100% of its container */
            height: auto; /* Maintain the aspect ratio */
        }
        .header h3 {
            font-size: 24px;
        }
        .detail {
            margin-bottom: 10px;
            text-align: left;
        }
        .detail strong {
            margin-right: 10px;
        }
        .product-details {
            margin-top: 20px;
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }
        .product-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .product-name {
            flex-grow: 1;
            text-align: left;
        }
        .quantity,
        .quantity strong {
            margin-right: 240px;
        }
        
        .quantityheader,
        .quantityheader strong {
            margin-right: 100px;
        }

        .price,
        .price strong {
            text-align: right;
        }

    </style>
</head>
<body>
    <div class="details-container">
        <div class="header">
            <img src="images/saebs_logo.png" alt="Logo">
            <h5> Cabuyao, Laguna </h5>
            <h5> +639 12 345 6789 </h5>
        </div>

        <?php
        // Database connection
        require_once 'db_connection.php';

        // Get the item ID from the query string
        $itemId = isset($_GET['id']) ? $_GET['id'] : '';

        // Prepare and execute the SQL query to fetch item details
        $query = "SELECT * FROM transaction_history WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Output item details
            $itemDetails = $result->fetch_assoc();
            ?>

            <div class="detail">
                <strong>Transaction ID:</strong> <?php echo $itemDetails['id']; ?>
            </div>
            <div class="detail">
                <strong>Customer Name:</strong> <?php echo $itemDetails['customer_name']; ?>
            </div>
            <div class="detail">
                <strong>Transaction Date:</strong> <?php echo $itemDetails['transaction_date']; ?>
            </div>
            <div class="detail">
                <strong>Cashier:</strong> <?php echo $itemDetails['cashier_name']; ?>
            </div>

            <!-- Fetch and display product details -->
            <div class="product-details">
              
                <h4>Product Details</h4>
                <div class="product-detail">
                    <div class="product-name"><strong>Product Name</strong></div>
                    <div class="quantityheader"><strong>Quantity</strong></div>
                    <div class="price"><strong>Price</strong></div>
                </div>
                <?php
                $productQuery = "SELECT product_name, quantity, price FROM transaction_product_details WHERE transaction_id = ?";
                $productStmt = $conn->prepare($productQuery);
                $productStmt->bind_param("i", $itemId);
                $productStmt->execute();
                $productResult = $productStmt->get_result();

                if ($productResult->num_rows > 0) {
                    while ($productDetails = $productResult->fetch_assoc()) {
                        ?>
                        <div class="product-detail">
                            <div class="product-name"><?php echo $productDetails['product_name']; ?></div>
                            <div class="quantity"><?php echo $productDetails['quantity']; ?></div>
                            <div class="price"><?php echo $productDetails['price']; ?></div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p>No product details found.</p>";
                }

                $productStmt->close();
                ?>
            </div>

            <div class="product-details">
                <div class="detail">
                    <strong>Total Price:</strong> ₱<?php echo $itemDetails['total_price']; ?>
                </div>
                <div class="detail">
                    <strong>Amount paid:</strong> ₱<?php echo $itemDetails['amount_paid']; ?>
                </div>
                <div class="detail">
                    <strong>Payment method:</strong> <?php echo $itemDetails['payment_method']; ?>
                </div>
					<div class="detail">
                    <strong>Reference Number:</strong> <?php echo $itemDetails['referenceNumber']; ?>
                </div>
            </div>
            <?php

        } else {
            echo "<p>Transaction not found.</p>";
        }

        $stmt->close();
        $conn->close();
        ?>
    </div>
</body>
</html>
