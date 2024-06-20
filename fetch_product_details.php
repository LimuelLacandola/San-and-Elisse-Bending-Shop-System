<?php
require_once 'db_connection.php';

$productId = $_GET['id'];

// Prepare and execute the SQL query to fetch product information by ID
$query = "SELECT * FROM inventory WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

// Display product information in the modal
if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
      	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Product Information</title>

    <style>
        body {
            font-family: Poppins, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }

        .container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2.title {
            color: #333;
        }

        form {
            margin-top: 10px;
            display: inline-table;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .form-label-container {
            width: 60%;
            margin-top: 20px;
            text-align: center;
        }

        .form-label {
          	padding: 5px;
            display: block;
            margin-top: 15px;
            width: 100%;
            text-align: left;
        }
      	
      	

        .input {
            width: calc(100% - 20px);
            padding: 10px;
          	font-family: Poppins, sans-serif;
          	font-size: 14px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-top: 5px;
        }
		
      	.description-input {
            width: calc(100% - 30px);
            padding: 20px;
            font-size: 18px; /* Adjusted font size for description */
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-top: 5px;
        }

        .submit-edit {
            border: none;
            outline: none;
            background-color: #333;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            color: white;
            font-size: 16px;
            transition: background-color 0.3s ease;
            cursor: pointer;
            justify-self: center;
            margin-top: 20px;
        }

        .submit-edit:hover {
            background-color: rgb(56, 90, 194);
        }

        img {
            margin-top: 10px;
            max-width: 100%;
            height: auto;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        label[for="imageFile"], img {
            width: 100%;
            text-align: center;
        }

        .input-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .input-group .input {
            width: calc(48% - 10px);
        }

      	.input-group textarea {
    width: 100%;
    padding: 10px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin-top: 5px;
    resize: vertical; /* Allow vertical resizing */
    min-height: 100px; /* Set a minimum height */
}

        .label-container-category,
        .label-container-measure {
            width: 50%;
            margin: 0 auto;
            text-align: left;
        }

        .label-container-category select,
        .label-container-measure select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <h2 class="title">Edit Product Information</h2>
    <form id="updateProductForm" action="update_product.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="productId" value="<?= $product['id'] ?>">
        <label for="productName" class="form-label">
            Product Name:
            <input type="text" id="productName" name="productName" value="<?= $product['productname'] ?>" required class="input">
        </label>
        
        <label for="brand" class="form-label">
            Brand:
            <input type="text" id="brand" name="brand" value="<?= $product['brand'] ?>" required class="input">
        </label>
<div class="label-container-category">
<label for="category"><span>Product Category</span>
    <select id="category" name="category" class="input" required>
        <option value="Light Bulb">Light Bulb</option>
        <option value="Grinder">Grinder</option>
        <option value="Sander">Sander</option>
      	<option value="Sprayer">Sprayer</option>
      	<option value="Calipers">Calipers</option>
      	<option value="Tape Measures">Tape Measures</option>
      	<option value="Meter">Meter</option>
      	<option value="Squares">Squares</option>
      	<option value="Pulleys">Pulleys</option>
      	<option value="Chisels">Chisels</option>
      	<option value="Drills">Drills</option>
      	<option value="Garden Tools">Garden Tools</option>
      	<option value="Hammer">Hammer</option>
      	<option value="Levels">Levels</option>
      	<option value="Brush">Brush</option>
      	<option value="Pliers">Pliers</option>
      	<option value="Saw">Saw</option>
      	<option value="Screwdriver">Screwdriver</option>
      	<option value="Wire Cutter">Wire Cutter</option>
      	<option value="Wrench">Wrench</option>
      	<option value="Heat Gun">Heat Gun</option>
      	<option value="Jigsaw">Jigsaw</option>
      	<option value="Impact Driver">Impact Driver</option>
      	<option value="Oscillating Tool">Oscillating Tool</option>
      	<option value="Hoses">Hoses</option>
      	<option value="Shovel">Shovel</option>
    </select>
    
</label><br>
      </div>
  <div class="label-container-measure">
      <label for="unitofmeasure"><span>Unit of measure</span>
    <select id="unitofmeasure" name="unitofmeasure" class="input" required>
        <option value="Per Piece">Per Piece</option>
        <option value="Bundled">Bundled</option>
    </select>
    
</label><br>
    </div>

        <label for="description" class="form-label">
    Description:
    <textarea id="description" name="description" class="input" required><?= $product['description'] ?></textarea>
</label>

        <label for="quantity" class="form-label">
            Quantity:
            <input type="number" id="quantity" name="quantity" value="<?= $product['quantity'] ?>" required class="input" required oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4)" required>
        </label>
        
        <label for="measurement" class="form-label">
            Measurement:
            <input type="text" id="measurement" name="measurement" value="<?= $product['measurement'] ?>" required class="input">
        </label>

        <label for="price" class="form-label">
            Price:
            <input type="number" step="0.01" id="price" name="price" value="<?= $product['price'] ?>" required class="input" required oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6)" required>
        </label>

        <label for="imageFile" class="form-label">
                New Image:
                <input type="file" id="imageFile" name="imageFile" accept="image/*">
            </label>

            <img src="<?= $product['image_url'] ?>" alt="Current Image" style="max-width: 300px;">

        
        <label for="supplierName" class="form-label">
            Supplier Name:
            <input type="text" id="supplierName" name="supplierName" value="<?= $product['supplier_name'] ?>" required class="input">
        </label>
        
        <label for="supplierEmail" class="form-label">
            Supplier Email:
            <input type="text" id="supplierEmail" name="supplierEmail" value="<?= $product['supplier_email'] ?>" required class="input">
        </label>
      
		<label for="supplierLocation" class="form-label">
            Supplier Location:
            <input type="text" id="supplierLocation" name="supplierLocation" value="<?= $product['supplier_location'] ?>" required class="input">
        </label>
      
        <label for="contactNumber" class="form-label">
            Contact Number:
            <input type="number" id="contactNumber" name="contactNumber" value="<?= $product['contact_number'] ?>" required class="input" required oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)" required>
        </label>
      
        <label for="contactPerson" class="form-label">
            Contact Person:
            <input type="text" id="contactPerson" name="contactPerson" value="<?= $product['contact_person'] ?>" required class="input">
        </label>
      
        <label for="lowStockTrigger" class="form-label">
            Lowstock Limit:
            <input type="number" id="lowStockTrigger" name="lowStockTrigger" value="<?= $product['lowstock_trigger'] ?>" required class="input">
        </label>

        
                 

        <!-- Add additional fields as needed -->

<input type="submit" value="save" class="submit-edit" title="Save" style="font-family: 'Material Icons'; font-size: 24px;"> 
    </form>


        <!-- Add your existing scripts if needed -->

    </body>
    </html>

    <?php
} else {
    echo 'Product not found.';
}

$stmt->close();
$conn->close();
?>
