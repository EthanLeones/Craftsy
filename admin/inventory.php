<?php
$page_title = 'Low Stock Management';
include 'includes/admin_header.php';
require_once '../config/database.php';
$total_products_count = 0;
$low_stock_count = 0;
$out_of_stock_count = 0;
$low_stock_products = [];
$stock_by_category = [];
$error_message = null;
$low_stock_threshold = 10; // Changed from 5 to 10 to show more products 
try {
    $conn = getDBConnection();
    $stmt_total_products = $conn->prepare("SELECT COUNT(*) FROM products");
    $stmt_total_products->execute();
    $total_products_count = $stmt_total_products->fetchColumn();
    $stmt_low_stock = $conn->prepare("SELECT COUNT(*) FROM products WHERE stock_quantity <= ? AND stock_quantity > 0");
    $stmt_low_stock->execute([$low_stock_threshold]);
    $low_stock_count = $stmt_low_stock->fetchColumn();
    $stmt_out_of_stock = $conn->prepare("SELECT COUNT(*) FROM products WHERE stock_quantity = 0");
    $stmt_out_of_stock->execute();
    $out_of_stock_count = $stmt_out_of_stock->fetchColumn();

    // Pagination for low stock products
    $low_stock_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $low_stock_limit = 10;
    $low_stock_offset = ($low_stock_page - 1) * $low_stock_limit;

    // Get total count of low stock products for pagination
    $stmt_total_low_stock = $conn->prepare("SELECT COUNT(*) FROM products WHERE stock_quantity <= ? AND active = 1");
    $stmt_total_low_stock->execute([$low_stock_threshold]);
    $total_low_stock = $stmt_total_low_stock->fetchColumn();
    $total_low_stock_pages = ceil($total_low_stock / $low_stock_limit);

    // Get low stock products with pagination
    $stmt_low_stock_products = $conn->prepare("
        SELECT id, name, stock_quantity, price, image_url 
        FROM products 
        WHERE stock_quantity <= ? AND active = 1 
        ORDER BY stock_quantity ASC 
        LIMIT $low_stock_limit OFFSET $low_stock_offset
    ");
    $stmt_low_stock_products->execute([$low_stock_threshold]);
    $low_stock_products = $stmt_low_stock_products->fetchAll(PDO::FETCH_ASSOC);

    $stmt_all_products = $conn->prepare("
        SELECT p.id, p.name, p.stock_quantity, p.image_url, p.category
        FROM products p
        ORDER BY p.name
    ");
    $stmt_all_products->execute();
    $all_products = $stmt_all_products->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching inventory data: " . $e->getMessage());
    $error_message = "Unable to load inventory data.";
}
?>
<style>
    .highlight-product {
        background-color: #fff3cd !important;
        border: 2px solid #856404 !important;
        transition: all 0.3s ease;
    }

    /* Stock level row styling */
    .out-of-stock-row {
        background-color: rgba(245, 227, 227, 0.6) !important;
        border-left: 4px solid rgba(139, 68, 68, 0.8) !important;
    }

    .low-stock-row {
        background-color: rgba(250, 240, 230, 0.6) !important;
        border-left: 4px solid rgba(165, 90, 0, 0.8) !important;
    }

    .warning-stock-row {
        background-color: rgba(255, 249, 230, 0.6) !important;
        border-left: 4px solid rgba(153, 105, 0, 0.8) !important;
    }

    .out-of-stock-row:hover {
        background-color: rgba(245, 227, 227, 0.8) !important;
    }

    .low-stock-row:hover {
        background-color: rgba(250, 240, 230, 0.8) !important;
    }

    .warning-stock-row:hover {
        background-color: rgba(255, 249, 230, 0.8) !important;
    }

    /* Status badges */
    .status-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-out-of-stock {
        background: rgba(245, 227, 227, 0.9);
        color: #6b1f1f;
        border: 1px solid rgba(139, 68, 68, 0.3);
    }

    .status-critical {
        background: rgba(250, 240, 230, 0.9);
        color: #7a4200;
        border: 1px solid rgba(165, 90, 0, 0.3);
    }

    .status-warning {
        background: rgba(255, 249, 230, 0.9);
        color: #6b4a00;
        border: 1px solid rgba(153, 105, 0, 0.3);
    }

    /* Stock badge updates */
    .stock-badge.out-of-stock {
        background: rgba(245, 227, 227, 0.9);
        color: #6b1f1f;
        border: 1px solid rgba(139, 68, 68, 0.3);
    }

    .stock-badge.low-stock {
        background: rgba(250, 240, 230, 0.9);
        color: #7a4200;
        border: 1px solid rgba(165, 90, 0, 0.3);
    }

    .stock-badge.warning-stock {
        background: rgba(255, 249, 230, 0.9);
        color: #6b4a00;
        border: 1px solid rgba(153, 105, 0, 0.3);
    }

    .stock-badge.good-stock {
        background: rgba(232, 245, 232, 0.9);
        color: #1e3a1e;
        border: 1px solid rgba(45, 90, 45, 0.3);
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        border-radius: 8px;
        width: 80%;
        max-width: 500px;
        position: relative;
    }

    .close-button {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        position: absolute;
        right: 15px;
        top: 10px;
        cursor: pointer;
    }

    .close-button:hover,
    .close-button:focus {
        color: black;
        text-decoration: none;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }

    .form-group input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }

    .form-group input:focus {
        outline: none;
        border-color: #9f86c0;
        box-shadow: 0 0 0 2px rgba(159, 134, 192, 0.2);
    }

    .form-group button {
        margin-right: 10px;
    }

    .button.secondary {
        background-color: #6c757d;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }

    .button.secondary:hover {
        background-color: #5a6268;
    }

    /* Pagination Styles */
    .pagination-container {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #f0f0f0;
    }

    .pagination-link {
        display: inline-block;
        padding: 8px 12px;
        background: #f8f9fa;
        color: #3f1a41;
        text-decoration: none;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
    }

    .pagination-link:hover {
        background: #e9ecef;
        color: #2d1230;
        transform: translateY(-1px);
    }

    .pagination-link.active {
        background: #3f1a41;
        color: white;
        border-color: #3f1a41;
    }
</style>
<div class="admin-wrapper">
    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
    </button>
    <?php include 'includes/admin_sidebar.php'; ?>
    <div class="admin-page-content">
        <h1 class="page-title">Low Stock Management</h1>
        <div class="kpi-cards">
            <div class="kpi-card">
                <div class="card-icon"><i class="fas fa-boxes"></i></div>
                <div class="card-details">
                    <div class="card-value"><?php echo $total_products_count; ?></div>
                    <div class="card-label">Total Products</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="card-icon"><i class="fas fa-warehouse"></i></div>
                <div class="card-details">
                    <div class="card-value"><?php echo $low_stock_count; ?></div>
                    <div class="card-label">Low Stock Items</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="card-icon"><i class="fas fa-truck-loading"></i></div>
                <div class="card-details">
                    <div class="card-value"><?php echo $out_of_stock_count; ?></div>
                    <div class="card-label">Out of Stock</div>
                </div>
            </div>
            <!-- Add more KPI cards here if needed -->
        </div>

        <div class="admin-section">
            <h2><i class="fas fa-exclamation-triangle"></i> Low Stock Products</h2>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Stock Level</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($error_message)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: red;"><?php echo $error_message; ?></td>
                            </tr>
                        <?php elseif (empty($low_stock_products)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">All products are well stocked!</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($low_stock_products as $product): ?>
                                <tr id="product-<?php echo $product['id']; ?>" class="<?php echo ($product['stock_quantity'] == 0) ? 'out-of-stock-row' : (($product['stock_quantity'] <= 5) ? 'low-stock-row' : 'warning-stock-row'); ?>">
                                    <td>
                                        <div class="product-info-cell">
                                            <img src="../<?php echo htmlspecialchars($product['image_url'] ?? 'images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-thumbnail">
                                            <span><?php echo htmlspecialchars($product['name']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="stock-badge <?php
                                                                    if ($product['stock_quantity'] == 0) echo 'out-of-stock';
                                                                    elseif ($product['stock_quantity'] <= 5) echo 'low-stock';
                                                                    else echo 'warning-stock';
                                                                    ?>" id="stock-display-<?php echo $product['id']; ?>">
                                            <?php echo htmlspecialchars($product['stock_quantity']); ?>
                                        </span>
                                    </td>
                                    <td><span class="price-text">P<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></span></td>
                                    <td>
                                        <?php if ($product['stock_quantity'] == 0): ?>
                                            <span class="status-badge status-out-of-stock">Out of Stock</span>
                                        <?php elseif ($product['stock_quantity'] <= 5): ?>
                                            <span class="status-badge status-critical">Critical</span>
                                        <?php else: ?>
                                            <span class="status-badge status-warning">Low Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="button small update-stock-button" data-id="<?php echo htmlspecialchars($product['id']); ?>" data-current-stock="<?php echo htmlspecialchars($product['stock_quantity']); ?>" data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                                            <i class="fas fa-edit"></i> Update Stock
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($total_low_stock_pages > 1): ?>
                <div class="pagination-container">
                    <?php for ($i = 1; $i <= $total_low_stock_pages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="pagination-link <?= ($low_stock_page == $i) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Update Stock Modal -->
<div id="update-stock-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h3>Update Stock Quantity</h3>
        <form id="update-stock-form">
            <input type="hidden" id="update_product_id" name="product_id">
            <div class="form-group">
                <label for="product_name_display">Product:</label>
                <input type="text" id="product_name_display" readonly style="background-color: #f5f5f5;">
            </div>
            <div class="form-group">
                <label for="current_stock_display">Current Stock:</label>
                <input type="text" id="current_stock_display" readonly style="background-color: #f5f5f5;">
            </div>
            <div class="form-group">
                <label for="new_stock_quantity">New Stock Quantity:</label>
                <input type="number" id="new_stock_quantity" name="stock_quantity" min="0" required>
            </div>
            <div class="form-group">
                <button type="submit" class="button primary">Update Stock</button>
                <button type="button" class="button secondary" onclick="closeStockModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<?php
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const updateStockModal = document.getElementById('update-stock-modal');
        const updateStockForm = document.getElementById('update-stock-form');
        const closeButton = document.querySelector('.close-button');

        // Check if we need to scroll to a specific product (from dashboard restock link)
        const hash = window.location.hash;
        if (hash && hash.startsWith('#product-')) {
            const productId = hash.replace('#product-', '');
            const productElement = document.querySelector(`[data-id="${productId}"]`);
            if (productElement) {
                productElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                productElement.classList.add('highlight-product');
                setTimeout(() => {
                    productElement.classList.remove('highlight-product');
                }, 3000);
            }
        }

        // Setup update stock button listeners
        setupUpdateStockButtons();

        // Handle form submission
        updateStockForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const productId = formData.get('product_id');
            const newStock = formData.get('stock_quantity');

            setButtonLoading(submitButton, true);

            fetch('process_update_stock.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');

                        // Update the stock display in the table
                        const stockDisplay = document.getElementById('stock-display-' + productId);
                        if (stockDisplay) {
                            stockDisplay.textContent = newStock;

                            // Update the stock badge class
                            let stockClass = 'stock-badge ';
                            if (newStock == 0) stockClass += 'out-of-stock';
                            else if (newStock <= 5) stockClass += 'low-stock';
                            else if (newStock <= 10) stockClass += 'warning-stock';
                            else stockClass += 'good-stock';

                            stockDisplay.className = stockClass;
                        }

                        // Update the button's data attribute
                        const button = document.querySelector(`[data-id="${productId}"].update-stock-button`);
                        if (button) {
                            button.setAttribute('data-current-stock', newStock);
                        }

                        // Close the modal
                        updateStockModal.style.display = 'none';

                        // Refresh the page after a short delay to update all sections
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showToast(data.message || 'Error updating stock. Please try again.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while updating stock. Please try again.', 'error');
                })
                .finally(() => {
                    setButtonLoading(submitButton, false);
                });
        });

        // Close modal functionality
        closeButton.addEventListener('click', function() {
            updateStockModal.style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target === updateStockModal) {
                updateStockModal.style.display = 'none';
            }
        });
    });

    // Setup update stock buttons
    function setupUpdateStockButtons() {
        document.querySelectorAll('.update-stock-button').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const currentStock = this.getAttribute('data-current-stock');
                const productName = this.getAttribute('data-product-name');

                console.log('Update stock for product ID:', productId);

                // Populate the modal
                document.getElementById('update_product_id').value = productId;
                document.getElementById('product_name_display').value = productName;
                document.getElementById('current_stock_display').value = currentStock;
                document.getElementById('new_stock_quantity').value = currentStock;

                // Show the modal
                document.getElementById('update-stock-modal').style.display = 'block';
                document.getElementById('new_stock_quantity').focus();
            });
        });
    }

    // Helper function for button loading state
    function setButtonLoading(button, isLoading) {
        if (isLoading) {
            button.disabled = true;
            button.textContent = 'Updating...';
            button.style.opacity = '0.7';
        } else {
            button.disabled = false;
            button.textContent = 'Update Stock';
            button.style.opacity = '1';
        }
    }

    // Toast notification function
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            z-index: 9999;
            background: ${type === 'success' ? '#28a745' : '#dc3545'};
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        `;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    function closeStockModal() {
        document.getElementById('update-stock-modal').style.display = 'none';
    }
</script>