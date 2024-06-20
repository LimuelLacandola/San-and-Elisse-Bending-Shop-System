<?php
require_once 'db_connection.php';

echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel = "icon" href = "images/saebs_logo.png" type = "image/x-icon"> 
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <meta charset="UTF-8">
    </head>
<body>';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $userId = filter_input(INPUT_POST, 'userId', FILTER_VALIDATE_INT);
    $fullname = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_STRING);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $user_role = filter_input(INPUT_POST, 'user_role', FILTER_SANITIZE_STRING);

    // Check if all required fields are filled
    if ($userId && $fullname !== null && $username !== null && $user_role !== null) {
        // Prepare and execute the SQL query to update user details
        $query = "UPDATE login SET fullname=?, username=?, user_role=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $fullname, $username, $user_role, $userId);

        if ($stmt->execute()) {
            // User details updated successfully
            $conn->close(); // Close the database connection before outputting JavaScript

            // Use SweetAlert for success message
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<script>
                    Swal.fire({
                        title: "Success!",
                        text: "User details updated successfully.",
                        icon: "success",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location.href = "employee_list.php"; // Replace with your actual listing page
                    });
                  </script>';
            exit();
        } else {
            echo 'Error updating user details: ' . $stmt->error;
        }

        $stmt->close();
    } else {
        echo 'Invalid input. Please fill in all required fields.';
    }
} else {
    // Redirect to the user listing page if accessed without form submission
    header('Location: user_listing.php'); // Replace 'user_listing.php' with your actual listing page
    exit();
}

$conn->close();
?>
