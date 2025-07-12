<?php
require_once 'includes/session.php';
require_once 'config/database.php';
requireLogin();
$page_title = 'My Orders';
include 'header.php';

$orders = [];
$user_id = getCurrentUserId();

if ($user_id) {
    try {
        $conn = getDBConnection();

        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error fetching user orders: " . $e->getMessage());
    }
}

?>

<style>
/* My Orders Page - Ultra Minimalistic & Modern Design */
.orders-page-wrapper {
    max-width: 1000px;
    margin: 0 auto;
    padding: 80px 40px;
    background: #ffffff;
    min-height: 80vh;
}

.orders-page-title {
    font-size: 2rem;
    color: #000000;
    text-align: center;
    margin-bottom: 80px;
    font-weight: 200;
    letter-spacing: 4px;
    text-transform: uppercase;
}

.orders-container {
    background-color: transparent;
}

.orders-empty-state {
    text-align: center;
    color: #666666;
    font-size: 1rem;
    margin: 80px 0;
    font-weight: 300;
    letter-spacing: 1px;
}

.orders-empty-state a {
    color: #000000;
    text-decoration: underline;
    font-weight: 400;
    margin-left: 5px;
}

.orders-list {
    margin-top: 40px;
}

.order-item {
    border: none;
    border-top: 1px solid #f8f8f8;
    padding: 40px 0;
    margin-bottom: 0;
    background-color: transparent;
    transition: all 0.4s ease;
    display: grid;
    grid-template-columns: 120px 1fr 150px 120px 120px;
    gap: 30px;
    align-items: center;
}

.order-item:hover {
    background-color: #fafafa;
    padding-left: 20px;
    padding-right: 20px;
}

.order-item:first-child {
    border-top: none;
}

.order-item-id {
    font-size: 0.8rem;
    color: #999999;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 400;
}

.order-item-id span {
    display: block;
    color: #000000;
    font-size: 1rem;
    font-weight: 400;
    margin-top: 5px;
}

.order-item-date {
    font-size: 0.85rem;
    color: #333333;
    font-weight: 300;
    line-height: 1.6;
}

.order-item-date .date-label {
    font-size: 0.75rem;
    color: #999999;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: block;
    margin-bottom: 5px;
}

.order-item-amount {
    font-size: 1.1rem;
    color: #000000;
    font-weight: 400;
    text-align: right;
}

.order-item-status {
    text-align: center;
}

.order-status-badge {
    display: inline-block;
    padding: 8px 16px;
    border: 1px solid #e0e0e0;
    font-size: 0.75rem;
    color: #666666;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 400;
    transition: all 0.3s ease;
}

.order-status-badge.pending {
    border-color: #f39c12;
    color: #f39c12;
}

.order-status-badge.processing {
    border-color: #3498db;
    color: #3498db;
}

.order-status-badge.shipped {
    border-color: #9b59b6;
    color: #9b59b6;
}

.order-status-badge.delivered {
    border-color: #27ae60;
    color: #27ae60;
}

.order-status-badge.cancelled {
    border-color: #e74c3c;
    color: #e74c3c;
}

.order-item-details {
    text-align: center;
}

.order-details-link {
    background-color: transparent;
    color: #666666;
    padding: 8px 0;
    border: none;
    border-bottom: 1px solid transparent;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.4s ease;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-decoration: none;
    display: inline-block;
}

.order-details-link:hover {
    color: #000000;
    border-bottom-color: #000000;
}

.orders-header {
    display: grid;
    grid-template-columns: 120px 1fr 150px 120px 120px;
    gap: 30px;
    padding: 20px 0;
    border-bottom: 2px solid #000000;
    margin-bottom: 40px;
}

.orders-header-item {
    font-size: 0.75rem;
    color: #999999;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-weight: 400;
}

.orders-header-item.center {
    text-align: center;
}

.orders-header-item.right {
    text-align: right;
}

@media (max-width: 768px) {
    .orders-page-wrapper {
        padding: 60px 20px;
    }
    
    .orders-page-title {
        font-size: 1.6rem;
        letter-spacing: 3px;
        margin-bottom: 60px;
    }
    
    .orders-header {
        display: none;
    }
    
    .order-item {
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 30px 0;
        text-align: left;
    }
    
    .order-item:hover {
        padding-left: 0;
        padding-right: 0;
    }
    
    .order-item-amount,
    .order-item-status,
    .order-item-details {
        text-align: left;
    }
    
    .order-item-id {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .order-item-id span {
        margin-top: 0;
    }
    
    .order-item-date {
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 15px;
    }
    
    .order-item-amount {
        font-size: 1.3rem;
        padding: 15px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .order-item-status {
        padding: 15px 0;
    }
}

@media (max-width: 480px) {
    .orders-page-wrapper {
        padding: 40px 15px;
    }
    
    .orders-page-title {
        font-size: 1.4rem;
        letter-spacing: 2px;
    }
    
    .order-item {
        padding: 25px 0;
    }
}
</style>

<div class="orders-page-wrapper">
    <h1 class="orders-page-title">My Orders</h1>

    <div class="orders-container">
        <?php if (empty($orders)): ?>
            <p class="orders-empty-state">
                You have no orders yet.
                <a href="shop.php">Start shopping</a>
            </p>
        <?php else: ?>
            <div class="orders-header">
                <div class="orders-header-item">Order ID</div>
                <div class="orders-header-item">Date</div>
                <div class="orders-header-item right">Total Amount</div>
                <div class="orders-header-item center">Status</div>
                <div class="orders-header-item center">Details</div>
            </div>
            
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-item">
                        <div class="order-item-id">
                            Order
                            <span>#<?php echo htmlspecialchars($order['id']); ?></span>
                        </div>
                        
                        <div class="order-item-date">
                            <span class="date-label">Order Date</span>
                            <?php echo htmlspecialchars(date('M j, Y', strtotime($order['created_at']))); ?>
                            <br>
                            <small style="color: #999999; font-size: 0.8rem;">
                                <?php echo htmlspecialchars(date('H:i', strtotime($order['created_at']))); ?>
                            </small>
                        </div>
                        
                        <div class="order-item-amount">
                            P<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?>
                        </div>
                        
                        <div class="order-item-status">
                            <span class="order-status-badge <?php echo strtolower($order['status']); ?>">
                                <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                            </span>
                        </div>
                        
                        <div class="order-item-details">
                            <a href="order_details.php?order_id=<?php echo htmlspecialchars($order['id']); ?>" class="order-details-link">
                                View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
        echo "showToast('" . addslashes($alert['message']) . "', '" . ($alert['type'] === 'success' ? 'success' : 'error') . "');";
    }
    ?>
});
</script>

<?php include 'footer.php'; ?> 