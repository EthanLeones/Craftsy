<?php
require_once '../includes/session.php';

// Admin authentication check
// requireAdminLogin(); // Assuming a function to check if admin is logged in
// if (!isAdmin()) { // Assuming an isAdmin() function
//     header('Location: ../login.php'); // Redirect non-admins
//     exit();
// }

// Basic admin check based on session variable set in login_process.php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Redirect to login page if not logged in as admin
    header('Location: ../login.php?error=unauthorized_admin');
    exit();
}

// You can optionally fetch admin user details here if needed for display
// $admin_user = getCurrentUser(); // Assuming getCurrentUser() fetches user details

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Dashboard'; ?> | Craftsy Nook Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="admin-page">
    <?php
    // Basic admin authentication check should be placed here or before including this header
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        // Redirect to login page if not logged in as admin
        header('Location: ../login.php?error=unauthorized_admin');
        exit();
    }
    ?>

    <div class="admin-wrapper">
        <!-- Admin content will be included by individual pages -->

<?php
// Check for session alert message and display as JavaScript alert
if (isset($_SESSION['alert'])) {
    $alert_type = $_SESSION['alert']['type'];
    $alert_message = $_SESSION['alert']['message'];
    echo "<script>if (typeof showToast === 'function') { showToast('" . addslashes($alert_message) . "', '" . ($alert_type === 'success' ? 'success' : 'error') . "'); }</script>";
    unset($_SESSION['alert']); // Clear the session variable
}
?>
<script>
    // Enhanced Admin Notification System
    function showToast(message, type = 'success', duration = 4000) {
        // Remove existing toasts
        const existingToasts = document.querySelectorAll('.admin-toast');
        existingToasts.forEach(toast => toast.remove());
        
        // Create new toast
        const toast = document.createElement('div');
        toast.className = `admin-toast toast-${type}`;
        
        const icon = type === 'success' ? 'fas fa-check-circle' : 
                     type === 'error' ? 'fas fa-exclamation-circle' : 
                     type === 'warning' ? 'fas fa-exclamation-triangle' : 
                     'fas fa-info-circle';
        
        toast.innerHTML = `
            <div class="toast-content">
                <i class="${icon}"></i>
                <span class="toast-message">${message}</span>
                <button class="toast-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Auto remove
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    // Enhanced loading state for buttons
    function setButtonLoading(button, loading = true) {
        if (loading) {
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            button.disabled = true;
            button.classList.add('loading');
        } else {
            button.innerHTML = button.dataset.originalText || button.innerHTML;
            button.disabled = false;
            button.classList.remove('loading');
        }
    }

    // Enhanced modal management
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            document.body.classList.add('modal-open');
            modal.classList.add('fade-in');
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('fade-out');
            setTimeout(() => {
                modal.style.display = 'none';
                modal.classList.remove('fade-in', 'fade-out');
                document.body.classList.remove('modal-open');
            }, 300);
        }
    }

    // Confirmation dialog
    function showConfirmDialog(message, onConfirm, onCancel = null) {
        const dialog = document.createElement('div');
        dialog.className = 'confirm-dialog-overlay';
        dialog.innerHTML = `
            <div class="confirm-dialog">
                <div class="confirm-header">
                    <i class="fas fa-question-circle"></i>
                    <h3>Confirm Action</h3>
                </div>
                <div class="confirm-body">
                    <p>${message}</p>
                </div>
                <div class="confirm-actions">
                    <button class="button secondary cancel-btn">Cancel</button>
                    <button class="button danger confirm-btn">Confirm</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(dialog);
        document.body.classList.add('modal-open');
        
        const confirmBtn = dialog.querySelector('.confirm-btn');
        const cancelBtn = dialog.querySelector('.cancel-btn');
        
        confirmBtn.onclick = () => {
            document.body.removeChild(dialog);
            document.body.classList.remove('modal-open');
            if (onConfirm) onConfirm();
        };
        
        cancelBtn.onclick = () => {
            document.body.removeChild(dialog);
            document.body.classList.remove('modal-open');
            if (onCancel) onCancel();
        };
        
        // Close on background click
        dialog.onclick = (e) => {
            if (e.target === dialog) {
                cancelBtn.click();
            }
        };
        
        setTimeout(() => dialog.classList.add('show'), 10);
    }

    // Auto-scroll to new content
    function scrollToElement(element, offset = 0) {
        const elementPosition = element.offsetTop - offset;
        window.scrollTo({
            top: elementPosition,
            behavior: 'smooth'
        });
    }

    // Mobile menu toggle functionality
    function toggleMobileMenu() {
        const sidebar = document.querySelector('.admin-sidebar');
        const overlay = document.querySelector('.mobile-menu-overlay');
        
        if (sidebar) {
            sidebar.classList.toggle('open');
            
            // Create overlay if it doesn't exist
            if (!overlay && sidebar.classList.contains('open')) {
                const newOverlay = document.createElement('div');
                newOverlay.className = 'mobile-menu-overlay';
                newOverlay.onclick = () => toggleMobileMenu();
                document.body.appendChild(newOverlay);
            } else if (overlay && !sidebar.classList.contains('open')) {
                overlay.remove();
            }
        }
    }

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.admin-sidebar');
        const toggle = document.querySelector('.mobile-menu-toggle');
        
        if (sidebar && sidebar.classList.contains('open') && 
            !sidebar.contains(event.target) && 
            !toggle.contains(event.target)) {
            toggleMobileMenu();
        }
    });
</script>
</body>
</html>