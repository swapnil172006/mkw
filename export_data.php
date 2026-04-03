<?php
include 'db.php';
session_start();

// --- 1. SECURE: Admin Authentication ---
if (!isset($_SESSION['admin_user'])) { 
    exit("Unauthorized Access: Secure Log Required."); 
}

// Set Headers for Professional CSV Download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=MKW_Executive_Report_'.date('Y-m-d_H-i').'.csv');

$output = fopen('php://output', 'w');

/* ---------------------------------------------------------
   PART 1: METADATA & EXECUTIVE SUMMARY
   --------------------------------------------------------- */
fputcsv($output, array('REPORT TITLE:', 'MKW ORIGINALS EXECUTIVE DATA ASSET'));
fputcsv($output, array('GENERATION DATE:', date('d M Y, H:i:s')));
fputcsv($output, array('REPORTING OFFICER:', strtoupper($_SESSION['admin_user'])));
fputcsv($output, array('---'));

// Financial Summary Calculations
$rev_res = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE status = 'Delivered'");
$total_rev = mysqli_fetch_assoc($rev_res)['total'] ?? 0;

$order_count_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders");
$total_orders = mysqli_fetch_assoc($order_count_res)['count'];

$member_count_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
$total_members = mysqli_fetch_assoc($member_count_res)['count'];

fputcsv($output, array('EXECUTIVE SUMMARY'));
fputcsv($output, array('Total Settled Revenue:', 'INR ' . number_format($total_rev, 2)));
fputcsv($output, array('Total Lifetime Orders:', $total_orders));
fputcsv($output, array('Total Registered Members:', $total_members));
fputcsv($output, array('---'));
fputcsv($output, array('')); // Spacer Row

/* ---------------------------------------------------------
   PART 2: SALES & ORDER LEDGER
   --------------------------------------------------------- */
fputcsv($output, array('SECTION A: SALES & ORDER LEDGER'));
fputcsv($output, array('ORDER ID', 'CUSTOMER NAME', 'TRANSACTION VALUE', 'ORDER STATUS', 'DATE ACQUIRED', 'DATE SETTLED'));

$order_query = "SELECT id, customer_name, total_amount, status, order_date, delivered_at FROM orders ORDER BY id DESC";
$order_result = mysqli_query($conn, $order_query);

while ($row = mysqli_fetch_assoc($order_result)) {
    $formatted_row = [
        '#MKW-' . str_pad($row['id'], 4, '0', STR_PAD_LEFT),
        strtoupper($row['customer_name']),
        number_format($row['total_amount'], 2),
        strtoupper($row['status']),
        date('d-m-Y', strtotime($row['order_date'])),
        ($row['delivered_at']) ? date('d-m-Y', strtotime($row['delivered_at'])) : 'OPEN/PENDING'
    ];
    fputcsv($output, $formatted_row);
}

fputcsv($output, array('')); // Spacer Row

/* ---------------------------------------------------------
   PART 3: MEMBER REGISTRY
   --------------------------------------------------------- */
fputcsv($output, array('SECTION B: MEMBER REGISTRY'));
fputcsv($output, array('MEMBER ID', 'USERNAME', 'EMAIL ADDRESS', 'REGISTRATION DATE'));

$user_query = "SELECT id, username, email, created_at FROM users ORDER BY id DESC";
$user_result = mysqli_query($conn, $user_query);

while ($user_row = mysqli_fetch_assoc($user_result)) {
    $formatted_user = [
        '#USR-' . str_pad($user_row['id'], 4, '0', STR_PAD_LEFT),
        strtoupper($user_row['username']),
        $user_row['email'],
        date('d-m-Y', strtotime($user_row['created_at']))
    ];
    fputcsv($output, $formatted_user);
}

/* ---------------------------------------------------------
   FOOTER
   --------------------------------------------------------- */
fputcsv($output, array('')); 
fputcsv($output, array('DOCUMENT END', 'CONFIDENTIAL - FOR EXECUTIVE USE ONLY'));

fclose($output);
exit();
?>