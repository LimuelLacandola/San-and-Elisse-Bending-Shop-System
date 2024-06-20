<?php
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect user data from the form
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $userRole = $_POST['user_role'];
  	$email = $_POST['email'];

    // Handle file upload
    $targetDir = "user_images/";
    $targetFile = $targetDir . basename($_FILES["user_image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Add User</title>
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

    // Check if the file already exists
    if (file_exists($targetFile)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'File already exists.',
				}).then(() => {
                window.location = 'employee_list.php';
            });
        </script>";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["user_image"]["size"] > 3000000) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'File is too large, upload a file below 3 MB',
                 }).then(() => {
                window.location = 'employee_list.php';
            });
        </script>";
        $uploadOk = 0;
    }

    // Allow certain file formats
    $allowedExtensions = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowedExtensions)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Only JPG, JPEG, PNG, and GIF files are allowed.',
                 }).then(() => {
                window.location = 'employee_list.php';
            });
        </script>";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error uploading the file.',
                 }).then(() => {
                window.location = 'employee_list.php';
            });
        </script>";
        exit();
    }

    if (move_uploaded_file($_FILES["user_image"]["tmp_name"], $targetFile)) {
        // Image uploaded successfully
        $userImage = $targetFile;
    } else {
        // Handle file upload error
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error uploading the file.',
            });
        </script>";
        exit();
    }

    // Hash the password using password_hash
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user data into the database
    $sql = "INSERT INTO login (fullname, username, password, user_role, user_image, email) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssss', $fullname, $username, $hashedPassword, $userRole, $userImage, $email);

    if ($stmt->execute()) {
        $response = "User added successfully.";

        // Use SweetAlert to display a success message
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '$response',
            }).then(() => {
                window.location = 'employee_list.php';
            });
        </script>";
    } else {
        $response = "Error adding the user: " . $stmt->error;

        // Use SweetAlert to display an error message
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '$response',
               }).then(() => {
                window.location = 'employee_list.php';
            });
        </script>";
    }

    $stmt->close();
    $conn->close();
} else {
    // Handle other HTTP request methods or invalid requests
    echo "Invalid request.";
}
?>
