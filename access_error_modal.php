<!DOCTYPE html>
<html lang="en">
<head>
	<link rel = "icon" href = "images/saebs_logo.png" type = "image/x-icon"> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

        body {
            margin: 0;
            display: flex;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            background-color: #f2f2f2;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }

        h1 {
            color: #e74c3c;
            font-size: 24px;
            margin-bottom: 10px;
        }

        p {
            color: #333;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .error-image {
            width: 100%;
            max-width: 200px;
            margin-bottom: 20px;
        }

        .modal-button {
            padding: 10px 20px;
            background-color: #323031;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .modal-button:hover {
            background-color: #595557;
        }
    </style>
</head>
<body>
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <img src="images/Unauthorized_Access.png" alt="Error Image" class="error-image">
            <h1>Unauthorized Access</h1>
            <p>You don't have permission to access this page.</p>
          	<p> Contact the admin for assistance.</p>
            <button id="goBackButton" class="modal-button">Go Back</button>
        </div>
    </div>

    <script>
        // JavaScript to show the modal and handle the "Go Back" button click
        const errorModal = document.getElementById('errorModal');
        const goBackButton = document.getElementById('goBackButton');

        goBackButton.addEventListener('click', () => {
            errorModal.style.display = 'none';
            window.history.back();
        });

        // Show the modal when the page loads
        errorModal.style.display = 'flex';
    </script>
</body>
</html>
