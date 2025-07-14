<?php
require_once '../config/database.php';
require_once '../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Invalid request method';
    header('Location: products.php');
    exit();
}

try {
    $required_fields = ['product_id', 'product_name', 'category', 'price', 'stock_quantity'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
        // Special handling for stock_quantity to allow 0
        if ($field === 'stock_quantity') {
            if ($_POST[$field] === '' || $_POST[$field] === null) {
                throw new Exception("Missing required field: $field");
            }
        } else if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $product_id = $_POST['product_id'];
    $product_name = trim($_POST['product_name']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);

    if ($price <= 0) {
        throw new Exception("Price must be greater than 0");
    }
    if ($stock_quantity < 0) {
        throw new Exception("Stock quantity cannot be negative");
    }

    $conn = getDBConnection();
    $conn->beginTransaction();

    try {
        $stmt = $conn->prepare("
            UPDATE products 
            SET name = ?, category = ?, description = ?, price = ?, stock_quantity = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$product_name, $category, $description, $price, $stock_quantity, $product_id]);

        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['product_image'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!in_array($file['type'], $allowed_types)) {
                throw new Exception("Invalid file type. Only JPG, PNG and GIF are allowed.");
            }

            $max_size = 15 * 1024 * 1024;
            if ($file['size'] > $max_size) {
                throw new Exception("File size too large. Maximum size is 15MB.");
            }

            $upload_dir = '../uploads/products/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('product_') . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $image_url = 'uploads/products/' . $new_filename;
                $stmt = $conn->prepare("UPDATE products SET image_url = ? WHERE id = ?");
                $stmt->execute([$image_url, $product_id]);
            } else {
                throw new Exception("Failed to upload image");
            }
        }

        $conn->commit();
        $_SESSION['success_message'] = 'Product updated successfully';
        header('Location: products.php');
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: products.php');
    exit();
}

?>