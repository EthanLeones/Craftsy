<?php
$page_title = 'Product Management';
include 'includes/admin_header.php';
require_once '../config/database.php';
$products = [];
$categories = [];
$error_message = null;
try {
    $conn = getDBConnection();
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    $sort = $_GET['sort'] ?? '';
    $whereClause = 'active = 1';
    $orderBy = 'created_at DESC';
    switch ($sort) {
        case 'in_stock_true':
            $orderBy = 'stock_quantity DESC';
            break;
        case 'in_stock_false':
            $orderBy = 'stock_quantity ASC';
            break;
        case 'is_active':
            $whereClause = 'active = 0';
            $orderBy = 'id ASC';
            break;
    }
    $stmt_total = $conn->query("SELECT COUNT(*) FROM products WHERE $whereClause");
    $totalResults = $stmt_total->fetchColumn();
    $totalPages = ceil($totalResults / $limit);
    $stmt_products = $conn->prepare("
        SELECT * FROM products 
        WHERE $whereClause
        ORDER BY $orderBy 
        LIMIT :limit OFFSET :offset
    ");
    $stmt_products->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt_products->bindValue(':offset', $offset, PDO::PARAM_INT);
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
    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
    </button>
    <?php include 'includes/admin_sidebar.php'; ?>
    <div class="admin-page-content">
        <h1 class="page-title">Product Management</h1>
        <div class="admin-section">
             <div class="section-header">
                 <h2>All Products</h2>
                 <button class="button primary" id="add-product-button"><i class="fas fa-plus"></i> Add New Product</button>
             </div>
            <div class="filter-section">
                <div class="filter-group">
                    <label for="sort" class="filter-label">Sort by:</label>
                    <select id="sort" name="sort" class="filter-select">
                        <option value="">-- Select --</option>
                        <option value="in_stock_true" <?= ($sort === 'in_stock_true') ? 'selected' : '' ?>>In Stock (High to Low)</option>
                        <option value="in_stock_false" <?= ($sort === 'in_stock_false') ? 'selected' : '' ?>>In Stock (Low to High)</option>
                        <option value="is_active" <?= ($sort === 'is_active') ? 'selected' : '' ?>>Inactive Products</option>
                    </select>
                </div>
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
                                <tr class="table-row <?php echo ($product['stock_quantity'] <= 5) ? 'low-stock-row' : ''; ?>">
                                    <td>
                                        <div class="product-info-cell">
                                            <img src="../<?php echo htmlspecialchars($product['image_url'] ?? 'images/placeholder_admin.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-thumbnail">
                                            <span><?php echo htmlspecialchars($product['name']); ?></span>
                                        </div>
                                    </td>
                                    <td>P<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></td>
                                    <td><span class="stock-badge <?php echo ($product['stock_quantity'] <= 5) ? 'low-stock' : 'in-stock'; ?>"><?php echo htmlspecialchars($product['stock_quantity']); ?></span></td>
                                    <td><span class="category-badge"><?php echo htmlspecialchars($product['category'] ?? 'N/A'); ?></span></td>
                                    <td><?php echo htmlspecialchars($product['updated_at'] ?? $product['created_at']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="button small edit-product-button" data-id="<?php echo htmlspecialchars($product['id']); ?>"><i class="fas fa-edit"></i> Edit</button>
                                            <?php if ($sort === 'is_active'): ?>
                                                <button class="button small success reactivate-product-button" data-id="<?php echo htmlspecialchars($product['id']); ?>"><i class="fas fa-undo"></i> Reactivate</button>
                                            <?php else: ?>
                                                <button class="button small danger delete-product-button" data-id="<?php echo htmlspecialchars($product['id']); ?>"><i class="fas fa-times"></i> Deactivate</button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if ($totalPages > 1): ?>
                    <div class="pagination-container">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?= $i ?>&sort=<?= urlencode($sort) ?>"  class="pagination-link <?= ($page == $i) ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
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
    if(addProductButton) {
        addProductButton.addEventListener('click', function() {
            addProductModal.style.display = 'block';
        });
    }
    if(addProductForm) {
        addProductForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitButton = this.querySelector('button[type="submit"]');
            setButtonLoading(submitButton, true);
            const formData = new FormData(this);
            fetch('process_add_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Product added successfully!', 'success');
                    addProductModal.style.display = 'none';
                    addProductForm.reset();
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(data.message || 'Error adding product. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while adding the product. Please try again.', 'error');
            })
            .finally(() => {
                setButtonLoading(submitButton, false);
            });
        });
    }
    editProductButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            console.log('Edit product with ID:', productId);
            fetch('get_product_details.php?id=' + productId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const product = data.product;
                        document.getElementById('edit_product_id').value = product.id;
                        document.getElementById('edit_product_name').value = product.name;
                        document.getElementById('edit_product_category').value = product.category;
                        document.getElementById('edit_product_description').value = product.description;
                        document.getElementById('edit_product_price').value = product.price;
                        document.getElementById('edit_stock_quantity').value = product.stock_quantity;
                        const currentImageElement = document.querySelector('#current-product-image img');
                        if (product.image_url) {
                            currentImageElement.src = '../' + product.image_url;
                            currentImageElement.style.display = 'block';
                        } else {
                            currentImageElement.style.display = 'none';
                        }
                        editProductModal.style.display = 'block';
                    } else {
                        showToast('Error loading product details.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while loading product details.', 'error');
                });
        });
    });
    const editProductForm = document.getElementById('edit-product-form');
    if (editProductForm) {
        editProductForm.addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"]');
            setButtonLoading(submitButton, true);
        });
    }
    deleteProductButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            console.log('Attempting to delete product with ID:', productId);
            showConfirmDialog('Are you sure you want to deactivate this product?', () => {
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
                        showToast('Product deactivated successfully!', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showToast('Error deactivating product: ' + (data.message || 'Unknown error'), 'error');
                        console.error('Error deleting product:', data.message || 'Unknown error');
                    }
                })
                .catch(error => {
                    console.error('AJAX Error deleting product:', error);
                    showToast('An error occurred while deactivating the product.', 'error');
                });
            });
        });
    });
    document.querySelectorAll('.reactivate-product-button').forEach(button => {
        button.addEventListener('click', function () {
            const productId = this.getAttribute('data-id');
            fetch('process_toggle_product_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&active=1'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Product reactivated successfully!', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast('Error reactivating product: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while reactivating the product.', 'error');
            });
        });
    });
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = button.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });
    window.addEventListener('click', function(event) {
        if (event.target === addProductModal) {
            addProductModal.style.display = 'none';
        }
        if (event.target === editProductModal) {
            editProductModal.style.display = 'none';
        }
    });
    document.getElementById('sort').addEventListener('change', function () {
        const selected = this.value;
        const url = new URL(window.location.href);
        url.searchParams.set('sort', selected);
        url.searchParams.set('page', 1); // Reset to first page on sort
        window.location.href = url.toString();
    });
});
</script>
<style>
    .pagination-container {
    text-align: center;
    margin-top: 20px;
    animation: fadeIn 0.5s ease-in-out;
}
.pagination-link {
    display: inline-block;
    padding: 6px 12px;
    margin: 2px;
    border-radius: 6px;
    background-color: #e5e7eb; /* gray-200 */
    color: #333;
    text-decoration: none;
    transition: background-color 0.3s ease;
}
.pagination-link:hover {
    background-color: #d1d5db; /* gray-300 */
}
.pagination-link.active {
    background-color: #9f86c0; /* purple-300 */
    color: white;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
