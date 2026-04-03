<?php
include 'db.php';
session_start();

// 1. Set headers to force download as Excel/CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=MKW_Orders_Report_' . date('Y-m-d') . '.csv');

// 2. Open the "output" stream
$output = fopen('php://output', 'w');

// 3. Set the column headers for Excel
fputcsv($output, array('Order ID', 'Customer Name', 'Email', 'Phone', 'Total Amount (INR)', 'Status', 'Address', 'Date'));

// 4. Fetch all orders from MySQL
$query = "SELECT id, customer_name, email, phone, total_amount, status, address, order_date FROM orders ORDER BY id DESC";
$result = mysqli_query($conn, $query);

// 5. Loop through the data and write to the file
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>