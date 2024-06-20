<?php
// fetch_weekly_sales_for_week.php

// Database connection
require_once 'db_connection.php';

// Get the selected week from the URL parameter
$selectedWeek = isset($_GET['selectedWeek']) ? $_GET['selectedWeek'] : '';

// Extract year and week number from the selectedWeek format (e.g., '2024-W06')
list($year, $weekNumber) = explode('-W', $selectedWeek);

// Fetch weekly sales data for the selected week
$weeklySalesQuery = "SELECT CONCAT('Week', WEEK(transaction_date, 1), ' (', DATE_FORMAT(MIN(transaction_date), '%M %e'), ' - ', DATE_FORMAT(MAX(transaction_date), '%M %e'), ')') AS week_range, 
                        SUM(total_price) AS weekly_sales_sum
                    FROM transaction_history
                    WHERE YEARWEEK(transaction_date, 1) = ? 
                    GROUP BY YEARWEEK(transaction_date, 1)
                    ORDER BY WEEK(transaction_date, 1)";
$stmt = $conn->prepare($weeklySalesQuery);
$stmt->bind_param('s', $selectedWeek);
$stmt->execute();
$result = $stmt->get_result();

$weeks = [];
$weeklySalesData = [];

while ($row = $result->fetch_assoc()) {
    $weeks[] = $row['week_range'];
    $weeklySalesData[] = $row['weekly_sales_sum'];
}

// Close the database connection
$stmt->close();
$conn->close();

// Return data as JSON
echo json_encode(['weekRanges' => $weeks, 'weeklySalesData' => $weeklySalesData]);
?>
