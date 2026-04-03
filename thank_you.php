<?php
include 'db.php';
session_start();

// 1. Fetch the Order ID passed from the payment session
$order_id = isset($_GET['order_id']) ? mysqli_real_escape_string($conn, $_GET['order_id']) : "N/A";

// Optional: Clear any remaining temporary checkout sessions here
unset($_SESSION['shipping']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed | MKW Originals</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: var(--dark); color: #fff; }

        .success-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 90vh;
            padding: 40px 20px;
        }

        .success-card {
            background: #0c0c0d;
            border: 1px solid var(--border);
            padding: 80px 50px;
            max-width: 600px;
            width: 100%;
            text-align: center;
            position: relative;
        }

        /* Luxury Tick Icon */
        .checkmark-circle {
            width: 80px;
            height: 80px;
            border: 1px solid var(--gold);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 40px;
            color: var(--gold);
            font-size: 32px;
            font-weight: 200;
        }

        h1 {
            font-family: 'Cinzel', serif;
            letter-spacing: 8px;
            text-transform: uppercase;
            font-size: 26px;
            margin-bottom: 20px;
            color: var(--white);
        }

        .order-status-text {
            color: #888;
            font-size: 14px;
            line-height: 1.8;
            letter-spacing: 1px;
            margin-bottom: 40px;
            font-weight: 300;
        }

        .order-id-box {
            background: rgba(197, 160, 89, 0.05);
            padding: 15px 30px;
            color: var(--gold);
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            letter-spacing: 3px;
            border: 1px solid #1a1a1c;
            display: inline-block;
            margin-bottom: 50px;
            text-transform: uppercase;
        }

        .action-group {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
        }

        .btn-gold {
            background: var(--gold);
            color: #000;
            padding: 20px 60px;
            text-decoration: none;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 3px;
            transition: 0.4s;
            width: 100%;
            max-width: 320px;
        }

        .btn-gold:hover {
            background: #fff;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.4);
        }

        .invoice-link {
            font-size: 10px;
            color: #555;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 1px solid transparent;
            transition: 0.3s;
            margin-top: 10px;
        }

        .invoice-link:hover { 
            color: var(--white); 
            border-bottom-color: var(--gold); 
        }

        @media (max-width: 600px) {
            .success-card { padding: 50px 30px; }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="success-wrapper">
    <div class="success-card">
        <div class="checkmark-circle">✓</div> 
        
        <h1>Order Confirmed</h1>
         <h2>Thank You</h2>
          <h3>&</h3>
           <h3>Visit Again</h3>
        
        <p class="order-status-text">
            Your selection has been successfully reserved within our Order. Our master artisans are now preparing your bespoke collection for its journey.
        </p>
        
        <div class="order-id-box">
            Order Reference: #MKW-ARC-<?php echo $order_id; ?>
        </div>

        <div class="action-group">
            <a href="track_orders.php" class="btn-gold">Track Your Order</a>
            
            <a href="print_invoice.php?id=<?php echo $order_id; ?>" target="_blank" class="invoice-link">
                Download Order Receipt (PDF/Print)
            </a>

            <a href="index.php" style="color:#333; font-size:9px; text-decoration:none; letter-spacing:2px; text-transform:uppercase; margin-top:30px;">
                Return to Home
            </a>
        </div>
    </div>
</div>

</body>
</html>