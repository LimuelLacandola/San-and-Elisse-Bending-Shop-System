<?php

// Check if a file was uploaded
if (isset($_FILES['uploaded_file']['name'])) {
    $file_name = $_FILES['uploaded_file']['name'];
    $file_size = $_FILES['uploaded_file']['size'];
    $file_tmp = $_FILES['uploaded_file']['tmp_name'];
    $file_type = $_FILES['uploaded_file']['type'];

    // Specify the directory where you want to save the uploaded images
    $upload_dir = __DIR__ . '/../inventory_images/'; // Go up one level and into inventory_images

    // Move the uploaded file to the specified directory
    move_uploaded_file($file_tmp, $upload_dir . $file_name);

    // Return the path to the uploaded file
    echo json_encode(['status' => 'success', 'message' => 'Image uploaded successfully', 'file_path' => $upload_dir . $file_name]);
} else {
    // No file was uploaded
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
}

?>
