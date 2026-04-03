<?php
include 'db.php';
session_start();

// Security: If not logged in, send to login page
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$session_user = mysqli_real_escape_string($conn, $_SESSION['username']);

// 1. Get user email to identify their specific orders
$user_res = mysqli_query($conn, "SELECT email FROM users WHERE username = '$session_user'");
$user_data = mysqli_fetch_assoc($user_res);
$user_email = $user_data['email'];

// 2. MASTER QUERY: Connects Orders -> Order Items -> Products
// This allows us to see the actual product Name and Image for every order
$order_query = "SELECT 
                    o.id as order_id, 
                    o.order_date, 
                    o.status, 
                    o.total_amount, 
                    p.name as product_name, 
                    p.image_url 
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN products p ON oi.product_id = p.id
                WHERE o.email = '$user_email' 
                ORDER BY o.order_date DESC";

$orders = mysqli_query($conn, $order_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Order| MKW Originals</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #050505; color: #fff; }
        .track-wrapper { max-width: 1200px; margin: 80px auto; padding: 0 5%; }
        
        h1 { 
            font-family: 'Cinzel', serif; 
            letter-spacing: 8px; 
            text-transform: uppercase; 
            margin-bottom: 50px; 
            color: var(--gold);
        }

        .archive-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .archive-item {
            background: #0c0c0d;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 20px;
            transition: 0.3s;
        }

        .archive-item:hover { border-color: var(--gold); }

        .item-img {
            width: 100px;
            height: 130px;
            border: 1px solid #1a1a1c;
            margin-right: 30px;
            background: #111;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .item-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-info { flex: 1; }
        .item-info h3 { 
            font-size: 18px; 
            text-transform: uppercase; 
            letter-spacing: 2px; 
            margin-bottom: 5px;
        }
        .item-info p { color: #555; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }

        .item-status { text-align: right; }

        .status-badge { 
            display: inline-block;
            padding: 6px 15px; 
            font-size: 9px; 
            letter-spacing: 2px;
            text-transform: uppercase;
            border: 1px solid #333;
            margin-bottom: 10px;
        }

        .status-paid { border-color: var(--gold); color: var(--gold); }

        .back-btn { 
            display: inline-block; 
            margin-top: 40px; 
            color: #444; 
            text-decoration: none; 
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 3px;
        }

        .invoice-link {
            display: block; 
            font-size: 9px; 
            color: #555; 
            text-decoration: none; 
            margin-top: 12px; 
            letter-spacing: 1px; 
            text-transform: uppercase;
            transition: 0.3s;
        }
        .invoice-link:hover { color: var(--gold); }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="track-wrapper">
    <h1>Your Order</h1>

    <?php if (mysqli_num_rows($orders) > 0): ?>
        <div class="archive-grid">
            <?php while($row = mysqli_fetch_assoc($orders)): ?>
                <div class="archive-item">
                    
                    <div class="item-img">
                        <?php if(!empty($row['image_url'])): ?>
                            <img src="<?php echo $row['image_url']; ?>" alt="Product">
                        <?php else: ?>
                            <span style="font-size: 10px; color: #333; letter-spacing: 2px;">MKW</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="item-info">
                        <p>Acquired: <?php echo date('d M, Y', strtotime($row['order_date'])); ?></p>
                        <h3><?php echo $row['product_name']; ?></h3>
                        <p>Ref: #MKW-<?php echo $row['order_id']; ?></p>
                    </div>

                    <div class="item-status">
                        <?php 
                            $status = $row['status'];
                            $class = (strpos($status, 'Paid') !== false || strpos($status, 'Delivered') !== false) ? 'status-paid' : '';
                        ?>
                        <span class="status-badge <?php echo $class; ?>">
                            <?php echo $status; ?>
                        </span>
                        
                        <div style="font-family: 'Cinzel'; color: var(--gold); font-size: 16px;">
                            ₹<?php echo number_format($row['total_amount'], 0); ?>
                        </div>

                        <a href="print_invoice.php?id=<?php echo $row['order_id']; ?>" 
                           target="_blank" 
                           class="invoice-link">
                             View Invoice
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-orders" style="text-align: center; padding: 100px; border: 1px dashed #222;">
            <p style="letter-spacing: 3px; color: #555;">NO COSTUMES FOUND IN YOUR ORDER.</p>
            <a href="index.php" class="back-btn" style="color: var(--gold); border-bottom: 1px solid var(--gold);">Browse Collection</a>
        </div>
    <?php endif; ?>

    <a href="index.php" class="back-btn">← Return to Home</a>
</div>

<?php include 'footer.php'; ?>

</body>
</html>