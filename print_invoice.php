<?php
include 'db.php';
session_start();

// 1. Security & Authentication Check
if (!isset($_GET['id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$order_id = mysqli_real_escape_string($conn, $_GET['id']);
$session_user = $_SESSION['username'];

// 2. Fetch the Main Order Details
// We also fetch the user email to double-check ownership
$order_res = mysqli_query($conn, "SELECT * FROM orders WHERE id = '$order_id'");
$order = mysqli_fetch_assoc($order_res);

if (!$order) {
    die("<div style='font-family:Cinzel; text-align:center; padding:100px;'>ARCHIVE RECORD NOT FOUND.</div>");
}

// 3. Fetch specific items with the price they were bought at (Historical Accuracy)
$items_query = "SELECT oi.quantity, oi.price as purchase_price, p.name, p.category 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = '$order_id'";
$items = mysqli_query($conn, $items_query);

// 4. Generate Digital Tracking Link
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$baseUrl = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$trackingUrl = $baseUrl . "/track_orders.php";
$qrCodeUrl = "https://chart.googleapis.com/chart?chs=100x100&cht=qr&chl=" . urlencode($trackingUrl) . "&choe=UTF-8";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Archive Record | #MKW-ARC-<?php echo $order_id; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #c5a059; --black: #000; --light-grey: #f8f8f8; }
        
        body { 
            font-family: 'Inter', sans-serif; 
            color: var(--black); 
            background: #fff; 
            margin: 0; 
            padding: 0; 
            -webkit-print-color-adjust: exact; 
        }

        /* Large Background Watermark */
        body::before {
            content: 'MKW';
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-family: 'Cinzel';
            font-size: 20rem;
            color: rgba(197, 160, 89, 0.04);
            z-index: -1;
        }

        .invoice-page { padding: 80px; max-width: 900px; margin: auto; border: 1px solid #eee; position: relative; background: #fff; }

        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 60px; border-bottom: 2px solid var(--black); padding-bottom: 30px; }
        .brand-block h1 { font-family: 'Cinzel'; letter-spacing: 10px; margin: 0; font-size: 30px; font-weight: 700; text-transform: uppercase; }
        .brand-block p { font-size: 9px; letter-spacing: 5px; text-transform: uppercase; color: var(--gold); margin-top: 5px; }

        .info-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 50px; margin-bottom: 60px; }
        .info-box h3 { font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: #aaa; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 15px; }
        .info-box p { font-size: 13px; line-height: 1.8; margin: 2px 0; }

        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th { background: var(--black); color: #fff; font-size: 9px; text-transform: uppercase; letter-spacing: 3px; padding: 18px; text-align: left; }
        td { padding: 20px 18px; border-bottom: 1px solid #f2f2f2; font-size: 13px; }

        .summary-wrapper { display: flex; justify-content: flex-end; margin-top: 50px; }
        .summary-box { width: 320px; background: var(--light-grey); padding: 30px; border-radius: 4px; }
        .summary-row { display: flex; justify-content: space-between; padding: 10px 0; font-size: 12px; }
        .summary-row.total { border-top: 1px solid #ddd; margin-top: 15px; padding-top: 20px; }
        .total-val { font-family: 'Cinzel'; font-weight: 700; font-size: 22px; color: var(--gold); }

        .footer { margin-top: 100px; display: flex; justify-content: space-between; align-items: flex-end; border-top: 1px solid #eee; padding-top: 40px; }
        .footer-text p { font-size: 8px; letter-spacing: 2px; color: #999; text-transform: uppercase; margin: 4px 0; }
        
        .qr-section { text-align: center; }
        .qr-section img { border: 1px solid #f0f0f0; padding: 8px; background: #fff; }
        .qr-section p { font-size: 7px; letter-spacing: 2px; text-transform: uppercase; color: #999; margin-top: 10px; }

        /* Floating Print Action */
        .print-actions { position: fixed; top: 30px; right: 30px; z-index: 100; }
        .btn { background: var(--gold); color: #000; border: none; padding: 15px 30px; cursor: pointer; font-size: 10px; letter-spacing: 3px; text-transform: uppercase; font-weight: bold; transition: 0.3s; }
        .btn:hover { background: #000; color: #fff; }

        @media print {
            .print-actions { display: none; }
            body::before { color: rgba(197, 160, 89, 0.08); }
            .invoice-page { border: none; padding: 0; width: 100%; }
        }
    </style>
</head>
<body>

    <div class="print-actions">
        <button class="btn" onclick="window.print()">Print Archive Record</button>
    </div>

    <div class="invoice-page">
        <div class="header-top">
            <div class="brand-block">
                <h1>MKW Originals</h1>
                <p>Authentic Couture Atelier</p>
            </div>
            <div style="text-align: right;">
                <p style="font-size: 10px; letter-spacing: 3px; color: #999; text-transform: uppercase; margin-bottom: 5px;">Reference Archive</p>
                <p style="font-family: 'Cinzel'; font-weight: 700; font-size: 16px;">#MKW-ARC-<?php echo $order_id; ?></p>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h3>Client Billing</h3>
                <p style="font-size: 17px; font-weight: 600; color: var(--gold); margin-bottom: 8px;"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                <p style="margin-top: 15px; font-weight: 600; color: #444;">TEL: <?php echo htmlspecialchars($order['phone']); ?></p>
            </div>
            <div class="info-box">
                <h3>Authentication</h3>
                <p>Date: <?php echo date('d F, Y', strtotime($order['order_date'])); ?></p>
                <p>Status: <?php echo strtoupper(htmlspecialchars($order['status'])); ?></p>
                <p>Registry: Verified Acquisition</p>
                <p>Email: <?php echo htmlspecialchars($order['email']); ?></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">Bespoke Article</th>
                    <th>Category</th>
                    <th>Qty</th>
                    <th style="text-align: right;">Valuation</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = mysqli_fetch_assoc($items)): ?>
                <tr>
                    <td style="font-weight: 600; letter-spacing: 0.8px;"><?php echo strtoupper($item['name']); ?></td>
                    <td style="text-transform: uppercase; font-size: 11px; color: #888;"><?php echo $item['category']; ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td style="text-align: right; font-weight: 600;">₹<?php echo number_format($item['purchase_price'], 0); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="summary-wrapper">
            <div class="summary-box">
                <div class="summary-row">
                    <span>Subtotal Valuation</span>
                    <span>₹<?php echo number_format($order['total_amount'], 0); ?></span>
                </div>
                <div class="summary-row" style="color: var(--gold); font-style: italic;">
                    <span>White-Glove Logistics</span>
                    <span>Complimentary</span>
                </div>
                <div class="summary-row total">
                    <span style="font-weight: 700; letter-spacing: 2px;">TOTAL INVESTMENT</span>
                    <span class="total-val">₹<?php echo number_format($order['total_amount'], 0); ?></span>
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="footer-text">
                <p>Thank you for curating your collection with MKW Originals.</p>
                <p>Handcrafted excellence is a tradition, not a standard.</p>
                <p style="color: #ccc; margin-top: 15px;">&copy; 2026 MKW ORIGINALS ATELIER | ALL RIGHTS RESERVED.</p>
            </div>
            
            <div class="qr-section">
                <img src="<?php echo $qrCodeUrl; ?>" alt="Tracking QR">
                <p>Digital Archive Access</p>
            </div>
        </div>
    </div>

</body>
</html>