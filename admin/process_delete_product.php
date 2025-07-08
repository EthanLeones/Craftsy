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

    $stmt_get_image = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt_get_image->execute([$product_id]);
    $product = $stmt_get_image->fetch(PDO::FETCH_ASSOC);
    $image_url = $product['image_url'] ?? null;

    $stmt_delete_product = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt_delete_product->execute([$product_id]);

    if ($stmt_delete_product->rowCount() > 0) {
        if ($image_url && $image_url !== 'images/placeholder_admin.png') {
            $image_path = __DIR__ . '/../' . $image_url;
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully.']);
    } else {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Product not found or could not be deleted.']);
    }

} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Error deleting product: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
     error_log("Error deleting product file: " . $e->getMessage());
     echo json_encode(['success' => false, 'message' => 'An error occurred during file deletion.']);
}