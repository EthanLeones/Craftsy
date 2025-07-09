<?php
require_once '../config/database.php';
require_once '../includes/session.php';

header('Content-Type: application/json');

$product_id = $_POST['product_id'] ?? null;

if ($product_id === null || !is_numeric($product_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
    exit();
}

$product_id = (int)$product_id;

try {
    $conn = getDBConnection();
    $conn->beginTransaction();

    // Optional: Check if product exists
    $stmt_check = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $stmt_check->execute([$product_id]);
    if (!$stmt_check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
        exit();
    }

    // Soft delete: set active = 0
    $stmt_soft_delete = $conn->prepare("UPDATE products SET active = 0 WHERE id = ?");
    $stmt_soft_delete->execute([$product_id]);

    if ($stmt_soft_delete->rowCount() > 0) {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Product marked as inactive.']);
    } else {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'No changes made.']);
    }

} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Error updating product status: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
