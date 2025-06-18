<?php
require_once 'includes/session.php';
require_once 'config/database.php';

header('Content-Type: application/json'); // Indicate JSON response

$response = ['success' => false, 'message' => 'An error occurred.'];

if (!isLoggedIn()) {
    $response['message'] = 'Please log in to add items to your cart.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = getCurrentUserId();
    $product_id = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;

    if ($product_id === null || $quantity <= 0) {
        $response['message'] = 'Invalid product or quantity.';
        echo json_encode($response);
        exit();
    }

    try {
        $conn = getDBConnection();

        // Check if product exists in cart for this user
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $cart_item = $stmt->fetch();

        if ($cart_item) {
            // Item exists, update quantity
            $new_quantity = $cart_item['quantity'] + $quantity;
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$new_quantity, $user_id, $product_id]);
        } else {
            // Item does not exist, insert new item
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $product_id, $quantity]);
        }

        $response['success'] = true;
        $response['message'] = 'Product added to cart!';
        // Optionally, return updated cart count
        $response['cart_count'] = getCartCount();

    } catch (PDOException $e) {
        error_log("Add to cart error: " . $e->getMessage());
        $response['message'] = 'Database error. Could not add item to cart.';
    }
}

echo json_encode($response); 