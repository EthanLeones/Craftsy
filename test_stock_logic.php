<?php
require_once 'config/database.php';

try {
    $conn = getDBConnection();
    
    echo "=== Order and Stock Status Test ===" . PHP_EOL;
    
    // Check all order statuses
    $stmt = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Order Status Summary:" . PHP_EOL;
    foreach ($statuses as $status) {
        echo "  - {$status['status']}: {$status['count']} orders" . PHP_EOL;
    }
    echo PHP_EOL;
    
    // Get a sample order (any status)
    $stmt = $conn->query("SELECT id, status FROM orders LIMIT 1");
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        echo "Sample Order: ID #{$order['id']}, Status: {$order['status']}" . PHP_EOL;
        
        // Get order items
        $stmt_items = $conn->prepare("
            SELECT oi.product_id, oi.quantity, p.name, p.stock_quantity 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt_items->execute([$order['id']]);
        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Order Items:" . PHP_EOL;
        foreach ($items as $item) {
            echo "  - Product: {$item['name']} (ID: {$item['product_id']})" . PHP_EOL;
            echo "    Ordered Quantity: {$item['quantity']}" . PHP_EOL;
            echo "    Current Stock: {$item['stock_quantity']}" . PHP_EOL;
            echo "    Stock if order cancelled: " . ($item['stock_quantity'] + $item['quantity']) . PHP_EOL;
            echo "    Stock if order restored: " . max(0, $item['stock_quantity'] - $item['quantity']) . PHP_EOL . PHP_EOL;
        }
        
        echo "=== Testing Stock Logic ===" . PHP_EOL;
        echo "Order #{$order['id']} current status: {$order['status']}" . PHP_EOL;
        echo "If changed to 'cancelled': stock will be restored (+quantity)" . PHP_EOL;
        echo "If changed from 'cancelled' to 'pending': stock will be reduced (-quantity)" . PHP_EOL;
        
    } else {
        echo "No orders found in database." . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
?>
