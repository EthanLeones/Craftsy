<?php
$page_title = 'Customer Inquiries';
include 'includes/admin_header.php';
// include 'includes/admin_sidebar.php'; // Sidebar will be included within admin-wrapper

// Admin authentication check should be placed here or in admin_header.php
// require_once '../includes/session.php';
// requireAdminLogin();

require_once '../config/database.php'; // Include database connection

$conn = getDBConnection();

// Fetch all threads with user info
$stmt_threads = $conn->prepare("SELECT t.*, u.name as customer_name, u.username FROM inquiry_threads t JOIN users u ON t.user_id = u.id ORDER BY t.updated_at DESC, t.created_at DESC");
$stmt_threads->execute();
$threads = $stmt_threads->fetchAll(PDO::FETCH_ASSOC);

// Determine selected thread
$selected_thread_id = isset($_GET['thread_id']) ? intval($_GET['thread_id']) : ($threads[0]['id'] ?? null);
$selected_thread = null;
$messages = [];
if ($selected_thread_id) {
    foreach ($threads as $t) {
        if ($t['id'] == $selected_thread_id) {
            $selected_thread = $t;
            break;
        }
    }
    // Fetch messages for the selected thread
    $stmt = $conn->prepare("SELECT m.*, u.name as sender_name, u.role as sender_role FROM inquiry_messages m JOIN users u ON m.sender_id = u.id WHERE m.thread_id = ? ORDER BY m.created_at ASC");
    $stmt->execute([$selected_thread_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<div class="admin-wrapper">
    <?php include 'includes/admin_sidebar.php'; ?>

    <div class="admin-page-content">
        <h1 class="page-title">Customer Inquiries (Chat)</h1>
        <div class="admin-inquiry-container" style="display: flex; gap: 32px;">
            <div class="thread-list" style="min-width: 220px;">
                <h3>Threads</h3>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($threads as $thread): ?>
                        <li style="margin-bottom: 10px;">
                            <a href="?thread_id=<?php echo $thread['id']; ?>" class="thread-link<?php echo $thread['id'] == $selected_thread_id ? ' active' : ''; ?>">
                                <?php echo htmlspecialchars($thread['customer_name'] ?? $thread['username']); ?>
                                <br><small><?php echo htmlspecialchars($thread['subject']); ?></small>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="chat-area" style="flex: 1;">
                <?php if ($selected_thread): ?>
                    <h3>Chat with <?php echo htmlspecialchars($selected_thread['customer_name'] ?? $selected_thread['username']); ?></h3>
                    <div class="chat-history" id="chat-history" style="max-height: 350px; overflow-y: auto; background: #f8f8f8; border-radius: 6px; padding: 12px; margin-bottom: 16px;">
                        <?php if (empty($messages)): ?>
                            <div class="chat-message system">No messages yet.</div>
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
                    <form id="admin-chat-form" class="chat-form" method="post" action="process_admin_reply.php">
                        <input type="hidden" name="thread_id" value="<?php echo htmlspecialchars($selected_thread['id']); ?>">
                        <textarea name="message" id="admin-message" rows="2" required placeholder="Type your reply..."></textarea>
                        <button type="submit">Send</button>
                    </form>
                <?php else: ?>
                    <p>No thread selected.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.thread-link { display: block; padding: 8px 12px; border-radius: 4px; background: #eee; color: #231942; text-decoration: none; }
.thread-link.active { background: #9f86c0; color: #fff; font-weight: bold; }
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
document.getElementById('admin-chat-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);
    fetch('process_admin_reply.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Failed to send message.');
        }
    })
    .catch(() => alert('Failed to send message.'));
});
</script>