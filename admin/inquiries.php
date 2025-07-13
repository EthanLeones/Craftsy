<?php
$page_title = 'Customer Inquiries';
include 'includes/admin_header.php';
require_once '../config/database.php'; 
$conn = getDBConnection();
$stmt_threads = $conn->prepare("SELECT t.*, u.name as customer_name, u.username FROM inquiry_threads t JOIN users u ON t.user_id = u.id ORDER BY t.updated_at DESC, t.created_at DESC");
$stmt_threads->execute();
$threads = $stmt_threads->fetchAll(PDO::FETCH_ASSOC);
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
    $stmt = $conn->prepare("SELECT m.*, u.name as sender_name, u.role as sender_role FROM inquiry_messages m JOIN users u ON m.sender_id = u.id WHERE m.thread_id = ? ORDER BY m.created_at ASC");
    $stmt->execute([$selected_thread_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="admin-wrapper">
    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
    </button>
    <?php include 'includes/admin_sidebar.php'; ?>
    <div class="admin-page-content">
        <h1 class="page-title"><i class="fas fa-comments"></i> Customer Inquiries</h1>
        <div class="admin-section">
            <div class="inquiries-container">
                <div class="thread-sidebar">
                    <div class="inquiry-sidebar-header">
                        <h3><i class="fas fa-list"></i> Conversations</h3>
                    </div>
                    <div class="thread-list">
                        <?php foreach ($threads as $thread): ?>
                            <div class="thread-item <?php echo $thread['id'] == $selected_thread_id ? 'active' : ''; ?>">
                                <a href="?thread_id=<?php echo $thread['id']; ?>" class="thread-link">
                                    <div class="thread-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="thread-info">
                                        <div class="thread-name"><?php echo htmlspecialchars($thread['customer_name'] ?? $thread['username']); ?></div>
                                        <div class="thread-subject"><?php echo htmlspecialchars($thread['subject']); ?></div>
                                        <div class="thread-date"><?php echo date('M j', strtotime($thread['updated_at'])); ?></div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="chat-main">
                    <?php if ($selected_thread): ?>
                        <div class="chat-header">
                            <div class="chat-title">
                                <i class="fas fa-user-circle"></i>
                                <span><?php echo htmlspecialchars($selected_thread['customer_name'] ?? $selected_thread['username']); ?></span>
                            </div>
                            <div class="chat-subject"><?php echo htmlspecialchars($selected_thread['subject']); ?></div>
                        </div>
                        <div class="chat-messages" id="chat-history">
                            <?php if (empty($messages)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-comments"></i>
                                    <p>No messages yet. Start the conversation!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($messages as $msg): ?>
                                    <div class="message <?php echo $msg['sender_role'] === 'admin' ? 'admin-message' : 'customer-message'; ?>">
                                        <div class="message-avatar">
                                            <i class="fas fa-<?php echo $msg['sender_role'] === 'admin' ? 'user-shield' : 'user'; ?>"></i>
                                        </div>
                                        <div class="message-content">
                                            <div class="message-header">
                                                <span class="sender-name"><?php echo htmlspecialchars($msg['sender_name']); ?></span>
                                                <span class="message-time"><?php echo date('M j, Y H:i', strtotime($msg['created_at'])); ?></span>
                                            </div>
                                            <div class="message-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="chat-input">
                            <form id="admin-chat-form" class="chat-form" method="post" action="process_admin_reply.php">
                                <input type="hidden" name="thread_id" value="<?php echo htmlspecialchars($selected_thread['id']); ?>">
                                <div class="input-group">
                                    <textarea name="message" id="admin-message" rows="3" required placeholder="Type your reply..." class="form-textarea"></textarea>
                                    <button type="submit" class="button send-button">
                                        <i class="fas fa-paper-plane"></i> Send Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-comments"></i>
                            <h3>No conversation selected</h3>
                            <p>Select a conversation from the sidebar to view messages.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
/* ======================================
   INQUIRIES PAGE - ADMIN DESIGN SYSTEM
   ====================================== */

.inquiries-container {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 30px;
    height: calc(100vh - 200px);
}

.thread-sidebar {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(63, 26, 65, 0.08);
    border: 1px solid #f0f0f0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.inquiry-sidebar-header {
    background: #3f1a41;
    color: #ffffff;
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.inquiry-sidebar-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.inquiry-sidebar-header h3 i {
    opacity: 0.8;
}

.thread-list {
    flex: 1;
    overflow-y: auto;
    max-height: calc(100vh - 280px);
}

.thread-item {
    border-bottom: 1px solid #f8f9fa;
    transition: all 0.3s ease;
}

.thread-item:hover {
    background: rgba(63, 26, 65, 0.02);
    transform: translateX(2px);
}

.thread-item.active {
    background: rgba(63, 26, 65, 0.05);
    border-left: 4px solid #3f1a41;
}

.thread-link {
    display: flex;
    align-items: center;
    padding: 18px 20px;
    text-decoration: none;
    color: #3f1a41;
    gap: 12px;
    transition: all 0.3s ease;
}

.thread-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666666;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.thread-item.active .thread-avatar {
    background: #3f1a41;
    color: #ffffff;
}

.thread-info {
    flex: 1;
    min-width: 0;
}

.thread-name {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 4px;
    color: #3f1a41;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.thread-subject {
    font-size: 0.8rem;
    color: #666666;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-weight: 400;
}

.thread-date {
    font-size: 0.75rem;
    color: #999999;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.chat-main {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(63, 26, 65, 0.08);
    border: 1px solid #f0f0f0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chat-header {
    background: #3f1a41;
    color: #ffffff;
    padding: 20px 25px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.chat-title {
    font-size: 1.2rem;
    font-weight: 500;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.chat-title i {
    opacity: 0.8;
}

.chat-subject {
    font-size: 0.9rem;
    opacity: 0.85;
    font-weight: 400;
}

.chat-messages {
    flex: 1;
    padding: 25px;
    overflow-y: auto;
    max-height: calc(100vh - 400px);
    background: #fafafa;
}

.message {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    animation: fadeInUp 0.3s ease;
}

.message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.admin-message .message-avatar {
    background: #3f1a41;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(63, 26, 65, 0.2);
}

.customer-message .message-avatar {
    background: #f8f9fa;
    color: #666666;
    border: 2px solid #e9ecef;
}

.message-content {
    flex: 1;
    min-width: 0;
    background: #ffffff;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border: 1px solid #f0f0f0;
}

.admin-message .message-content {
    border-left: 3px solid #3f1a41;
}

.customer-message .message-content {
    border-left: 3px solid #e9ecef;
}

.message-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f8f9fa;
}

.sender-name {
    font-weight: 600;
    font-size: 0.85rem;
    color: #3f1a41;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.message-time {
    font-size: 0.75rem;
    color: #999999;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.message-text {
    color: #555555;
    line-height: 1.6;
    font-size: 0.9rem;
    font-weight: 400;
}

.chat-input {
    border-top: 1px solid #f0f0f0;
    padding: 25px;
    background: #ffffff;
}

.input-group {
    display: flex;
    gap: 15px;
    align-items: flex-end;
}

.form-textarea {
    flex: 1;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 15px;
    font-size: 0.9rem;
    line-height: 1.5;
    resize: vertical;
    min-height: 60px;
    transition: all 0.3s ease;
    font-family: inherit;
    background: #fafafa;
}

.form-textarea:focus {
    outline: none;
    border-color: #3f1a41;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(63, 26, 65, 0.1);
}

.send-button {
    flex-shrink: 0;
    height: fit-content;
    min-width: 140px;
    white-space: nowrap;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #666666;
    text-align: center;
    padding: 40px;
}

.empty-state i {
    font-size: 3.5rem;
    margin-bottom: 20px;
    opacity: 0.3;
    color: #3f1a41;
}

.empty-state h3 {
    margin: 10px 0;
    color: #3f1a41;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.empty-state p {
    margin: 0;
    opacity: 0.8;
    font-size: 0.9rem;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .inquiries-container {
        grid-template-columns: 1fr;
        height: auto;
        gap: 20px;
    }
    
    .thread-sidebar {
        max-height: 300px;
        order: 2;
    }
    
    .chat-main {
        min-height: 500px;
        order: 1;
    }
    
    .chat-messages {
        max-height: 400px;
    }
    
    .input-group {
        flex-direction: column;
        gap: 10px;
    }
    
    .send-button {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .thread-link {
        padding: 15px;
    }
    
    .chat-header,
    .chat-input {
        padding: 20px;
    }
    
    .chat-messages {
        padding: 20px;
    }
    
    .message {
        gap: 10px;
    }
    
    .message-avatar {
        width: 32px;
        height: 32px;
        font-size: 0.8rem;
    }
    
    .message-content {
        padding: 12px;
    }
}
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
