<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel = "icon" href = "images/saebs_logo.png" type = "image/x-icon"> 
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <title>Request Restock</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            -moz-transform: scale(0.75, 0.75); /* Moz-browsers */
            zoom: 0.75; /* Other non-webkit browsers */
            zoom: 75%; /* Webkit browsers */
        }

        .table-container {
            max-height: 730px; /* Set the desired height for the container */
            overflow: auto;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 8px !important;
            margin-top: 20px;
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
            z-index: 2;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        input[type="checkbox"] {
            margin-right: 5px;
        }

        input[type="number"] {
            width: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .requested-text {
            color: #777;
            font-size: 12px;
        }

        button {
          	height: 60px;
          	width: 60px;
            margin-top: 10px;
            padding: 10px;
            background-color: #4caf50;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
          	background-color: #323031;
            display: block;
    		margin-left: auto;
    		margin-right: auto;
        }

        button:hover {
            background-color: #595557;
        }
    </style>
</head>
<body>
  
  <h2>Request Stock</h2>

<?php
include('db_connection.php');

// Fetch products with quantity 20 or below
$sql = "SELECT * FROM inventory WHERE quantity <= lowstock_trigger";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo '<form action="send_email.php" method="post">';
    echo '<div class="table-container">';
    echo '<table>';
    echo '<thead>';
    echo '<tr><th>Image</th><th>Product Name</th><th>Quantity</th><th>Supplier Name</th><th>Request Quantity</th><th>Delivery Date</th><th>Select All<input type="checkbox" id="selectAll"></th></tr>';
    echo '</thead>';
    echo '<tbody>';

    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td><img src="' . $row['image_url'] . '" alt="Product Image" style="max-width: 100px; max-height: 100px;"></td>';
        echo '<td>' . $row['productname'] . '</td>';
        echo '<td>' . $row['quantity'] . '</td>';
        echo '<td>' . $row['supplier_email'] . '</td>';
        echo '<td><input type="number" name="request_quantity[' . $row['id'] . ']" value="1" min="1"></td>';
        echo '<td>' . $row['delivery_date'] . '</td>';

        // Check email_status and disable checkbox if "Requested"
        $disabled = ($row['email_status'] == 'Requested') ? 'disabled' : '';
        $requestedText = ($row['email_status'] == 'Requested') ? '<span class="requested-text">Requested</span>' : '';

        echo '<td><input type="checkbox" name="selected_items[]" value="' . $row['id'] . '" ' . $disabled . '>' . $requestedText . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    echo '<button type="submit"><span class="material-icons">send</span></button>';
    echo '</form>';
} else {
    echo '<p>No products found with quantity 20 or below.</p>';
}

$conn->close();
?>
<script>
    document.getElementById('selectAll').addEventListener('change', function () {
        var checkboxes = document.getElementsByName('selected_items[]');
        for (var i = 0; i < checkboxes.length; i++) {
            // Only check or uncheck enabled checkboxes
            if (!checkboxes[i].disabled) {
                checkboxes[i].checked = this.checked;
            }
        }
    });
</script>

</body>
</html>
