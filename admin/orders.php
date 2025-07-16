<?php
$page_title = 'Order Management';
include 'includes/admin_header.php';
require_once '../config/database.php';
$new_orders_count = 0;
$shipped_orders_count = 0;
$completed_orders_count = 0;
$processing_shipping_orders_count = 0;
$orders = [];
$error_message = null;
try {
    $conn = getDBConnection();

    // Pagination settings
    $limit = 10; // Orders per page
    $pending_page = isset($_GET['pending_page']) && is_numeric($_GET['pending_page']) ? (int)$_GET['pending_page'] : 1;
    $processing_page = isset($_GET['processing_page']) && is_numeric($_GET['processing_page']) ? (int)$_GET['processing_page'] : 1;
    $delivered_page = isset($_GET['delivered_page']) && is_numeric($_GET['delivered_page']) ? (int)$_GET['delivered_page'] : 1;
    $cancelled_page = isset($_GET['cancelled_page']) && is_numeric($_GET['cancelled_page']) ? (int)$_GET['cancelled_page'] : 1;

    // Calculate offsets
    $pending_offset = ($pending_page - 1) * $limit;
    $processing_offset = ($processing_page - 1) * $limit;
    $delivered_offset = ($delivered_page - 1) * $limit;
    $cancelled_offset = ($cancelled_page - 1) * $limit;

    // Get counts for KPI cards
    $stmt_new_orders = $conn->prepare("SELECT COUNT(*) FROM orders WHERE status = 'pending' AND (is_deleted IS NULL OR is_deleted = 0)");
    $stmt_new_orders->execute();
    $new_orders_count = $stmt_new_orders->fetchColumn();

    $stmt_shipped_orders = $conn->prepare("SELECT COUNT(*) FROM orders WHERE status = 'shipped' AND (is_deleted IS NULL OR is_deleted = 0)");
    $stmt_shipped_orders->execute();
    $shipped_orders_count = $stmt_shipped_orders->fetchColumn();

    $stmt_processing_shipping_orders = $conn->prepare("SELECT COUNT(*) FROM orders WHERE status IN ('processing', 'shipping') AND (is_deleted IS NULL OR is_deleted = 0)");
    $stmt_processing_shipping_orders->execute();
    $processing_shipping_orders_count = $stmt_processing_shipping_orders->fetchColumn();

    $stmt_completed_orders = $conn->prepare("SELECT COUNT(*) FROM orders WHERE status = 'delivered' AND (is_deleted IS NULL OR is_deleted = 0)");
    $stmt_completed_orders->execute();
    $completed_orders_count = $stmt_completed_orders->fetchColumn() ?? 0;

    $stmt_cancelled_orders = $conn->prepare("SELECT COUNT(*) FROM orders WHERE status = 'cancelled' AND (is_deleted IS NULL OR is_deleted = 0)");
    $stmt_cancelled_orders->execute();
    $cancelled_orders_count = $stmt_cancelled_orders->fetchColumn() ?? 0;

    // Get pagination totals for each status
    $pending_total_pages = ceil($new_orders_count / $limit);
    $processing_total_pages = ceil($processing_shipping_orders_count / $limit);
    $delivered_total_pages = ceil($completed_orders_count / $limit);
    $cancelled_total_pages = ceil($cancelled_orders_count / $limit);

    // Get paginated orders for each status
    // Pending orders
    $stmt_pending_orders = $conn->prepare("
        SELECT o.*, u.username, COUNT(oi.id) as num_items
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.status = 'pending' AND (o.is_deleted IS NULL OR o.is_deleted = 0)
        GROUP BY o.id
        ORDER BY o.order_date DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt_pending_orders->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt_pending_orders->bindValue(':offset', $pending_offset, PDO::PARAM_INT);
    $stmt_pending_orders->execute();
    $pending_orders = $stmt_pending_orders->fetchAll(PDO::FETCH_ASSOC);

    // Processing & Shipping orders
    $stmt_processing_orders = $conn->prepare("
        SELECT o.*, u.username, COUNT(oi.id) as num_items
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.status IN ('processing', 'shipping') AND (o.is_deleted IS NULL OR o.is_deleted = 0)
        GROUP BY o.id
        ORDER BY o.order_date DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt_processing_orders->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt_processing_orders->bindValue(':offset', $processing_offset, PDO::PARAM_INT);
    $stmt_processing_orders->execute();
    $processing_orders = $stmt_processing_orders->fetchAll(PDO::FETCH_ASSOC);

    // Delivered orders
    $stmt_delivered_orders = $conn->prepare("
        SELECT o.*, u.username, COUNT(oi.id) as num_items
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.status = 'delivered' AND (o.is_deleted IS NULL OR o.is_deleted = 0)
        GROUP BY o.id
        ORDER BY o.order_date DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt_delivered_orders->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt_delivered_orders->bindValue(':offset', $delivered_offset, PDO::PARAM_INT);
    $stmt_delivered_orders->execute();
    $delivered_orders = $stmt_delivered_orders->fetchAll(PDO::FETCH_ASSOC);

    // Cancelled orders
    $stmt_cancelled_orders_list = $conn->prepare("
        SELECT o.*, u.username, COUNT(oi.id) as num_items
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.status = 'cancelled' AND (o.is_deleted IS NULL OR o.is_deleted = 0)
        GROUP BY o.id
        ORDER BY o.order_date DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt_cancelled_orders_list->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt_cancelled_orders_list->bindValue(':offset', $cancelled_offset, PDO::PARAM_INT);
    $stmt_cancelled_orders_list->execute();
    $cancelled_orders_list = $stmt_cancelled_orders_list->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching order data: " . $e->getMessage());
    $error_message = "Unable to load order data.";
}
?>
<div class="admin-wrapper">
    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
    </button>
    <?php include 'includes/admin_sidebar.php'; ?>
    <div class="admin-page-content">
        <h1 class="page-title">Order Management</h1>
        <div class="kpi-cards">
            <div class="kpi-card">
                <div class="card-icon"><i class="fas fa-clock"></i></div>
                <div class="card-details">
                    <div class="card-value"><?php echo $new_orders_count; ?></div>
                    <div class="card-label">Pending Orders</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="card-icon"><i class="fas fa-truck"></i></div>
                <div class="card-details">
                    <div class="card-value"><?php echo $processing_shipping_orders_count; ?></div>
                    <div class="card-label">Processing & Shipping Orders</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="card-icon"><i class="fas fa-check-circle"></i></div>
                <div class="card-details">
                    <div class="card-value"><?php echo $completed_orders_count; ?></div>
                    <div class="card-label">Completed Orders</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="card-icon"><i class="fas fa-times-circle"></i></div>
                <div class="card-details">
                    <div class="card-value"><?php echo $cancelled_orders_count; ?></div>
                    <div class="card-label">Cancelled Orders</div>
                </div>
            </div>
        </div>
        <div class="admin-section">
            <h2><i class="fas fa-clock"></i> Pending Orders</h2>
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
                        <?php elseif (empty($pending_orders)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No pending orders found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pending_orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                    <td><?php echo htmlspecialchars($order['num_items']); ?></td>
                                    <td>P<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                                    <td><span class="status-badge status-<?php echo strtolower($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="button small secondary view-order-button" data-id="<?php echo htmlspecialchars($order['id']); ?>"><i class="fas fa-eye"></i> View</button>
                                            <button class="button small edit-order-button" data-id="<?php echo htmlspecialchars($order['id']); ?>" data-status="<?php echo htmlspecialchars($order['status']); ?>"><i class="fas fa-edit"></i> Edit</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if ($pending_total_pages > 1): ?>
                    <div class="pagination-container">
                        <?php
                        $queryParams = [];
                        if (isset($_GET['processing_page'])) $queryParams['processing_page'] = $_GET['processing_page'];
                        if (isset($_GET['delivered_page'])) $queryParams['delivered_page'] = $_GET['delivered_page'];
                        if (isset($_GET['cancelled_page'])) $queryParams['cancelled_page'] = $_GET['cancelled_page'];
                        ?>
                        <?php for ($i = 1; $i <= $pending_total_pages; $i++): ?>
                            <?php
                            $queryParams['pending_page'] = $i;
                            $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : "?pending_page=$i";
                            ?>
                            <a href="<?= $queryString ?>" class="pagination-link <?= ($pending_page == $i) ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="admin-section">
            <h2><i class="fas fa-truck"></i> Processing & Shipping Orders</h2>
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
                        <?php elseif (empty($processing_orders)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No processing/shipping orders found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($processing_orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                    <td><?php echo htmlspecialchars($order['num_items']); ?></td>
                                    <td>P<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                                    <td><span class="status-badge status-<?php echo strtolower($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="button small secondary view-order-button" data-id="<?php echo htmlspecialchars($order['id']); ?>"><i class="fas fa-eye"></i> View</button>
                                            <button class="button small edit-order-button" data-id="<?php echo htmlspecialchars($order['id']); ?>" data-status="<?php echo htmlspecialchars($order['status']); ?>"><i class="fas fa-edit"></i> Edit</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if ($processing_total_pages > 1): ?>
                    <div class="pagination-container">
                        <?php
                        $queryParams = [];
                        if (isset($_GET['pending_page'])) $queryParams['pending_page'] = $_GET['pending_page'];
                        if (isset($_GET['delivered_page'])) $queryParams['delivered_page'] = $_GET['delivered_page'];
                        if (isset($_GET['cancelled_page'])) $queryParams['cancelled_page'] = $_GET['cancelled_page'];
                        ?>
                        <?php for ($i = 1; $i <= $processing_total_pages; $i++): ?>
                            <?php
                            $queryParams['processing_page'] = $i;
                            $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : "?processing_page=$i";
                            ?>
                            <a href="<?= $queryString ?>" class="pagination-link <?= ($processing_page == $i) ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="admin-section">
            <h2><i class="fas fa-check-circle"></i> Delivered Orders</h2>
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
                        <?php elseif (empty($delivered_orders)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No delivered orders found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($delivered_orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                    <td><?php echo htmlspecialchars($order['num_items']); ?></td>
                                    <td>P<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                                    <td><span class="status-badge status-<?php echo strtolower($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="button small secondary view-order-button" data-id="<?php echo htmlspecialchars($order['id']); ?>"><i class="fas fa-eye"></i> View</button>
                                            <button class="button small edit-order-button" data-id="<?php echo htmlspecialchars($order['id']); ?>" data-status="<?php echo htmlspecialchars($order['status']); ?>"><i class="fas fa-edit"></i> Edit</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if ($delivered_total_pages > 1): ?>
                    <div class="pagination-container">
                        <?php
                        $queryParams = [];
                        if (isset($_GET['pending_page'])) $queryParams['pending_page'] = $_GET['pending_page'];
                        if (isset($_GET['processing_page'])) $queryParams['processing_page'] = $_GET['processing_page'];
                        if (isset($_GET['cancelled_page'])) $queryParams['cancelled_page'] = $_GET['cancelled_page'];
                        ?>
                        <?php for ($i = 1; $i <= $delivered_total_pages; $i++): ?>
                            <?php
                            $queryParams['delivered_page'] = $i;
                            $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : "?delivered_page=$i";
                            ?>
                            <a href="<?= $queryString ?>" class="pagination-link <?= ($delivered_page == $i) ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="admin-section">
            <h2><i class="fas fa-times-circle"></i> Cancelled Orders</h2>
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
                        <?php elseif (empty($cancelled_orders_list)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No cancelled orders found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cancelled_orders_list as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                    <td><?php echo htmlspecialchars($order['num_items']); ?></td>
                                    <td>P<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                                    <td><span class="status-badge status-<?php echo strtolower($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="button small secondary view-order-button" data-id="<?php echo htmlspecialchars($order['id']); ?>"><i class="fas fa-eye"></i> View</button>
                                            <button class="button small edit-order-button" data-id="<?php echo htmlspecialchars($order['id']); ?>" data-status="<?php echo htmlspecialchars($order['status']); ?>"><i class="fas fa-edit"></i> Edit</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if ($cancelled_total_pages > 1): ?>
                    <div class="pagination-container">
                        <?php
                        $queryParams = [];
                        if (isset($_GET['pending_page'])) $queryParams['pending_page'] = $_GET['pending_page'];
                        if (isset($_GET['processing_page'])) $queryParams['processing_page'] = $_GET['processing_page'];
                        if (isset($_GET['delivered_page'])) $queryParams['delivered_page'] = $_GET['delivered_page'];
                        ?>
                        <?php for ($i = 1; $i <= $cancelled_total_pages; $i++): ?>
                            <?php
                            $queryParams['cancelled_page'] = $i;
                            $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : "?cancelled_page=$i";
                            ?>
                            <a href="<?= $queryString ?>" class="pagination-link <?= ($cancelled_page == $i) ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
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

<style>
    /* Pagination Styles */
    .pagination-container {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #f0f0f0;
    }

    .pagination-link {
        display: inline-block;
        padding: 8px 12px;
        background: #f8f9fa;
        color: #3f1a41;
        text-decoration: none;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
    }

    .pagination-link:hover {
        background: #e9ecef;
        color: #2d1230;
        transform: translateY(-1px);
    }

    .pagination-link.active {
        background: #3f1a41;
        color: white;
        border-color: #3f1a41;
    }

    /* Status badge styles */
    .status-badge {
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
        min-width: 70px;
        text-align: center;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-processing {
        background: #cce5ff;
        color: #003d82;
        border: 1px solid #80bdff;
    }

    .status-shipping {
        background: #b8daff;
        color: #004085;
        border: 1px solid #6cb2eb;
    }

    .status-delivered {
        background: #d4edda;
        color: #155724;
        border: 1px solid #a3d977;
    }

    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f1aeb5;
    }

    .action-buttons {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .action-buttons .button.small {
        font-size: 12px;
        padding: 4px 8px;
    }

    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        border-radius: 8px;
        width: 80%;
        max-width: 800px;
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
    }

    .close-button {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        position: absolute;
        right: 15px;
        top: 10px;
        cursor: pointer;
    }

    .close-button:hover,
    .close-button:focus {
        color: black;
        text-decoration: none;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }

    .form-group select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        box-sizing: border-box;
    }

    .form-group select:focus {
        outline: none;
        border-color: #3f1a41;
        box-shadow: 0 0 0 2px rgba(63, 26, 65, 0.1);
    }

    @media (max-width: 768px) {
        .pagination-container {
            flex-wrap: wrap;
            gap: 5px;
        }

        .pagination-link {
            padding: 6px 10px;
            font-size: 0.8rem;
        }

        .action-buttons {
            flex-direction: column;
        }

        .modal-content {
            width: 95%;
            margin: 10% auto;
        }
    }
</style>