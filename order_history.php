<?php
require_once 'includes/session.php';
require_once 'config/database.php';

requireLogin();

$page_title = 'Order History';
include 'header.php';

$user_id = getCurrentUserId();
$orders = [];

try {
    $conn = getDBConnection();

    $stmt_orders = $conn->prepare("SELECT id, order_date, total_amount, status FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt_orders->execute([$user_id]);
    $orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching order history: " . $e->getMessage());
    $_SESSION['alert'] = ['type' => 'danger', 'message' => 'An error occurred while loading your order history.'];
}

?>

<style>
    .order-history-container {
        max-width: 900px;
        margin: 20px auto;
        padding: 20px;
        background-color: #fff; 
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .order-history-container h1 {
        text-align: center;
        color: #231942; 
        margin-bottom: 20px;
    }

    .order-history-container table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .order-history-container th, .order-history-container td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e0b1cb; 
    }

    .order-history-container th {
        background-color: #5e548e; 
        color: white;
        font-weight: bold;
    }

    .order-history-container tbody tr:nth-child(even) {
        background-color: #f8f4fa; 
    }

    .order-history-container tbody tr:hover {
        background-color: #e0b1cb;
    }

    .order-history-container td a {
        color: #9f86c0; 
        text-decoration: none;
    }

    .order-history-container td a:hover {
        text-decoration: underline;
    }

    .order-history-container .button {
         display: inline-block;
         padding: 10px 20px;
         background-color: #9f86c0;
         color: white;
         text-decoration: none;
         border-radius: 5px;
         transition: background-color 0.3s ease;
         margin-top: 20px;
    }

    .order-history-container .button:hover {
        background-color: #5e548e;
    }


</style>

        <h1 class="page-title">My Order History</h1>

        <div class="order-history-container">
            <?php if (empty($orders)): ?>
                <p style="text-align: center;">You have not placed any orders yet.</p>
                <p style="text-align: center;"><a href="shop.php" class="button primary">Start Shopping</a></p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars((new DateTime($order['order_date']))->format('F j, Y H:i')); ?></td>
                                <td>P<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($order['status']); ?></td>
                                <td><a href="order_confirmation.php?order_id=<?php echo htmlspecialchars($order['id']); ?>">View Details</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?>

<?php

?> 