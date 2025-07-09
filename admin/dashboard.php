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
    $stmt_today_sales = $conn->prepare("SELECT SUM(total_amount) FROM orders WHERE order_date BETWEEN ? AND ? AND status != 'cancelled'");
    $stmt_today_sales->execute([$today_start, $today_end]);
    $today_sales = $stmt_today_sales->fetchColumn() ?? 0;

    $stmt_new_orders = $conn->prepare("SELECT COUNT(*) FROM orders WHERE status = 'pending' AND order_date BETWEEN ? AND ?");
    $stmt_new_orders->execute([$today_start, $today_end]);
    $new_orders_count = $stmt_new_orders->fetchColumn() ?? 0;

    $stmt_inquiries_count = $conn->prepare("SELECT COUNT(*) FROM inquiry_threads");
    $stmt_inquiries_count->execute();
    $inquiries_count = $stmt_inquiries_count->fetchColumn() ?? 0;

    $stmt_pending_orders = $conn->prepare("SELECT o.id, o.order_date, o.total_amount, o.status, u.username, COUNT(oi.id) as num_items FROM orders o JOIN users u ON o.user_id = u.id JOIN order_items oi ON o.id = oi.order_id WHERE o.status = 'pending' AND o.order_date BETWEEN ? AND ? GROUP BY o.id ORDER BY o.order_date DESC LIMIT 10");
    $stmt_pending_orders->execute([$today_start, $today_end]);
    $pending_orders = $stmt_pending_orders->fetchAll(PDO::FETCH_ASSOC);

    $stmt_low_stock_products = $conn->prepare("SELECT id, name, stock_quantity, image_url FROM products WHERE stock_quantity <= ? AND stock_quantity > 0 AND active = 1 ORDER BY stock_quantity ASC");
    $stmt_low_stock_products->execute([$low_stock_threshold]);
    $low_stock_products = $stmt_low_stock_products->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching dashboard data: " . $e->getMessage());
    $error_message = "Unable to load dashboard data.";
}

?>

<div class="admin-wrapper">
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
            <h2>Pending Orders</h2>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Username</th>
                            <th># Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($error_message)): ?>
                             <tr>
                                 <td colspan="6" style="text-align: center; color: red;"><?php echo $error_message; ?></td>
                             </tr>
                        <?php elseif (empty($pending_orders)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No pending orders.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pending_orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['num_items'] ?? 0); ?></td>
                                    <td>P<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                                    <td>
                                        <a href="orders.php?view_order_id=<?php echo htmlspecialchars($order['id']); ?>" class="button small secondary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-section">
            <h2>Low Stock Products</h2>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Stock #</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                         <?php if (isset($error_message)): ?>
                              <tr>
                                  <td colspan="3" style="text-align: center; color: red;"><?php echo $error_message; ?></td>
                              </tr>
                         <?php elseif (empty($low_stock_products)): ?>
                             <tr>
                                 <td colspan="3" style="text-align: center;">No products are low in stock.</td>
                             </tr>
                         <?php else: ?>
                             <?php foreach ($low_stock_products as $product): ?>
                                 <tr>
                                     <td>
                                         <div class="product-info-cell">
                                             <img src="../<?php echo htmlspecialchars($product['image_url'] ?? 'images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-thumbnail">
                                             <span><?php echo htmlspecialchars($product['name']); ?></span>
                                         </div>
                                     </td>
                                     <td><?php echo htmlspecialchars($product['stock_quantity']); ?></td>
                                     <td>
                                         <a href="products.php?edit_product_id=<?php echo htmlspecialchars($product['id']); ?>" class="button small">Update Stock</a>
                                     </td>
                                 </tr>
                             <?php endforeach; ?>
                         <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div> <!-- Close admin-page-content -->
</div> <!-- Close admin-wrapper -->

<?php
// Add any necessary scripts here
?>