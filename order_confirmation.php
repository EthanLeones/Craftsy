<?php
require_once 'includes/session.php';
require_once 'config/database.php';

requireLogin();

$page_title = 'Order Confirmation';
include 'header.php';

$user_id = getCurrentUserId();
$order_id = $_GET['order_id'] ?? null;
$order = null;
$order_items = [];

if (!$order_id) {
    $_SESSION['alert'] = ['type' => 'danger', 'message' => 'No order specified.'];
    header('Location: index.php'); // Or wherever appropriate
    exit();
}

try {
    $conn = getDBConnection();

    $stmt_order = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt_order->execute([$order_id, $user_id]);
    $order = $stmt_order->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Order not found or you do not have permission to view it.'];
        header('Location: order_history.php'); 
        exit();
    }

    $stmt_items = $conn->prepare("SELECT oi.*, p.name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $stmt_items->execute([$order_id]);
    $order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching order confirmation data: " . $e->getMessage());
    $_SESSION['alert'] = ['type' => 'danger', 'message' => 'An error occurred while loading order details.'];
     header('Location: index.php'); 
     exit();
}

?>

<style>
/* Order Confirmation Page - Ultra Minimalistic & Modern Design */
.order-confirmation-wrapper {
    max-width: 900px;
    margin: 0 auto;
    padding: 80px 40px;
    background: #ffffff;
    min-height: 80vh;
}

.order-confirmation-title {
    font-size: 2rem;
    color: #000000;
    text-align: center;
    margin-bottom: 30px;
    font-weight: 200;
    letter-spacing: 4px;
    text-transform: uppercase;
}

.order-confirmation-success {
    text-align: center;
    margin-bottom: 80px;
    padding: 40px 0;
    border-bottom: 1px solid #f5f5f5;
}

.order-confirmation-success h2 {
    font-size: 1.5rem;
    color: #000000;
    margin-bottom: 20px;
    font-weight: 300;
    letter-spacing: 2px;
}

.order-confirmation-success p {
    color: #666666;
    font-size: 1rem;
    font-weight: 300;
    letter-spacing: 1px;
    margin: 0;
}

.order-confirmation-container {
    background-color: transparent;
}

.order-details-section {
    margin-bottom: 60px;
}

.order-details-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f0f0f0;
}

.order-number {
    font-size: 0.8rem;
    color: #999999;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-weight: 400;
}

.order-status {
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
    gap: 60px;
    margin-bottom: 60px;
}

.order-info-section {
    background-color: transparent;
}

.order-info-title {
    font-size: 0.8rem;
    color: #999999;
    margin-bottom: 25px;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.order-info-content {
    color: #333333;
    font-size: 0.95rem;
    font-weight: 300;
    line-height: 1.8;
}

.order-info-content strong {
    color: #000000;
    font-weight: 400;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: inline-block;
    min-width: 120px;
}

.order-info-content pre {
    margin: 0;
    font-family: inherit;
    white-space: pre-line;
    color: #333333;
    font-size: 0.95rem;
    font-weight: 300;
    line-height: 1.8;
    margin-top: 10px;
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
}

.order-items-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 25px 0;
    border-bottom: 1px solid #f8f8f8;
    transition: all 0.4s ease;
}

.order-item:hover {
    background-color: #fafafa;
    padding-left: 20px;
    padding-right: 20px;
}

.order-item:last-child {
    border-bottom: none;
}

.order-item-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    flex-shrink: 0;
}

.order-item-details {
    flex: 1;
}

.order-item-name {
    font-size: 1rem;
    color: #000000;
    margin-bottom: 8px;
    font-weight: 400;
}

.order-item-quantity {
    font-size: 0.85rem;
    color: #666666;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.order-item-price {
    font-size: 1rem;
    color: #000000;
    font-weight: 400;
    text-align: right;
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

.order-actions-section {
    text-align: center;
    border-top: 1px solid #f5f5f5;
    padding-top: 60px;
}

.order-actions-links {
    margin-bottom: 40px;
}

.order-actions-links a {
    color: #666666;
    text-decoration: underline;
    font-size: 0.9rem;
    font-weight: 300;
    letter-spacing: 0.5px;
    transition: color 0.3s ease;
}

.order-actions-links a:hover {
    color: #000000;
}

.order-continue-btn {
    background-color: #000000;
    color: #ffffff;
    padding: 18px 60px;
    border: none;
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

.order-continue-btn:hover {
    background-color: #333333;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

@media (max-width: 768px) {
    .order-confirmation-wrapper {
        padding: 60px 20px;
    }
    
    .order-confirmation-title {
        font-size: 1.6rem;
        letter-spacing: 3px;
        margin-bottom: 20px;
    }
    
    .order-confirmation-success {
        margin-bottom: 60px;
        padding: 30px 0;
    }
    
    .order-confirmation-success h2 {
        font-size: 1.3rem;
        letter-spacing: 1px;
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
    
    .order-item {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .order-item-image {
        width: 100px;
        height: 100px;
    }
    
    .order-total {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .order-confirmation-wrapper {
        padding: 40px 15px;
    }
    
    .order-confirmation-title {
        font-size: 1.4rem;
        letter-spacing: 2px;
    }
    
    .order-continue-btn {
        padding: 15px 40px;
        font-size: 0.8rem;
    }
}
</style>

<div class="order-confirmation-wrapper">
    <h1 class="order-confirmation-title">Order Confirmation</h1>

    <div class="order-confirmation-success">
        <h2>Thank You For Your Order!</h2>
        <p>Your order has been placed successfully.</p>
    </div>

    <div class="order-confirmation-container">
        <div class="order-details-section">
            <div class="order-details-header">
                <div class="order-number">Order #<?php echo htmlspecialchars($order['id']); ?></div>
                <div class="order-status"><?php echo htmlspecialchars($order['status']); ?></div>
            </div>

            <div class="order-info-grid">
                <div class="order-info-section">
                    <h3 class="order-info-title">Order Information</h3>
                    <div class="order-info-content">
                        <p><strong>Order Date:</strong><br><?php echo htmlspecialchars((new DateTime($order['order_date']))->format('F j, Y H:i')); ?></p>
                        <p><strong>Payment Method:</strong><br><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $order['payment_method']))); ?></p>
                    </div>
                </div>

                <div class="order-info-section">
                    <h3 class="order-info-title">Shipping Address</h3>
                    <div class="order-info-content">
                        <pre><?php echo htmlspecialchars($order['shipping_address_line1']); ?><?php echo !empty($order['shipping_address_line2']) ? "\n" . htmlspecialchars($order['shipping_address_line2']) : ''; ?>
<?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_state_province']); ?> <?php echo htmlspecialchars($order['shipping_postal_code']); ?>
<?php echo htmlspecialchars($order['shipping_country']); ?>
Contact: <?php echo htmlspecialchars($order['shipping_contact_number']); ?></pre>
                    </div>
                </div>
            </div>
        </div>

        <div class="order-items-section">
            <h3 class="order-items-title">Items Ordered</h3>
            <ul class="order-items-list">
                <?php foreach ($order_items as $item): ?>
                    <li class="order-item">
                        <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'images/placeholder.png'); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                             class="order-item-image">
                        <div class="order-item-details">
                            <div class="order-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="order-item-quantity">Qty: <?php echo htmlspecialchars($item['quantity']); ?></div>
                        </div>
                        <div class="order-item-price">
                            P<?php echo htmlspecialchars(number_format($item['price_at_time'] * $item['quantity'], 2)); ?>
                        </div>
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

        <div class="order-actions-section">
            <div class="order-actions-links">
                <a href="order_history.php">View all your orders</a>
            </div>
            <a href="shop.php" class="order-continue-btn">Continue Shopping</a>
        </div>
    </div>
</div>

</div> <!-- Close container from header.php -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle session alerts with toast notifications
    <?php
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        echo "showToast('" . addslashes($alert['message']) . "', '" . ($alert['type'] === 'success' || $alert['type'] === 'danger' ? ($alert['type'] === 'success' ? 'success' : 'error') : 'error') . "');";
    }
    ?>
});
</script>

<?php include 'footer.php'; ?> 