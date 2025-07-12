<?php
require_once 'includes/session.php';
require_once 'config/database.php';
$page_title = 'Contact Us';
include 'header.php';

$user_id = isLoggedIn() ? getCurrentUserId() : null;
$conn = getDBConnection();

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

$messages = [];
if ($thread) {
    $stmt = $conn->prepare("SELECT m.*, u.name as sender_name, u.role as sender_role FROM inquiry_messages m JOIN users u ON m.sender_id = u.id WHERE m.thread_id = ? ORDER BY m.created_at ASC");
    $stmt->execute([$thread['id']]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Display session alerts as toast notifications
if (isset($_SESSION['alert_message'])) {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            showToast("' . addslashes($_SESSION['alert_message']) . '", "' . ($_SESSION['alert_type'] ?? 'success') . '");
        });
    </script>';
    unset($_SESSION['alert_message']);
    unset($_SESSION['alert_type']);
}
?>

<div class="contact-page-wrapper">
    <h1 class="contact-page-title">Contact Admin</h1>
    
    <div class="contact-chat-container">
        <?php if ($user_id): ?>
            <div class="chat-history-wrapper">
                <div class="chat-history" id="chat-history">
                    <?php if (empty($messages)): ?>
                        <div class="chat-message system">
                            <div class="chat-text">No messages yet. Start the conversation below.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="chat-message <?php echo $msg['sender_role'] === 'admin' ? 'admin' : 'customer'; ?>">
                                <div class="chat-sender"><?php echo htmlspecialchars($msg['sender_name']); ?></div>
                                <div class="chat-message-content">
                                    <div class="chat-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                                    <div class="chat-timestamp"><?php echo date('M j, Y H:i', strtotime($msg['created_at'])); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="chat-form-wrapper">
                <form id="chat-form" class="chat-form" method="post" action="contact_process.php">
                    <input type="hidden" name="thread_id" value="<?php echo htmlspecialchars($thread['id']); ?>">
                    <div class="chat-form-textarea-wrapper">
                        <label class="chat-form-label">Your Message</label>
                        <textarea name="message" id="message" rows="3" required placeholder="Type your message here..."></textarea>
                    </div>
                    <button type="submit" class="chat-send-btn">Send Message</button>
                </form>
            </div>
        <?php else: ?>
            <p class="contact-login-prompt">
                Please <a href="login.php">log in</a> to contact the admin.
            </p>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('chat-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);
    
    // Disable button during submission
    const submitBtn = form.querySelector('.chat-send-btn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';
    
    fetch('contact_process.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Message sent successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Failed to send message.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Send Message';
        }
    })
    .catch(error => {
        showToast('Failed to send message. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Message';
    });
});

// Auto-scroll to bottom of chat history
function scrollToBottom() {
    const chatHistory = document.getElementById('chat-history');
    if (chatHistory) {
        chatHistory.scrollTop = chatHistory.scrollHeight;
    }
}

// Scroll to bottom on page load
document.addEventListener('DOMContentLoaded', scrollToBottom);
</script>

<?php include 'footer.php'; ?> 