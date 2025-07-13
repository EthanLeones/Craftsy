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
                                <tr>
                                    <td>
                                        <div class="product-info-cell">
                                            <img src="../<?php echo htmlspecialchars($product['image_url'] ?? 'images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-thumbnail">
                                            <span><?php echo htmlspecialchars($product['name']); ?></span>
                                        </div>
                                    </td>
                                    <td><span class="stock-badge <?php echo ($product['stock_quantity'] <= 5) ? 'low-stock' : 'in-stock'; ?>"><?php echo htmlspecialchars($product['stock_quantity']); ?></span></td>
                                    <td><span class="sales-badge"><?php echo htmlspecialchars($product['sold_count'] ?? 0); ?></span></td>
                                    <td><span class="revenue-text">P<?php echo htmlspecialchars(number_format($product['revenue'] ?? 0, 2)); ?></span></td>
                                    <td>
                                        <button class="button small update-stock-button" data-id="<?php echo htmlspecialchars($product['id']); ?>"><i class="fas fa-edit"></i> Update Stock</button>
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
                              <tr>
                                  <td>
                                      <div class="product-info-cell">
                                          <img src="../<?php echo htmlspecialchars($product['image_url'] ?? 'images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-thumbnail">
                                          <span><?php echo htmlspecialchars($product['name']); ?></span>
                                      </div>
                                  </td>
                                  <td><span class="stock-badge low-stock"><?php echo htmlspecialchars($product['stock_quantity']); ?></span></td>
                                  <td><span class="price-text">P<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></span></td>
                                  <td>
                                      <button class="button small update-stock-button" data-id="<?php echo htmlspecialchars($product['id']); ?>"><i class="fas fa-edit"></i> Update Stock</button>
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
<?php
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const updateStockButtons = document.querySelectorAll('.update-stock-button');
        updateStockButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                console.log('Update stock for product ID:', productId);
                 window.location.href = 'process_edit_product.php?id=' + productId;
            });
        });
    });
</script> 
