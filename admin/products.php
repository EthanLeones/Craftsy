<?php
$page_title = 'Product Management';
include 'includes/admin_header.php';

require_once '../config/database.php';

$products = [];
$categories = [];
$error_message = null;

try {
    $conn = getDBConnection();
    $stmt_products = $conn->prepare("
        SELECT p.* 
        FROM products p 
        ORDER BY p.created_at DESC
    ");
    $stmt_products->execute();
    $products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

    $categories = [
        ['id' => 1, 'name' => 'Classic'],
        ['id' => 2, 'name' => 'Pouch'],
        ['id' => 3, 'name' => 'Clutch'],
        ['id' => 4, 'name' => 'Handy'],
        ['id' => 5, 'name' => 'Sling'],
        ['id' => 6, 'name' => 'Carry All'],
        ['id' => 7, 'name' => 'Accessories'],
    ];

} catch (PDOException $e) {
    error_log("Error fetching products or categories: " . $e->getMessage());
    $error_message = "Unable to load data.";
}

?>

<div class="admin-wrapper">
    <?php include 'includes/admin_sidebar.php'; ?>

    <div class="admin-page-content">
        <h1 class="page-title">Product Management</h1>

        <div class="admin-section">
             <div class="section-header">
                 <h2>All Products</h2>
                 <button class="button primary" id="add-product-button">Add New Product</button>
             </div>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Category</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($error_message)): ?>
                             <tr>
                                 <td colspan="6" style="text-align: center; color: red;"><?php echo $error_message; ?></td>
                             </tr>
                        <?php elseif (empty($products)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No products found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr <?php echo ($product['stock_quantity'] <= 5) ? 'class="low-stock"' : ''; ?>>
                                    <td>
                                        <div class="product-info-cell">
                                            <img src="../<?php echo htmlspecialchars($product['image_url'] ?? 'images/placeholder_admin.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-thumbnail">
                                            <span><?php echo htmlspecialchars($product['name']); ?></span>
                                        </div>
                                    </td>
                                    <td>P<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($product['stock_quantity']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($product['updated_at'] ?? $product['created_at']); ?></td>
                                    <td>
                                        <button class="button small edit-product-button" data-id="<?php echo htmlspecialchars($product['id']); ?>">Edit</button>
                                        <button class="button small danger delete-product-button" data-id="<?php echo htmlspecialchars($product['id']); ?>">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Product Modal -->
        <div id="add-product-modal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close-button">&times;</span>
                <h3>Add New Product</h3>
                <form id="add-product-form" action="process_add_product.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="add_product_name">Product Name:</label>
                        <input type="text" id="add_product_name" name="product_name" required>
                    </div>
                    <div class="form-group">
                        <label for="add_product_category">Category:</label>
                        <select id="add_product_category" name="category" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['name']); ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="add_product_description">Description:</label>
                        <textarea id="add_product_description" name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="add_product_price">Price:</label>
                        <input type="number" id="add_product_price" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="add_stock_quantity">Stock Quantity:</label>
                        <input type="number" id="add_stock_quantity" name="stock_quantity" required>
                    </div>
                    <div class="form-group">
                        <label for="add_product_image">Product Image:</label>
                        <input type="file" id="add_product_image" name="product_image" accept="image/*">
                    </div>
                    <button type="submit" class="button primary">Add Product</button>
                </form>
            </div>
        </div>

        <!-- Edit Product Modal -->
        <div id="edit-product-modal" class="modal" style="display:none;">
             <div class="modal-content">
                 <span class="close-button">&times;</span>
                 <h3>Edit Product</h3>
                 <form id="edit-product-form" action="process_edit_product.php" method="post" enctype="multipart/form-data">
                     <input type="hidden" id="edit_product_id" name="product_id">
                     <div class="form-group">
                         <label for="edit_product_name">Product Name:</label>
                         <input type="text" id="edit_product_name" name="product_name" required>
                     </div>
                     <div class="form-group">
                         <label for="edit_product_category">Category:</label>
                         <select id="edit_product_category" name="category" required>
                             <option value="">Select Category</option>
                             <?php foreach ($categories as $category): ?>
                                 <option value="<?php echo htmlspecialchars($category['name']); ?>">
                                     <?php echo htmlspecialchars($category['name']); ?>
                                 </option>
                             <?php endforeach; ?>
                         </select>
                     </div>
                     <div class="form-group">
                         <label for="edit_product_description">Description:</label>
                         <textarea id="edit_product_description" name="description"></textarea>
                     </div>
                     <div class="form-group">
                         <label for="edit_product_price">Price:</label>
                         <input type="number" id="edit_product_price" name="price" step="0.01" required>
                     </div>
                     <div class="form-group">
                         <label for="edit_stock_quantity">Stock Quantity:</label>
                         <input type="number" id="edit_stock_quantity" name="stock_quantity" required>
                     </div>
                      <div class="form-group">
                          <label>Current Image:</label>
                          <div id="current-product-image"><img src="" alt="Current Product Image" style="max-width: 100px; height: auto;"></div>
                      </div>
                     <div class="form-group">
                         <label for="edit_product_image">Change Image:</label>
                         <input type="file" id="edit_product_image" name="product_image" accept="image/*">
                     </div>
                     <button type="submit" class="button primary">Save Changes</button>
                 </form>
             </div>
         </div>


    </div> <!-- Close admin-page-content -->
</div> <!-- Close admin-wrapper -->

<?php
// Add necessary scripts for modals and AJAX
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addProductButton = document.getElementById('add-product-button');
    const addProductModal = document.getElementById('add-product-modal');
    const editProductModal = document.getElementById('edit-product-modal');
    const closeButtons = document.querySelectorAll('.modal .close-button');
    const editProductButtons = document.querySelectorAll('.edit-product-button');
    const deleteProductButtons = document.querySelectorAll('.delete-product-button');
    const addProductForm = document.getElementById('add-product-form');

    // Show Add Product Modal
    if(addProductButton) {
        addProductButton.addEventListener('click', function() {
            addProductModal.style.display = 'block';
        });
    }

    // Handle Add Product Form Submission
    if(addProductForm) {
        addProductForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.textContent;
            submitButton.textContent = 'Adding Product...';
            submitButton.disabled = true;

            // Create FormData object
            const formData = new FormData(this);

            // Send AJAX request
            fetch('process_add_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    alert('Product added successfully!');
                    // Close modal
                    addProductModal.style.display = 'none';
                    // Reset form
                    addProductForm.reset();
                    // Reload page to show new product
                    window.location.reload();
                } else {
                    // Show error message
                    alert(data.message || 'Error adding product. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the product. Please try again.');
            })
            .finally(() => {
                // Reset button state
                submitButton.textContent = originalButtonText;
                submitButton.disabled = false;
            });
        });
    }

    // Show Edit Product Modal and Load Data
    editProductButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            console.log('Edit product with ID:', productId);

            // AJAX request to fetch product data
            fetch('get_product_details.php?id=' + productId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const product = data.product;
                        // Populate the edit form
                        document.getElementById('edit_product_id').value = product.id;
                        document.getElementById('edit_product_name').value = product.name;
                        document.getElementById('edit_product_category').value = product.category;
                        document.getElementById('edit_product_description').value = product.description;
                        document.getElementById('edit_product_price').value = product.price;
                        document.getElementById('edit_stock_quantity').value = product.stock_quantity;

                        // Set current image
                        const currentImageElement = document.querySelector('#current-product-image img');
                        if (product.image_url) {
                            currentImageElement.src = '../' + product.image_url;
                            currentImageElement.style.display = 'block';
                        } else {
                            currentImageElement.style.display = 'none';
                        }

                        // Show the modal
                        editProductModal.style.display = 'block';
                    } else {
                        alert('Error loading product details.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading product details.');
                });
        });
    });

    // Handle Edit Product Form Submission
    const editProductForm = document.getElementById('edit-product-form');
    if (editProductForm) {
        editProductForm.addEventListener('submit', function(e) {
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.textContent = 'Saving Changes...';
            submitButton.disabled = true;
        });
    }

    // Handle Delete Product
    deleteProductButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            console.log('Attempting to delete product with ID:', productId);

            if (confirm('Are you sure you want to delete this product?')) {
                fetch('process_delete_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'product_id=' + productId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Optionally show a success message
                        // alert('Product deleted successfully!'); // Removed alert
                        window.location.reload(); // Reload page to update the list
                    } else {
                        // Optionally show an error message
                        // alert('Error deleting product: ' + (data.message || 'Unknown error')); // Removed alert
                         console.error('Error deleting product:', data.message || 'Unknown error'); // Log error to console
                    }
                })
                .catch(error => {
                    console.error('AJAX Error deleting product:', error);
                    // alert('An error occurred while deleting the product.'); // Removed alert
                });
            }
        });
    });

    // Close Modals
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = button.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === addProductModal) {
            addProductModal.style.display = 'none';
        }
        if (event.target === editProductModal) {
            editProductModal.style.display = 'none';
        }
    });

});
</script>