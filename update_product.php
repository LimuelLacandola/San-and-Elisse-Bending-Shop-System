<?php
session_start();

ini_set('display_errors', 1);

require_once 'db_connection.php';

$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['productId'];
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
  	$LowStockTrigger = $_POST['lowStockTrigger'];


  
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
       if ($_FILES['imageFile']['size'] > 0) {
        $targetDir = "inventory_images/";
        $targetFile = $targetDir . basename($_FILES["imageFile"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check file size
        if ($_FILES["image_url"]["size"] > 3 * 1024 * 1024) {
            echo '<script>
            Swal.fire({
                title: "Error",
                text: "File is too large.",
                icon: "error"
            }).then(() => {
                window.location.href = "edit_product.php?id=' . $productId . '";
                // Reload the inventory.php page
                window.location.href = "inventory.php";
            });
          </script>';
    
    
            exit();
        }

        // Allow certain file formats
        $allowedExtensions = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowedExtensions)) {
		echo '<script>
        Swal.fire({
            title: "Error!",
            text: "Only JPG, JPEG, PNG, and GIF files are allowed.",
            icon: "error",
            timer: 1000,
            showConfirmButton: false
        }).then(() => {
            window.location.href = "edit_product.php?id=' . $productId . '";
        });
      </script>';
exit();

        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES["imageFile"]["tmp_name"], $targetFile)) {
            // Update image link in the database
            $imageLink = $targetFile;
            $updateImageQuery = "UPDATE inventory SET image_url = ? WHERE id = ?";
            $stmtUpdateImage = $conn->prepare($updateImageQuery);
            $stmtUpdateImage->bind_param("si", $imageLink, $productId);
            $stmtUpdateImage->execute();
            $stmtUpdateImage->close();
        } else {
            // Handle file upload error
echo '<script>
        Swal.fire({
            title: "Error!",
            text: "Unable to upload the file.",
            icon: "error",
            timer: 1000,
            showConfirmButton: false
        }).then(() => {
            window.location.href = "edit_product.php?id=' . $productId . '";
        });
      </script>';
exit();

        }
    }

  
        $productNameQuery = "SELECT productname FROM inventory WHERE id = ?";
    $stmtProductName = $conn->prepare($productNameQuery);
    $stmtProductName->bind_param("i", $productId);
    $stmtProductName->execute();
    $stmtProductName->bind_result($oldProductName);
    $stmtProductName->fetch();
    $stmtProductName->close();
  
  
    // Update other product information in the database
    $updateQuery = "UPDATE inventory SET productname = ?, brand = ?, description = ?, category = ?, unit_of_measure = ?, measurement = ?, quantity = ?, price = ?, supplier_name = ?, supplier_email = ?, supplier_location = ?, contact_number = ?, contact_person = ?, lowstock_trigger = ?  WHERE id = ?";
    $stmtUpdate = $conn->prepare($updateQuery);
    $stmtUpdate->bind_param("ssssssidsssssii", $productName, $brand, $description, $category, $unitofmeasure, $measurement, $quantity, $price, $supplierName, $supplierEmail, $supplierLocation, $contactNumber, $contactPerson, $LowStockTrigger, $productId);

    if ($stmtUpdate->execute()) {
              $logMessage = "$username updated the product '$oldProductName'";
        $logQuery = "INSERT INTO action_log (username, action, log_date) VALUES (?, ?, NOW())";
        $stmtLog = $conn->prepare($logQuery);
        $stmtLog->bind_param("ss", $username, $logMessage);
        $stmtLog->execute();
        $stmtLog->close();
        // Product updated successfully, redirect
    echo '<script>
            Swal.fire({
                title: "Success!",
                text: "Product updated successfully!",
                icon: "success",
                timer: 1000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = "inventory.php";
            });
          </script>';
        exit();
    } else {
        // Error updating product
    echo '<script>
            Swal.fire({
                title: "Error!",
                text: "Unable to update product.",
                icon: "error",
                timer: 1000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = "edit_product.php?id=' . $productId . '";
            });
          </script>';

        exit();
    }

    $stmtUpdate->close();
    $conn->close();
} else {
    // Redirect if accessed without a POST request
    header("Location: inventory.php");
    exit();
}
?>

  