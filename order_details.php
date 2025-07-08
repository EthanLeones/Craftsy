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
    .order-details-container {
        background-color: rgba(255, 255, 255, 0.95); 
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-top: 20px; 
    }

    .order-summary-section, .shipping-details-section, .order-items-section {
        background-color: #fff; 
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        border: 1px solid #eee; 
    }

    .order-summary-section h3, .shipping-details-section h3, .order-items-section h3 {
        color: #333; 
        margin-top: 0;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    .order-details-container p, .order-details-container table {
         color: #555; 
    }

     .order-items-section table.table thead th {
         background-color: #f8f8f8;
     }

</style>

        <?php if ($order): ?>
            <h1 class="page-title">Order Details #<?php echo htmlspecialchars($order['id']); ?></h1>

            <div class="order-details-container">
                <div class="order-summary-section">
                     <h3>Order Summary</h3>
                     <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['created_at']); ?></p>
                     <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($order['status'])); ?></p>
                     <p><strong>Total Amount:</strong> P<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></p>
                </div>

                <div class="shipping-details-section">
                     <h3>Shipping Information</h3>
                     <p><strong>Payment Method:</strong> <?php echo htmlspecialchars(ucfirst($order['payment_method'])); ?></p>
                     <p><strong>Shipping Address:</strong></p>
                     <?php if ($order['shipping_address_line1']): ?>
                          <p>
                              <?php echo htmlspecialchars($order['shipping_address_line1']); ?><br>
                              <?php echo htmlspecialchars($order['shipping_address_line2']); ?><br>
                              <?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_state_province']); ?> <?php echo htmlspecialchars($order['shipping_postal_code']); ?><br>
                              <?php echo htmlspecialchars($order['shipping_country']); ?><br>
                              Contact: <?php echo htmlspecialchars($order['shipping_contact_number']); ?>
                          </p>
                     <?php else: ?>
                          <p>Address details not available.</p>
                     <?php endif; ?>
                     <?php if (!empty($order['contact_number'])): ?>
                         <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($order['contact_number']); ?></p>
                     <?php endif; ?>
                </div>

                <div class="order-items-section">
                    <h3>Items in This Order</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td>
                                         <div class="order-item-details">
                                             <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                             <span><?php echo htmlspecialchars($item['name']); ?></span>
                                         </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td>P<?php echo htmlspecialchars(number_format($item['price_at_time'], 2)); ?></td>
                                    <td>P<?php echo htmlspecialchars(number_format($item['price_at_time'] * $item['quantity'], 2)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                 <div class="back-link">
                     <a href="myorders.php" class="button">Back to My Orders</a>
                 </div>

        <?php else: ?>
            <h1 class="page-title">Order Not Found</h1>
            <p style="text-align: center;">The order you are looking for does not exist or you do not have permission to view it.</p>
        <?php endif; ?>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?> 