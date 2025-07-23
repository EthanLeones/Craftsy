<?php
$page_title = 'Dashboard';
include 'includes/admin_header.php';
require_once '../config/database.php';
$today_sales = 0;
$new_orders_count = 0;
$inquiries_count = 0;
$pending_orders = [];
$low_stock_products = [];
$top_selling_products = [];
$error_message = null;
$low_stock_threshold = 10;
try {
    $conn = getDBConnection();
    $today_start = date('Y-m-d 00:00:00');
    $today_end = date('Y-m-d 23:59:59');
    $stmt_today_sales = $conn->prepare("
        SELECT SUM(total_amount) 
        FROM orders 
        WHERE order_date BETWEEN ? AND ? 
        AND status != 'cancelled'
    ");
    $stmt_today_sales->execute([$today_start, $today_end]);
    $today_sales = $stmt_today_sales->fetchColumn() ?? 0;
    $stmt_new_orders = $conn->prepare("
        SELECT COUNT(*) 
        FROM orders 
        WHERE status = 'pending' 
        AND order_date BETWEEN ? AND ?
    ");
    $stmt_new_orders->execute([$today_start, $today_end]);
    $new_orders_count = $stmt_new_orders->fetchColumn() ?? 0;
    $stmt_inquiries_count = $conn->prepare("SELECT COUNT(*) FROM inquiry_threads");
    $stmt_inquiries_count->execute();
    $inquiries_count = $stmt_inquiries_count->fetchColumn() ?? 0;
    // Pagination for pending orders
    $pending_orders_page = isset($_GET['pending_page']) && is_numeric($_GET['pending_page']) ? (int)$_GET['pending_page'] : 1;
    $pending_orders_limit = 3;
    $pending_orders_offset = ($pending_orders_page - 1) * $pending_orders_limit;

    // Get total count of pending orders for today
    $stmt_total_pending = $conn->prepare("
        SELECT COUNT(*) 
        FROM orders 
        WHERE status = 'pending' 
        AND order_date BETWEEN ? AND ?
    ");
    $stmt_total_pending->execute([$today_start, $today_end]);
    $total_pending_orders = $stmt_total_pending->fetchColumn();
    $total_pending_pages = ceil($total_pending_orders / $pending_orders_limit);

    $stmt_pending_orders = $conn->prepare("
        SELECT o.id, o.order_date, o.total_amount, o.status, u.username, COUNT(oi.id) AS num_items 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.status = 'pending' 
        AND o.order_date BETWEEN ? AND ? 
        GROUP BY o.id 
        ORDER BY o.order_date DESC 
        LIMIT $pending_orders_limit OFFSET $pending_orders_offset
    ");
    $stmt_pending_orders->execute([$today_start, $today_end]);
    $pending_orders = $stmt_pending_orders->fetchAll(PDO::FETCH_ASSOC);
    $low_stock_page = isset($_GET['low_stock_page']) && is_numeric($_GET['low_stock_page']) ? (int)$_GET['low_stock_page'] : 1;
    $low_stock_limit = 5;
    $low_stock_offset = ($low_stock_page - 1) * $low_stock_limit;
    $stmt_total_low_stock = $conn->prepare("
        SELECT COUNT(*) 
        FROM products 
        WHERE stock_quantity <= ? 
        AND stock_quantity > 0 
        AND active = 1
    ");
    $stmt_total_low_stock->execute([$low_stock_threshold]);
    $total_low_stock = $stmt_total_low_stock->fetchColumn();
    $total_low_stock_pages = ceil($total_low_stock / $low_stock_limit);
    $stmt_low_stock_products = $conn->prepare("
        SELECT id, name, stock_quantity, image_url 
        FROM products 
        WHERE stock_quantity <= ? 
        AND stock_quantity >=    0 
        AND active = 1 
        ORDER BY stock_quantity ASC 
        LIMIT $low_stock_limit OFFSET $low_stock_offset
    ");
    $stmt_low_stock_products->execute([$low_stock_threshold]);
    $low_stock_products = $stmt_low_stock_products->fetchAll(PDO::FETCH_ASSOC);

    // Top selling products (30 days)
    $thirty_days_ago = date('Y-m-d H:i:s', strtotime('-30 days'));
    $stmt_top_selling = $conn->prepare("
        SELECT p.id, p.name, p.stock_quantity, p.price, p.image_url, 
               SUM(oi.quantity) as sold_count, 
               SUM(oi.quantity * oi.price_at_time) as revenue 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        JOIN orders o ON oi.order_id = o.id 
        WHERE o.order_date >= ? AND p.active = 1 AND o.status != 'cancelled'
        GROUP BY p.id 
        ORDER BY sold_count DESC 
        LIMIT 5
    ");
    $stmt_top_selling->execute([$thirty_days_ago]);
    $top_selling_products = $stmt_top_selling->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching dashboard data: " . $e->getMessage());
    $error_message = "Unable to load dashboard data.";
}
?>
<div class="admin-wrapper">
    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
    </button>
    <?php include 'includes/admin_sidebar.php'; ?>
    <div class="admin-page-content">
        <h1 class="page-title">Dashboard</h1>
        <div class="kpi-cards">
            <div class="kpi-card">
                <div class="card-icon"><i class="fas fa-peso-sign"></i></div>
                <div class="card-details">
                    <div class="card-value">P<?php echo number_format($today_sales, 2); ?></div>
                    <div class="card-label">Today's Sales</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="card-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="card-details">
                    <div class="card-value"><?php echo $new_orders_count; ?></div>
                    <div class="card-label">New Orders</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="card-icon"><i class="fas fa-envelope"></i></div>
                <div class="card-details">
                    <div class="card-value"><?php echo $inquiries_count; ?></div>
                    <div class="card-label">Total Inquiries</div>
                </div>
            </div>
        </div>

        <!-- Top Selling Products - Full Width Section -->
        <div class="admin-section">
            <h2><i class="fas fa-trophy"></i> Top Selling Products (30 Days)</h2>
            <?php if (isset($error_message)): ?>
                <div class="empty-state" style="color: #e74c3c;">
                    <?php echo $error_message; ?>
                </div>
            <?php elseif (empty($top_selling_products)): ?>
                <div class="empty-state">
                    No sales data available for the last 30 days.
                </div> <?php else: ?>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Units Sold</th>
                                <th>Revenue</th>
                                <th>Current Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_selling_products as $product): ?>
                                <tr class="top-selling-row">
                                    <td>
                                        <div class="product-info-cell">
                                            <img src="../<?php echo htmlspecialchars($product['image_url'] ?? 'images/placeholder.png'); ?>"
                                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                class="product-thumbnail">
                                            <span><?php echo htmlspecialchars($product['name']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="sold-count-badge"><?php echo htmlspecialchars($product['sold_count']); ?> units</span>
                                    </td>
                                    <td>
                                        <span class="revenue-text">P<?php echo htmlspecialchars(number_format($product['revenue'], 2)); ?></span>
                                    </td>
                                    <td>
                                        <span class="stock-badge <?php
                                                                    if ($product['stock_quantity'] == 0) echo 'out-of-stock';
                                                                    elseif ($product['stock_quantity'] <= 5) echo 'critical-stock';
                                                                    elseif ($product['stock_quantity'] <= 10) echo 'warning-stock';
                                                                    else echo 'good-stock';
                                                                    ?>">
                                            <?php echo htmlspecialchars($product['stock_quantity']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="products.php?edit=<?php echo htmlspecialchars($product['id']); ?>" class="button small">
                                            <i class="fas fa-edit"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-section">
            <h2>Today's Overview</h2>
            <div class="modern-dashboard-grid">
                <div class="dashboard-section">
                    <h3><i class="fas fa-clock"></i> Pending Orders</h3>
                    <div id="pending-orders-content">
                        <?php if (isset($error_message)): ?>
                            <div class="empty-state" style="color: #e74c3c;">
                                <?php echo $error_message; ?>
                            </div>
                        <?php elseif (empty($pending_orders)): ?>
                            <div class="empty-state">
                                No pending orders today.
                            </div>
                        <?php else: ?>
                            <?php foreach ($pending_orders as $order): ?>
                                <div class="order-item-card">
                                    <div class="order-item-header">
                                        <span class="order-id">Order #<?php echo htmlspecialchars($order['id']); ?></span>
                                        <span class="order-amount">P<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></span>
                                    </div>
                                    <div class="order-details">
                                        <strong><?php echo htmlspecialchars($order['username']); ?></strong> •
                                        <?php echo htmlspecialchars($order['num_items'] ?? 0); ?> items •
                                        <?php echo htmlspecialchars(date('g:i A', strtotime($order['order_date']))); ?>
                                    </div>
                                    <a href="orders.php?view_order_id=<?php echo htmlspecialchars($order['id']); ?>" class="button small">View Order</a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php if ($total_pending_pages > 1): ?>
                        <div class="pagination-container">
                            <?php for ($i = 1; $i <= $total_pending_pages; $i++): ?>
                                <a href="?pending_page=<?= $i ?><?= isset($_GET['low_stock_page']) ? '&low_stock_page=' . $_GET['low_stock_page'] : '' ?>"
                                    class="pagination-link <?= ($pending_orders_page == $i) ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="dashboard-section">
                    <h3><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</h3>
                    <div id="low-stock-content">
                        <?php if (isset($error_message)): ?>
                            <div class="empty-state" style="color: #e74c3c;">
                                <?php echo $error_message; ?>
                            </div>
                        <?php elseif (empty($low_stock_products)): ?>
                            <div class="empty-state">
                                All products are well stocked.
                            </div>
                        <?php else: ?>
                            <?php foreach ($low_stock_products as $product): ?>
                                <div class="product-stock-item">
                                    <img src="../<?php echo htmlspecialchars($product['image_url'] ?? 'images/placeholder.png'); ?>"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                                        class="stock-product-image">
                                    <div class="stock-product-info">
                                        <div class="stock-product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                        <div class="stock-quantity"><?php echo htmlspecialchars($product['stock_quantity']); ?> left in stock</div>
                                    </div>
                                    <div class="stock-actions">
                                        <a href="inventory.php#product-<?php echo htmlspecialchars($product['id']); ?>" class="button small">
                                            <i class="fas fa-external-link-alt"></i> Manage
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php if ($total_low_stock_pages > 1): ?>
                        <div class="pagination-container">
                            <?php for ($i = 1; $i <= $total_low_stock_pages; $i++): ?>
                                <a href="?low_stock_page=<?= $i ?><?= isset($_GET['pending_page']) ? '&pending_page=' . $_GET['pending_page'] : '' ?>"
                                    class="pagination-link <?= ($low_stock_page == $i) ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div> <!-- Close admin-page-content -->
</div> <!-- Close admin-wrapper -->
<?php
?>
<style>
    /* Modern Dashboard Enhancements */
    .modern-dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-top: 30px;
    }

    .dashboard-section {
        background: #ffffff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 8px 30px rgba(63, 26, 65, 0.08);
        border: 1px solid #f0f0f0;
    }

    .dashboard-section h3 {
        font-size: 1.1rem;
        color: #3f1a41;
        margin: 0 0 25px 0;
        font-weight: 400;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dashboard-section h3 i {
        font-size: 0.9rem;
        opacity: 0.8;
    }

    .order-item-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 15px;
        border-left: 4px solid #3f1a41;
        transition: all 0.3s ease;
    }

    .order-item-card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(63, 26, 65, 0.1);
    }

    .order-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .order-id {
        font-weight: 600;
        color: #3f1a41;
        font-size: 0.9rem;
    }

    .order-amount {
        font-weight: 600;
        color: #27ae60;
        font-size: 1rem;
    }

    .order-details {
        font-size: 0.8rem;
        color: #666666;
        margin-bottom: 10px;
    }

    .product-stock-item {
        display: flex;
        align-items: center;
        gap: 15px;
        background: rgba(255, 249, 230, 0.6);
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        border-left: 4px solid rgba(153, 105, 0, 0.8);
        transition: all 0.3s ease;
    }

    .product-stock-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(153, 105, 0, 0.1);
        background: rgba(255, 249, 230, 0.8);
    }

    .stock-product-image {
        width: 45px;
        height: 45px;
        object-fit: cover;
        border-radius: 6px;
        flex-shrink: 0;
    }

    .stock-product-info {
        flex: 1;
    }

    .stock-product-name {
        font-weight: 500;
        color: #3f1a41;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    .stock-quantity {
        font-size: 0.8rem;
        color: #6b4a00;
        font-weight: 600;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #666666;
        font-style: italic;
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

    /* Top Selling Products - Table Style */
    .top-selling-row {
        background-color: rgba(232, 245, 232, 0.4) !important;
        border-left: 4px solid rgba(45, 90, 45, 0.6) !important;
    }

    .top-selling-row:hover {
        background-color: rgba(232, 245, 232, 0.6) !important;
    }

    .sold-count-badge {
        background: rgba(232, 245, 232, 0.9);
        color: #1e3a1e;
        border: 1px solid rgba(45, 90, 45, 0.3);
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .revenue-text {
        font-weight: 600;
        color: #1e3a1e;
        font-size: 1rem;
    }

    .stock-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stock-badge.out-of-stock {
        background: rgba(245, 227, 227, 0.9);
        color: #6b1f1f;
        border: 1px solid rgba(139, 68, 68, 0.3);
    }

    .stock-badge.critical-stock {
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

    /* Stock Actions */
    .stock-actions {
        display: flex;
        gap: 8px;
        flex-direction: column;
    }

    .quick-update-btn {
        background: rgba(232, 245, 232, 0.9) !important;
        color: #1e3a1e !important;
        border: 1px solid rgba(45, 90, 45, 0.3) !important;
    }

    .quick-update-btn:hover {
        background: rgba(232, 245, 232, 1) !important;
        border-color: rgba(45, 90, 45, 0.5) !important;
    }


    /* Product thumbnail styles */
    .product-info-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .product-thumbnail {
        width: 45px;
        height: 45px;
        object-fit: cover;
        border-radius: 6px;
        flex-shrink: 0;
    }

    @media (max-width: 1200px) {
        .modern-dashboard-grid {
            grid-template-columns: 1fr;
            gap: 25px;
        }
    }

    @media (max-width: 768px) {
        .modern-dashboard-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .dashboard-section {
            padding: 20px;
        }
    }
</style>

<!-- Quick Stock Update Modal -->
<div id="quick-stock-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-button" onclick="closeQuickStockModal()">&times;</span>
        <h3>Quick Stock Update</h3>
        <form id="quick-stock-form">
            <input type="hidden" id="quick_product_id" name="product_id">
            <div class="form-group">
                <label for="quick_product_name_display">Product:</label>
                <input type="text" id="quick_product_name_display" readonly style="background-color: #f5f5f5;">
            </div>
            <div class="form-group">
                <label for="quick_current_stock_display">Current Stock:</label>
                <input type="text" id="quick_current_stock_display" readonly style="background-color: #f5f5f5;">
            </div>
            <div class="form-group">
                <label for="quick_new_stock_quantity">New Stock Quantity:</label>
                <input type="number" id="quick_new_stock_quantity" name="stock_quantity" min="0" required>
            </div>
            <div class="form-group">
                <button type="submit" class="button primary">Update Stock</button>
                <button type="button" class="button secondary" onclick="closeQuickStockModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function quickUpdateStock(productId, productName, currentStock) {
        document.getElementById('quick_product_id').value = productId;
        document.getElementById('quick_product_name_display').value = productName;
        document.getElementById('quick_current_stock_display').value = currentStock;
        document.getElementById('quick_new_stock_quantity').value = currentStock;
        document.getElementById('quick-stock-modal').style.display = 'block';
        document.getElementById('quick_new_stock_quantity').focus();
    }

    function closeQuickStockModal() {
        document.getElementById('quick-stock-modal').style.display = 'none';
    }

    // Handle quick stock form submission
    document.addEventListener('DOMContentLoaded', function() {
        const quickStockForm = document.getElementById('quick-stock-form');

        if (quickStockForm) {
            quickStockForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');

                submitButton.textContent = 'Updating...';
                submitButton.disabled = true;

                fetch('process_update_stock.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            closeQuickStockModal();
                            // Refresh the page to show updated stock
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('An error occurred while updating stock.', 'error');
                    })
                    .finally(() => {
                        submitButton.textContent = 'Update Stock';
                        submitButton.disabled = false;
                    });
            });
        }
    });

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

    // Modal styling
    const modalStyle = document.createElement('style');
    modalStyle.textContent = `
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
        box-sizing: border-box;
    }

    .form-group input:focus {
        outline: none;
        border-color: #9f86c0;
        box-shadow: 0 0 0 2px rgba(159, 134, 192, 0.2);
    }

    .form-group button {
        margin-right: 10px;
    }    `;
    document.head.appendChild(modalStyle);
</script>