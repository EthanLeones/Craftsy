<?php
$page_title = 'Inventory Management';
include 'includes/admin_header.php';
require_once '../config/database.php'; 
$total_products_count = 0;
$low_stock_count = 0;
$out_of_stock_count = 0;
$top_selling_products = [];
$low_stock_products = [];
$stock_by_category = [];
$error_message = null;
$low_stock_threshold = 5; 
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
    $thirty_days_ago = date('Y-m-d H:i:s', strtotime('-30 days'));
    $stmt_top_selling = $conn->prepare("SELECT p.id, p.name, p.stock_quantity, p.price, p.image_url, SUM(oi.quantity) as sold_count, SUM(oi.quantity * oi.price_at_time) as revenue FROM order_items oi JOIN products p ON oi.product_id = p.id JOIN orders o ON oi.order_id = o.id WHERE o.order_date >= ? AND active = 1 GROUP BY p.id ORDER BY sold_count DESC LIMIT 10"); // Limit to top 10
    $stmt_top_selling->execute([$thirty_days_ago]);
    $top_selling_products = $stmt_top_selling->fetchAll(PDO::FETCH_ASSOC);
    $stmt_low_stock_products = $conn->prepare("SELECT id, name, stock_quantity, price, image_url FROM products WHERE stock_quantity <= ? AND stock_quantity > 0  AND active = 1 ORDER BY stock_quantity ASC");
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
</style>
<div class="admin-wrapper">
    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
    </button>
    <?php include 'includes/admin_sidebar.php'; ?>
    <div class="admin-page-content">
        <h1 class="page-title">Inventory Management</h1>
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
            <h2><i class="fas fa-trophy"></i> Top Selling Products (30 Days)</h2>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Stock</th>
                            <th>Sold</th>
                            <th>Revenue</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($error_message)): ?>
                             <tr>
                                 <td colspan="5" style="text-align: center; color: red;"><?php echo $error_message; ?></td>
                             </tr>
                        <?php elseif (empty($top_selling_products)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No top selling products found in the last 30 days.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($top_selling_products as $product): ?>
                                <tr id="product-<?php echo $product['id']; ?>">
                                    <td>
                                        <div class="product-info-cell">
                                            <img src="../<?php echo htmlspecialchars($product['image_url'] ?? 'images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-thumbnail">
                                            <span><?php echo htmlspecialchars($product['name']); ?></span>
                                        </div>
                                    </td>
                                    <td><span class="stock-badge <?php echo ($product['stock_quantity'] <= 5) ? 'low-stock' : 'in-stock'; ?>" id="stock-display-<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['stock_quantity']); ?></span></td>
                                    <td><span class="sales-badge"><?php echo htmlspecialchars($product['sold_count'] ?? 0); ?></span></td>
                                    <td><span class="revenue-text">P<?php echo htmlspecialchars(number_format($product['revenue'] ?? 0, 2)); ?></span></td>
                                    <td>
                                        <button class="button small update-stock-button" data-id="<?php echo htmlspecialchars($product['id']); ?>" data-current-stock="<?php echo htmlspecialchars($product['stock_quantity']); ?>" data-product-name="<?php echo htmlspecialchars($product['name']); ?>"><i class="fas fa-edit"></i> Update Stock</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
     <div class="admin-section">
         <h2><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</h2>
         <div class="admin-table-container">
             <table class="admin-table">
                 <thead>
                     <tr>
                         <th>Product</th>
                         <th>Stock</th>
                         <th>Price</th>
                         <th>Actions</th>
                     </tr>
                 </thead>
                 <tbody>
                      <?php if (isset($error_message)): ?>
                          <tr>
                              <td colspan="4" style="text-align: center; color: red;"><?php echo $error_message; ?></td>
                          </tr>
                      <?php elseif (empty($low_stock_products)): ?>
                          <tr>
                              <td colspan="4" style="text-align: center;">No products are currently low in stock.</td>
                          </tr>
                      <?php else: ?>
                          <?php foreach ($low_stock_products as $product): ?>
                              <tr id="product-<?php echo $product['id']; ?>">
                                  <td>
                                      <div class="product-info-cell">
                                          <img src="../<?php echo htmlspecialchars($product['image_url'] ?? 'images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-thumbnail">
                                          <span><?php echo htmlspecialchars($product['name']); ?></span>
                                      </div>
                                  </td>
                                  <td><span class="stock-badge low-stock" id="stock-display-<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['stock_quantity']); ?></span></td>
                                  <td><span class="price-text">P<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></span></td>
                                  <td>
                                      <button class="button small update-stock-button" data-id="<?php echo htmlspecialchars($product['id']); ?>" data-current-stock="<?php echo htmlspecialchars($product['stock_quantity']); ?>" data-product-name="<?php echo htmlspecialchars($product['name']); ?>"><i class="fas fa-edit"></i> Update Stock</button>
                                  </td>
                              </tr>
                          <?php endforeach; ?>
                      <?php endif; ?>
                 </tbody>
             </table>
         </div>
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
        const updateStockButtons = document.querySelectorAll('.update-stock-button');
        const updateStockModal = document.getElementById('update-stock-modal');
        const updateStockForm = document.getElementById('update-stock-form');
        const closeButton = document.querySelector('.close-button');

        // Check if we need to scroll to a specific product (from dashboard restock link)
        const hash = window.location.hash;
        if (hash && hash.startsWith('#product-')) {
            const productId = hash.replace('#product-', '');
            const productElement = document.querySelector(`[data-id="${productId}"]`);
            if (productElement) {
                productElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                productElement.classList.add('highlight-product');
                setTimeout(() => {
                    productElement.classList.remove('highlight-product');
                }, 3000);
            }
        }

        updateStockButtons.forEach(button => {
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
                updateStockModal.style.display = 'block';
                document.getElementById('new_stock_quantity').focus();
            });
        });

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
                        stockDisplay.className = 'stock-badge ' + (newStock <= 5 ? 'low-stock' : 'in-stock');
                    }
                    
                    // Update the button's data attribute
                    const button = document.querySelector(`[data-id="${productId}"]`);
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

    function closeStockModal() {
        document.getElementById('update-stock-modal').style.display = 'none';
    }
</script>
