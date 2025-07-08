<?php
require_once 'includes/session.php';

clearUserSession();

header('Location: index.php');
exit();
?> 