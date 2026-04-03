<?php
// error_reporting(E_ALL); 
// ini_set('display_errors', 1);

include 'db.php';
session_start();

if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

$user_name = "";
$user_email = "";

if (isset($_SESSION['username'])) {
    $session_user = mysqli_real_escape_string($conn, $_SESSION['username']);
    $user_query = mysqli_query($conn, "SELECT * FROM users WHERE username = '$session_user'");
    if ($u_data = mysqli_fetch_assoc($user_query)) {
        $user_name = $u_data['username'];
        $user_email = $u_data['email'];
    }
}

$total = 0;
$order_items = []; 

foreach ($_SESSION['cart'] as $id => $item_data) {
    // FIX: Check if $item_data is an array (which happens if you store sizes) or a number
    $qty = is_array($item_data) ? (int)$item_data['quantity'] : (int)$item_data;
    
    $clean_id = mysqli_real_escape_string($conn, $id);
    $res = mysqli_query($conn, "SELECT * FROM products WHERE id = '$clean_id'");
    
    if ($item = mysqli_fetch_assoc($res)) {
        // Ensure price is treated as a float/number
        $price = (float)$item['price'];
        
        // Line 32 FIX: Multiply number by number
        $total += ($price * $qty);
        
        $item['qty'] = $qty;
        // If your cart has sizes, grab it here
        $item['size'] = is_array($item_data) ? ($item_data['size'] ?? '') : '';
        
        $order_items[] = $item;
    }
}

if (isset($_POST['place_order'])) {
    $_SESSION['shipping'] = [
        'name' => mysqli_real_escape_string($conn, $_POST['name']),
        'email' => mysqli_real_escape_string($conn, $_POST['email']),
        'phone' => mysqli_real_escape_string($conn, $_POST['phone']),
        'address' => mysqli_real_escape_string($conn, $_POST['address']),
        'total' => $total,
        'items' => $order_items // Useful for the final order placement
    ];
    header("Location: payment.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Checkout | MKW Originals</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: var(--dark); color: #fff; }
        .checkout-container { max-width: 1200px; margin: 60px auto; padding: 0 5%; }
        .checkout-header { text-align: center; margin-bottom: 50px; }
        .checkout-header h1 { font-family: 'Cinzel', serif; letter-spacing: 8px; font-size: 24px; color: var(--gold); }
        .checkout-flex { display: flex; gap: 50px; align-items: flex-start; }
        .billing-form { flex: 1.6; background: #0c0c0d; padding: 40px; border: 1px solid var(--border); }
        .order-summary { flex: 1; background: #080808; padding: 35px; border: 1px solid var(--border); position: sticky; top: 120px; }
        h2 { font-family: 'Playfair Display', serif; font-size: 18px; letter-spacing: 2px; margin-bottom: 25px; color: var(--gold); text-transform: uppercase; }
        .input-group { margin-bottom: 25px; }
        .input-group label { display: block; font-size: 10px; letter-spacing: 2px; color: #666; margin-bottom: 8px; text-transform: uppercase; }
        .input-group input, .input-group textarea { width: 100%; padding: 15px 0; background: transparent; border: none; border-bottom: 1px solid #222; color: #fff; outline: none; font-size: 14px; transition: 0.3s; }
        .input-group input:focus { border-bottom-color: var(--gold); }
        .summary-item { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 15px; color: #888; letter-spacing: 1px; }
        .total-row { border-top: 1px solid #222; padding-top: 20px; margin-top: 20px; display: flex; justify-content: space-between; font-family: 'Cinzel', serif; font-size: 18px; }
        .place-order-btn { width: 100%; padding: 20px; background: var(--gold); color: #000; font-weight: bold; border: none; cursor: pointer; text-transform: uppercase; letter-spacing: 3px; margin-top: 30px; transition: 0.4s; }
        .place-order-btn:hover { background: #fff; transform: translateY(-3px); }
        @media (max-width: 900px) { .checkout-flex { flex-direction: column; } }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="checkout-container">
    <header class="checkout-header">
        <h1>Secure Checkout</h1>
        <p style="font-size: 10px; color: #555; letter-spacing: 3px; margin-top: 5px;">EST. 2026 | MKW ORIGINALS ATELIER</p>
    </header>

    <div class="checkout-flex">
        <div class="billing-form">
            <h2>Shipping & Liaison</h2>
            <form method="POST">
                <div class="input-group">
                    <label>Recipient Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user_name); ?>" required>
                </div>
                
                <div class="input-group">
                    <label>Email for Correspondence</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" required>
                </div>
                
                <div class="input-group">
                    <label>Mobile Contact</label>
                    <input type="text" name="phone" placeholder="+91" required>
                </div>
                
                <div class="input-group">
                    <label>Delivery Atelier / Residence</label>
                    <textarea name="address" rows="3" required></textarea>
                </div>
                
                <button type="submit" name="place_order" class="place-order-btn">Proceed to Secure Payment</button>
            </form>
        </div>

        <div class="order-summary">
            <h2>Order Manifest</h2>
            <?php foreach($order_items as $item): ?>
                <div class="summary-item">
                    <span>
                        <?php echo $item['name']; ?> (x<?php echo $item['qty']; ?>)
                        <?php if(!empty($item['size'])) echo "<br><small>Size: ".$item['size']."</small>"; ?>
                    </span>
                    <span>₹<?php echo number_format($item['price'] * $item['qty'], 0); ?></span>
                </div>
            <?php endforeach; ?>
            
            <div class="total-row">
                <span>Total Due</span>
                <span style="color: var(--gold);">₹<?php echo number_format($total, 0); ?></span>
            </div>
            
            <p style="font-size: 9px; color: #444; margin-top: 20px; text-transform: uppercase; line-height: 1.6;">
                * Complimentary White-Glove Shipping Applied to your collection.
            </p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>