<?php
require_once '../includes/session.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An error occurred.'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit();
}

$order_id = $_POST['order_id'] ?? null;
$status = strtolower(trim($_POST['status'] ?? ''));

$allowed_statuses = ['pending', 'processing', 'shipping', 'delivered', 'cancelled', 'failed'];

if (!$order_id || !$status || !in_array($status, $allowed_statuses)) {
    $response['message'] = 'Missing or invalid order ID or status.';
    echo json_encode($response);
    exit();
}

try {
    $conn = getDBConnection();

    $stmt_current = $conn->prepare("SELECT status FROM orders WHERE id = ?");
    $stmt_current->execute([$order_id]);
    $current = $stmt_current->fetch(PDO::FETCH_ASSOC);

    if (!$current) {
        $response['message'] = 'Order not found.';
        echo json_encode($response);
        exit();
    }

    $current_status = strtolower($current['status']);

    // Handle stock adjustments based on status transitions
    $needs_stock_adjustment = false;
    $restore_stock = false;
    $reduce_stock = false;

    if ($current_status !== 'cancelled' && $status === 'cancelled') {
        // Order is being cancelled - restore stock
        $restore_stock = true;
        $needs_stock_adjustment = true;
    } elseif ($current_status === 'cancelled' && $status !== 'cancelled') {
        // Order is being restored from cancelled - reduce stock again
        $reduce_stock = true;
        $needs_stock_adjustment = true;
    }

    // Begin transaction for consistency
    $conn->beginTransaction();

    try {
        // Handle stock adjustments if needed
        if ($needs_stock_adjustment) {
            // Get all order items for this order
            $stmt_items = $conn->prepare("
                SELECT oi.product_id, oi.quantity 
                FROM order_items oi 
                WHERE oi.order_id = ?
            ");
            $stmt_items->execute([$order_id]);
            $order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

            if ($restore_stock) {
                // Restore stock when cancelling order
                $stmt_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
                foreach ($order_items as $item) {
                    $stmt_stock->execute([$item['quantity'], $item['product_id']]);
                    error_log("Restored stock: +{$item['quantity']} for product {$item['product_id']}");
                }
            } elseif ($reduce_stock) {
                // Reduce stock when restoring order from cancelled
                // First check if we have enough stock for all items
                foreach ($order_items as $item) {
                    $stmt_check = $conn->prepare("SELECT stock_quantity FROM products WHERE id = ?");
                    $stmt_check->execute([$item['product_id']]);
                    $current_stock = (int)$stmt_check->fetchColumn();

                    if ($current_stock < $item['quantity']) {
                        $conn->rollback();
                        $response['message'] = "Insufficient stock to restore order. Product ID {$item['product_id']} has {$current_stock} in stock but needs {$item['quantity']}.";
                        echo json_encode($response);
                        exit();
                    }
                }

                // If we have enough stock for all items, proceed with reduction
                $stmt_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?");
                foreach ($order_items as $item) {
                    $affected = $stmt_stock->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                    if ($stmt_stock->rowCount() === 0) {
                        // This shouldn't happen since we checked above, but just in case
                        $conn->rollback();
                        $response['message'] = "Failed to reduce stock for product ID {$item['product_id']}. Please try again.";
                        echo json_encode($response);
                        exit();
                    }
                    error_log("Reduced stock: -{$item['quantity']} for product {$item['product_id']}");
                }
            }
        }

        // Handle status transitions
        if ($current_status === 'cancelled' && $status !== 'cancelled') {
            // Restoring from cancelled status - ensure not marked as deleted
            $stmt_restore = $conn->prepare("UPDATE orders SET status = ?, is_deleted = 0 WHERE id = ?");
            $stmt_restore->execute([$status, $order_id]);
        } elseif ($status === 'cancelled') {
            // Setting to cancelled - update status but keep is_deleted as 0 so it shows in cancelled table
            $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled', is_deleted = 0 WHERE id = ?");
            $stmt->execute([$order_id]);
        } else {
            // Normal status update
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $order_id]);
        }

        // Commit the transaction
        $conn->commit();

        // Check if any update was made
        $changes_made = false;
        if (isset($stmt_restore)) {
            $changes_made = $stmt_restore->rowCount() > 0;
        } elseif (isset($stmt)) {
            $changes_made = $stmt->rowCount() > 0;
        }

        if ($changes_made || $needs_stock_adjustment) {
            $response['success'] = true;
            $response['message'] = 'Order status updated successfully.';
        } else {
            $response['message'] = 'No changes made.';
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in stock adjustment or status update: " . $e->getMessage());
        $response['message'] = 'Error updating order status.';
    }
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    error_log("Error updating order status for order #$order_id: " . $e->getMessage());
    $response['message'] = 'Database error.';
}

echo json_encode($response);
exit();
