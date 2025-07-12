<?php
require_once 'includes/session.php';
require_once 'config/database.php';
requireLogin();

$order = null;

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $user_id = getCurrentUserId();
    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $user_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $stmt_items = $conn->prepare("SELECT oi.*, p.name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
            $stmt_items->execute([$order_id]);
            $order['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
        }

    } catch (PDOException $e) {
        error_log("Error fetching specific order details: " . $e->getMessage());
        $order = null; 
    }
}

$page_title = $order ? 'Order Details #' . $order['id'] : 'Order Not Found';
include 'header.php';

?>

<style>
/* Order Details Page - Ultra Minimalistic & Modern Design */
.order-details-wrapper {
    max-width: 1000px;
    margin: 0 auto;
    padding: 80px 40px;
    background: #ffffff;
    min-height: 80vh;
}

.order-details-title {
    font-size: 2rem;
    color: #000000;
    text-align: center;
    margin-bottom: 80px;
    font-weight: 200;
    letter-spacing: 4px;
    text-transform: uppercase;
}

.order-details-container {
    background-color: transparent;
}

.order-details-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 60px;
    padding-bottom: 30px;
    border-bottom: 1px solid #f0f0f0;
}

.order-details-number {
    font-size: 0.8rem;
    color: #999999;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-weight: 400;
}

.order-details-status {
    font-size: 0.8rem;
    color: #000000;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 400;
    padding: 8px 16px;
    border: 1px solid #000000;
}

.order-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 80px;
    margin-bottom: 80px;
}

.order-info-section {
    background-color: transparent;
}

.order-info-title {
    font-size: 0.8rem;
    color: #999999;
    margin-bottom: 30px;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 2px;
    border-bottom: 1px solid #f5f5f5;
    padding-bottom: 15px;
}

.order-info-content {
    color: #333333;
    font-size: 0.95rem;
    font-weight: 300;
    line-height: 1.8;
}

.order-info-content p {
    margin-bottom: 20px;
}

.order-info-content strong {
    color: #000000;
    font-weight: 400;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: block;
    margin-bottom: 8px;
}

.order-shipping-address {
    margin-top: 15px;
    padding: 20px;
    background-color: #fafafa;
    border: 1px solid #f0f0f0;
    line-height: 1.8;
    color: #333333;
    font-weight: 300;
}

.order-items-section {
    margin-bottom: 60px;
}

.order-items-title {
    font-size: 0.8rem;
    color: #999999;
    margin-bottom: 40px;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 2px;
    text-align: center;
    border-bottom: 1px solid #f5f5f5;
    padding-bottom: 15px;
}

.order-items-header {
    display: grid;
    grid-template-columns: 2fr 120px 150px 150px;
    gap: 30px;
    padding: 20px 0;
    border-bottom: 2px solid #000000;
    margin-bottom: 40px;
}

.order-items-header-item {
    font-size: 0.75rem;
    color: #999999;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-weight: 400;
}

.order-items-header-item.center {
    text-align: center;
}

.order-items-header-item.right {
    text-align: right;
}

.order-items-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.order-item-row {
    display: grid;
    grid-template-columns: 2fr 120px 150px 150px;
    gap: 30px;
    align-items: center;
    padding: 30px 0;
    border-bottom: 1px solid #f8f8f8;
    transition: all 0.4s ease;
}

.order-item-row:hover {
    background-color: #fafafa;
    padding-left: 20px;
    padding-right: 20px;
}

.order-item-row:last-child {
    border-bottom: none;
}

.order-item-product {
    display: flex;
    align-items: center;
    gap: 20px;
}

.order-item-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    flex-shrink: 0;
}

.order-item-name {
    font-size: 1rem;
    color: #000000;
    font-weight: 400;
    line-height: 1.4;
}

.order-item-quantity {
    text-align: center;
    font-size: 1rem;
    color: #333333;
    font-weight: 400;
}

.order-item-price {
    text-align: right;
    font-size: 1rem;
    color: #333333;
    font-weight: 300;
}

.order-item-subtotal {
    text-align: right;
    font-size: 1rem;
    color: #000000;
    font-weight: 400;
}

.order-total-section {
    display: flex;
    justify-content: flex-end;
    padding: 30px 0;
    border-top: 2px solid #000000;
    margin-bottom: 60px;
}

.order-total {
    display: flex;
    align-items: center;
    gap: 40px;
}

.order-total-label {
    font-size: 0.8rem;
    color: #999999;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-weight: 400;
}

.order-total-amount {
    font-size: 1.8rem;
    color: #000000;
    font-weight: 300;
}

.order-back-section {
    text-align: center;
    border-top: 1px solid #f5f5f5;
    padding-top: 60px;
}

.order-back-btn {
    background-color: transparent;
    color: #666666;
    padding: 18px 60px;
    border: 1px solid #e0e0e0;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.4s ease;
    font-weight: 300;
    text-transform: uppercase;
    letter-spacing: 2px;
    border-radius: 0;
    text-decoration: none;
    display: inline-block;
}

.order-back-btn:hover {
    border-color: #000000;
    color: #000000;
    transform: translateY(-2px);
}

.order-not-found {
    text-align: center;
    margin: 80px 0;
}

.order-not-found h1 {
    font-size: 2rem;
    color: #000000;
    margin-bottom: 30px;
    font-weight: 200;
    letter-spacing: 4px;
    text-transform: uppercase;
}

.order-not-found p {
    color: #666666;
    font-size: 1rem;
    font-weight: 300;
    letter-spacing: 1px;
}

@media (max-width: 768px) {
    .order-details-wrapper {
        padding: 60px 20px;
    }
    
    .order-details-title {
        font-size: 1.6rem;
        letter-spacing: 3px;
        margin-bottom: 60px;
    }
    
    .order-details-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .order-info-grid {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .order-items-header {
        display: none;
    }
    
    .order-item-row {
        grid-template-columns: 1fr;
        gap: 20px;
        text-align: left;
    }
    
    .order-item-row:hover {
        padding-left: 0;
        padding-right: 0;
    }
    
    .order-item-product {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .order-item-image {
        width: 100px;
        height: 100px;
    }
    
    .order-item-quantity,
    .order-item-price,
    .order-item-subtotal {
        text-align: left;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .order-item-quantity::before {
        content: 'Quantity: ';
        color: #999999;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .order-item-price::before {
        content: 'Price: ';
        color: #999999;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .order-item-subtotal::before {
        content: 'Subtotal: ';
        color: #999999;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .order-total {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .order-details-wrapper {
        padding: 40px 15px;
    }
    
    .order-details-title {
        font-size: 1.4rem;
        letter-spacing: 2px;
    }
    
    .order-back-btn {
        padding: 15px 40px;
        font-size: 0.8rem;
    }
    
    .order-not-found h1 {
        font-size: 1.6rem;
        letter-spacing: 3px;
    }
}
</style>

<div class="order-details-wrapper">
    <?php if ($order): ?>
        <h1 class="order-details-title">Order Details</h1>

        <div class="order-details-container">
            <div class="order-details-header">
                <div class="order-details-number">Order #<?php echo htmlspecialchars($order['id']); ?></div>
                <div class="order-details-status"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></div>
            </div>

            <div class="order-info-grid">
                <div class="order-info-section">
                    <h3 class="order-info-title">Order Information</h3>
                    <div class="order-info-content">
                        <p>
                            <strong>Order Date</strong>
                            <?php echo htmlspecialchars(date('F j, Y H:i', strtotime($order['created_at']))); ?>
                        </p>
                        <p>
                            <strong>Payment Method</strong>
                            <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $order['payment_method']))); ?>
                        </p>
                        <?php if (!empty($order['contact_number'])): ?>
                            <p>
                                <strong>Contact Number</strong>
                                <?php echo htmlspecialchars($order['contact_number']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="order-info-section">
                    <h3 class="order-info-title">Shipping Information</h3>
                    <div class="order-info-content">
                        <strong>Delivery Address</strong>
                        <?php if ($order['shipping_address_line1']): ?>
                            <div class="order-shipping-address">
                                <?php echo htmlspecialchars($order['shipping_address_line1']); ?><br>
                                <?php if (!empty($order['shipping_address_line2'])): ?>
                                    <?php echo htmlspecialchars($order['shipping_address_line2']); ?><br>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_state_province']); ?> <?php echo htmlspecialchars($order['shipping_postal_code']); ?><br>
                                <?php echo htmlspecialchars($order['shipping_country']); ?><br>
                                Contact: <?php echo htmlspecialchars($order['shipping_contact_number']); ?>
                            </div>
                        <?php else: ?>
                            <p>Address details not available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="order-items-section">
                <h3 class="order-items-title">Items in This Order</h3>
                
                <div class="order-items-header">
                    <div class="order-items-header-item">Product</div>
                    <div class="order-items-header-item center">Quantity</div>
                    <div class="order-items-header-item right">Price</div>
                    <div class="order-items-header-item right">Subtotal</div>
                </div>
                
                <ul class="order-items-list">
                    <?php foreach ($order['items'] as $item): ?>
                        <li class="order-item-row">
                            <div class="order-item-product">
                                <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'images/placeholder.png'); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="order-item-image">
                                <div class="order-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            </div>
                            <div class="order-item-quantity"><?php echo htmlspecialchars($item['quantity']); ?></div>
                            <div class="order-item-price">P<?php echo htmlspecialchars(number_format($item['price_at_time'], 2)); ?></div>
                            <div class="order-item-subtotal">P<?php echo htmlspecialchars(number_format($item['price_at_time'] * $item['quantity'], 2)); ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="order-total-section">
                <div class="order-total">
                    <span class="order-total-label">Total</span>
                    <span class="order-total-amount">P<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></span>
                </div>
            </div>

            <div class="order-back-section">
                <a href="myorders.php" class="order-back-btn">Back to My Orders</a>
            </div>
        </div>

    <?php else: ?>
        <div class="order-not-found">
            <h1>Order Not Found</h1>
            <p>The order you are looking for does not exist or you do not have permission to view it.</p>
            <div style="margin-top: 40px;">
                <a href="myorders.php" class="order-back-btn">Back to My Orders</a>
            </div>
        </div>
    <?php endif; ?>
</div>

</div> <!-- Close container from header.php -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle session alerts with toast notifications
    <?php
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        echo "showToast('" . addslashes($alert['message']) . "', '" . ($alert['type'] === 'success' ? 'success' : 'error') . "');";
    }
    ?>
});
</script>

<?php include 'footer.php'; ?> 