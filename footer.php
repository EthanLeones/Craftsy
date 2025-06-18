    <footer class="footer">
        <div class="footer-col">
            <h4>About Us</h4>
            <p>Craftsy Nook is your premier online destination for handcrafted artisanal products. We connect talented artisans with craft enthusiasts worldwide.</p>
            <!-- Social media icons placeholder -->
        </div>
         <div class="footer-col">
            <h4>Quick Links</h4>
            <a href="shop.php">Shop</a>
            <a href="about.php">About Us</a>
            <a href="contact.php">Contact</a>
            <a href="#">Shipping Info</a>
        </div>
         <div class="footer-col">
            <h4>Customer Service</h4>
            <a href="faq.php">FAQ</a>
            <a href="returns_exchanges.php">Returns & Exchanges</a>
            <a href="privacy_policy.php">Privacy Policy</a>
            <a href="terms_of_service.php">Terms of Service</a>
        </div>
         <div class="footer-col">
            <h4>Contact Info</h4>
            <p>Email: support@craftsynook.com</p>
            <p>Phone: (555) 123-4567</p>
            <p>Hours: Mon-Fri 9am-5pm EST</p>
        </div>
    </footer>
     <div class="footer-bottom">
         <p>&copy; 2023 Craftsy Nook. All rights reserved.</p>
     </div>

<!-- Toast Notification Container -->
<div id="toast-container" style="position: fixed; top: 24px; right: 24px; z-index: 9999;"></div>
<script>
function showToast(message, type = 'success', duration = 3000) {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.textContent = message;
    toast.style.cssText = `
        min-width: 200px;
        margin-bottom: 12px;
        padding: 14px 24px;
        border-radius: 6px;
        color: #fff;
        font-weight: 500;
        font-size: 1rem;
        box-shadow: 0 2px 8px #0002;
        background: ${type === 'success' ? '#5E548E' : '#C0392B'};
        opacity: 0.95;
        transition: opacity 0.3s;
    `;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}
</script>
<style>
.toast { pointer-events: auto; margin-top: 0; }
.toast.success { background: #5E548E; }
.toast.error { background: #C0392B; }
</style>

</body>
</html> 