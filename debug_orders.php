<?php
require_once 'includes/session.php';
require_once 'config/database.php';

requireLogin();
$user_id = getCurrentUserId();

try {
    $conn = getDBConnection();
    
    echo "<h2>Debug: Orders Analysis for User ID: $user_id</h2>";
    
    // Check for duplicate orders
    echo "<h3>1. Check for duplicate orders in database:</h3>";
    $stmt = $conn->prepare("
        SELECT id, user_id, status, total_amount, order_date, created_at, is_deleted,
               COUNT(*) as count_duplicates
        FROM orders 
        WHERE user_id = ?
        GROUP BY id, user_id, status, total_amount, order_date, created_at, is_deleted
        HAVING COUNT(*) > 1
        ORDER BY id
    ");
    $stmt->execute([$user_id]);
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($duplicates)) {
        echo "<p>No duplicate orders found in database.</p>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>Order ID</th><th>Status</th><th>Total</th><th>Date</th><th>Duplicate Count</th></tr>";
        foreach ($duplicates as $dup) {
            echo "<tr>";
            echo "<td>" . $dup['id'] . "</td>";
            echo "<td>" . $dup['status'] . "</td>";
            echo "<td>" . $dup['total_amount'] . "</td>";
            echo "<td>" . $dup['order_date'] . "</td>";
            echo "<td>" . $dup['count_duplicates'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Show all orders for user
    echo "<h3>2. All orders for user (raw from database):</h3>";
    $stmt = $conn->prepare("
        SELECT id, status, total_amount, order_date, created_at, is_deleted
        FROM orders 
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $all_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Order ID</th><th>Status</th><th>Total</th><th>Order Date</th><th>Created At</th><th>Is Deleted</th></tr>";
    foreach ($all_orders as $order) {
        echo "<tr>";
        echo "<td>" . $order['id'] . "</td>";
        echo "<td>" . $order['status'] . "</td>";
        echo "<td>" . $order['total_amount'] . "</td>";
        echo "<td>" . $order['order_date'] . "</td>";
        echo "<td>" . $order['created_at'] . "</td>";
        echo "<td>" . ($order['is_deleted'] ?? 0) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show order items for each order
    echo "<h3>3. Order items for each order:</h3>";
    foreach ($all_orders as $order) {
        echo "<h4>Order ID: " . $order['id'] . " (Total: " . $order['total_amount'] . ")</h4>";
        $stmt_items = $conn->prepare("
            SELECT oi.id, oi.product_id, oi.quantity, oi.price_at_time, p.name, p.image_url
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
            ORDER BY oi.id
        ");
        $stmt_items->execute([$order['id']]);
        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($items)) {
            echo "<p>No items found for this order.</p>";
        } else {
            echo "<table border='1'>";
            echo "<tr><th>Item ID</th><th>Product ID</th><th>Product</th><th>Quantity</th><th>Price</th><th>Image URL</th></tr>";
            foreach ($items as $item) {
                echo "<tr>";
                echo "<td>" . $item['id'] . "</td>";
                echo "<td>" . $item['product_id'] . "</td>";
                echo "<td>" . $item['name'] . "</td>";
                echo "<td>" . $item['quantity'] . "</td>";
                echo "<td>" . $item['price_at_time'] . "</td>";
                echo "<td>" . $item['image_url'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    // Test the exact same query used in myorders.php
    echo "<h3>4. Testing myorders.php query logic:</h3>";
    
    // Get orders the same way as myorders.php
    $stmt = $conn->prepare("
        SELECT o.id, o.order_date, o.total_amount, o.status, o.created_at 
        FROM orders o 
        WHERE o.user_id = ? AND (o.is_deleted = 0 OR o.is_deleted IS NULL)
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $test_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($test_orders as &$test_order) {
        echo "<h4>Testing Order ID: " . $test_order['id'] . "</h4>";
        
        // Get order items - same query as myorders.php
        $stmt_items = $conn->prepare("
            SELECT oi.product_id, oi.quantity, oi.price_at_time as price, p.name, p.image_url 
            FROM order_items oi 
            INNER JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ? 
            ORDER BY oi.id
        ");
        $stmt_items->execute([$test_order['id']]);
        $test_order['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Found " . count($test_order['items']) . " items:<br>";
        foreach ($test_order['items'] as $item) {
            echo "- " . $item['name'] . " (Product ID: " . $item['product_id'] . ", Qty: " . $item['quantity'] . ")<br>";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
