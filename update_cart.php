<?php
require_once 'includes/session.php';
require_once 'config/database.php';

header('Content-Type: application/json'); 

$response = ['success' => false, 'message' => 'An error occurred.'];

if (!isLoggedIn()) {
    $response['message'] = 'Please log in to update your cart.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = getCurrentUserId();
    $product_id = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? null;

    if ($product_id === null || $quantity === null || $quantity < 0) {
        $response['message'] = 'Invalid product or quantity.';
        echo json_encode($response);
        exit();
    }

    try {
        $conn = getDBConnection();

        if ($quantity > 0) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$quantity, $user_id, $product_id]);
        } else {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
        }

        $response['success'] = true;
        $response['message'] = 'Cart updated successfully!';
        $response['cart_count'] = getCartCount();

    } catch (PDOException $e) {
        error_log("Update cart error: " . $e->getMessage());
        $response['message'] = 'Database error. Could not update cart.';
    }
}

echo json_encode($response); 