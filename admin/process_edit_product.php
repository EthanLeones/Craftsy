<?php
require_once '../config/database.php';
require_once '../includes/session.php'; // Assuming session is used for admin login

// Admin authentication check
// requireAdminLogin(); // Implement this function
// if (!isAdmin()) {
//     header('Location: ../login.php');
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Invalid request method';
    header('Location: products.php');
    exit();
}

try {
    // Validate required fields
    $required_fields = ['product_id', 'product_name', 'category', 'price', 'stock_quantity'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $product_id = $_POST['product_id'];
    $product_name = trim($_POST['product_name']);
    $category = trim($_POST['category']); // Get category name
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);

    // Validate price and stock
    if ($price <= 0) {
        throw new Exception("Price must be greater than 0");
    }
    if ($stock_quantity < 0) {
        throw new Exception("Stock quantity cannot be negative");
    }

    $conn = getDBConnection();

    // Start transaction
    $conn->beginTransaction();

    try {
        // Update product details
        $stmt = $conn->prepare("
            UPDATE products 
            SET name = ?, category = ?, description = ?, price = ?, stock_quantity = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$product_name, $category, $description, $price, $stock_quantity, $product_id]);

        // Handle image upload if a new image was provided
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['product_image'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!in_array($file['type'], $allowed_types)) {
                throw new Exception("Invalid file type. Only JPG, PNG and GIF are allowed.");
            }

            $max_size = 15 * 1024 * 1024; // 15MB
            if ($file['size'] > $max_size) {
                throw new Exception("File size too large. Maximum size is 15MB.");
            }

            // Create uploads directory if it doesn't exist
            $upload_dir = '../uploads/products/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Generate unique filename
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('product_') . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Update product image URL in database
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