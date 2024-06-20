<?php
date_default_timezone_set('Asia/Manila');
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}


// Database connection
require_once 'db_connection.php';
require_once 'access_control.php';


// Retrieve user information from the login table based on the username
$sql = "SELECT id, user_image, fullname, username, user_role, lockscreen FROM login WHERE username = '{$_SESSION['username']}'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch the user data
    $userData = $result->fetch_assoc();
      // Check access control for employee
    restrictAccessToEmployee($_SESSION['user_role']);
    // Check access control for cashier
} else {
    echo "User not found";
}

	// Pass lockscreen status to JavaScript
echo '<script>';
echo 'var lockscreenStatus = "' . $userData['lockscreen'] . '";';
echo '</script>';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  	    <link rel = "icon" href = "images/saebs_logo.png" type = "image/x-icon"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
    <title>Point of Sale</title>
    <style>
        /* Add your CSS styles here */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

        .container {
            display: flex;
        }

        #item-container {
            flex: 1;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            padding: 20px;
			overflow-y: auto; /* Make the container scrollable vertically */	
            max-height: 125vh; /* Set maximum height to 80% of the viewport height */
        }

.item-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    margin: 10px;
    padding: 15px;
    cursor: pointer;
    width: 100px; 
    text-align: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease-in-out;
}

.item-card:hover {
    transform: scale(1.05);
}

.item-card img {
    max-width: 100%;
    height: auto;
    border-radius: 6px;
    margin-bottom: 10px;
}

.item-card p {
    font-size: 12px; /* Adjust the font size for paragraphs */
    margin: 5px 0;
    color: #555;
}

.item-card h3 {
    font-size: 14px; /* Adjust the font size for headings */
    margin: 8px 0;
    color: #333;
}



        #selected-items {
            flex: 1;
            margin-top: 20px;
            padding: 20px;
            border-left: 1px solid #ccc;
          
        }

        #selected-items-table {
            width: 100%;
            border-collapse: collapse;
        }

        #selected-items-table th,
        #selected-items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        #selected-items-table th {
            background-color: #f2f2f2;
        }

        .quantity-input {
            width: 50px;
        }

        #search-bar {
            margin-bottom: 10px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #56514B;
            font-size: 16px;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center; /* Center horizontally */
            -moz-transform: scale(0.75, 0.75); /* Moz-browsers */
            zoom: 0.75; /* Other non-webkit browsers */
            zoom: 75%; /* Webkit browsers */
        }

        h2 {
            margin-bottom: 10px;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }
      
  .checkout {
    display: flex;
    justify-content: space-between;
  }

  .checkout h2 {
    text-align: left;
  }

  .checkout #checkout-button {
    text-align: right;
    margin-right: 50px;
    margin-bottom: 30px;
    margin-top: 30px;
  }
      
       #payment-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            width: 300px;
            text-align: center;
            border-radius: 8px;
        }

        #payment-modal h2 {
            margin-bottom: 20px;
        }

        #payment-modal label {
            display: block;
            margin-bottom: 5px;
            text-align: left;
        }

        #payment-modal input,
        #payment-modal select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        #payment-modal button {
            background-color: #333;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #payment-modal button:hover {
            background-color: #555;
        }

        #payment-modal .cancel-button {
            background-color: #ccc;
            color: #000;
            margin-right: 10px;
        }

        #payment-modal .cancel-button:hover {
            background-color: #555;
        }

        #modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
      
      .item-card.disabled {
    cursor: not-allowed;
    opacity: 0.5;
}
#payment-modal #proceed-button:disabled {
    background-color: #ccc;
    color: #666;
    cursor: not-allowed;
    opacity: 0.7;
}
      


        #payment-amount-display {
            font-weight: bold;
        }

        button {
            background-color: #333;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #555;
        }

        #payment-modal .cancel-button {
            background-color: #333;
            color: #fff;
            margin-right: 10px;
        }

        #payment-modal .cancel-button:hover {
            background-color: #555;
        }

        #payment-modal #proceed-button:disabled,
        .item-card.disabled {
            background-color: #ccc;
            color: #666;
            cursor: not-allowed;
            opacity: 0.7;
        }
      
        #search-bar {
            width: 30%;
            padding: 10px;
            margin-top: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
      
        #goBackButton {
	    position: fixed;
	    top: 20px;
	    left: 20px;
	    background-color: #333;
	    color: #fff;
	    padding: 10px;
	    border: none;
	    border-radius: 50%;
	    cursor: pointer;
	    transition: background-color 0.3s;
	    z-index: 1000;
        }

        #goBackButton:hover {
            background-color: #555;
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
      <button id="goBackButton">
  <i class="material-icons" title="Back">home</i>
  </button>

    <input type="text" id="search-bar" placeholder="Search products" oninput="filterItems()">

    <div class="container">
        <div id="item-container">
<?php
require_once 'db_connection.php';

$sql = "SELECT * FROM inventory WHERE product_status = 'active'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Set the item card class based on quantity
        $itemCardClass = $row['quantity'] == 0 ? 'item-card disabled' : 'item-card';
        
        echo '<div class="' . $itemCardClass . '" data-productname="' . $row['productname'] . '" ';
        
        // Only attach the onclick attribute if the quantity is not 0
        if ($row['quantity'] > 0) {
echo 'onclick="addItem(' . $row['id'] . ', \'' . $row['productname'] . '\', ' . $row['price'] . ', ' . $row['quantity'] . ', \'' . $row['image_url'] . '\')"';
        }
        
        echo '>';
        echo '<img src="' . $row['image_url'] . '" alt="' . $row['productname'] . '">';
        echo '<p style="font-weight: bold;">' . $row['productname'] . '</p>';
        echo '<p>₱' . $row['price'] . '</p>';
        echo '<p>Quantity: ' . $row['quantity'] . '</p>';
        echo '</div>';
    }
} else {
    echo 'No items in inventory.';
}

$conn->close();
?>
        </div>

        <div id="selected-items">
            <h2>Selected Items</h2>
    <div class="checkout">
        <h2>Total Price: <span id="total-price">₱0</span></h2>
        <button id="checkout-button" onclick="openPaymentModal()">Checkout</button>
    </div>
          
   <!-- Payment Modal -->
              <div id="modal-backdrop"></div>
<div id="payment-modal">
    <h2>Payment Details</h2>
    <label for="customer-name">Customer Name:</label>
<input type="text" id="customer-name" oninput="updatePaymentAmountDisplay()">

    <label for="payment-method">Payment Method:</label>
    <select id="payment-method">
        <option value="cash">Cash</option>
        <option value="GCash">GCash</option>
        <option value="Maya">Maya</option>
        <!-- Add more payment methods as needed -->
    </select>

    <label for="payment-amount">Payment Amount: (Pay: <span id="payment-amount-display"></span>) </label>
    <input type="number" id="payment-amount" min="0" oninput="updatePaymentAmountDisplay()" required>

    <label for="reference-number">Reference Number: (Type "Cash" if the payment method is Cash")</label>
    <input type="text" id="reference-number">

<button id="proceed-button" onclick="processPayment()" disabled>Proceed</button>
    <button onclick="closePaymentModal()">Cancel</button>
</div>



            <table id="selected-items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="selected-items-list"></tbody>
            </table>
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
      var selectedItems = [];
function addItem(id, productName, price, availableQuantity, imageURL) {
    Swal.fire({
        title: 'Enter quantity for ' + productName,
        input: 'number',
        inputLabel: 'Please enter a quantity between 1 and ' + availableQuantity + ' pcs.',
        imageUrl: imageURL,
        imageWidth: 300,
  		imageHeight: 300,
        inputAttributes: {
            min: 1,
            max: availableQuantity,
            step: 1
        },
        inputValue: 1,
        showCancelButton: true,
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            var parsedQuantity = parseInt(result.value);

            // Check if the entered quantity is a valid positive integer and does not exceed the available quantity
    if (!isNaN(parsedQuantity) && parsedQuantity > 0 && parsedQuantity <= availableQuantity) {
        var itemDetails = {
            id: id,
            productName: productName,
            price: price,
            quantity: parsedQuantity
        };

        // Add the selected item to the global array
        selectedItems.push(itemDetails);

        displaySelectedItem(itemDetails);
        updateTotalPrice();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid quantity',
                    text: 'Please enter a valid quantity between 1 and ' + availableQuantity,
                });
            }
        }
    });
}

function displaySelectedItem(item) {
    var tableBody = document.getElementById('selected-items-list');
    var newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>${item.productName}</td>
        <td>${item.price}</td>
        <td><input type="number" class="quantity-input" value="${item.quantity}" min="1" oninput="updateTotalPrice()"></td>
        <td>₱${item.price * item.quantity}</td>
        <td class="action-buttons">
            <button onclick="updateQuantity(this, ${item.id})">Update</button>
            <button onclick="removeItem(this, ${item.id})">Remove</button>
        </td>
    `;
    tableBody.appendChild(newRow);

    updateTotalPrice();
}


function updateQuantity(button, itemId) {
    var row = button.closest('tr');
    var quantityInput = row.querySelector('.quantity-input');
    var newQuantity = parseInt(quantityInput.value);

    // Update the quantity in the UI
    quantityInput.value = newQuantity;

    // Update the quantity in the selectedItems array
    var selectedItem = selectedItems.find(item => item.id === itemId);
    if (selectedItem) {
        selectedItem.quantity = newQuantity;
    }

    // Update the total column
    var price = parseFloat(row.querySelector('td:nth-child(2)').innerText.replace('₱', ''));
    var totalColumn = row.querySelector('td:nth-child(4)');
    totalColumn.innerText = '₱' + (price * newQuantity);

    updateTotalPrice(); // Recalculate total price
}





function removeItem(button, itemId) {
    var row = button.parentElement.parentElement;
    row.remove();
    updateTotalPrice(); // Add this line to recalculate total price
}


        function updateTotalPrice() {
            var totalPrice = 0;
            var rows = document.querySelectorAll('#selected-items-list tr');

            rows.forEach(function (row) {
                var price = parseFloat(row.querySelector('td:nth-child(2)').innerText.replace('₱', ''));
                var quantity = parseInt(row.querySelector('.quantity-input').value);
                totalPrice += price * quantity;
            });

            document.getElementById('total-price').innerText = '₱' + totalPrice;
        }

        function filterItems() {
            var searchBar = document.getElementById('search-bar');
            var filter = searchBar.value.toLowerCase();
            var items = document.querySelectorAll('.item-card');

            items.forEach(function (item) {
                var productName = item.getAttribute('data-productname').toLowerCase();
                if (productName.includes(filter)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
      
    function openPaymentModal() {
        // Initialize payment amount display with the current total price
        var totalPrice = document.getElementById('total-price').innerText;
        document.getElementById('payment-amount-display').innerText = totalPrice;

      

        document.getElementById('modal-backdrop').style.display = 'block';
        document.getElementById('payment-modal').style.display = 'block';
    }

        function closePaymentModal() {
            document.getElementById('modal-backdrop').style.display = 'none';
            document.getElementById('payment-modal').style.display = 'none';
        }

 function updatePaymentAmountDisplay() {
    var paymentAmount = parseFloat(document.getElementById('payment-amount').value);
    var totalPrice = parseFloat(document.getElementById('total-price').innerText.replace('₱', ''));

    // Check if customer name is not empty and payment amount is greater than or equal to the total price
    var customerName = document.getElementById('customer-name').value.trim();
    var proceedButton = document.getElementById('proceed-button');

    if (customerName !== '' && !isNaN(paymentAmount) && paymentAmount >= totalPrice) {
        proceedButton.disabled = false;
    } else {
        proceedButton.disabled = true;
    }
}


    function processPayment() {
        var paymentAmount = parseFloat(document.getElementById('payment-amount').value);
        var totalPrice = parseFloat(document.getElementById('total-price').innerText.replace('₱', ''));
        var customerName = document.getElementById('customer-name').value.trim();
        var paymentMethod = document.getElementById('payment-method').value;
        var referenceNumber = document.getElementById('reference-number').value;

        // Assuming you have fetched the cashier name in your PHP code and stored it in a variable like $userData['fullname']
        var cashierName = "<?php echo isset($userData['fullname']) ? $userData['fullname'] : ''; ?>";

        if (customerName !== '' && !isNaN(paymentAmount) && paymentAmount >= totalPrice) {
            // Send data to the server using AJAX
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'save_transaction.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                closePaymentModal();
				window.location.href = 'receipt.php';
            } else {
                alert('Error processing payment. Please try again.');
				window.location.href = 'pos.php';

            }
        }
    };

// Include the selected items with updated quantities in the request
var params = 'customerName=' + encodeURIComponent(customerName) +
             '&totalPrice=' + encodeURIComponent(totalPrice) +
             '&paymentMethod=' + encodeURIComponent(paymentMethod) +
             '&amountPaid=' + encodeURIComponent(paymentAmount) +
             '&referenceNumber=' + encodeURIComponent(referenceNumber) +
             '&cashierName=' + encodeURIComponent(cashierName) +
             '&selectedItems=' + encodeURIComponent(JSON.stringify(selectedItems));

xhr.send(params);

            // Only set the payment amount display when the payment is processed
            document.getElementById('payment-amount-display').innerText = '₱' + paymentAmount;

            // Add logic to process payment (e.g., send data to the server)
						Swal.fire({
                        icon: 'success',
                        title: 'Payment processed successfully!',
                        confirmButtonText: 'OK',
                    }).then(() => {
                        closePaymentModal();
                        window.location.href = 'receipt.php?' + params;
                    }); 
        }else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error processing payment. Please try again.',
                        confirmButtonText: 'OK',
                    }).then(() => {
                        window.location.href = 'pos.php';
                    });
    }
    }
        function goToHome() {
            // Add the URL of your home page or desired destination
            window.location.href = 'index.php';
        }
    </script>
<script>
    const goBackButton = document.getElementById('goBackButton');
    goBackButton.addEventListener('click', () => {
        window.location.href = 'index.php';
    });
</script>

</body>
</html>
