<?php
require_once '../config/database.php';
require_once '../includes/session.php';

// Assume admin authentication is required
// requireAdminLogin();
// if (!isAdmin()) {
//     echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
//     exit();
// }

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

    // Get product image URL before deleting the product record
    $stmt_get_image = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt_get_image->execute([$product_id]);
    $product = $stmt_get_image->fetch(PDO::FETCH_ASSOC);
    $image_url = $product['image_url'] ?? null;

    // Delete product from database
    $stmt_delete_product = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt_delete_product->execute([$product_id]);

    // Check if deletion was successful (optional, but good practice)
    if ($stmt_delete_product->rowCount() > 0) {
        // Delete associated image file if it exists and is not the placeholder
        if ($image_url && $image_url !== 'images/placeholder_admin.png') {
            $image_path = __DIR__ . '/../' . $image_url; // Path relative to the script location
            if (file_exists($image_path)) {
                unlink($image_path); // Delete the file
            }
        }
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully.']);
    } else {
        // Product with given ID not found or no rows affected
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Product not found or could not be deleted.']);
    }

} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Error deleting product: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
     error_log("Error deleting product file: " . $e->getMessage());
     // Note: We commit the database change even if file deletion fails
     // $conn->rollBack(); // Uncomment if file deletion failure should roll back DB
     echo json_encode(['success' => false, 'message' => 'An error occurred during file deletion.']);
}?> 