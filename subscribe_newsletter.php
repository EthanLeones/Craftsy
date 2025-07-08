<?php
require_once 'config/database.php';
require_once 'includes/session.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid request method.'];
    header('Location: index.php');
    exit();
}

$email = $_POST['email'] ?? '';


if (empty($email)) {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Email address cannot be empty.'];
    header('Location: index.php');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
     $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid email format.'];
     header('Location: index.php');
     exit();
}

$conn = getDBConnection();

try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['alert'] = ['type' => 'info', 'message' => 'You are already subscribed to the newsletter.'];
        header('Location: index.php');
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
    $stmt->execute([$email]);

    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Thank you for subscribing to our newsletter!'];
    header('Location: index.php');
    exit();

} catch (PDOException $e) {
    error_log("Newsletter subscription error: " . $e->getMessage());
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'An unexpected error occurred during subscription.'];
    header('Location: error.php'); // Redirect to a generic error page
    exit();
}

?> 