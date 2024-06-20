<?php
require_once 'db_connection.php';

$supplierId = $_GET['id'];

// Prepare and execute the SQL query to fetch supplier information by ID
$query = "SELECT * FROM supplier WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $supplierId);
$stmt->execute();
$result = $stmt->get_result();

// Display supplier information in the modal
if ($result->num_rows > 0) {
    $supplier = $result->fetch_assoc();
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Supplier Information</title>

        <!-- Add your existing styles and scripts -->
        <style>
                       .modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    border-radius: 10px;
    text-align: center;
}



       .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

        /* Center the form within the modal */
        .form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .title {
            align-items: center;
            font-size: 28px;
            color: #333;
            font-weight: 600;
            letter-spacing: -1px;
            position: relative;
            display: flex;
            align-items: center;
            padding-left: 30px;
            justify-content: center; /* Center the title horizontally */
        }

        .flex {
            display: flex;
            width: 100%;
            gap: 6px;
        }

        .form label {
            position: relative;
            margin-bottom: 10px;
        }

        .form label .input {
            width: 100%;
            padding: 10px 10px 20px 10px;
            outline: 0;
            border: 1px solid rgba(105, 105, 105, 0.397);
            border-radius: 10px;
        }

        .form label .input + span {
            position: absolute;
            left: 10px;
            top: 15px;
            color: grey;
            font-size: 0.9em;
            cursor: text;
            transition: 0.3s ease;
        }

        .form label .input:placeholder-shown + span {
            top: 15px;
            font-size: 0.9em;
        }

        .form label .input:focus + span, .form label .input:valid + span {
            top: 30px;
            font-size: 0.7em;
            font-weight: 600;
        }

        .form label .input:valid + span {
            color: #333;
        }

        .submit {
            border: none;
            outline: none;
            background-color: #333;
            padding: 10px;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            transform: .3s ease;
        }

        .submit:hover {
            background-color: rgb(56, 90, 194);
        }
        </style>
    </head>
    <body>
        <h2 class="title">Edit Supplier Information</h2>
        <form id="updateSupplierForm" action="update_supplier.php" method="post">
            <input type="hidden" name="supplierId" value="<?= $supplier['id'] ?>">
            
            <label for="supplierName" class="form-label">
                Supplier Name:
                <input type="text" id="supplierName" name="supplierName" value="<?= $supplier['supplier_name'] ?>" required class="input">
            </label>

            <label for="emailAddress" class="form-label">
                Email Address:
                <input type="email" id="emailAddress" name="emailAddress" value="<?= $supplier['email_address'] ?>" required class="input">
            </label>

            <label for="contactNo" class="form-label">
                Contact No:
                <input type="text" id="contactNo" name="contactNo" value="<?= $supplier['contact_no'] ?>" required class="input">
            </label>

            <!-- Add additional fields as needed -->

            <input type="submit" value="Save" class="submit">
        </form>

        <!-- Add your existing scripts if needed -->

    </body>
    </html>

    <?php
} else {
    echo 'Supplier not found.';
}

$stmt->close();
$conn->close();
?>
