<?php
require_once 'includes/session.php';
require_once 'config/database.php';

requireLogin(); // Ensure the user is logged in

$page_title = 'Order Confirmation';
include 'header.php';

$user_id = getCurrentUserId();
$order_id = $_GET['order_id'] ?? null;
$order = null;
$order_items = [];

if (!$order_id) {
    // Redirect if no order ID is provided
    $_SESSION['alert'] = ['type' => 'danger', 'message' => 'No order specified.'];
    header('Location: index.php'); // Or wherever appropriate
    exit();
}

try {
    $conn = getDBConnection();

    // Fetch order details for the current user
    $stmt_order = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt_order->execute([$order_id, $user_id]);
    $order = $stmt_order->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        // Redirect if order not found or doesn't belong to the user
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Order not found or you do not have permission to view it.'];
        header('Location: order_history.php'); // Redirect to order history
        exit();
    }

    // Fetch order items
    $stmt_items = $conn->prepare("SELECT oi.*, p.name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $stmt_items->execute([$order_id]);
    $order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching order confirmation data: " . $e->getMessage());
    $_SESSION['alert'] = ['type' => 'danger', 'message' => 'An error occurred while loading order details.'];
     header('Location: index.php'); // Or wherever appropriate
     exit();
}

?>

        <h1 class="page-title">Order Confirmation</h1>

        <div class="order-confirmation-container">
            <h2>Thank You For Your Order!</h2>
            <p>Your order has been placed successfully.</p>

            <div class="order-details">
                <h3>Order Summary (Order #<?php echo htmlspecialchars($order['id']); ?>)</h3>
                <p><strong>Order Date:</strong> <?php echo htmlspecialchars((new DateTime($order['order_date']))->format('F j, Y H:i')); ?></p>
                <p><strong>Total Amount:</strong> P<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></p>
                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>

                <h4>Shipping Address:</h4>
                <pre><?php echo htmlspecialchars($order['shipping_address_line1']); ?><?php echo !empty($order['shipping_address_line2']) ? '\n' . htmlspecialchars($order['shipping_address_line2']) : ''; ?>
<?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_state_province']); ?> <?php echo htmlspecialchars($order['shipping_postal_code']); ?>
<?php echo htmlspecialchars($order['shipping_country']); ?>
Contact: <?php echo htmlspecialchars($order['shipping_contact_number']); ?></pre>

                <h4>Items Ordered:</h4>
                <ul class="order-items-list">
                    <?php foreach ($order_items as $item): ?>
                        <li>
                            <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" width="50">
                            <?php echo htmlspecialchars($item['name']); ?> (Qty: <?php echo htmlspecialchars($item['quantity']); ?>) - P<?php echo htmlspecialchars(number_format($item['price_at_time'] * $item['quantity'], 2)); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="order-actions">
                <p><a href="order_history.php">View all your orders</a></p>
                <a href="shop.php" class="button primary">Continue Shopping</a>
            </div>
        </div>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?>

<?php
// Check for session alert message and display as JavaScript alert
// ... existing code ...
?> 