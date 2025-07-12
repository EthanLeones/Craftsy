<?php $page_title = 'About Us'; ?>
<?php include 'header.php'; ?>
<style>
    .about-content {
        display: flex;
        flex-wrap: wrap; 
        gap: 30px;
        align-items: center;
    }
    .about-text {
        flex: 1 1 400px;
        background: #f8d5e6;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(94, 84, 142, 0.08);
        padding: 32px 28px;
        margin: 12px 0;
    }

    .about-image {
        flex: 1 1 300px; 
        text-align: center;
    }

    .about-image img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .about-section:nth-of-type(even) .about-content {
        flex-direction: row-reverse;
    }

     .about-section h2 {
         color: #231942;
         text-align: center;
         margin-bottom: 20px;
     }

     .about-text h3 {
         color: #5e548e;
         margin-bottom: 10px;
         font-size: 1.2em;
         font-weight: bold;
     }

     .about-text p {
         color: #231942;
         line-height: 1.6;
         margin-bottom: 15px;
         font-size: 1.1em;
         font-weight: bold;
     }

    .page-title {
        font-size: 2.8rem;
        font-weight: bold;
        color: #5E548E;
        margin-bottom: 18px;
    }
    .about-subtitle {
        font-size: 1.5rem;
        color: #231942;
        margin-bottom: 40px;
        text-align: center;
        font-weight: 500;
    }

</style>

        <h1 class="page-title">About Craftsy Nook</h1>
        <p class="about-subtitle">Celebrating Filipino Craftsmanship Through Sustainable Fashion</p>

        <section class="about-section">
            <h2>Our Story</h2>
            <div class="about-content">
                <div class="about-text">
                    <p>Founded in 2023, Craftsy Nook began with a simple mission: to showcase the exceptional craftsmanship of Filipino artisans while promoting sustainable fashion through our handcrafted bayong bags.</p>
                    <p>Each bag in our collection tells a story of tradition, skill, and dedication, passed down through generations of Filipino craftspeople.</p>
                </div>
                <div class="about-image">
                    <img src="images/about-img.jpg" alt="Craftsy Nook Story Image">
                </div>
            </div>
        </section>

        <section class="about-section">
            <h2>Our Values</h2>
             <div class="about-content">
                  <div class="about-text">
                     <h3>Sustainability</h3>
                     <p>We use eco-friendly materials and sustainable practices in all our products.</p>
                     <h3>Craftsmanship</h3>
                     <p>Every bag is carefully handcrafted by skilled Filipino artisans.</p>
                      <h3>Community</h3>
                      <p>We support local communities by providing fair wages and sustainable livelihoods.</p>
                  </div>
                   <div class="about-image">
                       <img src="images/client-bag.jpg" alt="Craftsy Nook Values Image">
                   </div>
             </div>
        </section>

         <section class="about-section">
             <h2>Our Artisans</h2>
              <div class="about-content">
                   <div class="about-text">
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