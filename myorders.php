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

        // Get orders with their items and product details
        $stmt = $conn->prepare("
            SELECT DISTINCT o.id, o.order_date, o.total_amount, o.status, o.created_at 
            FROM orders o 
            WHERE o.user_id = ? 
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get order items for each order
        foreach ($orders as &$order) {
            $stmt_items = $conn->prepare("
                SELECT oi.*, p.name, p.image_url, p.price 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ? 
                LIMIT 3
            ");
            $stmt_items->execute([$order['id']]);
            $order['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
            
            // Count total items in order
            $stmt_count = $conn->prepare("SELECT COUNT(*) as total_items FROM order_items WHERE order_id = ?");
            $stmt_count->execute([$order['id']]);
            $order['total_items'] = $stmt_count->fetchColumn();
        }

    } catch (PDOException $e) {
        error_log("Error fetching user orders: " . $e->getMessage());
    }
}

?>

<style>
    body {
        background: #f8f9fa;
        color: #3f1a41;
    }

    .orders-page-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 60px 40px;
        background: white;
        min-height: 70vh;
    }

    .orders-page-title {
        text-align: center;
        font-size: 1.8rem;
        font-weight: 400;
        color: #3f1a41;
        margin-bottom: 50px;
        text-transform: uppercase;
        letter-spacing: 3px;
    }

    .orders-grid {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    .order-card {
        background: #ffffff;
        border: 1px solid #f0f0f0;
        border-radius: 8px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(63, 26, 65, 0.08);
        transition: all 0.3s ease;
    }

    .order-card:hover {
        box-shadow: 0 8px 30px rgba(63, 26, 65, 0.15);
        transform: translateY(-2px);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #f0f0f0;
    }

    .order-info {
        flex: 1;
    }

    .order-id {
        font-size: 1.1rem;
        font-weight: 600;
        color: #3f1a41;
        margin-bottom: 8px;
    }

    .order-date {
        font-size: 0.9rem;
        color: #666666;
        font-weight: 400;
        margin-bottom: 8px;
    }

    .order-total {
        font-size: 1.2rem;
        font-weight: 600;
        color: #3f1a41;
    }

    .order-status {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        align-self: flex-start;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-processing {
        background: #d1ecf1;
        color: #0c5460;
    }

    .status-shipped {
        background: #d4edda;
        color: #155724;
    }

    .status-delivered {
        background: #d4edda;
        color: #155724;
    }

    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
    }

    .order-items {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
    }

    .order-item-preview {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fafafa;
        padding: 12px;
        border-radius: 6px;
        flex: 1;
        min-width: 250px;
    }

    .item-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        flex-shrink: 0;
    }

    .item-details {
        flex: 1;
    }

    .item-name {
        font-size: 0.9rem;
        font-weight: 500;
        color: #3f1a41;
        margin-bottom: 4px;
        line-height: 1.3;
    }

    .item-quantity {
        font-size: 0.8rem;
        color: #666666;
        font-weight: 400;
    }

    .more-items {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #3f1a41;
        color: #ffffff;
        border-radius: 6px;
        padding: 12px;
        font-size: 0.8rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        min-width: 100px;
    }

    .order-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        padding-top: 20px;
        border-top: 1px solid #f0f0f0;
    }

    .order-btn {
        background: #3f1a41;
        color: #ffffff;
        padding: 12px 24px;
        border: none;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .order-btn:hover {
        background: #2d1230;
        color: #ffffff;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(63, 26, 65, 0.3);
    }

    .order-btn-secondary {
        background: transparent;
        color: #3f1a41;
        border: 1px solid #3f1a41;
    }

    .order-btn-secondary:hover {
        background: #3f1a41;
        color: #ffffff;
    }

    .orders-empty-state {
        text-align: center;
        padding: 80px 20px;
        color: #3f1a41;
    }

    .orders-empty-state h2 {
        font-size: 1.5rem;
        font-weight: 400;
        margin-bottom: 20px;
        color: #3f1a41;
    }

    .orders-empty-state p {
        font-size: 1.1rem;
        margin-bottom: 30px;
        color: #666666;
        font-weight: 400;
    }

    .orders-empty-state .order-btn {
        display: inline-block;
        margin-top: 20px;
    }

    @media (max-width: 768px) {
        .orders-page-wrapper {
            padding: 30px 15px;
        }

        .order-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .order-items {
            flex-direction: column;
        }

        .order-item-preview {
            min-width: 100%;
        }

        .order-actions {
            flex-direction: column;
            gap: 10px;
        }

        .order-btn {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="orders-page-wrapper">
    <h1 class="orders-page-title">My Orders</h1>

    <div class="orders-container">
        <?php if (empty($orders)): ?>
            <div class="orders-empty-state">
                <h2>No Orders Yet</h2>
                <p>You haven't placed any orders yet. Start exploring our collection!</p>
                <a href="shop.php" class="order-btn">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="orders-grid">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <div class="order-id">Order #<?php echo htmlspecialchars($order['id']); ?></div>
                                <div class="order-date">
                                    <?php 
                                    $order_date = $order['order_date'] ?? $order['created_at'];
                                    echo htmlspecialchars((new DateTime($order_date))->format('F j, Y \a\t g:i A')); 
                                    ?>
                                </div>
                                <div class="order-total">P<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></div>
                            </div>
                            <div class="order-status status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </div>
                        </div>

                        <?php if (!empty($order['items'])): ?>
                            <div class="order-items">
                                <?php foreach (array_slice($order['items'], 0, 3) as $item): ?>
                                    <div class="order-item-preview">
                                        <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'images/placeholder.png'); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="item-image">
                                        <div class="item-details">
                                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                            <div class="item-quantity">Qty: <?php echo htmlspecialchars($item['quantity']); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if ($order['total_items'] > 3): ?>
                                    <div class="more-items">
                                        +<?php echo ($order['total_items'] - 3); ?> more
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="order-actions">
                            <a href="order_details.php?order_id=<?php echo htmlspecialchars($order['id']); ?>" 
                               class="order-btn order-btn-secondary">View Details</a>
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