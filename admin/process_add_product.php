<?php

// Start session and include necessary files
// require_once '../includes/session.php';
// requireAdminLogin(); // Ensure only admins can access this page

require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Initialize response array
$response = ['success' => false, 'message' => ''];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $product_name = filter_var($_POST['product_name'] ?? '', FILTER_SANITIZE_STRING);
    $category = filter_var($_POST['category'] ?? '', FILTER_SANITIZE_STRING); // Get category name
    $description = filter_var($_POST['description'] ?? '', FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);
    $stock_quantity = filter_var($_POST['stock_quantity'] ?? '', FILTER_VALIDATE_INT);

    $image_url = null;

    // Basic validation
    if (empty($product_name) || empty($category) || $price === false || $price < 0 || $stock_quantity === false || $stock_quantity < 0) {
        $response['message'] = 'Invalid input data. Please check all required fields.';
        echo json_encode($response);
        exit();
    }

    // Handle image upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../images/products/'; // Directory to save images
        // Ensure the upload directory exists and is writable
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                $response['message'] = 'Failed to create upload directory.';
                echo json_encode($response);
                exit();
            }
        }

        $file_tmp_path = $_FILES['product_image']['tmp_name'];
        $file_name = $_FILES['product_image']['name'];
        $file_size = $_FILES['product_image']['size'];
        $file_type = $_FILES['product_image']['type'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Generate a unique filename to prevent overwriting
        $new_file_name = md5(time() . $file_name) . '.' . $file_extension;
        $dest_path = $upload_dir . $new_file_name;

        // Allowed file extensions and MIME types
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

        if (in_array($file_extension, $allowed_extensions) && in_array($file_type, $allowed_types) && $file_size <= 15 * 1024 * 1024) { // Max 15MB
            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $image_url = 'images/products/' . $new_file_name; // Store relative path in DB
            } else {
                $response['message'] = 'Error uploading file. Please try again.';
                echo json_encode($response);
                exit();
            }
        } else {
            $response['message'] = 'Invalid file type or size. Please upload a valid image file (JPG, PNG, GIF) under 15MB.';
            echo json_encode($response);
            exit();
        }
    }

    // Insert data into the database
    try {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("INSERT INTO products (name, category, description, price, stock_quantity, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$product_name, $category, $description, $price, $stock_quantity, $image_url]);

        $response['success'] = true;
        $response['message'] = 'Product added successfully!';

    } catch (PDOException $e) {
        error_log("Error adding product: " . $e->getMessage());
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
exit();

?> 