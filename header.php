<?php
require_once 'includes/session.php';

$current_page = basename($_SERVER['PHP_SELF']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Craftsy Nook' : 'Craftsy Nook'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }

        .profile-dropdown .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9; 
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            right: 0;
            top: 100%;
            border-radius: 5px;
            overflow: hidden;
        }

        .profile-dropdown .dropdown-content a {
            color: #3f1a41;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
        }

        .profile-dropdown .dropdown-content a:hover {
            background-color: #e0b1cb;
        }

        .profile-dropdown:hover .dropdown-content {
            display: block;
        }

    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <div class="header-left">
                <div class="logo">
                    <a href="index.php">
                        <img src="images/logo.jpg" alt="Craftsy Nook Logo" class="logo-img">
                    </a>
                </div>
                <nav class="nav-links">
                    <a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">HOME</a>
                    <a href="shop.php" class="nav-link <?php echo ($current_page == 'shop.php') ? 'active' : ''; ?>">SHOP</a>
                    <a href="about.php" class="nav-link <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">ABOUT</a>
                    <a href="contact.php" class="nav-link <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">CONTACT</a>
                </nav>
            </div>
            <div class="header-right">
                <div class="header-icons">
                    <?php if (!isLoggedIn()): ?>
                        <div class="auth-links">
                            <a href="login.php" class="auth-link">Login</a>
                            <a href="register.php" class="auth-link register">Register</a>
                        </div>
                    <?php endif; ?>
                    <a href="cart.php" class="icon-link cart-link">
                        <img src="images/cart-icon.png" alt="Cart" class="icon-img">
                        <?php if (isLoggedIn()): ?>
                            <span class="cart-count"><?php echo getCartCount(); ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if (isLoggedIn()): ?>
                        <div class="profile-dropdown">
                            <img src="images/user-icon.png" alt="Profile" class="icon-img profile-icon">
                            <div class="dropdown-content">
                                <a href="profile.php">My Profile</a>
                                <a href="myorders.php">My Orders</a>
                                <a href="logout.php">Logout</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="container main-content">

<script>
// Global Toast Notification Function
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }, 100);
}
</script>

<?php
if (isset($_SESSION['alert'])) {
    $alert_type = $_SESSION['alert']['type'];
    $alert_message = $_SESSION['alert']['message'];
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('" . addslashes($alert_message) . "', '" . ($alert_type === 'success' ? 'success' : 'error') . "');
        });
    </script>";
    unset($_SESSION['alert']);
}
?>