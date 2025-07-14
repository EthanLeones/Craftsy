<?php
require_once '../config/database.php';
require_once '../includes/session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    if (!isset($_POST['product_id']) || !isset($_POST['stock_quantity'])) {
        throw new Exception("Missing required fields");
    }

    $product_id = intval($_POST['product_id']);
    $stock_quantity = intval($_POST['stock_quantity']);

    if ($product_id <= 0) {
        throw new Exception("Invalid product ID");
    }

    if ($stock_quantity < 0) {
        throw new Exception("Stock quantity cannot be negative");
    }

    $conn = getDBConnection();
    
    // First check if the product exists
    $stmt_check = $conn->prepare("SELECT id, name FROM products WHERE id = ? AND active = 1");
    $stmt_check->execute([$product_id]);
    $product = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        throw new Exception("Product not found");
    }

    // Update the stock quantity
    $stmt_update = $conn->prepare("UPDATE products SET stock_quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt_update->execute([$stock_quantity, $product_id]);

    echo json_encode([
        'success' => true, 
        'message' => 'Stock updated successfully for ' . $product['name'],
        'product_id' => $product_id,
        'new_stock' => $stock_quantity
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>
