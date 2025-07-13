<?php
$page_title = 'Homepage';
include 'header.php';
require_once 'includes/session.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
<style>
         .handbag img {
            max-width: 400px;
        }/* Homepage - Ultra Minimalistic & Modern Design */
    .homepage-wrapper {
        width: 100%;
        margin: 0;
        padding: 0;
    }

    .container2 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 100px;
        padding: 100px 40px;
        max-width: 1600px;
        margin: 0 auto;
        align-items: center;
        justify-items: center;
    }

    .handbag {
        text-align: center;
        color: #fff;
        max-width: 520px;
        width: 100%;
    }

    .handbag img {
        width: 100%;
        max-width: 500px;
        height: auto;
        border-radius: 0;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
        transition: transform 0.4s ease;
    }

    .handbag img:hover {
        transform: translateY(-12px) scale(1.02);
    }

    .label {
        font-size: 1.8rem;
        font-weight: 400;
        margin-top: 35px;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 3px;
        color: #ffffff;
    }

    .subtext {
        font-size: 1rem;
        margin-top: 0;
        opacity: 0.8;
        font-weight: 300;
        letter-spacing: 1px;
        line-height: 1.4;
        color: #ffffff;
    }

    body {
        scroll-behavior: smooth;
        scroll-snap-type: y mandatory;
        overflow-y: scroll;
        margin: 0;
        padding: 0;
    }

    section {
        margin: 0;
        padding: 0;
        min-height: 100vh;
        max-width: 100%;
        scroll-snap-align: start;
        background-size: cover;
        background-position: center;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .section1 {
        height: 100vh;
        background-image: url('images/bg1.png');
        background-size: cover;
        background-position: center;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: flex-start;
    }

    .section2 {
        min-height: 100vh;
        background-color: #3f1a41;
        padding: 0;
    }

    .section3 {
        min-height: 70vh;
        background-color: #3f1a41;
        background-image: url('images/bg3.png');
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .handbag-text {
        max-width: 600px;
        padding: 40px;
        margin-left: 5%;
        background-color: rgba(63, 26, 65, 0.9);
        color: white;
        text-align: left;
        backdrop-filter: blur(10px);
    }

    .handbag-text h1 {
        font-size: 2.8rem;
        font-weight: 300;
        margin-bottom: 20px;
        letter-spacing: 2px;
        line-height: 1.2;
        text-transform: uppercase;
    }

    .handbag-text p {
        font-size: 1.1rem;
        line-height: 1.6;
        font-weight: 300;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .newsletter {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 60px 40px;
        color: #ffffff;
        max-width: 600px;
        width: 100%;
    }

    .newsletter h1 {
        font-size: 2.2rem;
        font-weight: 300;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 4px;
    }

    .newsletter p {
        font-size: 1rem;
        margin-bottom: 40px;
        font-weight: 300;
        letter-spacing: 1px;
        opacity: 0.9;
    }

    .newsletter-form {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 25px;
        width: 100%;
        max-width: 400px;
    }

    .newsletter-form input[type="email"] {
        width: 100%;
        padding: 20px 0 15px 0;
        border: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.5);
        background-color: transparent;
        font-size: 1rem;
        color: #ffffff;
        box-sizing: border-box;
        transition: all 0.4s ease;
        font-weight: 300;
        text-align: center;
    }

    .newsletter-form input[type="email"]:focus {
        outline: none;
        border-bottom-color: #ffffff;
        transform: translateY(-2px);
    }

    .newsletter-form input[type="email"]::placeholder {
        color: rgba(255, 255, 255, 0.7);
        font-weight: 300;
        text-align: center;
    }

    .newsletter-form button {
        background-color: #ffffff;
        color: #3f1a41;
        padding: 15px 40px;
        border: none;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.4s ease;
        font-weight: 400;
        text-transform: uppercase;
        letter-spacing: 2px;
        border-radius: 0;
    }

    .newsletter-form button:hover {
        background-color: rgba(255, 255, 255, 0.9);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(255, 255, 255, 0.3);
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .container2 {
            gap: 40px;
            padding: 60px 30px;
        }
        
        .handbag-text {
            margin-left: 3%;
            padding: 30px;
        }
        
        .handbag-text h1 {
            font-size: 2.4rem;
        }
    }

    @media (max-width: 900px) {
        .container2 {
            grid-template-columns: 1fr;
            gap: 60px;
            padding: 50px 20px;
        }

        .handbag {
            max-width: 550px;
        }

        .handbag img {
            max-width: 450px;
        }

        .section1 {
            align-items: flex-start;
            padding-top: 120px;
        }

        .handbag-text {
            margin-left: 0;
            margin: 0 20px;
            max-width: none;
            width: calc(100% - 40px);
        }

        .handbag-text h1 {
            font-size: 2rem;
            letter-spacing: 1px;
        }

        .handbag-text p {
            font-size: 1rem;
        }

        .newsletter {
            padding: 40px 20px;
        }

        .newsletter h1 {
            font-size: 1.8rem;
            letter-spacing: 3px;
        }
    }

    @media (max-width: 600px) {
        .container2 {
            padding: 40px 15px;
            gap: 40px;
        }

        .handbag {
            max-width: 100%;
        }

        .handbag img {
            max-width: 280px;
        }

        .label {
            font-size: 1.4rem;
            letter-spacing: 2px;
        }

        .subtext {
            font-size: 0.9rem;
        }

        .section1 {
            padding-top: 100px;
        }

        .handbag-text {
            margin: 0 15px;
            padding: 25px;
        }

        .handbag-text h1 {
            font-size: 1.6rem;
            letter-spacing: 1px;
        }

        .handbag-text p {
            font-size: 0.95rem;
        }

        .newsletter {
            padding: 30px 15px;
        }

        .newsletter h1 {
            font-size: 1.5rem;
            letter-spacing: 2px;
        }

        .newsletter p {
            font-size: 0.9rem;
        }

        .newsletter-form {
            max-width: 100%;
        }

        .newsletter-form button {
            padding: 12px 30px;
            font-size: 0.8rem;
        }
    }

    @media (max-width: 480px) {
        .section1 {
            padding-top: 80px;
        }

        .handbag-text h1 {
            font-size: 1.4rem;
        }

        .handbag-text p {
            font-size: 0.9rem;
        }

        .newsletter h1 {
            font-size: 1.3rem;
            letter-spacing: 1px;
        }

        .newsletter-form input[type="email"] {
            font-size: 0.9rem;
            padding: 15px 0 12px 0;
        }

        .newsletter-form button {
            padding: 10px 25px;
            font-size: 0.75rem;
        }
    }
</style>

<div class="homepage-wrapper">
    <div class="section section1">
        <div class="handbag-text">
            <h1>Handcrafted Bayong Bags</h1>
            <p>Discover our collection of beautifully crafted Filipino bags that blend tradition with modern style.</p>
        </div>
    </div>
    
    <div class="section section2">
        <div class="container2">
            <div class="handbag">
                <img src="images/handbag1.png" alt="Handcrafted handbag with sunflowers">
                <div class="label">Handcrafted</div>
                <div class="subtext">Carefully made by skilled artisans</div>
            </div>
            <div class="handbag">
                <img src="images/handbag2.png" alt="Sustainable handbag with purple flowers">
                <div class="label">Sustainable</div>
                <div class="subtext">Eco-friendly materials and practices</div>
            </div>
            <div class="handbag">
                <img src="images/handbag3.png" alt="Unique handbag with yellow flowers">
                <div class="label">Unique</div>
                <div class="subtext">One-of-a-kind designs for every style</div>
            </div>
        </div>
    </div>
    
    <div class="section section3">
        <div class="newsletter">
            <h1>Stay Updated</h1>
            <p>Subscribe to our newsletter for exclusive offers and updates</p>
            <form action="subscribe_newsletter.php" method="post" class="newsletter-form">
                <input type="email" name="email" placeholder="Enter your email address" required>
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </div>
</div>

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


    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }, 100);
    }
</script>