<?php
include 'db.php';
session_start();

// 1. SECURITY: Redirect if shipping data or cart is missing
if (!isset($_SESSION['shipping']) || empty($_SESSION['cart'])) {
    header("Location: checkout.php");
    exit();
}

$order_data = $_SESSION['shipping'];

// 2. HANDLE FINAL SUBMISSION
if (isset($_POST['complete_order'])) {
    $method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    
    // Sanitize session data
    $name = mysqli_real_escape_string($conn, $order_data['name']);
    $email = mysqli_real_escape_string($conn, $order_data['email']);
    $phone = mysqli_real_escape_string($conn, $order_data['phone']);
    $address = mysqli_real_escape_string($conn, $order_data['address']);
    $total = mysqli_real_escape_string($conn, $order_data['total']);

    // Insert the main order
    $sql = "INSERT INTO orders (customer_name, email, phone, address, total_amount, status) 
            VALUES ('$name', '$email', '$phone', '$address', '$total', 'Paid via $method')";

    if (mysqli_query($conn, $sql)) {
        $new_order_id = mysqli_insert_id($conn); 

        // 3. MASTER LOOP: Save every item
        foreach ($_SESSION['cart'] as $p_id => $qty) {
            // Fix: Ensure $qty is a number, not an array
            if (is_array($qty)) { $qty = 1; }
            
            $p_id = mysqli_real_escape_string($conn, $p_id);
            $qty = (int)$qty; 
            
            $price_res = mysqli_query($conn, "SELECT price FROM products WHERE id = '$p_id'");
            $price_data = mysqli_fetch_assoc($price_res);
            $current_price = (float)($price_data['price'] ?? 0);

            $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                         VALUES ('$new_order_id', '$p_id', '$qty', '$current_price')";
            
            mysqli_query($conn, $item_sql);
        }
        
        unset($_SESSION['cart']);
        unset($_SESSION['shipping']);
        
        header("Location: thank_you.php?order_id=" . $new_order_id);
        exit();
    } else {
        echo "Atelier Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Payment | MKW Originals</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { 
            background: radial-gradient(circle at center, #1a1a1c 0%, #050505 100%); 
            color: #fff; 
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .payment-wrapper { 
            max-width: 600px; 
            margin: 80px auto; 
            padding: 0 20px;
            perspective: 1000px; /* Enables 3D space */
        }

        .payment-box { 
            background: rgba(18, 18, 20, 0.8);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1); 
            padding: 50px; 
            text-align: center; 
            border-radius: 2px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transform: rotateX(2deg); /* Subtle 3D tilt */
            transition: transform 0.5s ease;
        }
        
        .payment-box:hover {
            transform: rotateX(0deg) translateY(-5px);
        }

        h2 { font-weight: 200; letter-spacing: 8px; text-transform: uppercase; margin-bottom: 10px; color: #c5a059; }
        
        .amount-banner { 
            font-size: 32px; 
            margin-bottom: 40px; 
            font-weight: 200; 
            color: #fff; 
            padding: 20px;
            background: linear-gradient(90deg, transparent, rgba(197, 160, 89, 0.1), transparent);
        }

        .method-option { 
            background: rgba(255,255,255,0.03); 
            border: 1px solid #222; 
            padding: 20px; 
            margin-bottom: 15px; 
            cursor: pointer; 
            display: flex; 
            align-items: center; 
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase; 
            font-size: 10px; 
            letter-spacing: 3px;
        }

        .method-option:hover { 
            border-color: #c5a059; 
            background: rgba(197, 160, 89, 0.08);
            padding-left: 30px; /* Interactive sliding effect */
        }

        .method-option input { margin-right: 20px; accent-color: #c5a059; }

        .details-section { 
            display: none; 
            background: rgba(0, 0, 0, 0.3); 
            padding: 25px; 
            border: 1px solid rgba(197, 160, 89, 0.2); 
            margin-bottom: 20px; 
            transform-origin: top;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: scaleY(0.9); }
            to { opacity: 1; transform: scaleY(1); }
        }

        .card-input-group { margin-bottom: 20px; text-align: left; }
        .card-input-group input {
            width: 100%; 
            background: transparent; 
            border: none; 
            border-bottom: 1px solid #333; 
            color: #fff; 
            padding: 12px 0; 
            outline: none; 
            font-size: 13px; 
            letter-spacing: 2px;
            transition: 0.3s;
        }
        .card-input-group input:focus { border-color: #c5a059; padding-left: 10px; }

        .qr-code img { 
            width: 180px; 
            height: 180px; 
            margin: 10px auto; 
            display: block; 
            border: 4px solid #1a1a1c;
            filter: grayscale(1) invert(1); /* Matches dark aesthetic */
            transition: 0.5s;
        }
        .qr-code img:hover { filter: grayscale(0) invert(0); }

        .pay-btn { 
            width: 100%; 
            padding: 22px; 
            background: #c5a059; 
            color: #000; 
            border: none; 
            font-weight: 900; 
            text-transform: uppercase; 
            letter-spacing: 5px; 
            cursor: pointer; 
            margin-top: 20px; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
            transition: 0.4s;
        }
        
        .pay-btn:hover { 
            background: #fff; 
            box-shadow: 0 15px 30px rgba(197, 160, 89, 0.4);
            transform: translateY(-5px);
        }

        .floating-label { color: #555; font-size: 9px; margin-bottom: 5px; display: block; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="payment-wrapper">
    <div class="payment-box">
        <h2>Secure Payment</h2>
        <p style="font-size: 9px; letter-spacing: 4px; color: #666; margin-bottom: 20px;">AUTHENTIC PIECE ACQUISITION</p>
        
        <div class="amount-banner">₹<?php echo number_format($order_data['total'], 0); ?></div>

        <form method="POST" id="payment-form">
            <label class="method-option">
                <input type="radio" name="payment_method" value="UPI" onclick="showSection('upi-section')" required>
                <span>Digital Protocol (UPI)</span>
            </label>
            <div id="upi-section" class="details-section">
                <div class="qr-code">
                    <img src="qr.jpeg" alt="MKW UPI QR"> 
                </div>
                <p style="font-size: 9px; color: #c5a059; margin-top: 15px; letter-spacing: 1px;">SCAN TO AUTHORIZE TRANSACTION</p>
            </div>

            <label class="method-option">
                <input type="radio" name="payment_method" value="Card" onclick="showSection('card-section')">
                <span>Credit / Debit Vault</span>
            </label>
            <div id="card-section" class="details-section">
                <div class="card-input-group">
                    <span class="floating-label">HOLDER</span>
                    <input type="text" placeholder="NAME ON CARD">
                </div>
                <div class="card-input-group">
                    <span class="floating-label">CARD NUMBER</span>
                    <input type="text" placeholder="0000 0000 0000 0000" maxlength="19">
                </div>
                <div style="display: flex; gap: 20px;">
                    <div class="card-input-group" style="flex: 1;">
                        <span class="floating-label">EXPIRY</span>
                        <input type="text" placeholder="MM / YY" maxlength="5">
                    </div>
                    <div class="card-input-group" style="flex: 1;">
                        <span class="floating-label">CVV</span>
                        <input type="password" placeholder="***" maxlength="3">
                    </div>
                </div>
            </div>

            <label class="method-option">
                <input type="radio" name="payment_method" value="COD" onclick="showSection('cod-section')">
                <span>Cash on Delivery</span>
            </label>
            <div id="cod-section" class="details-section">
                <p style="font-size: 11px; color: #888; line-height: 1.6;">Physical currency exchange upon arrival. Subject to Liaison verification.</p>
            </div>

            <button type="submit" name="complete_order" class="pay-btn">Confirm Purchases </button>
        </form>
    </div>
</div>

<script>
    function showSection(sectionId) {
        // Hide all with a slight fade
        document.querySelectorAll('.details-section').forEach(section => {
            section.style.display = 'none';
        });
        // Show target
        const target = document.getElementById(sectionId);
        target.style.display = 'block';
    }
</script>

</body>
</html>