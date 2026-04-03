<?php
include 'db.php';
session_start();

/**
 * 1. MANDATORY LOGIN CHECK
 * Protects the archive; only logged-in users can reserve pieces.
 */
if (!isset($_SESSION['user_id'])) {
    // If the user isn't logged in, redirect to login page
    header("Location: login.php?error=login_required");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. VALIDATE POST DATA
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    
    $product_id = intval($_POST['product_id']);
    $selected_size = isset($_POST['selected_size']) ? mysqli_real_escape_string($conn, $_POST['selected_size']) : 'Standard';

    if ($product_id <= 0) {
        header("Location: index.php");
        exit();
    }

    /**
     * 3. INITIALIZE ATELIER BAG (SESSION CART)
     * We use a unique key combining ID and Size so the user can add 
     * the same product in different sizes (e.g., one 'S' and one 'M').
     */
    $cart_key = $product_id . "_" . $selected_size;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    // 4. INCREMENT QUANTITY OR ADD NEW ENTRY
    if (isset($_SESSION['cart'][$cart_key])) {
        $_SESSION['cart'][$cart_key]['quantity']++;
    } else {
        $_SESSION['cart'][$cart_key] = [
            'id' => $product_id,
            'size' => $selected_size,
            'quantity' => 1
        ];
    }

    /**
     * 5. OPTIONAL: SAVE TO DATABASE (PERSISTENT CART)
     * If you want the cart to stay saved even if the user logs out, 
     * you can uncomment this SQL block.
     */
    /*
    $check_cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id' AND product_id = '$product_id' AND size = '$selected_size'");
    if(mysqli_num_rows($check_cart) > 0) {
        mysqli_query($conn, "UPDATE cart SET quantity = quantity + 1 WHERE user_id = '$user_id' AND product_id = '$product_id' AND size = '$selected_size'");
    } else {
        mysqli_query($conn, "INSERT INTO cart (user_id, product_id, size, quantity) VALUES ('$user_id', '$product_id', '$selected_size', 1)");
    }
    */

    // 6. REDIRECT WITH STATUS
    header("Location: cart.php?status=added&item=" . urlencode($selected_size));
    exit();

} else {
    // Prevent direct URL access
    header("Location: index.php");
    exit();
}
?>