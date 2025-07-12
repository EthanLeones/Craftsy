<?php
require_once 'includes/session.php';
require_once 'config/database.php';

header('Content-Type: application/json');

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


        $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE id = ? AND active = 1");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $response['message'] = 'Product not found or inactive.';
            echo json_encode($response);
            exit();
        }

        $available_stock = (int)$product['stock_quantity'];


        $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

        $existing_quantity = $cart_item ? (int)$cart_item['quantity'] : 0;
        $total_requested = $existing_quantity + $quantity;

        if ($total_requested > $available_stock) {
            $response['message'] = "You can't add more than the available stock. You already have $existing_quantity in your cart.";
            echo json_encode($response);
            exit();
        }


        if ($cart_item) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$total_requested, $user_id, $product_id]);
        } else {
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $product_id, $quantity]);
        }

        $response['success'] = true;
        $response['message'] = 'Product added to cart!';
        $response['cart_count'] = getCartCount();
    } catch (PDOException $e) {
        error_log("Add to cart error: " . $e->getMessage());
        $response['message'] = 'Database error. Could not add item to cart.';
    }
}

echo json_encode($response);
