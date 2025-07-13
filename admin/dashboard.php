<?php
$page_title = 'Dashboard';
include 'includes/admin_header.php';
require_once '../config/database.php';
$today_sales = 0;
$new_orders_count = 0;
$inquiries_count = 0;
$pending_orders = [];
$low_stock_products = [];
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
    $stmt_pending_orders = $conn->prepare("
        SELECT o.id, o.order_date, o.total_amount, o.status, u.username, COUNT(oi.id) AS num_items 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.status = 'pending' 
        AND o.order_date BETWEEN ? AND ? 
        GROUP BY o.id 
        ORDER BY o.order_date DESC 
        LIMIT 10
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
        WHERE stock_quantity <= :threshold 
        AND stock_quantity > 0 
        AND active = 1 
        ORDER BY stock_quantity ASC 
        LIMIT :limit OFFSET :offset
    ");
    $stmt_low_stock_products->bindValue(':threshold', $low_stock_threshold, PDO::PARAM_INT);
    $stmt_low_stock_products->bindValue(':limit', $low_stock_limit, PDO::PARAM_INT);
    $stmt_low_stock_products->bindValue(':offset', $low_stock_offset, PDO::PARAM_INT);
    $stmt_low_stock_products->execute();
    $low_stock_products = $stmt_low_stock_products->fetchAll(PDO::FETCH_ASSOC);
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
        <div class="admin-section">
            <h2>Today's Overview</h2>
            <div class="modern-dashboard-grid">
                <div class="dashboard-section">
                    <h3>Pending Orders</h3>
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
                <div class="dashboard-section">
                    <h3>Low Stock Alert</h3>
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
                                <a href="products.php?edit_product_id=<?php echo htmlspecialchars($product['id']); ?>" class="button small">Restock</a>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($total_low_stock_pages > 1): ?>
                            <div class="pagination-container">
                                <?php for ($i = 1; $i <= $total_low_stock_pages; $i++): ?>
                                    <a href="?low_stock_page=<?= $i ?>" class="pagination-link <?= ($low_stock_page == $i) ? 'active' : '' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
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
    gap: 40px;
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
    font-size: 1.2rem;
    color: #3f1a41;
    margin: 0 0 25px 0;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 2px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
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
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    border-left: 4px solid #e74c3c;
    transition: all 0.3s ease;
}
.product-stock-item:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(231, 76, 60, 0.1);
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
    color: #e74c3c;
    font-weight: 600;
}
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #666666;
    font-style: italic;
}
@media (max-width: 768px) {
    .modern-dashboard-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
}
</style>
