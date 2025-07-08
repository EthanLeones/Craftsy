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

        <h1 class="page-title">My Orders</h1>

        <div class="orders-container">
            <?php if (empty($orders)): ?>
                <p style="text-align: center;">You have no orders yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                                <td>P<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($order['status'])); ?></td>
                                <td><a href="order_details.php?order_id=<?php echo htmlspecialchars($order['id']); ?>">View Details</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?> 