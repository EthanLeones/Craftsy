<?php
$page_title = 'Register';
require_once 'includes/session.php';
include 'header.php';

if (isset($_SESSION['alert_message'])) {
    $message = $_SESSION['alert_message'];
    $type = $_SESSION['alert_type'] ?? 'success';

    echo '<script>
        console.log("Session message found:", "' . addslashes($message) . '", "' . $type . '");
        
        window.addEventListener("load", function() {
            console.log("Page loaded, calling showToast");
            if (typeof showToast === "function") {
                console.log("showToast function found, calling it now");
                showToast("' . addslashes($message) . '", "' . $type . '");
            } else {
                console.error("showToast function not found");
                // Fallback alert
                alert("' . addslashes($message) . '");
            }
        });
    </script>';
    unset($_SESSION['alert_message']);
    unset($_SESSION['alert_type']);
} else {
    echo '<script>console.log("No session alert message found");</script>';
}
?>

<div class="login-page-wrapper">
    <h1 class="login-page-title">Create Your Account</h1>

    <form action="register_process.php" method="post" class="modern-form">
        <div class="modern-form-group">
            <input type="text" id="name" name="name" class="modern-form-input" placeholder="Enter your full name" required>
        </div>

        <div class="modern-form-group">
            <input type="text" id="username" name="username" class="modern-form-input" placeholder="Choose a username" required>
        </div>

        <div class="modern-form-group">
            <input type="email" id="email" name="email" class="modern-form-input" placeholder="Enter your email" required>
        </div>

        <div class="modern-form-group">
            <input type="password" id="password" name="password" class="modern-form-input" placeholder="Enter your password" required>
        </div>

        <div class="modern-form-group">
            <input type="password" id="confirm_password" name="confirm_password" class="modern-form-input" placeholder="Confirm your password" required>
        </div>

        <div class="modern-form-group privacy-checkbox-group">
            <input type="checkbox" id="privacy_agreement" name="privacy_agreement" required>
            <label for="privacy_agreement">
                I agree to the <a href="#" id="privacy-policy-link">Data Privacy Policy</a> and Terms of Service
            </label>
        </div>

        <button type="submit" name="register" class="modern-form-btn">Create Account</button>

        <div class="modern-form-links">
            <p>Already have an account? <a href="login.php">Sign In</a></p>
        </div>
    </form>
</div>

<div id="privacy-modal" class="privacy-modal">
    <div class="privacy-modal-content">
        <div class="privacy-modal-header">
            <h2>Data Privacy Policy</h2>
            <span class="privacy-modal-close">&times;</span>
        </div>
        <div class="privacy-modal-body">
            <h3>Information We Collect</h3>
            <p>When you create an account with Craftsy, we collect the following information:</p>
            <ul>
                <li><strong>Personal Information:</strong> Full name, username, and email address</li>
                <li><strong>Account Security:</strong> Encrypted password for account protection</li>
                <li><strong>Address Information:</strong> Shipping and billing addresses when provided</li>
                <li><strong>Order Information:</strong> Purchase history and preferences</li>
                <li><strong>Communication Data:</strong> Messages sent through our contact forms</li>
            </ul>

            <h3>How We Use Your Information</h3>
            <p>We use your personal information to:</p>
            <ul>
                <li>Create and manage your account</li>
                <li>Process orders and deliver products</li>
                <li>Provide customer support</li>
                <li>Send important account notifications</li>
                <li>Improve our services and user experience</li>
                <li>Comply with legal obligations</li>
            </ul>

            <h3>Information Sharing</h3>
            <p>We do not sell, trade, or share your personal information with third parties except:</p>
            <ul>
                <li>With your explicit consent</li>
                <li>To trusted service providers who assist in our operations</li>
                <li>When required by law or legal process</li>
                <li>To protect our rights and safety</li>
            </ul>

            <h3>Data Security</h3>
            <p>We implement appropriate security measures to protect your personal information:</p>
            <ul>
                <li>Encrypted password storage</li>
                <li>Secure database connections</li>
                <li>Regular security audits</li>
                <li>Limited access to personal data</li>
            </ul>

            <h3>Your Rights</h3>
            <p>You have the right to:</p>
            <ul>
                <li>Access your personal data</li>
                <li>Correct inaccurate information</li>
                <li>Request data deletion</li>
                <li>Withdraw consent at any time</li>
                <li>Contact us about privacy concerns</li>
            </ul>

            <h3>Contact Information</h3>
            <p>For any privacy-related questions or requests, please contact us through our <a href="contact.php">contact page</a>.</p>

            <p><strong>Last updated:</strong> July 16, 2025</p>
        </div>
        <div class="privacy-modal-footer">
            <button class="privacy-accept-btn" onclick="acceptPrivacyPolicy()">I Understand and Agree</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('name').focus();
    });


    document.querySelector('.modern-form').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('.modern-form-btn');
        const name = this.querySelector('#name').value.trim();
        const username = this.querySelector('#username').value.trim();
        const email = this.querySelector('#email').value.trim();
        const password = this.querySelector('#password').value.trim();
        const confirmPassword = this.querySelector('#confirm_password').value.trim();
        const privacyAgreement = this.querySelector('#privacy_agreement').checked;


        if (!name || !username || !email || !password || !confirmPassword) {
            e.preventDefault();
            showToast('Please fill in all fields', 'error');
            return;
        }

        if (!privacyAgreement) {
            e.preventDefault();
            showToast('Please agree to the Data Privacy Policy to continue', 'error');
            return;
        }

        if (name.length < 2) {
            e.preventDefault();
            showToast('Name must be at least 2 characters long', 'error');
            return;
        }

        if (username.length < 3) {
            e.preventDefault();
            showToast('Username must be at least 3 characters long', 'error');
            return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            showToast('Please enter a valid email address', 'error');
            return;
        }


        if (password.length < 6) {
            e.preventDefault();
            showToast('Password must be at least 6 characters long', 'error');
            return;
        }


        if (password !== confirmPassword) {
            e.preventDefault();
            showToast('Passwords do not match', 'error');
            return;
        }


        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating Account...';
        submitBtn.style.opacity = '0.7';
    });

    document.getElementById('name').addEventListener('input', function() {
        this.style.borderColor = this.value.length >= 2 ? '#28a745' : '#dc3545';
    });

    document.getElementById('username').addEventListener('input', function() {
        this.style.borderColor = this.value.length >= 3 ? '#28a745' : '#dc3545';
    });

    document.getElementById('email').addEventListener('input', function() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        this.style.borderColor = emailRegex.test(this.value) ? '#28a745' : '#dc3545';
    });

    document.getElementById('password').addEventListener('input', function() {
        this.style.borderColor = this.value.length >= 6 ? '#28a745' : '#dc3545';

        const confirmPassword = document.getElementById('confirm_password');
        if (confirmPassword.value) {
            confirmPassword.style.borderColor = this.value === confirmPassword.value ? '#28a745' : '#dc3545';
        }
    });

    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        this.style.borderColor = this.value === password ? '#28a745' : '#dc3545';
    });

    document.getElementById('privacy-policy-link').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('privacy-modal').style.display = 'block';
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    });

    document.querySelector('.privacy-modal-close').addEventListener('click', function() {
        document.getElementById('privacy-modal').style.display = 'none';
        document.body.style.overflow = 'auto';
    });

    window.addEventListener('click', function(e) {
        const modal = document.getElementById('privacy-modal');
        if (e.target === modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });

    function acceptPrivacyPolicy() {
        document.getElementById('privacy_agreement').checked = true;
        document.getElementById('privacy-modal').style.display = 'none';
        document.body.style.overflow = 'auto';
        showToast('Privacy policy accepted successfully!', 'success');
    }

    if (!document.getElementById('toast-style')) {
        const style = document.createElement('style');
        style.id = 'toast-style';
        style.textContent = `
    .toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        left: auto;
        transform: none;
        min-width: 300px;
        max-width: 450px;
        padding: 14px 24px;
        border-radius: 6px;
        color: #fff;
        font-size: 1rem;
        z-index: 9999;
        opacity: 0;
        pointer-events: none;
        background: #333;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: opacity 0.3s, right 0.4s, top 0.3s;
        display: flex !important;
        align-items: center;
        justify-content: space-between;
        word-wrap: break-word;
    }
    .toast.show {
        opacity: 1;
        pointer-events: auto;
        right: 20px;
        top: auto;
    }
    .toast.success { background: #28a745; }
    .toast.error { background: #dc3545; }
    .toast.info { background: #007bff; }
    .toast.warning { background: #f39c12; }
    
    /* Privacy Policy Checkbox Styles */
    .privacy-checkbox-group {
        display: flex !important;
        align-items: flex-start !important;
        gap: 10px !important;
        margin: 20px 0 !important;
        padding: 15px !important;
        border: 1px solid #e0e0e0 !important;
        border-radius: 6px !important;
        background: #f9f9f9 !important;
    }
    
    .privacy-checkbox-group input[type="checkbox"] {
        margin-top: 3px !important;
        transform: scale(1.2) !important;
        accent-color: #3f1a41 !important;
    }
    
    .privacy-checkbox-group label {
        font-size: 0.9rem !important;
        color: #333 !important;
        line-height: 1.4 !important;
        margin: 0 !important;
    }
    
    .privacy-checkbox-group a {
        color: #3f1a41 !important;
        text-decoration: underline !important;
        font-weight: 500 !important;
    }
    
    .privacy-checkbox-group a:hover {
        color: #2d1230 !important;
    }
    
    /* Privacy Policy Modal Styles */
    .privacy-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.3s ease;
    }
    
    .privacy-modal-content {
        background-color: #fff;
        margin: 2% auto;
        padding: 0;
        border-radius: 8px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        animation: slideIn 0.3s ease;
    }
    
    .privacy-modal-header {
        background: #3f1a41;
        color: white;
        padding: 20px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .privacy-modal-header h2 {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 400;
    }
    
    .privacy-modal-close {
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        opacity: 0.8;
        transition: opacity 0.3s;
    }
    
    .privacy-modal-close:hover {
        opacity: 1;
    }
    
    .privacy-modal-body {
        padding: 30px;
        max-height: 60vh;
        overflow-y: auto;
        line-height: 1.6;
    }
    
    .privacy-modal-body h3 {
        color: #3f1a41;
        margin-top: 25px;
        margin-bottom: 10px;
        font-size: 1.1rem;
        font-weight: 500;
    }
    
    .privacy-modal-body ul {
        margin: 10px 0;
        padding-left: 20px;
    }
    
    .privacy-modal-body li {
        margin-bottom: 8px;
        color: #555;
    }
    
    .privacy-modal-body p {
        color: #666;
        margin: 15px 0;
    }
    
    .privacy-modal-body a {
        color: #3f1a41;
        text-decoration: underline;
    }
    
    .privacy-modal-footer {
        background: #f8f9fa;
        padding: 20px 30px;
        text-align: center;
        border-top: 1px solid #eee;
    }
    
    .privacy-accept-btn {
        background: #3f1a41 !important;
        color: white !important;
        padding: 12px 30px !important;
        border: none !important;
        border-radius: 6px !important;
        cursor: pointer !important;
        font-size: 1rem !important;
        font-weight: 500 !important;
        text-transform: uppercase !important;
        letter-spacing: 1px !important;
        transition: all 0.3s ease !important;
    }
    
    .privacy-accept-btn:hover {
        background: #2d1230 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(63, 26, 65, 0.3) !important;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideIn {
        from { transform: translateY(-50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    @media (max-width: 768px) {
        .privacy-modal-content {
            width: 95%;
            margin: 5% auto;
        }
        
        .privacy-modal-header,
        .privacy-modal-body,
        .privacy-modal-footer {
            padding: 20px;
        }
        
        .privacy-checkbox-group {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
        
        .privacy-checkbox-group input[type="checkbox"] {
            margin-bottom: 10px !important;
        }
    }
    
    .toast button {
        background: none !important;
        border: none !important;
        color: inherit !important;
        font-size: 18px !important;
        font-weight: bold !important;
        margin-left: 10px !important;
        cursor: pointer !important;
        padding: 0 5px !important;
        text-transform: none !important;
        letter-spacing: normal !important;
        transition: opacity 0.3s ease !important;
        flex-shrink: 0;
    }
    .toast button:hover {
        opacity: 0.7 !important;
        background: none !important;
        color: inherit !important;
        transform: none !important;
        box-shadow: none !important;
    }
    .toast:not(:first-of-type) {
        bottom: calc(20px + (80px * var(--toast-index, 0)));
    }
    @media (max-width: 600px) {
        .toast {
            left: 50%;
            right: auto;
            transform: translateX(-50%);
            min-width: 90vw;
            max-width: 98vw;
            font-size: 0.95rem;
            padding: 12px 10px;
        }
        .toast.show {
            right: auto;
            left: 50%;
            transform: translateX(-50%);
        }
    }
    `;
        document.head.appendChild(style);
    }
</script>

<?php include 'footer.php'; ?>