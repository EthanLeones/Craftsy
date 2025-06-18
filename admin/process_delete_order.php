<?php
require_once '../includes/session.php';
require_once '../config/database.php';

// Assume admin authentication is required and handled here or in session.php
// requireAdminLogin(); // You should implement this function

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? null;

    if (!$order_id) {
        $response['message'] = 'Missing order ID.';
        echo json_encode($response);
        exit();
    }

    try {
        $conn = getDBConnection();

        // Start transaction (optional but good practice for related operations if any)
        // $conn->beginTransaction();

        // Delete the order
        // Foreign key with ON DELETE CASCADE should handle order_items deletion
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);

        // Check if a row was actually deleted
        if ($stmt->rowCount() > 0) {
            // $conn->commit(); // Commit transaction if started
            $response['success'] = true;
            $response['message'] = 'Order deleted successfully.';
        } else {
            // $conn->rollBack(); // Roll back transaction if started
            $response['message'] = 'Order not found or could not be deleted.';
        }

    } catch (PDOException $e) {
        // if ($conn->inTransaction()) { $conn->rollBack(); } // Roll back transaction on error
        error_log("Error deleting order: " . $e->getMessage());
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

echo json_encode($response);
exit();
?> 