<?php
require_once '../config/database.php';
require_once '../includes/session.php'; 


$inquiry_id = $_GET['id'] ?? null;

if ($inquiry_id === null || !is_numeric($inquiry_id)) {
    echo '<p style="color: red;">Invalid inquiry ID.</p>';
    exit();
}

$inquiry_id = (int)$inquiry_id;
$conn = getDBConnection();

$inquiry_details = null;
$inquiry_responses = [];
$error_message = null;

try {
    $stmt_inquiry = $conn->prepare("SELECT i.*, u.username, u.name as customer_name FROM inquiries i LEFT JOIN users u ON i.user_id = u.id WHERE i.id = ?");
    $stmt_inquiry->execute([$inquiry_id]);
    $inquiry_details = $stmt_inquiry->fetch(PDO::FETCH_ASSOC);

    if ($inquiry_details) {
        $stmt_responses = $conn->prepare("SELECT * FROM inquiry_responses WHERE inquiry_id = ? ORDER BY created_at ASC");
        $stmt_responses->execute([$inquiry_id]);
        $inquiry_responses = $stmt_responses->fetchAll(PDO::FETCH_ASSOC);

         if ($inquiry_details['status'] === 'new') {
              $stmt_mark_read = $conn->prepare("UPDATE inquiries SET status = 'read' WHERE id = ?");
              $stmt_mark_read->execute([$inquiry_id]);
         }

    } else {
        $error_message = "Inquiry not found.";
    }

} catch (PDOException $e) {
    error_log("Error fetching inquiry details for view: " . $e->getMessage());
    $error_message = "Database error fetching inquiry details.";
}

?>

<?php if (isset($error_message)): ?>
    <p style="color: red;"><?php echo $error_message; ?></p>
<?php elseif ($inquiry_details): ?>
    <div class="inquiry-details-content">
        <h4>Customer Information:</h4>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($inquiry_details['customer_name'] ?? 'N/A'); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($inquiry_details['email']); ?></p>
         <?php if ($inquiry_details['user_id']): ?>
              <p><strong>Username:</strong> <?php echo htmlspecialchars($inquiry_details['username']); ?></p>
         <?php endif; ?>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($inquiry_details['date_sent']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($inquiry_details['status']); ?></p>

        <h4>Message:</h4>
        <p><?php echo nl2br(htmlspecialchars($inquiry_details['message'])); ?></p>

        <h4>Previous Replies:</h4>
        <?php if (empty($inquiry_responses)): ?>
            <p>No replies yet.</p>
        <?php else: ?>
            <?php foreach ($inquiry_responses as $response): ?>
                <div class="inquiry-response">
                    <p><strong>Reply from <?php echo htmlspecialchars($response['responded_by'] ?? 'Admin'); ?> on <?php echo htmlspecialchars($response['created_at']); ?>:</strong></p>
                    <p><?php echo nl2br(htmlspecialchars($response['response_text'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <h4>Your Reply:</h4>
        <form id="reply-inquiry-form" action="process_reply_inquiry.php" method="post">
            <input type="hidden" name="inquiry_id" value="<?php echo htmlspecialchars($inquiry_details['id']); ?>">
            <div class="form-group">
                <textarea name="reply_text" rows="4" required></textarea>
            </div>
            <button type="submit" class="button primary">Send Reply</button>
        </form>

    </div>
<?php endif; ?> 