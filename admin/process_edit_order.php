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

    // Check if any update was made
    $changes_made = false;
    if (isset($stmt_restore)) {
        $changes_made = $stmt_restore->rowCount() > 0;
    } elseif (isset($stmt)) {
        $changes_made = $stmt->rowCount() > 0;
    }

    if ($changes_made) {
        $response['success'] = true;
        $response['message'] = 'Order status updated successfully.';
    } else {
        $response['message'] = 'No changes made.';
    }

} catch (PDOException $e) {
    error_log("Error updating order status for order #$order_id: " . $e->getMessage());
    $response['message'] = 'Database error.';
}

echo json_encode($response);
exit();
