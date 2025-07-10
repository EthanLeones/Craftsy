<?php
require_once '../includes/session.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An error occurred.'];

$order_id = $_POST['order_id'] ?? $_GET['order_id'] ?? null;

if (!$order_id || !filter_var($order_id, FILTER_VALIDATE_INT)) {
    $response['message'] = 'Invalid or missing order ID.';
    echo json_encode($response);
    exit();
}

try {
    $conn = getDBConnection();

    $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled', is_deleted = 1 WHERE id = ?");
    $stmt->execute([$order_id]);

    if ($stmt->rowCount() > 0) {
        $response['success'] = true;
        $response['message'] = 'Order marked as cancelled and deleted.';
    } else {
        $response['message'] = 'Order not found or already deleted.';
    }

} catch (PDOException $e) {
    error_log("Error processing cancellation for order #$order_id: " . $e->getMessage());
    $response['message'] = 'Database error.';
}

echo json_encode($response);
exit();
