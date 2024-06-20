<?php

require_once 'db_connection.php';

// Check if item_ids are set in the URL
$itemIds = isset($_GET['item_ids']) ? explode(",", $_GET['item_ids']) : array();

// Fetch item details from the database
$itemDetails = array();

if (!empty($itemIds)) {
$query = "SELECT productname, image_url FROM inventory WHERE id IN (" . implode(',', $itemIds) . ")";
$result = mysqli_query($conn, $query);

if ($result) {
    $itemDetails = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    // Display the MySQL error if there's an issue with the query
    echo "MySQL Error: " . mysqli_error($conn);
}

    // Close the database connection
    mysqli_close($conn);
} else {
    echo "Invalid item IDs.";
    exit; // Add exit to prevent further execution
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="icon" href="images/saebs_logo.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Schedule</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Style to center the button in the modal */
        #dateForm {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Style to set the button color to #333 */
        .btn-success {
            background-color: #333 !important;
        }
    </style>
</head>
<body>

<!-- Modal -->
<div class="modal" id="myModal" data-backdrop="static" data-keyboard="false">
<div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <!-- Modal Header -->
<div class="modal-header">
    <div class="container text-center">
        <div class="row">
            <div class="col">
                <img src="images/saebslogo.png" alt="Store Logo" style="width: auto; max-height: 55px; margin-top: 10px; margin-bottom: 10px;">
            </div>
        </div>
        <div class="row">
            <div class="col">
                <h4 class="modal-title">
<?php echo "Enter a delivery date for the products "; ?>
                </h4>
            </div>
        </div>
    </div>
</div>

            <!-- Modal Body -->
<div class="modal-body text-center">

                <!-- Date Input Form -->
                <form id="dateForm">
                    <div class="form-group">
                        <label for="dateInput">Select Date:</label>
                        <input type="date" class="form-control" id="dateInput" required>
                    </div>
                    <button type="button" class="btn btn-success" onclick="sendDate()">Send</button>
                </form>
            </div>


        </div>
    </div>
</div>

<!-- Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Log the item IDs to the console for debugging
    console.log("Item IDs:", <?php echo json_encode($itemIds); ?>);

// Function to handle date submission
function sendDate() {
    // Fetch the date value from the input field
    var dateValue = document.getElementById("dateInput").value;

    // Retrieve the item_ids from the URL parameter
    var itemIds = <?php echo json_encode($itemIds); ?>;

    // Send the date value and product IDs to the PHP script using AJAX
    $.ajax({
        type: 'POST',
        url: 'save_restock_date.php',
        data: { itemIds: itemIds, dateValue: dateValue },
        success: function(response) {
            console.log(response);
            // Handle the response accordingly

            // Show SweetAlert on success
            Swal.fire({
                icon: 'success',
                title: 'Delivery Scheduled!',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Redirect to another page (change the URL accordingly)
                window.location.href = 'https://gmail.com/'; // Replace with your desired URL
            });
        }
    });

    // For demonstration, we'll simply log it to the console
    console.log("Selected Date: " + dateValue);
}

    // Function to auto-open the modal when the page loads
    $(document).ready(function() {
        $('#myModal').modal('show');
    });
</script>
</body>
</html>
