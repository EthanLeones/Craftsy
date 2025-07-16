<?php
require_once 'includes/session.php';
require_once 'config/database.php';

requireLogin(); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prevent duplicate form submissions
    $form_token = $_POST['form_token'] ?? '';
    $session_token = $_SESSION['form_token'] ?? '';
    
    if (empty($form_token) || $form_token !== $session_token) {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Invalid form submission. Please try again.'];
        header('Location: checkout.php?error=invalid_token');
        exit();
    }
    
    // Clear the token to prevent reuse
    unset($_SESSION['form_token']);
    
    $user_id = getCurrentUserId();
    $shipping_address_id = $_POST['shipping_address'] ?? null;
    $payment_method = $_POST['payment_method'] ?? null;

    $proof_of_payment_url = null; 

    if (($payment_method === 'bank_transfer' || $payment_method === 'gcash') && isset($_FILES['proof_of_payment']) && $_FILES['proof_of_payment']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['proof_of_payment'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($file['type'], $allowed_types)) {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Invalid file type for proof of payment. Only JPG, PNG, GIF are allowed.'];
            header('Location: checkout.php?error=invalid_proof_type');
            exit();
        }

        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_size) {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Proof of payment file size too large. Maximum size is 5MB.'];
            header('Location: checkout.php?error=proof_too_large');
            exit();
        }

        $upload_dir = 'images/proof/'; 
        $absolute_upload_dir = __DIR__ . '/' . $upload_dir;

        if (!file_exists($absolute_upload_dir)) {
            mkdir($absolute_upload_dir, 0777, true);
        }

        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('proof_') . '.' . $file_extension;
        $upload_path = $absolute_upload_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $proof_of_payment_url = $upload_dir . $new_filename;
        } else {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Failed to upload proof of payment file.'];
            header('Location: checkout.php?error=proof_upload_failed');
            exit();
        }
    }

    if (($payment_method === 'bank_transfer' || $payment_method === 'gcash') && $proof_of_payment_url === null) {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Proof of payment is required for the selected payment method.'];
        header('Location: checkout.php?error=proof_required');
        exit();
    }

    if (!$user_id || !$shipping_address_id || !$payment_method) {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Missing required order information.'];
        header('Location: checkout.php?error=missing_details');
        exit();
    }

    try {
        $conn = getDBConnection();
        
        // Start transaction with proper isolation level to prevent race conditions
        $conn->beginTransaction();
        
        // Lock the user's cart to prevent concurrent modifications
        $stmt_lock = $conn->prepare("SELECT 1 FROM cart WHERE user_id = ? FOR UPDATE");
        $stmt_lock->execute([$user_id]);

        $stmt_cart = $conn->prepare("SELECT c.*, p.name, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        $stmt_cart->execute([$user_id]);
        $cart_items = $stmt_cart->fetchAll(PDO::FETCH_ASSOC);

        if (empty($cart_items)) {
            $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Your cart is empty.'];
            $conn->rollBack();
            header('Location: cart.php');
            exit();
        }

        $total_amount = array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $cart_items));

        $stmt_address = $conn->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt_address->execute([$shipping_address_id, $user_id]);
        $shipping_address = $stmt_address->fetch(PDO::FETCH_ASSOC);

        if (!$shipping_address) {
             $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Invalid shipping address selected.'];
             $conn->rollBack();
             header('Location: checkout.php?error=invalid_address');
             exit();
        }

        $stmt_order = $conn->prepare("INSERT INTO orders (user_id, shipping_address_line1, shipping_address_line2, shipping_city, shipping_state_province, shipping_postal_code, shipping_country, shipping_contact_number, total_amount, payment_method, proof_of_payment_url, order_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending')");
        $stmt_order->execute([
            $user_id,
            $shipping_address['address_line1'] ?? '',
            $shipping_address['address_line2'] ?? '',
            $shipping_address['city'] ?? '',
            $shipping_address['state_province'] ?? '',
            $shipping_address['postal_code'] ?? '',
            $shipping_address['country'] ?? '',
            $shipping_address['contact_number'] ?? '',
            $total_amount,
            $payment_method,
            $proof_of_payment_url
        ]);

        $order_id = $conn->lastInsertId();

        $stmt_order_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
        foreach ($cart_items as $item) {
            $stmt_order_item->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        }

        $stmt_update_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
        foreach ($cart_items as $item) {
            $stmt_update_stock->execute([$item['quantity'], $item['product_id']]);
        }

        $stmt_clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt_clear_cart->execute([$user_id]);

        $conn->commit();

        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Order placed successfully!'];
        header('Location: order_confirmation.php?order_id=' . $order_id); // Redirect to confirmation page
        exit();

    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Error placing order: " . $e->getMessage());
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'An error occurred while placing your order. Please try again.'];
        header('Location: checkout.php?error=db_error'); // Redirect back to checkout with error
        exit();
    }

} else {
    header('Location: checkout.php');
    exit();
}
?> 