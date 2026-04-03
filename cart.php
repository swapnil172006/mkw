<?php
include 'db.php';
session_start();

// --- 1. Handle Cart Updates (Increase, Decrease, Remove, Clear) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    $id = isset($_POST['product_id']) ? $_POST['product_id'] : null;
    $action = $_POST['action'];

    // SAFETY CHECK: If the cart item is accidentally an array, reset it to 1
    // This prevents the "Cannot increment array" fatal error
    if ($id && isset($_SESSION['cart'][$id]) && is_array($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] = 1;
    }

    if ($action == 'increase') {
        $_SESSION['cart'][$id]++;
    } elseif ($action == 'decrease') {
        if (isset($_SESSION['cart'][$id]) && $_SESSION['cart'][$id] > 1) {
            $_SESSION['cart'][$id]--;
        } else {
            unset($_SESSION['cart'][$id]);
        }
    } elseif ($action == 'remove') {
        unset($_SESSION['cart'][$id]);
    } elseif ($action == 'clear_all') {
        unset($_SESSION['cart']); 
    }

    header("Location: cart.php");
    exit();
}

// Support for legacy GET removal
if (isset($_GET['remove'])) {
    $id_to_remove = $_GET['remove'];
    unset($_SESSION['cart'][$id_to_remove]);
    header("Location: cart.php");
    exit();
}

$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart | MKW Originals</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .cart-container { max-width: 1200px; margin: 60px auto; padding: 0 8%; }
        .cart-header { border-bottom: 1px solid #1a1a1c; padding-bottom: 20px; margin-bottom: 40px; display: flex; justify-content: space-between; align-items: center; }
        .cart-header h1 { font-weight: 200; letter-spacing: 12px; text-transform: uppercase; font-size: 24px; color: #fff; }
        .clear-bag-btn { background: transparent; border: 1px solid #333; color: #555; padding: 8px 15px; font-size: 9px; letter-spacing: 2px; text-transform: uppercase; cursor: pointer; transition: 0.3s; }
        .clear-bag-btn:hover { border-color: #ff4d4d; color: #ff4d4d; }
        .cart-table { width: 100%; border-collapse: separate; border-spacing: 0 15px; }
        .cart-table th { text-align: left; padding: 15px; color: #555; text-transform: uppercase; font-size: 10px; letter-spacing: 3px; border-bottom: 1px solid #1a1a1c; }
        .cart-table td { background: #111113; padding: 20px; border-top: 1px solid #1a1a1c; border-bottom: 1px solid #1a1a1c; color: #fff; }
        .cart-img-cell { width: 100px; }
        .cart-product-img { width: 80px; height: 100px; object-fit: cover; border: 1px solid #222; }
        .item-info strong { display: block; font-size: 14px; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 5px; }
        .item-info small { color: #c5a059; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; }
        .price-text { color: #f4f4f4; font-size: 14px; letter-spacing: 1px; }
        .qty-controls { display: flex; align-items: center; justify-content: center; gap: 15px; }
        .qty-btn { background: transparent; border: 1px solid #333; color: #fff; width: 28px; height: 28px; cursor: pointer; font-size: 16px; transition: 0.3s; display: flex; align-items: center; justify-content: center; }
        .qty-btn:hover { border-color: #c5a059; color: #c5a059; }
        .qty-num { font-size: 14px; min-width: 20px; text-align: center; color: #fff; }
        .remove-link-btn { background: none; border: none; color: #ff4d4d; cursor: pointer; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; padding: 0; }
        .cart-summary { margin-top: 50px; display: flex; flex-direction: column; align-items: flex-end; gap: 20px; }
        .total-row { font-size: 22px; font-weight: 200; letter-spacing: 4px; text-transform: uppercase; color: #fff; }
        .total-row span { color: #c5a059; font-weight: bold; margin-left: 20px; }
        .btn-group { display: flex; gap: 30px; align-items: center; margin-top: 20px; }
        .continue-link { color: #666; text-decoration: none; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; transition: 0.3s; }
        .continue-link:hover { color: #fff; }
        .buy-now-btn { background: #c5a059; color: #fff; padding: 12px 30px; text-decoration: none; text-transform: uppercase; font-size: 12px; letter-spacing: 2px; transition: 0.3s; border: none; cursor: pointer; }
        .buy-now-btn:hover { background: #a38446; }
        .empty-cart { text-align: center; padding: 100px 0; color: #666; text-transform: uppercase; letter-spacing: 2px; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="cart-container">
    <div class="cart-header">
        <h1>Shopping Cart</h1>
        <?php if (!empty($_SESSION['cart'])): ?>
            <form method="POST">
                <input type="hidden" name="update_cart" value="1">
                <button type="submit" name="action" value="clear_all" class="clear-bag-btn" onclick="return confirm('Empty the entire bag?');">
                    Clear Cart
                </button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (!empty($_SESSION['cart'])): ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Piece</th>
                    <th>Details</th>
                    <th>Price</th>
                    <th style="text-align: center;">Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($_SESSION['cart'] as $id => $quantity) {
                    // CRITICAL FIX: Ensure $quantity is a number, not an array
                    if (is_array($quantity)) {
                        $quantity = 1; 
                    }

                    $clean_id = mysqli_real_escape_string($conn, $id);
                    $res = mysqli_query($conn, "SELECT * FROM products WHERE id = '$clean_id'");
                    $item = mysqli_fetch_assoc($res);
                    
                    if ($item) {
                        // CRITICAL FIX: Use explicit numeric casting (int/float) 
                        // to prevent "Unsupported operand types"
                        $item_price = (float)$item['price'];
                        $item_qty = (int)$quantity;
                        $subtotal = $item_price * $item_qty;
                        $total += $subtotal;
                        ?>
                        <tr>
                            <td class="cart-img-cell">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="Product" class="cart-product-img">
                            </td>
                            <td class="item-info">
                                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                <small><?php echo htmlspecialchars(ucfirst($item['category'] ?? 'Originals')); ?> Collection</small>
                            </td>
                            <td class="price-text">₹<?php echo number_format($item_price, 0); ?></td>
                            <td>
                                <form method="POST" class="qty-controls">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($id); ?>">
                                    <input type="hidden" name="update_cart" value="1">
                                    <button type="submit" name="action" value="decrease" class="qty-btn">−</button>
                                    <span class="qty-num"><?php echo $item_qty; ?></span>
                                    <button type="submit" name="action" value="increase" class="qty-btn">+</button>
                                </form>
                            </td>
                            <td class="price-text" style="color: #c5a059;">₹<?php echo number_format($subtotal, 0); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($id); ?>">
                                    <input type="hidden" name="update_cart" value="1">
                                    <button type="submit" name="action" value="remove" class="remove-link-btn">Remove</button>
                                </form>
                            </td>
                        </tr>
                <?php 
                    } 
                } 
                ?>
            </tbody>
        </table>

        <div class="cart-summary">
            <div class="total-row">Total Valuation <span>₹<?php echo number_format($total, 0); ?></span></div>
            <div class="btn-group">
                <a href="index.php" class="continue-link">← Return to Collection</a>
                <a href="checkout.php" class="buy-now-btn">Proceed to Continue </a>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <p>Your shopping cart is currently empty</p>
            <br>
            <a href="index.php" class="buy-now-btn">Explore Collections</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

</body>
</html>