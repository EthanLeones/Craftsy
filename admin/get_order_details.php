<?php
require_once '../config/database.php';
require_once '../includes/session.php'; // Assuming session is used for admin login

// Admin authentication check
// requireAdminLogin(); // Implement this function
// if (!isAdmin()) {
//     echo '<p style="color: red;">Unauthorized access.</p>';
//     exit();
// }

$order_id = $_GET['id'] ?? null;

if ($order_id === null || !is_numeric($order_id)) {
    echo '<p style="color: red;">Invalid order ID.</p>';
    exit();
}

$order_id = (int)$order_id;
$conn = getDBConnection();

$order_details = null;
$order_items = [];
$error_message = null;

try {
    // Fetch order details, including user email
    $stmt_order = $conn->prepare("SELECT o.*, u.username, u.name as customer_name, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt_order->execute([$order_id]);
    $order_details = $stmt_order->fetch(PDO::FETCH_ASSOC);

    if ($order_details) {
        // Fetch order items
        $stmt_items = $conn->prepare("SELECT oi.*, p.name as product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $stmt_items->execute([$order_id]);
        $order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

    } else {
        $error_message = "Order not found.";
    }

} catch (PDOException $e) {
    error_log("Error fetching order details for view: " . $e->getMessage());
    $error_message = "Database error fetching order details.";
}

?>

<?php if (isset($error_message)): ?>
    <p style="color: red;"><?php echo $error_message; ?></p>
<?php elseif ($order_details): ?>
    <div class="order-details-content">
        <p><strong>Order #:</strong> <?php echo htmlspecialchars($order_details['id']); ?></p>
        <p><strong>Customer:</strong> <?php echo htmlspecialchars($order_details['customer_name'] ?? $order_details['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($order_details['email']); ?></p>
        <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order_details['order_date']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($order_details['status']); ?></p>
        <p><strong>Total Amount:</strong> P<?php echo htmlspecialchars(number_format($order_details['total_amount'], 2)); ?></p>

        <?php if (!empty($order_details['proof_of_payment_url'])): ?>
            <?php 
                $proof_image_path = __DIR__ . '/../' . $order_details['proof_of_payment_url'];
                if (file_exists($proof_image_path)): 
            ?>
                <h4>Proof of Payment:</h4>
                <div>
                    <a href="../<?php echo htmlspecialchars($order_details['proof_of_payment_url']); ?>" target="_blank">
                        <img src="../<?php echo htmlspecialchars($order_details['proof_of_payment_url']); ?>" alt="Proof of Payment" style="max-width: 200px; height: auto; border: 1px solid #ccc; border-radius: 4px;">
                    </a>
                </div>
            <?php else: ?>
                 <p>Proof of payment file not found.</p>
            <?php endif; ?>
        <?php endif; ?>

        <h4>Shipping Address:</h4>
        <?php if ($order_details['shipping_address_line1']): ?>
             <p>
                 <?php echo htmlspecialchars($order_details['shipping_address_line1']); ?><br>
                 <?php echo htmlspecialchars($order_details['shipping_address_line2']); ?><br>
                 <?php echo htmlspecialchars($order_details['shipping_city']); ?>, <?php echo htmlspecialchars($order_details['shipping_state_province']); ?> <?php echo htmlspecialchars($order_details['shipping_postal_code']); ?><br>
                 <?php echo htmlspecialchars($order_details['shipping_country']); ?><br>
                 Contact: <?php echo htmlspecialchars($order_details['shipping_contact_number']); ?>
             </p>
        <?php else: ?>
             <p>Address details not available.</p>
        <?php endif; ?>

        <h4>Order Items:</h4>
        <?php if (empty($order_items)): ?>
            <p>No items found for this order.</p>
        <?php else: ?>
            <table class="admin-table small-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>P<?php echo htmlspecialchars(number_format($item['price_at_time'], 2)); ?></td>
                            <td>P<?php echo htmlspecialchars(number_format($item['price_at_time'] * $item['quantity'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Add more details as needed -->

    </div>
<?php endif; ?> 