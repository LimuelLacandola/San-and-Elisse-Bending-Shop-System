<?php
// fetch_weekly_sales_card.php

// Database connection
require_once 'db_connection.php';

// Fetch weekly sales data from the transaction_history table
$weeklySalesQuery = "SELECT CONCAT('Week', WEEK(transaction_date, 1), ' (', DATE_FORMAT(MIN(transaction_date), '%M %e'), ' - ', DATE_FORMAT(MAX(transaction_date), '%M %e'), ')') AS week_range, 
                        SUM(total_price) AS weekly_sales_sum
                    FROM transaction_history
                    GROUP BY YEARWEEK(transaction_date, 1)
                    ORDER BY WEEK(transaction_date, 1)";
$weeklySalesResult = $conn->query($weeklySalesQuery);

$weeks = [];
$weeklySalesData = [];

while ($row = $weeklySalesResult->fetch_assoc()) {
    $weeks[] = $row['week_range'];
    $weeklySalesData[] = $row['weekly_sales_sum'];
}

// Close the database connection
$conn->close();

// Return data as JSON
echo json_encode(['weekRanges' => $weeks, 'weeklySalesData' => $weeklySalesData]);


?>
