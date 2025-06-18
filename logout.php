<?php
require_once 'includes/session.php';

// Clear the session
clearUserSession();

// Redirect to home page
header('Location: index.php');
exit();
?> 