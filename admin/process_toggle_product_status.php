<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$product_id = $_POST['product_id'] ?? null;
$active = $_POST['active'] ?? null;

if (!is_numeric($product_id) || !in_array($active, ['0', '1'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit();
}

try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE products SET active = ? WHERE id = ?");
    $stmt->execute([$active, $product_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made.']);
    }
} catch (PDOException $e) {
    error_log("Toggle active error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
exit();
?>