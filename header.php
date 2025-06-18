<?php
require_once 'includes/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Craftsy Nook' : 'Craftsy Nook'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Style for the profile dropdown container */
        .profile-dropdown {
            position: relative;
            display: inline-block; /* Or block, depending on desired layout */
        }

        /* Style for the dropdown content (hidden by default) */
        .profile-dropdown .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9; /* Light background for the dropdown */
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1; /* Ensure it appears above other content */
            right: 0; /* Align dropdown to the right of the icon */
            top: 100%; /* Position below the icon */
            border-radius: 5px;
            overflow: hidden; /* Prevents rounded corners from being cut off */
        }

        /* Style for dropdown links */
        .profile-dropdown .dropdown-content a {
            color: #231942; /* Dark text color */
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
        }

        /* Change color of dropdown links on hover */
        .profile-dropdown .dropdown-content a:hover {
            background-color: #e0b1cb; /* Light pink hover effect */
        }

        /* Show the dropdown menu when the profile-dropdown container is hovered */
        .profile-dropdown:hover .dropdown-content {
            display: block;
        }

        /* Set background image for the body */
        body {
            background-image: url('images/background.jpg');
            background-size: cover; /* Cover the entire viewport */
            background-repeat: no-repeat;
            background-attachment: fixed; /* Keep background fixed while scrolling */
            background-position: center center; /* Center the background image */
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
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="shop.php" class="nav-link">Shop</a>
                    <a href="about.php" class="nav-link">About</a>
                    <a href="contact.php" class="nav-link">Contact</a>
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

<?php
// Check for session alert message and display as JavaScript alert
// if (isset($_SESSION['alert'])) {
//     $alert_type = $_SESSION['alert']['type'];
//     $alert_message = $_SESSION['alert']['message'];
//     echo "<script>alert('" . addslashes($alert_message) . "');</script>";
//     unset($_SESSION['alert']); // Clear the session variable
// }
?> 