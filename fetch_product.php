<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}


require_once 'db_connection.php';

$productId = $_GET['id'];

// Prepare and execute the SQL query to fetch product information by ID
$query = "SELECT * FROM inventory WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

// Display product information in the modal
if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
      <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
      <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Product Information</title>
                <script>
            function confirmDisable(productId) {
                var confirmResult = confirm("Are you sure you want to disable this product?");
                if (confirmResult) {
                    // If user clicks OK, proceed with disable action
                    disableProduct(productId);
                }
            }

            function disableProduct(productId) {
                // Add your AJAX or form submission logic to update product status to 'disabled' in the database
                // You can use XMLHttpRequest or fetch API for this purpose
                // Example using fetch API:
                fetch('disable_product.php?id=' + productId, {
                    method: 'POST',
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to disable product');
                    }
                    // Reload the page or update UI as needed
                    location.reload();
                })
                .catch(error => console.error('Error:', error));
            }

            function enableProduct(productId) {
                // Add your AJAX or form submission logic to update product status to 'active' in the database
                // Similar to the disableProduct function
                fetch('enable_product.php?id=' + productId, {
                    method: 'POST',
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to enable product');
                    }
                    // Reload the page or update UI as needed
                    location.reload();
                })
                .catch(error => console.error('Error:', error));
            }
        </script>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');
            

            .flex {
                display: flex;
                gap: 10px;
                justify-content: center;
            }
.editButton,
.deleteButton,
.enableButton {
    padding: 10px;
    background-color: #333;
    color: #fff;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    transition: background-color 0.3s;
    margin-right: 10px;
}

.editButton:hover,
.deleteButton:hover,
.enableButton:hover { /* Corrected selector for :hover state */
    background-color: #555;
}

          	
          <style>
.material-symbols-outlined {
  font-variation-settings:
  'FILL' 0,
  'wght' 400,
  'GRAD' 0,
  'opsz' 24
}

                  p {
           text-align: left;
            padding: 10px 0; /* 10px top and bottom padding, 0 left and right */
            max-width: 800px; /* Optionally set a maximum width for paragraphs */
            margin: 0 auto; /* Center-align paragraphs within the body */
        }
        </style>
    </head>
    <body>
        <h2 class="title">Product Information</h2>
        <img src="<?= $product['image_url'] ?>" alt="Product Image" style="max-width: 200px;">
        <p><strong>Product Name:</strong> <?= $product['productname'] ?></p>
        <p><strong>Brand:</strong> <?= $product['brand'] ?></p>
        <p><strong>Category:</strong> <?= $product['category'] ?></p>
        <p><strong>Description:</strong> <?= $product['description'] ?></p>
      	<p><strong>Measurement:</strong> <?= $product['measurement'] ?></p>
        <p><strong>Quantity:</strong> <?= $product['quantity'] ?></p>
      	<p><strong>Status:</strong>
    <?php
if ($product['quantity'] >= 50) {
    $statusText = 'On Stock';
    $statusColor = 'green';
} elseif ($product['quantity'] > 20) {
    $statusText = 'Critical';
    $statusColor = 'orange';
} elseif ($product['quantity'] > 0) {
    $statusText = 'Low Stock';
    $statusColor = 'red';
} else {
    $statusText = 'No Stock';
    $statusColor = 'gray';
}
    ?>
    <span style="color: <?= $statusColor ?>;"><strong><?= $statusText ?></strong></span>
</p>
      	<p><strong>Unit of measure:</strong> <?= $product['unit_of_measure'] ?></p>
        <p><strong>Measurement:</strong> <?= $product['measurement'] ?></p>
		<p><strong>Price:</strong>  <?= number_format($product['price'], 2) ?></p>
        <p><strong>Supplier Name:</strong> <?= $product['supplier_name'] ?></p>
        <p><strong>Supplier Email:</strong> <?= $product['supplier_email'] ?></p>
        <p><strong>Supplier Location:</strong> <?= $product['supplier_location'] ?></p>
        <p><strong>Contact Number:</strong> <?= $product['contact_number'] ?></p>
        <p><strong>Contact Person:</strong> <?= $product['contact_person'] ?></p>
      	<p><strong>Low Stock Limit:</strong> <?= $product['lowstock_trigger'] ?></p>







<?php
        if ($_SESSION['user_role'] === 'admin') {
            echo '<div class="flex">';
            echo '<button class="editButton" title="Edit product" data-product-id="' . $product['id'] . '"><i class="material-icons">edit</i></button>';
            
            if ($product['product_status'] === 'active') {
                // If product status is 'active', hide the enable button
                echo '<button class="deleteButton" title="Disable product" onclick="disableProduct(' . $productId . ')"><i class="material-icons">visibility_off</i></button>';
                echo '<button class="enableButton" style="display: none;" title="Enable product" onclick="enableProduct(' . $product['id'] . ')"><i class="material-icons">visibility</i></button>';
            } elseif ($product['product_status'] === 'disabled') {
                // If product status is 'disabled', hide the disable button
                echo '<button class="deleteButton" style="display: none;" title="Disable product" onclick="disableProduct(' . $productId . ')"><i class="material-icons">visibility_off</i></button>';
                echo '<button class="enableButton" title="Enable product" onclick="enableProduct(' . $product['id'] . ')"><i class="material-icons">visibility</i></button>';
            }

            echo '</div>';
        } elseif ($_SESSION['user_role'] === 'frontdesk' || $_SESSION['user_role'] === 'cashier') {
            // No action for employees (you can add specific employee logic here)
        } else {
            echo "Invalid user role.";
        }
?>





    </body>
    </html>

    <?php
} else {
    echo 'Product not found.';
}

$stmt->close();
$conn->close();