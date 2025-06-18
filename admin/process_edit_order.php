<?php
require_once '../includes/session.php';
require_once '../config/database.php';

// Assume admin authentication is required and handled here or in session.php
// requireAdminLogin(); // You should implement this function

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? null;
    $status = $_POST['status'] ?? null;

    // Validate inputs
    if (!$order_id || !$status) {
        $response['message'] = 'Missing order ID or status.';
        echo json_encode($response);
        exit();
    }

    // Basic validation for status (should match allowed ENUM values in database)
    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'failed']; // Add 'failed' if needed
    if (!in_array($status, $allowed_statuses)) {
        $response['message'] = 'Invalid status value.';
        echo json_encode($response);
        exit();
    }

    try {
        $conn = getDBConnection();

        // Update the order status
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);

        // Check if a row was actually updated
        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
            $response['message'] = 'Order status updated successfully.';
        } else {
            $response['message'] = 'Order not found or status was already the same.';
        }

    } catch (PDOException $e) {
        error_log("Error updating order status: " . $e->getMessage());
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
exit();
?> 