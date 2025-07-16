<?php $page_title = 'About Us'; ?>
<?php include 'header.php'; ?>
<style>
    .about-section {
        padding: 60px 20px;
        min-height: 60vh;
        display: flex;
        align-items: center;
        border: 0;
        margin: 0;
    }

    .about-content {
        display: flex;
        flex-direction: row;
        gap: 40px;
        align-items: center;
        max-width: 1400px;
        margin: 0 auto;
        width: 100%;
        padding: 0 20px;
    }

    .about-text {
        flex: 1;
        color: white;
    }

    .about-image {
        flex: 1;
        text-align: center;
    }

    .about-image img {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    }

    /* Story Section Styles */
    .story-section {
        background-color: #3f1a41;
    }

    .story-section h2 {
        color: white;
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 30px;
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .story-section p {
        color: white;
        line-height: 1.8;
        font-size: 1.1rem;
        margin-bottom: 20px;
    }

    /* Values Section Styles */
    .values-section {
        background-color: #f5f5f5;
    }

    .values-section h2 {
        color: #3f1a41;
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 30px;
        text-transform: uppercase;
        letter-spacing: 2px;
        text-align: right;
    }

    .values-text {
        text-align: right;
        color: #333;
    }

    .value-item {
        margin-bottom: 25px;
    }

    .value-item h3 {
        color: #3f1a41;
        font-size: 1.3rem;
        font-weight: bold;
        margin-bottom: 8px;
    }

    .value-item p {
        color: #666;
        line-height: 1.6;
        font-size: 1rem;
    }

    /* Artisans Section Styles */
    .artisans-section {
        background-color: #3f1a41;
    }

    .artisans-section h2 {
        color: white;
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 30px;
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .artisans-section p {
        color: white;
        line-height: 1.8;
        font-size: 1.1rem;
        margin-bottom: 20px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .about-content {
            flex-direction: column;
            gap: 30px;
            padding: 0 10px;
        }

        .about-section {
            padding: 40px 10px;
        }

        .story-section h2,
        .values-section h2,
        .artisans-section h2 {
            font-size: 2rem;
            text-align: center;
        }
    }
</style>

<section class="about-section story-section">
    <div class="about-content">
        <div class="about-text">
            <h2>OUR STORY</h2>
            <p>Founded in 2023, Craftsy Nook began with a simple mission: to showcase the exceptional craftsmanship of Filipino artisans while promoting sustainable fashion through our handcrafted bayong bags.</p>
            <p>Each bag in our collection tells a story of tradition, skill, and dedication, passed down through generations of Filipino craftspeople.</p>
        </div>
        <div class="about-image">
            <img src="images/about-img.jpg" alt="Craftsy Nook Story Image">
        </div>
    </div>
</section>

<section class="about-section values-section">
    <div class="about-content">
        <div class="about-image">
            <img src="images/client-bag.jpg" alt="Craftsy Nook Values Image">
        </div>
        <div class="about-text values-text">
            <h2>OUR VALUES</h2>
            <div class="value-item">
                <h3>Sustainability</h3>
                <p>We use eco-friendly materials and sustainable practices in all our products.</p>
            </div>
            <div class="value-item">
                <h3>Craftsmanship</h3>
                <p>Every bag is carefully handcrafted by skilled Filipino artisans.</p>
            </div>
            <div class="value-item">
                <h3>Community</h3>
                <p>We support local communities by providing fair wages and sustainable livelihoods.</p>
            </div>
        </div>
    </div>
</section>

<section class="about-section artisans-section">
    <div class="about-content">

        <div class="about-text">
            <h2>OUR ARTISANS</h2>
            <p>Behind every Craftsy Nook bag is a skilled artisan who has mastered the art of traditional handweaving and bag-making. Our artisans combine time-honored techniques with contemporary design to create pieces that are both beautiful and functional.</p>
            <p>We're proud to provide fair wages and sustainable employment to our team of artisans, empowering local Filipino cultural heritage while creating economic opportunities.</p>
        </div>
        <div class="about-image">
            <img src="images/insta-img.jpg" alt="Craftsy Nook Artisans Image">
        </div>
    </div>
</section>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?>

<?php

if (isset($_SESSION['alert'])) {
    $alert_type = $_SESSION['alert']['type'];
    $alert_message = $_SESSION['alert']['message'];
    echo "<script>alert('" . addslashes($alert_message) . "');</script>";
    unset($_SESSION['alert']);
}
?>