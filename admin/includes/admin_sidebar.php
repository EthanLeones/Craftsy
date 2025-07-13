<div class="admin-sidebar">
    <div class="sidebar-header">
        <img src="../images/logo.jpg" alt="Craftsy Nook Logo" style="max-width: 80px; margin-bottom: 20px; border-radius: 8px; opacity: 0.9;">
        <h3>Admin Panel</h3>
    </div>
    
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> Dashboard
        </a></li>
        <li><a href="products.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : ''; ?>">
            <i class="fas fa-shopping-bag"></i> Products
        </a></li>
        <li><a href="orders.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'orders.php') ? 'active' : ''; ?>">
            <i class="fas fa-receipt"></i> Orders
        </a></li>
        <li><a href="inquiries.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'inquiries.php') ? 'active' : ''; ?>">
            <i class="fas fa-comments"></i> Inquiries
        </a></li>
        <li><a href="inventory.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'inventory.php') ? 'active' : ''; ?>">
            <i class="fas fa-boxes"></i> Inventory
        </a></li>
    </ul>
    
    <div class="sidebar-footer">
        <a href="../logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div> 