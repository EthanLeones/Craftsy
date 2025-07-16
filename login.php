<?php
$page_title = 'Login';
require_once 'includes/session.php';
include 'header.php';

// Display session alerts as toast notifications
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
    <h1 class="login-page-title">Welcome Back</h1>


    <form action="login_process.php" method="post" class="modern-form">
        <div class="modern-form-group">
            <input type="text" id="username" name="username" class="modern-form-input" placeholder="Enter your username" required>
        </div>

        <div class="modern-form-group">
            <input type="password" id="password" name="password" class="modern-form-input" placeholder="Enter your password" required>
        </div>

        <div class="modern-form-group privacy-checkbox-group">
            <input type="checkbox" id="privacy_agreement" name="privacy_agreement" required>
            <label for="privacy_agreement">
                I agree to the <a href="#" id="privacy-policy-link">Data Privacy Policy</a> and Terms of Service
            </label>
        </div>

        <button type="submit" name="login" class="modern-form-btn">Sign In</button>

        <div class="modern-form-links">
            <p><a href="forgot_password.php">Forgot Password?</a></p>
            <p>Don't have an account? <a href="register.php">Create Account</a></p>
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
            <p>When you log in to Craftsy, we collect the following information:</p>
            <ul>
                <li><strong>Login Credentials:</strong> Username and encrypted password for authentication</li>
                <li><strong>Session Data:</strong> Temporary information to maintain your login session</li>
                <li><strong>Activity Information:</strong> Pages visited and actions performed during your session</li>
                <li><strong>Device Information:</strong> Browser type, IP address, and access timestamps</li>
            </ul>

            <h3>How We Use Your Information</h3>
            <p>We use your login information to:</p>
            <ul>
                <li>Authenticate your identity and provide secure access</li>
                <li>Maintain your login session</li>
                <li>Personalize your shopping experience</li>
                <li>Ensure account security and prevent unauthorized access</li>
                <li>Track your orders and preferences</li>
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
                <li>Secure session management</li>
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
    // Auto-focus on first input
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('username').focus();
    });

    // Form validation and enhanced UX
    document.querySelector('.modern-form').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('.modern-form-btn');
        const username = this.querySelector('#username').value.trim();
        const password = this.querySelector('#password').value.trim();
        const privacyAgreement = this.querySelector('#privacy_agreement').checked;

        // Only show toast for client-side validation errors
        if (!username || !password) {
            e.preventDefault();
            showToast('Please fill in all fields', 'error');
            return;
        }

        if (!privacyAgreement) {
            e.preventDefault();
            showToast('Please agree to the Data Privacy Policy to continue', 'error');
            return;
        }

        if (username.length < 3) {
            e.preventDefault();
            showToast('Username must be at least 3 characters long', 'error');
            return;
        }

        if (password.length < 6) {
            e.preventDefault();
            showToast('Password must be at least 6 characters long', 'error');
            return;
        }

        // Add loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Signing In...';
        submitBtn.style.opacity = '0.7';
    });

    // Add input event listeners for real-time feedback
    document.getElementById('username').addEventListener('input', function() {
        this.style.borderColor = this.value.length >= 3 ? '#28a745' : '#dc3545';
    });

    document.getElementById('password').addEventListener('input', function() {
        this.style.borderColor = this.value.length >= 6 ? '#28a745' : '#dc3545';
    });

    // Privacy policy modal functionality
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

    document.querySelector('.toast')

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
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }
        
        .privacy-modal-header {
            padding: 20px 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #3f1a41;
            color: white;
            border-radius: 8px 8px 0 0;
        }
        
        .privacy-modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 500;
        }
        
        .privacy-modal-close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.3s ease;
        }
        
        .privacy-modal-close:hover {
            opacity: 0.7;
        }
        
        .privacy-modal-body {
            padding: 30px;
            line-height: 1.6;
            color: #333;
        }
        
        .privacy-modal-body h3 {
            color: #3f1a41;
            margin-top: 25px;
            margin-bottom: 15px;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .privacy-modal-body h3:first-child {
            margin-top: 0;
        }
        
        .privacy-modal-body ul {
            margin: 15px 0;
            padding-left: 25px;
        }
        
        .privacy-modal-body li {
            margin-bottom: 8px;
        }
        
        .privacy-modal-body a {
            color: #3f1a41;
            text-decoration: underline;
        }
        
        .privacy-modal-body a:hover {
            color: #2d1230;
        }
        
        .privacy-modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            background: #f9f9f9;
            border-radius: 0 0 8px 8px;
        }
        
        .privacy-accept-btn {
            background: #3f1a41;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .privacy-accept-btn:hover {
            background: #2d1230;
            transform: translateY(-1px);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @media (max-width: 768px) {
            .privacy-modal-content {
                width: 95%;
                margin: 5% auto;
                max-height: 85vh;
            }
            
            .privacy-modal-header,
            .privacy-modal-body,
            .privacy-modal-footer {
                padding: 20px;
            }
            
            .privacy-checkbox-group {
                flex-direction: column !important;
                gap: 8px !important;
            }
            
            .privacy-checkbox-group input[type="checkbox"] {
                margin-top: 0 !important;
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