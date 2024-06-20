<?php
include 'db_connection.php';

// Fetch supplier names from the inventory table
$sql = "SELECT DISTINCT supplier_name FROM inventory";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Names</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
        }

        .card {
            cursor: pointer;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 10px;
            padding: 15px;
            text-align: center;
            width: 200px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
          	
          	
        }

        .modal-content {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
          	width: 500px;
          	height: 300px;
          	overflow-x: auto;
        }
      	
      	    .item-container {
        text-align: left;
    }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <h2>Supplier Names</h2>

    <div class="card-container">
<?php
// Display supplier names in cards
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $supplierName = $row['supplier_name'];

        // Fetch count of items with the selected supplier_name and quantity <= 20
        $sqlCount = "SELECT COUNT(*) as item_count FROM inventory WHERE supplier_name = '$supplierName' AND quantity <= 20";
        $resultCount = $conn->query($sqlCount);
        $rowCount = $resultCount->fetch_assoc();
        $itemCount = $rowCount['item_count'];

        // Display the card with the supplier name and item count
        echo '<div class="card" onclick="openModal(\'' . $supplierName . '\')">'  . $supplierName . "<br>". ' (' . $itemCount . ' Low stock items)</div>';
    }
} else {
    echo "No supplier names found in the inventory.";
}

// Close the database connection
$conn->close();
?>
    </div>

    <!-- Modal -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h3 id="modalTitle"></h3>
        <div id="modalContent"></div>
        <button onclick="sendItems()">Send</button>
    </div>
</div>

<script>
    function openModal(supplierName) {
        document.getElementById("modalTitle").innerText = supplierName;
        document.getElementById("myModal").style.display = "flex";

        // Make an AJAX request to get items with the selected supplier_name
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                var items = JSON.parse(this.responseText);
                displayItems(items);
            }
        };
        xmlhttp.open("GET", "get_items.php?supplier_name=" + supplierName, true);
        xmlhttp.send();
    }

    function displayItems(items) {
        var modalContent = document.getElementById("modalContent");
        modalContent.innerHTML = "<h3>Items:</h3>";

        if (items.length > 0) {
            for (var i = 0; i < items.length; i++) {
                var productName = items[i].productname || 'N/A';
                var quantity = items[i].quantity || 'N/A';

modalContent.innerHTML += "<div class='item-container'>" +
                          "<input type='checkbox' id='itemCheckbox" + i + "'>" +
                          "<label for='itemCheckbox" + i + "'>Product: " + escapeHtml(productName) +
                          ", Quantity: " + escapeHtml(quantity) + "</label>" +"</div>";
            }
        } else {
            modalContent.innerHTML += "<p>No items found for this supplier.</p>";
        }
    }

    // Function to escape HTML special characters
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };

        return text.replace(/[&<>"']/g, function (m) {
            return map[m];
        });
    }

    function closeModal() {
        document.getElementById("myModal").style.display = "none";
    }

    // Close modal if clicked outside the content
    window.onclick = function (event) {
        var modal = document.getElementById("myModal");
        if (event.target == modal) {
            closeModal();
        }
    }
</script>

</body>

</html>
