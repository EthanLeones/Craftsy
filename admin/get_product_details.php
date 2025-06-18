<?php
require_once '../config/database.php';
require_once '../includes/session.php'; // Assuming session is used for admin login

header('Content-Type: application/json');

// Admin authentication check
// requireAdminLogin(); // Implement this function
// if (!isAdmin()) {
//     echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
//     exit();
// }

$product_id = $_GET['id'] ?? null;

if ($product_id === null || !is_numeric($product_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
    exit();
}

$conn = getDBConnection();

try {
    $stmt = $conn->prepare("
        SELECT p.* 
        FROM products p 
        WHERE p.id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo json_encode(['success' => true, 'product' => $product]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
    }

} catch (PDOException $e) {
    error_log("Error fetching product details: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}

?> 