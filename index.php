<?php
$page_title = 'Homepage';
include 'header.php';
require_once 'includes/session.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
<style>
    /* Style for the feature item icons */
    .feature-item img {
        max-width: 80px; /* Adjust size as needed */
        height: auto;
        display: block;
        margin: 0 auto 15px auto; /* Center image and add space below */
    }

    .features {
        display: flex;
        justify-content: space-around; /* Distribute space around items */
        flex-wrap: wrap; /* Allow items to wrap on smaller screens */
        gap: 20px; /* Add space between items */
    }

    .feature-item {
        flex: 1 1 250px; /* Allow items to grow/shrink but maintain a base width */
        text-align: center;
        padding: 20px;
        border: 1px solid #e0b1cb; /* Light pink border */
        border-radius: 8px;
        background-color: #fff; /* White background */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

     .feature-item h3 {
         color: #5e548e; /* Medium purple for titles */
         margin-bottom: 10px;
     }

     .feature-item p {
         color: #231942; /* Dark text color */
         font-size: 0.9em;
     }

    .hero-section {
        text-align: center;
        padding: 90px 20px 70px 20px;
        background-color: #f8d5e6;
        color: #231942;
        margin-bottom: 40px;
        border-radius: 0 0 32px 32px;
        box-shadow: 0 8px 32px rgba(224, 177, 203, 0.18);
        position: relative;
    }
    .hero-section img {
        max-width: 220px;
        width: 100%;
        margin: 0 auto 32px auto;
        display: block;
        filter: drop-shadow(0 4px 16px #e0b1cb88);
    }
    .hero-section h1 {
        font-family: 'Playfair Display', serif;
        font-size: 3.2rem;
        font-weight: 700;
        margin-bottom: 18px;
        color: #5E548E;
        letter-spacing: 1.5px;
        text-shadow: 0 2px 8px #e0b1cb33;
    }
    .hero-section p {
        font-family: 'Montserrat', sans-serif;
        font-size: 1.5rem;
        font-weight: 500;
        color: #231942;
        margin-bottom: 32px;
        letter-spacing: 0.5px;
    }
    .hero-buttons a {
        font-family: 'Montserrat', sans-serif;
        font-size: 1.15rem;
        font-weight: 700;
        padding: 14px 32px;
        margin: 0 12px;
        background-color: #5E548E;
        color: #fff;
        border-radius: 6px;
        text-decoration: none;
        box-shadow: 0 2px 8px #e0b1cb44;
        transition: background 0.3s, color 0.3s, box-shadow 0.3s;
    }
    .hero-buttons a:hover {
        background-color: #e0b1cb;
        color: #231942;
        box-shadow: 0 4px 16px #e0b1cb88;
    }
</style>

    <section class="hero-section">
        <img src="images/logo.jpg" alt="Craftsy Nook Logo">
        <h1>Handcrafted Bayong Bags</h1>
        <p>Discover our collection of beautifully crafted Filipino bags that blend tradition with modern style.</p>
        <div class="hero-buttons">
            <a href="shop.php" class="shop-now">Shop Now</a>
            <a href="about.php" class="learn-more">Learn More</a>
        </div>
    </section>

    <div class="container">
        <div class="featured-products-section" style="text-align: center; margin-bottom: 40px;">
            <h2 class="section-title">Featured Products</h2>
            <!-- Placeholder for featured products -->
            <img src="images/offer-img.png" alt="Special Offer" style="max-width: 100%; height: auto; margin-top: 20px; display: block; margin-left: auto; margin-right: auto;">
        </div>
    </div>

     <div class="container">
         <div class="features">
             <div class="feature-item">
                 <img src="images/hand-icon.png" alt="Handcrafted Icon">
                 <h3>Handcrafted</h3>
                 <p>Each piece is carefully made by skilled artisans</p>
             </div>
              <div class="feature-item">
                 <img src="images/sus-icon.png" alt="Sustainable Icon">
                 <h3>Sustainable</h3>
                 <p>Eco-friendly materials and practices</p>
             </div>
              <div class="feature-item">
                 <img src="images/unique-icon.png" alt="Unique Icon">
                 <h3>Unique</h3>
                 <p>One-of-a-kind designs for every style</p>
             </div>
         </div>
     </div>

    <section class="stay-updated">
        <h2>Stay Updated</h2>
        <p>Subscribe to our newsletter for exclusive offers and updates</p>

        <form action="subscribe_newsletter.php" method="post">
            <input type="email" name="email" placeholder="Enter your email address" required>
            <button type="submit">Subscribe</button>
        </form>
    </section>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?>

<script>
<?php
if (isset($_SESSION['alert'])) {
    $alert_type = $_SESSION['alert']['type'];
    $alert_message = $_SESSION['alert']['message'];
    echo "showToast('" . addslashes($alert_message) . "', '" . ($alert_type === 'success' ? 'success' : 'error') . "');";
    unset($_SESSION['alert']);
}
?>
</script> 