<?php
require_once 'includes/session.php';
require_once 'config/database.php';

header('Content-Type: application/json'); 

$response = ['success' => false, 'message' => 'An error occurred.'];

if (!isLoggedIn()) {
    $response['message'] = 'Please log in to remove items from your cart.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = getCurrentUserId();
    $product_id = $_POST['product_id'] ?? null;

    if ($product_id === null) {
        $response['message'] = 'Invalid product.';
        echo json_encode($response);
        exit();
    }

    try {
        $conn = getDBConnection();

        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);

        $response['success'] = true;
        $response['message'] = 'Item removed from cart!';
        $response['cart_count'] = getCartCount();

    } catch (PDOException $e) {
        error_log("Remove from cart error: " . $e->getMessage());
        $response['message'] = 'Database error. Could not remove item from cart.';
    }
}

echo json_encode($response); 