<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the data from the form
    $supplierName = $_POST["supplier_name"];
    $emailAddress = $_POST["email_address"];
    $contactNo = $_POST["contact_no"];

    // Perform the database connection and insertion here
require_once 'db_connection.php';

// Prepare and bind the statement
$insertQuery = $conn->prepare("INSERT INTO supplier (supplier_name, email_address, contact_no) VALUES (?, ?, ?)");
$insertQuery->bind_param("sss", $supplierName, $emailAddress, $contactNo);

// Execute the statement
if ($insertQuery->execute()) {
    header("Location: supplier.php");
} else {
    echo "Error: " . $conn->error;
}

// Close the statement
$insertQuery->close();

}
?>
