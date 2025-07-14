<?php



require_once '../config/database.php';


header('Content-Type: application/json');


$response = ['success' => false, 'message' => ''];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_name = filter_var($_POST['product_name'] ?? '', FILTER_SANITIZE_STRING);
    $category = filter_var($_POST['category'] ?? '', FILTER_SANITIZE_STRING); // Get category name
    $description = filter_var($_POST['description'] ?? '', FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);
    $stock_quantity = filter_var($_POST['stock_quantity'] ?? '', FILTER_VALIDATE_INT);

    $image_url = null;


    if (empty($product_name) || empty($category) || $price === false || $price < 0 || $stock_quantity === false || $stock_quantity < 0) {
        $response['message'] = 'Invalid input data. Please check all required fields.';
        echo json_encode($response);
        exit();
    }


    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../images/products/';
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


        $new_file_name = md5(time() . $file_name) . '.' . $file_extension;
        $dest_path = $upload_dir . $new_file_name;


        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

        if (in_array($file_extension, $allowed_extensions) && in_array($file_type, $allowed_types) && $file_size <= 15 * 1024 * 1024) { // Max 15MB
            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $image_url = 'images/products/' . $new_file_name;
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

    try {
        $conn = getDBConnection();

        $stmt = $conn->prepare("INSERT INTO products (name, category, description, active, price, stock_quantity, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$product_name, $category, $description, 1, $price, $stock_quantity, $image_url]);

        if ($result) {
            $product_id = $conn->lastInsertId();

            $response['success'] = true;
            $response['message'] = $product_name . ' added successfully!';
            $response['product_id'] = $product_id;
            $response['new_stock'] = $stock_quantity;
        } else {
            $response['message'] = 'Failed to insert product into database.';
        }
    } catch (PDOException $e) {
        error_log("Error adding product: " . $e->getMessage());
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Debug logging
error_log("Add Product Response: " . json_encode($response));

echo json_encode($response);
