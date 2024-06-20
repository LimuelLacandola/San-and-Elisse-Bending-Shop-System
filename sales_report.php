<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Database connection
require_once 'db_connection.php';


$totalProducts = 0;
$lowStockItems = [];

// Retrieve user information from the login table based on the username
$sql = "SELECT id, user_image, fullname, username, user_role FROM login WHERE username = '{$_SESSION['username']}'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch the user data
    $userData = $result->fetch_assoc();
} else {
    echo "User not found";
}

?>

<!DOCTYPE html>
<html>
<head>
    <link rel = "icon" href =  
"https://z-p3-scontent.fmnl33-3.fna.fbcdn.net/v/t1.15752-9/397997179_250856261335004_614763989636093522_n.png?_nc_cat=110&ccb=1-7&_nc_sid=8cd0a2&_nc_eui2=AeFAQHoVlJwD-o722MTZbqjNdAN0zl-EgQB0A3TOX4SBAHo4-7I3jVCp1ZpSpzk8h8rreNXwovhcMiIAX8vJlzwe&_nc_ohc=UQ2ASeBN5AcAX9ZRE0z&_nc_oc=AQkjY3_LMBhKeGNlIaHR_Lkk6QJundYBnfwKzhTqTuJifJSEJ47zDdUHEIXCOTu3qVgB3xJC2qq1RDl9iBC9HN8c&_nc_ht=z-p3-scontent.fmnl33-3.fna&oh=03_AdQQX55ul_5PZ8nuIHCCl3WQJaXGzIyLZjsylDCapniYLw&oe=65869B24" 
        type = "image/x-icon"> 
        
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <title>Sales Report</title>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');
        body {
            margin: 0;
            display: flex;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
        }
        .content {
            flex: 1;
            padding: 20px;
            
        }
        
  .navigation {
            background-color: #333;
            color: #fff;
            width: 250px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            transition: width 0.5s ease; /* Add transition for smoother resizing */

            
        }

        .navigation a {
            color: #fff;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
            padding: 10px; /* Add padding for better hover effect */
    border-radius: 8px; /* Add border radius */
    transition: background-color 0.3s ease, color 0.3s ease; /* Add transitions for background and text color */
        }
        
        .navigation a:not(:last-child) {
            margin-bottom: 10px;
        }
        .navigation a:hover {
                background-color: #cccccc; /* Change background color on hover */
    color: #333; /* Change text color on hover */
    text-decoration: none; /* Remove underline on hover */
        }
        
        .navigation i {
    margin-right: 10px; /* Adjust the space between icon and text */
        vertical-align: middle; /* Align the icon vertically in the middle */
        margin-bottom: 8px;

}
        
        canvas {
            margin-top: 20px;
        }
        /* Style for the dropdown container */
        .dropdown {
            position: relative;
            display: inline-block;
            vertical-align: middle;
            width: 150px; /* Adjust the width as needed */
        }

        /* Style for the select element */
        .styled-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: transparent;
            border: 1px solid #ccc;
            padding: 8px 20px 8px 10px;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
            cursor: pointer;
        }

        /* Style for the select arrow */
        .styled-select::after {
            content: "\25BC";
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            pointer-events: none;
        }
        
                        .loader-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            display: none;
        }

.loader {
  --dim: 3rem;
  width: var(--dim);
  height: var(--dim);
  position: relative;
  animation: spin988 2s linear infinite;
}

.loader .circle {
  --color: #333;
  --dim: 1.2rem;
  width: var(--dim);
  height: var(--dim);
  background-color: var(--color);
  border-radius: 50%;
  position: absolute;
}

.loader .circle:nth-child(1) {
  top: 0;
  left: 0;
}

.loader .circle:nth-child(2) {
  top: 0;
  right: 0;
}

.loader .circle:nth-child(3) {
  bottom: 0;
  left: 0;
}

.loader .circle:nth-child(4) {
  bottom: 0;
  right: 0;
}

@keyframes spin988 {
  0% {
    transform: scale(1) rotate(0);
  }

  20%, 25% {
    transform: scale(1.3) rotate(90deg);
  }

  45%, 50% {
    transform: scale(1) rotate(180deg);
  }

  70%, 75% {
    transform: scale(1.3) rotate(270deg);
  }

  95%, 100% {
    transform: scale(1) rotate(360deg);
  }
}


        .user-info {
            margin-top: auto;
            display: flex;
            align-items: center;
            text-decoration: none; /* Remove underline from the link */
            color: #fff;
        }

        .user-info img {
            width: 30px;
            height: 30px;
            margin-right: 10px;
            border-radius: 50%;
        }
        
                    #logoLink img {
        width: 250px; /* Adjust the width as needed */
        height: auto; /* Maintains the aspect ratio */
        margin-bottom: -10px; /* Adjust this value to fine-tune the alignment */
        margin-left: -52px;
    }

    </style>
</head>
<body>
    
                <div class="loader-container" id="loaderContainer">
        <div class="loader">
    <div class="circle"></div>
    <div class="circle"></div>
    <div class="circle"></div>
    <div class="circle"></div>
</div>

    </div>
    
    <div class="navigation">
    <a href="index.php" id="logoLink">
        <img src="images/saebs_logo.png" alt="Logo">
    </a>
    <a href="index.php"><i class="material-icons">home</i> Home</a>
    <a href="inventory.php"><i class="material-icons">inbox</i> Inventory</a>
    <a href="notification.php"><i class="material-icons">notifications</i>Notifications</a>

    <a href="pos.php"><i class="material-icons">attach_money</i> Point of Sale</a>
    <a href="sales_report.php"><i class="material-icons">insert_chart_outlined</i> Sales Report</a>
    <a href="transaction_history.php"><i class="material-icons">history</i> Transaction History</a>
            <a href="refund.php"><i class="material-icons">history</i> Refund</a>


    <?php
    if ($_SESSION['user_role'] === 'admin') {
        echo '<a href="employee_list.php"><i class="material-icons">people</i> Employee List</a>';
        echo '<a href="restock.php"><i class="material-icons">shopping_cart</i> Restock</a>';
        echo '<a href="supplier.php"><i class="material-icons">local_shipping</i> Supplier</a>';
        echo '<a href="view_sessions.php"><i class="material-icons">access_time</i> Sessions</a>';
        
    } elseif ($_SESSION['user_role'] === 'employee') {
        // Employee can only access limited features
    } else {
        echo "Invalid user role.";
    }
    ?>
    
    <a href="restock_history.php"><i class="material-icons">history</i> Restock History</a>
    <a href="logout.php" id="logoutLink"><i class="material-icons">exit_to_app</i> Logout</a>

    <?php
    // Check if the user has a custom image, otherwise, display a default image
    $userImage = isset($userData["user_image"]) ? $userData["user_image"] : "path/to/default-image.png";
    ?>
    <a href="user_info.php" class="user-info">
        <img src="<?php echo $userImage; ?>" alt="User Image">
    </a>
</div>

    <div class="content">
    <h2>Sales Report</h2>
    <div>
    <center><div class="dropdown">
        <select id="toggleSales" class="styled-select">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
        </select>
    </div>
</div>
    </center>

    <canvas id="salesChart"></canvas>
     <button onclick="printSalesReport()">Print Report</button>
        </div>
<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "saebdatabase";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

 
<!-- Add the following script just before the closing </body> tag -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Show loader on form submission
        document.getElementById('loaderContainer').style.display = 'flex';

        // Redirect to login.php after a delay (adjust as needed)
        setTimeout(function () {
           document.getElementById('loaderContainer').style.display = 'none';
        }, 1000); // Redirect after 1 second (1000 milliseconds)
    });
</script>

<script>
    var salesChart;

        function fetchAndRenderData(selectedOption) {
            var url = '';
            if (selectedOption === 'daily') {
                url = 'fetch_daily_sales.php';
            } else if (selectedOption === 'weekly') {
                url = 'fetch_weekly_sales.php';
            } else if (selectedOption === 'monthly') {
                url = 'fetch_monthly_sales.php';
            }

            fetch(url)
                .then(response => response.json())
                .then(salesData => {
                    if (salesChart) {
                        salesChart.destroy();
                    }

                    var ctx = document.getElementById('salesChart').getContext('2d');
                    salesChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: Object.keys(salesData),
                            datasets: [{
                                label: selectedOption === 'daily' ? 'Daily Sales' :
                                    (selectedOption === 'weekly' ? 'Weekly Sales' : 'Monthly Sales'),
                                data: Object.values(salesData),
                                backgroundColor: 'rgba(51, 51, 51, 0.6)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Number of Transactions'
                                    }
                                }
                            }
                        }
                    });
                });
        }

        var toggleSales = document.getElementById('toggleSales');
        toggleSales.addEventListener('change', function () {
            fetchAndRenderData(this.value);
        });

        // Initial chart render
        fetchAndRenderData(toggleSales.value);

        function printSalesReport() {
            console.log("Successfully Clicked!");
            var canvas = document.getElementById('salesChart');
            var dataUrl = canvas.toDataURL(); // Get the data URL of the canvas

            var windowContent = '<!DOCTYPE html>';
            windowContent += '<html>';
            windowContent += '<head><title>Sales Report</title></head>';
            windowContent += '<body>';
            windowContent += '<img src="' + dataUrl + '" alt="Sales Report">';
            windowContent += '</body>';
            windowContent += '</html>';

            var printWin = window.open('', '_blank');
            printWin.document.open();
            printWin.document.write(windowContent);
            printWin.document.close();
            printWin.print();
        }
    </script>

</body>

</html>