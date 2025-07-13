<?php
require_once '../config/database.php';
require_once '../includes/session.php'; 


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
    $stmt_order = $conn->prepare("SELECT o.*, u.username, u.name as customer_name, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt_order->execute([$order_id]);
    $order_details = $stmt_order->fetch(PDO::FETCH_ASSOC);

    if ($order_details) {
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
    <div class="error-state">
        <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
    </div>
<?php elseif ($order_details): ?>
    <div class="order-details-modal">
        <div class="order-header">
            <div class="order-info-grid">
                <div class="info-item">
                    <label>Order #</label>
                    <span class="order-number"><?php echo htmlspecialchars($order_details['id']); ?></span>
                </div>
                <div class="info-item">
                    <label>Status</label>
                    <span class="status-badge status-<?php echo strtolower($order_details['status']); ?>"><?php echo htmlspecialchars($order_details['status']); ?></span>
                </div>
                <div class="info-item">
                    <label>Total Amount</label>
                    <span class="total-amount">P<?php echo htmlspecialchars(number_format($order_details['total_amount'], 2)); ?></span>
                </div>
                <div class="info-item">
                    <label>Order Date</label>
                    <span class="order-date"><?php echo date('M j, Y H:i', strtotime($order_details['order_date'])); ?></span>
                </div>
            </div>
        </div>

        <div class="customer-section">
            <h4><i class="fas fa-user"></i> Customer Information</h4>
            <div class="customer-grid">
                <div class="info-item">
                    <label>Name</label>
                    <span><?php echo htmlspecialchars($order_details['customer_name'] ?? $order_details['username']); ?></span>
                </div>
                <div class="info-item">
                    <label>Email</label>
                    <span><?php echo htmlspecialchars($order_details['email']); ?></span>
                </div>
            </div>
        </div>

        <?php if (!empty($order_details['proof_of_payment_url'])): ?>
            <?php 
                $proof_image_path = __DIR__ . '/../' . $order_details['proof_of_payment_url'];
                if (file_exists($proof_image_path)): 
            ?>
                <div class="payment-section">
                    <h4><i class="fas fa-receipt"></i> Proof of Payment</h4>
                    <div class="payment-proof">
                        <a href="../<?php echo htmlspecialchars($order_details['proof_of_payment_url']); ?>" target="_blank" class="proof-link">
                            <img src="../<?php echo htmlspecialchars($order_details['proof_of_payment_url']); ?>" alt="Proof of Payment" class="proof-image">
                            <div class="proof-overlay">
                                <i class="fas fa-search-plus"></i>
                                <span>View Full Size</span>
                            </div>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i> Proof of payment file not found.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="shipping-section">
            <h4><i class="fas fa-shipping-fast"></i> Shipping Address</h4>
            <?php if ($order_details['shipping_address_line1']): ?>
                <div class="address-card">
                    <div class="address-line"><?php echo htmlspecialchars($order_details['shipping_address_line1']); ?></div>
                    <?php if ($order_details['shipping_address_line2']): ?>
                        <div class="address-line"><?php echo htmlspecialchars($order_details['shipping_address_line2']); ?></div>
                    <?php endif; ?>
                    <div class="address-line">
                        <?php echo htmlspecialchars($order_details['shipping_city']); ?>, 
                        <?php echo htmlspecialchars($order_details['shipping_state_province']); ?> 
                        <?php echo htmlspecialchars($order_details['shipping_postal_code']); ?>
                    </div>
                    <div class="address-line"><?php echo htmlspecialchars($order_details['shipping_country']); ?></div>
                    <div class="contact-line">
                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($order_details['shipping_contact_number']); ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i> Address details not available.
                </div>
            <?php endif; ?>
        </div>

        <div class="items-section">
            <h4><i class="fas fa-shopping-bag"></i> Order Items</h4>
            <?php if (empty($order_items)): ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <p>No items found for this order.</p>
                </div>
            <?php else: ?>
                <div class="items-table">
                    <table class="admin-table">
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
                                    <td class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><span class="quantity-badge"><?php echo htmlspecialchars($item['quantity']); ?></span></td>
                                    <td class="price-text">P<?php echo htmlspecialchars(number_format($item['price_at_time'], 2)); ?></td>
                                    <td class="subtotal-text">P<?php echo htmlspecialchars(number_format($item['price_at_time'] * $item['quantity'], 2)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>
    
    <style>
    .order-details-modal {
        max-height: 70vh;
        overflow-y: auto;
    }
    
    .order-header {
        background: linear-gradient(135deg, #3f1a41, #5a2c64);
        color: white;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }
    
    .order-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .info-item label {
        font-size: 0.8rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .order-number {
        font-size: 1.2rem;
        font-weight: 700;
    }
    
    .total-amount {
        font-size: 1.2rem;
        font-weight: 700;
        color: #ffd700;
    }
    
    .order-date {
        font-size: 0.9rem;
    }
    
    .customer-section, .payment-section, .shipping-section, .items-section {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .customer-section h4, .payment-section h4, .shipping-section h4, .items-section h4 {
        margin: 0 0 1rem 0;
        color: #3f1a41;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .customer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }
    
    .customer-grid .info-item label {
        font-weight: 600;
        color: #666;
        font-size: 0.9rem;
    }
    
    .customer-grid .info-item span {
        color: #333;
        font-size: 0.95rem;
    }
    
    .payment-proof {
        position: relative;
        display: inline-block;
    }
    
    .proof-link {
        display: block;
        position: relative;
        overflow: hidden;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }
    
    .proof-link:hover {
        transform: scale(1.02);
    }
    
    .proof-image {
        max-width: 250px;
        height: auto;
        display: block;
        border-radius: 8px;
    }
    
    .proof-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        color: white;
        text-align: center;
    }
    
    .proof-link:hover .proof-overlay {
        opacity: 1;
    }
    
    .proof-overlay i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    
    .address-card {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 1rem;
    }
    
    .address-line {
        margin-bottom: 0.5rem;
        color: #333;
        font-size: 0.95rem;
    }
    
    .contact-line {
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid #dee2e6;
        color: #3f1a41;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .quantity-badge {
        background: #e3f2fd;
        color: #1976d2;
        padding: 0.3rem 0.6rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
        min-width: 30px;
        text-align: center;
    }
    
    .product-name {
        font-weight: 600;
        color: #333;
    }
    
    .subtotal-text {
        font-weight: 600;
        color: #2e7d32;
    }
    
    .items-table .admin-table {
        margin: 0;
        box-shadow: none;
        border: 1px solid #e9ecef;
    }
    
    @media (max-width: 768px) {
        .order-info-grid {
            grid-template-columns: 1fr;
        }
        
        .customer-grid {
            grid-template-columns: 1fr;
        }
        
        .proof-image {
            max-width: 100%;
        }
    }
    </style>
<?php endif; ?> 