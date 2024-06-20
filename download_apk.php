<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="images/saebs_logo.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Android App</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            flex-direction: column;
        }

        h1 {
            color: #333;
        }

        button {
            padding: 10px 20px;
            background-color: #323031;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #595557;
        }
    </style>
</head>
<body>

    <img src="images/saebslogo.png">
    <h1>Download Android Application of San and Elisse Bending Shop</h1>

<button onclick="downloadFile()">Download App</button>

<script>
    function downloadFile() {
        // Replace 'YOUR_DIRECT_DOWNLOAD_LINK' with the actual direct download link of your APK file
        var directDownloadLink = 'https://saeb.lightsolus.xyz/android_studio/SAEBSSMS.apk';

        // Directly navigate to the download link
        window.location.href = directDownloadLink;
    }
</script>


</body>
</html>
