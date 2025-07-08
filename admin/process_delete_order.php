<?php
require_once '../includes/session.php';
require_once '../config/database.php';

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

        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);

        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
            $response['message'] = 'Order deleted successfully.';
        } else {
            $response['message'] = 'Order not found or could not be deleted.';
        }

    } catch (PDOException $e) {
        error_log("Error deleting order: " . $e->getMessage());
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

echo json_encode($response);
exit();
?>