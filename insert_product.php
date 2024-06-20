<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_connection.php';
$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = $_POST['productName'];
    $brand = $_POST['brand'];
    $description = $_POST['description'];
    $category = $_POST['category'];
  	$unitofmeasure = $_POST['unitofmeasure'];
    $measurement = $_POST['measurement'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $supplierName = $_POST['supplierName'];
    $supplierEmail = $_POST['supplierEmail'];
  	$supplierLocation = $_POST['supplierLocation'];
  	$contactNumber = $_POST['contactNumber'];
  	$contactPerson = $_POST['contactPerson'];
  	$LowStockTrigger = $_POST['LowStockTrigger'];

    // Include SweetAlert CDN links
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Update Product</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap" rel="stylesheet">
        <style>
            /* Customize the font for SweetAlert2 */
            .swal2-popup {
                font-family: \'Poppins\', sans-serif;
            }
        </style>
    </head>
    <body>';

    // Handle file upload
    $targetDir = "inventory_images/";
    $targetFile = $targetDir . basename($_FILES["image_url"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if the file already exists
    if (file_exists($targetFile)) {
        echo '<script>
                Swal.fire({
                    title: "Error",
                    text: "File already exists.",
                    icon: "error"
                }).then(() => {
                    window.location.href = "inventory.php?error=1";
                });
              </script>';
        exit();
    }

    // Check file size
    if ($_FILES["image_url"]["size"] > 5000000) {
        echo '<script>
                Swal.fire({
                    title: "Error",
                    text: "File is too large.",
                    icon: "error"
                }).then(() => {
                    window.location.href = "inventory.php?error=1";
                });
              </script>';
        exit();
    }

    // Allow certain file formats
    $allowedExtensions = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowedExtensions)) {
        echo '<script>
                Swal.fire({
                    title: "Error",
                    text: "Only JPG, JPEG, PNG, and GIF files are allowed.",
                    icon: "error"
                }).then(() => {
                    window.location.href = "inventory.php?error=1";
                });
              </script>';
        exit();
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo '<script>
                Swal.fire({
                    title: "Error",
                    text: "Error uploading the file.",
                    icon: "error"
                }).then(() => {
                    window.location.href = "inventory.php?error=1";
                });
              </script>';
        exit();
    }

    if (move_uploaded_file($_FILES["image_url"]["tmp_name"], $targetFile)) {
        // Image uploaded successfully
        $imageLink = $targetFile;

        // Insert product into the database
        $query = "INSERT INTO inventory (productname, brand, description, category, unit_of_measure, measurement, quantity, price, image_url, supplier_name, supplier_email, supplier_location, contact_number, contact_person, lowstock_trigger) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssidssssssi", $productName, $brand, $description, $category, $unitofmeasure, $measurement, $quantity, $price, $imageLink, $supplierName, $supplierEmail, $supplierLocation, $contactNumber, $contactPerson, $LowStockTrigger);

        if ($stmt->execute()) {
          
                  $logMessage = "$username added a new product '$productName'";
        $logQuery = "INSERT INTO action_log (username, action, log_date) VALUES (?, ?, NOW())";
        $stmtLog = $conn->prepare($logQuery);
        $stmtLog->bind_param("ss", $username, $logMessage);
        $stmtLog->execute();
        $stmtLog->close();
            // Product added successfully, display SweetAlert and redirect
            echo '<script>
                    Swal.fire({
                        title: "Product Added",
                        text: "Product added successfully.",
                        icon: "success"
                    }).then(() => {
                        window.location.href = "inventory.php";
                    });
                  </script>';
        } else {
            // Error adding product, display SweetAlert and redirect with an error parameter
            echo '<script>
                    Swal.fire({
                        title: "Error",
                        text: "Error adding product.",
                        icon: "error"
                    }).then(() => {
                        window.location.href = "inventory.php?error=1";
                    });
                  </script>';
        }

        $stmt->close();
    } else {
        // Handle file upload error
        echo '<script>
                Swal.fire({
                    title: "Error",
                    text: "Error uploading the file.",
                    icon: "error"
                }).then(() => {
                    window.location.href = "inventory.php?error=1";
                });
              </script>';
    }

    $conn->close();
    echo '</body></html>';
}
?>
