<?php
require_once 'db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expanded Transaction Summary</title>
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
table {
    border-collapse: collapse;
    width: 100%;
    border: 1px solid white; /* Set border color to white */
    border-radius: 8px; /* Add rounded borders to the table */
    margin-top: 20px; /* Add some space above the table */
}

th, td {
    padding: 16px; /* Increase padding for better spacing */
    text-align: left;
    position: sticky;
    border-bottom: 1px solid white; /* Set border color to white */
}

th {
    background-color: black;
}

thead {
    position: sticky;
      color: white; /* Set white text color */

    top: 0;
}

tr:hover {
    background-color: #f5f5f5;
}

/* Style for alternating row colors for better readability */
tr:nth-child(even) {
    background-color: #f9f9f9;
}

        /* Responsive design for smaller screens */
        @media screen and (max-width: 600px) {
            th, td {
                padding: 12px;
            }
        }
    </style>
</head>

<body>
    <?php
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch transactions from the last 7 days
    $query = "SELECT th.id, th.customer_name, th.transaction_date, th.total_price, th.payment_method, th.amount_paid, th.cashier_name, tpd.product_name, tpd.quantity
          FROM transaction_history th
          JOIN transaction_product_details tpd ON th.id = tpd.transaction_id
          WHERE th.transaction_date >= CURDATE() - INTERVAL 7 DAY";

    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        // Output table header
        echo "<table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Transaction Date</th>
                    <th>Total Price</th>
                    <th>Payment Method</th>
                    <th>Amount Paid</th>
                    <th>Cashier Name</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>";

        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['customer_name']}</td>
                <td>{$row['transaction_date']}</td>
                <td>{$row['total_price']}</td>
                <td>{$row['payment_method']}</td>
                <td>{$row['amount_paid']}</td>
                <td>{$row['cashier_name']}</td>
                <td>{$row['product_name']}</td>
                <td>{$row['quantity']}</td>
            </tr>";
        }

        echo "</tbody></table>";
    } else {
        echo "No transactions found.";
    }

    $conn->close();
    ?>
</body>

</html>
