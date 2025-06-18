<?php
require_once 'includes/session.php';
require_once 'config/database.php';
$page_title = 'Contact Us';
include 'header.php';

$user_id = isLoggedIn() ? getCurrentUserId() : null;
$conn = getDBConnection();

// Fetch or create the user's thread
$thread = null;
if ($user_id) {
    $stmt = $conn->prepare("SELECT * FROM inquiry_threads WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $thread = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$thread) {
        // Create a new thread if none exists
        $stmt_new = $conn->prepare("INSERT INTO inquiry_threads (user_id, subject) VALUES (?, ?)");
        $stmt_new->execute([$user_id, 'General Inquiry']);
        $thread_id = $conn->lastInsertId();
        $stmt = $conn->prepare("SELECT * FROM inquiry_threads WHERE id = ?");
        $stmt->execute([$thread_id]);
        $thread = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Fetch messages for the thread
$messages = [];
if ($thread) {
    $stmt = $conn->prepare("SELECT m.*, u.name as sender_name, u.role as sender_role FROM inquiry_messages m JOIN users u ON m.sender_id = u.id WHERE m.thread_id = ? ORDER BY m.created_at ASC");
    $stmt->execute([$thread['id']]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<h1 class="page-title">Contact Admin (Chat)</h1>
<div class="contact-chat-container">
    <?php if ($user_id): ?>
        <div class="chat-history" id="chat-history">
            <?php if (empty($messages)): ?>
                <div class="chat-message system">No messages yet. Start the conversation below.</div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="chat-message <?php echo $msg['sender_role'] === 'admin' ? 'admin' : 'customer'; ?>">
                        <span class="sender"><?php echo htmlspecialchars($msg['sender_name']); ?>:</span>
                        <span class="text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></span>
                        <span class="timestamp"><?php echo date('Y-m-d H:i', strtotime($msg['created_at'])); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <form id="chat-form" class="chat-form" method="post" action="contact_process.php">
            <input type="hidden" name="thread_id" value="<?php echo htmlspecialchars($thread['id']); ?>">
            <textarea name="message" id="message" rows="2" required placeholder="Type your message..."></textarea>
            <button type="submit">Send</button>
        </form>
    <?php else: ?>
        <p>Please <a href="login.php">log in</a> to contact the admin.</p>
    <?php endif; ?>
</div>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?>

<style>
.contact-chat-container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 24px; }
.chat-history { max-height: 350px; overflow-y: auto; margin-bottom: 16px; background: #f8f8f8; border-radius: 6px; padding: 12px; }
.chat-message { margin-bottom: 10px; padding: 8px 12px; border-radius: 6px; display: flex; flex-direction: column; }
.chat-message.customer { background: #e0b1cb; align-items: flex-end; }
.chat-message.admin { background: #9f86c0; color: #fff; align-items: flex-start; }
.chat-message.system { text-align: center; color: #888; background: none; }
.sender { font-weight: bold; margin-bottom: 2px; }
.text { margin-bottom: 2px; }
.timestamp { font-size: 0.8em; color: #555; align-self: flex-end; }
.chat-form { display: flex; gap: 8px; }
.chat-form textarea { flex: 1; resize: none; border-radius: 4px; border: 1px solid #ccc; padding: 8px; }
.chat-form button { padding: 8px 18px; border-radius: 4px; background: #231942; color: #fff; border: none; }
</style>

<script>
document.getElementById('chat-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);
    fetch('contact_process.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Reload chat on success
            window.location.reload();
        } else {
            alert(data.message || 'Failed to send message.');
        }
    })
    .catch(() => alert('Failed to send message.'));
});
</script>

<?php
// Dummy function to get user email - replace with actual DB call if needed
function getUserEmail($user_id) {
    // In a real application, you would fetch the email from the users table
    // using the user_id. For now, return a placeholder or empty string.
    return ''; // Placeholder
}
?> 