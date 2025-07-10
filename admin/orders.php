<?php
$page_title = 'Order Management';
include 'includes/admin_header.php';


require_once '../config/database.php'; 

$new_orders_count = 0;
$shipped_orders_count = 0;
$completed_orders_count = 0;
$orders = [];
$error_message = null;


try {
    $conn = getDBConnection();

    $stmt_new_orders = $conn->prepare("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
    $stmt_new_orders->execute();
    $new_orders_count = $stmt_new_orders->fetchColumn();

    $stmt_shipped_orders = $conn->prepare("SELECT COUNT(*) FROM orders WHERE status = 'shipped'");
    $stmt_shipped_orders->execute();
    $shipped_orders_count = $stmt_shipped_orders->fetchColumn();

    $stmt_completed_orders = $conn->prepare("SELECT COUNT(*) FROM orders WHERE status = 'delivered'"); // Assuming 'delivered' means completed
    $stmt_completed_orders->execute();
    $completed_orders_count = $stmt_completed_orders->fetchColumn() ?? 0;

    $stmt_cancelled_orders = $conn->prepare("SELECT COUNT(*) FROM orders WHERE status = 'cancelled'");
    $stmt_cancelled_orders->execute();
    $cancelled_orders_count = $stmt_cancelled_orders->fetchColumn() ?? 0;

    $stmt_orders = $conn->prepare("
        SELECT o.*, u.username, COUNT(oi.id) as num_items
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN order_items oi ON o.id = oi.order_id
        GROUP BY o.id
        ORDER BY o.order_date DESC
    ");
    $stmt_orders->execute();
    $orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching order data: " . $e->getMessage());
    $error_message = "Unable to load order data.";
}

?>

<div class="admin-wrapper">
    <?php include 'includes/admin_sidebar.php'; ?>

    <div class="admin-page-content">
        <h1 class="page-title">Order Management</h1>

        <div class="kpi-cards">
            <div class="kpi-card">
                <div class="card-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="card-details">
                    <div class="card-value"><?php echo $new_orders_count; ?></div>
                    <div class="card-label">Num of New Orders</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="card-icon"><i class="fas fa-truck"></i></div>
                <div class="card-details">
                    <div class="card-value"><?php echo $shipped_orders_count; ?></div>
                    <div class="card-label">Num of Shipped Orders</div>
                </div>
            </div>

            <div class="kpi-card">
                 <div class="card-icon"><i class="fas fa-check-circle"></i></div>
                 <div class="card-details">
                     <div class="card-value"><?php echo $completed_orders_count; ?></div>
                     <div class="card-label">Num of Completed Orders</div>
                 </div>
             </div>

            <div class="kpi-card">
                <div class="card-icon"><i class="fas fa-times-circle"></i></div>
                <div class="card-details">
                    <div class="card-value"><?php echo $cancelled_orders_count; ?></div>
                    <div class="card-label">Num of Cancelled Orders</div>
                </div>
            </div>  
             <!-- Add more KPI cards here if needed -->
        </div>

        <div class="admin-section">
            <h2>Orders</h2>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th># Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($error_message)): ?>
                             <tr>
                                 <td colspan="7" style="text-align: center; color: red;"><?php echo $error_message; ?></td>
                             </tr>
                        <?php elseif (empty($orders)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No orders found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <?php if ($order['is_deleted']): ?>
                                    <?php continue;  ?>
                                <?php endif; ?>
                                <?php if ($order['status'] === 'delivered'): ?>
                                    <?php continue; ?>
                                <?php endif; ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                    <td><?php echo htmlspecialchars($order['num_items']); ?></td>
                                    <td>P<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                                    <td>
                                        <button class="button small secondary view-order-button" data-id="<?php echo htmlspecialchars($order['id']); ?>">View</button>
                                        <button class="button small edit-order-button" data-id="<?php echo htmlspecialchars($order['id']); ?>" data-status="<?php echo htmlspecialchars($order['status']); ?>">Edit</button>
                                        
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-section">
            <h2>Delivered Orders</h2>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th># Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($error_message)): ?>
                             <tr>
                                 <td colspan="7" style="text-align: center; color: red;"><?php echo $error_message; ?></td>
                             </tr>
                        <?php elseif (empty($orders)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No orders found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <?php if ($order['status'] === 'delivered'): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                    <td><?php echo htmlspecialchars($order['num_items']); ?></td>
                                    <td>P<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                                    <td>
                                        <button class="button small secondary view-order-button" data-id="<?php echo htmlspecialchars($order['id']); ?>">View</button>
                                        <button class="button small edit-order-button" data-id="<?php echo htmlspecialchars($order['id']); ?>" data-status="<?php echo htmlspecialchars($order['status']); ?>">Edit</button>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>


        <div class="admin-section">
            <h2>Cancelled Orders</h2>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th># Items</th>
                            <th>Total</th>
                            <th>Status</th> 
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($error_message)): ?>
                             <tr>
                                 <td colspan="7" style="text-align: center; color: red;"><?php echo $error_message; ?></td>
                             </tr>
                        <?php elseif (empty($orders)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No orders found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <?php if ($order['status'] !== 'cancelled'): ?>
                                    <?php continue; ?>
                                <?php endif; ?>
                                <?php if ($order['is_deleted']): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                    <td><?php echo htmlspecialchars($order['num_items']); ?></td>
                                    <td>P<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                                    <td>
                                        <button class="button small secondary view-order-button" data-id="<?php echo htmlspecialchars($order['id']); ?>">View</button>
                                        <button class="button small edit-order-button" data-id="<?php echo htmlspecialchars($order['id']); ?>" data-status="<?php echo htmlspecialchars($order['status']); ?>">Edit</button>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>


        <div id="view-order-modal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close-button">&times;</span>
                <h3>Order Details</h3>
                <div id="order-details-content">
                    <!-- Order details will be loaded here via AJAX -->
                    <p style="text-align: center;">Loading order details...</p>
                </div>
            </div>
        </div>


        <div id="edit-order-modal" class="modal" style="display:none;">
             <div class="modal-content">
                 <span class="close-button">&times;</span>
                 <h3>Edit Order Status</h3>
                 <form id="edit-order-form" action="process_edit_order.php" method="post">
                     <input type="hidden" id="edit_order_id" name="order_id">
                     <div class="form-group">
                         <label for="edit_order_status">Order Status:</label>
                         <select id="edit_order_status" name="status" required>
                             <option value="pending">Pending</option>
                             <option value="processing">Processing</option>
                             <option value="shipping">Shipping</option>
                             <option value="delivered">Delivered</option>
                             <option value="cancelled">Cancelled</option>
                         </select>
                     </div>
                     <button type="submit" class="button primary">Save Changes</button>
                 </form>
             </div>
         </div>

    </div> <!-- Close admin-page-content -->
</div> <!-- Close admin-wrapper -->

<?php
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewOrderModal = document.getElementById('view-order-modal');
    const editOrderModal = document.getElementById('edit-order-modal');
    const closeButtons = document.querySelectorAll('.modal .close-button');
    const viewOrderButtons = document.querySelectorAll('.view-order-button');
    const editOrderButtons = document.querySelectorAll('.edit-order-button');
    const orderDetailsContent = document.getElementById('order-details-content');
    const editOrderStatusSelect = document.getElementById('edit_order_status');
    const editOrderIdInput = document.getElementById('edit_order_id');
    const editOrderForm = document.getElementById('edit-order-form');
    const editSaveButton = document.querySelector('.button.primary');

    viewOrderButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-id');
            console.log('View order details for ID:', orderId);

            orderDetailsContent.innerHTML = '<p style="text-align: center;">Loading order details for ID: ' + orderId + '...</p>';
            viewOrderModal.style.display = 'block';

            fetch('get_order_details.php?id=' + orderId)
                .then(response => response.text()) 
                .then(html => {
                    orderDetailsContent.innerHTML = html; 
                })
                .catch(error => {
                    console.error('AJAX Error fetching order details:', error);
                    orderDetailsContent.innerHTML = '<p style="text-align: center; color: red;">Error loading order details.</p>';
                });
        });
    });

    editOrderButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-id');
            const currentStatus = this.getAttribute('data-status');
            console.log('Edit order status for ID:', orderId, 'Current status:', currentStatus);

            editOrderIdInput.value = orderId;

            editOrderStatusSelect.value = currentStatus;

            editOrderModal.style.display = 'block';
        });
    });



    editOrderForm.addEventListener('submit', function(event) {
        event.preventDefault(); 

        editSaveButton.disabled = true;

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                 throw new Error('Network response was not ok');
            }
            return response.json(); 
        })
        .then(data => {
            if (data.success) {
                editOrderModal.style.display = 'none';
                window.location.reload();
            } else {
                console.error('Error updating order:', data.message || 'Unknown error');
            }
        })
        .catch(error => {
            console.error('AJAX Error updating order:', error);

        })
         .finally(() => {
            editSaveButton.disabled = false;
         });
    });

    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = button.closest('.modal');
            if (modal) {
                modal.style.display = 'none';

                if (modal.id === 'view-order-modal') {
                    orderDetailsContent.innerHTML = '<p style="text-align: center;">Loading order details...</p>'; // Reset view modal content
                } else if (modal.id === 'edit-order-modal') {
                    document.getElementById('edit-order-form').reset(); 
                }
            }
        });
    });


    window.addEventListener('click', function(event) {
        if (event.target == viewOrderModal) {
            viewOrderModal.style.display = 'none';
            orderDetailsContent.innerHTML = '<p style="text-align: center;">Loading order details...</p>'; // Reset view modal content
        }
         if (event.target == editOrderModal) {
            editOrderModal.style.display = 'none';
            document.getElementById('edit-order-form').reset(); // Reset edit form
        }
    });

});

</script> 